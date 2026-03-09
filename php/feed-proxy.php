<?php
/**
 * Proxy for Heimat-Info feed.
 * Returns JSON with the latest posts including full content, images, and links.
 * 
 * Usage: php/feed-proxy.php?count=2
 * Optional: &nocache=1 to bypass cache
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// --- Configuration ---
$feedUrl = 'https://www.heimat-info.de/embeddings/posts/v1/?pt=Default&pt=Event&pt=Rss&ct=f8618426-5859-4654-a3a4-87487b8954d3';
$cacheFile = __DIR__ . '/feed-cache.json';
$cacheTTL = 900; // 15 minutes in seconds
$defaultCount = 1;
$maxCount = 10;

// --- Parameters ---
$count = isset($_GET['count']) ? min(max((int)$_GET['count'], 1), $maxCount) : $defaultCount;
$noCache = isset($_GET['nocache']) && $_GET['nocache'] == '1';

// --- Cache check ---
if (!$noCache && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    if ($cached !== null) {
        $cached['posts'] = array_slice($cached['posts'], 0, $count);
        $cached['cached'] = true;
        echo json_encode($cached, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// --- Fetch feed ---
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'MV-Heustreu-Website/1.0'
    ]
]);

$html = @file_get_contents($feedUrl, false, $context);
if ($html === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Feed konnte nicht geladen werden.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Extract __NUXT_DATA__ JSON ---
$posts = [];

if (preg_match('/<script[^>]*id="__NUXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
    $nuxtRaw = $matches[1];
    $nuxtData = json_decode($nuxtRaw, true);

    if (is_array($nuxtData)) {
        // The Nuxt data is a flat array with references by index.
        // We need to find post objects by looking for the pattern:
        // { attachments: [...], id: "uuid", organizationId: "...", title: "...", content: "...", ... }
        //
        // Strategy: find all indices that contain "title" key and look like post objects
        
        $allPosts = [];
        
        for ($i = 0; $i < count($nuxtData); $i++) {
            if (is_array($nuxtData[$i]) && !is_int(array_key_first($nuxtData[$i]))) {
                $obj = $nuxtData[$i];
                // Check if this looks like a post object
                if (isset($obj['title']) && isset($obj['content']) && isset($obj['id']) && isset($obj['attachments'])) {
                    $post = [];
                    
                    // Resolve title
                    $post['title'] = isset($nuxtData[$obj['title']]) ? $nuxtData[$obj['title']] : '';
                    
                    // Resolve content (full HTML)
                    $post['content'] = isset($nuxtData[$obj['content']]) ? $nuxtData[$obj['content']] : '';
                    $post['content'] = preg_replace('/<p>\s*(&nbsp;|\xC2\xA0)?\s*<\/p>/i', '', $post['content']);
                    
                    // Resolve content preview
                    if (isset($obj['contentPreview'])) {
                        $post['contentPreview'] = isset($nuxtData[$obj['contentPreview']]) ? $nuxtData[$obj['contentPreview']] : '';
                    }
                    
                    // Resolve id
                    $post['id'] = isset($nuxtData[$obj['id']]) ? $nuxtData[$obj['id']] : '';
                    
                    // Resolve date
                    $post['createdOn'] = isset($obj['createdOn']) && isset($nuxtData[$obj['createdOn']]) 
                        ? $nuxtData[$obj['createdOn']] : '';
                    
                    // Resolve type
                    $post['type'] = isset($obj['type']) && isset($nuxtData[$obj['type']]) 
                        ? $nuxtData[$obj['type']] : 'Default';
                    
                    // Resolve startDate (for events)
                    $post['startDate'] = isset($obj['startDate']) && $obj['startDate'] !== null && isset($nuxtData[$obj['startDate']]) 
                        ? $nuxtData[$obj['startDate']] : null;
                    
                    // Build detail link
                    $post['link'] = 'https://www.heimat-info.de/beitraege/' . $post['id'];
                    
                    // Resolve attachments (images)
                    $post['images'] = [];
                    if (isset($obj['attachments']) && isset($nuxtData[$obj['attachments']])) {
                        $attachArr = $nuxtData[$obj['attachments']];
                        if (is_array($attachArr)) {
                            foreach ($attachArr as $attachIdx) {
                                if (isset($nuxtData[$attachIdx]) && is_array($nuxtData[$attachIdx])) {
                                    $attachObj = $nuxtData[$attachIdx];
                                    if (isset($attachObj['url']) && isset($nuxtData[$attachObj['url']])) {
                                        $imgUrl = $nuxtData[$attachObj['url']];
                                        $imgType = isset($attachObj['type']) && isset($nuxtData[$attachObj['type']]) 
                                            ? $nuxtData[$attachObj['type']] : '';
                                        if ($imgType === 'Picture' || preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $imgUrl)) {
                                            $post['images'][] = $imgUrl;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    $allPosts[] = $post;
                }
            }
        }
        
        $posts = $allPosts;
    }
}

// --- Build response ---
$response = [
    'posts' => $posts,
    'fetchedAt' => date('c'),
    'cached' => false
];

// --- Write cache (all posts) ---
file_put_contents($cacheFile, json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

// --- Return only requested count ---
$response['posts'] = array_slice($posts, 0, $count);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);