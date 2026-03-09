<?php
require_once "mail.php";
require_once "../configs/credentials_db.php";

$input = json_decode(file_get_contents('php://input'));

if ($input) {

  // Honeypot check (server-side!)
  if (!empty($input->website)) {
    echo "success"; // pretend it worked so bots don't retry
    exit;
  }

  // Sanitize inputs
  $name = trim($input->name ?? '');
  $mailadd = trim($input->mail ?? '');
  $subject = trim($input->subject ?? '');
  $message = trim($input->message ?? '');

  // Basic validation
  if (empty($name) || empty($mailadd) || empty($subject) || empty($message)) {
    echo "Validation failed";
    exit;
  }

  if (!filter_var($mailadd, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email";
    exit;
  }

  // Build json for mail function
  $json = new stdClass();
  $json->from = $mailadd;
  $json->fromname = $name;
  $json->subject = $subject;
  $json->altbody = "";
  $json->body = "Neue Nachricht vom Kontaktformular der Webseite.\n\nAbsender: " . $name . " (" . $mailadd . ")\nBetreff: " . $subject . "\n\n" . $message;
  $json->to = array(
    array('mail' => 'vorstand@mv-heustreu.de', 'name' => 'Vorstand MV Heustreu'),
  );
  $json->bcc = array(
    array('mail' => 'admin@mv-heustreu.de', 'name' => 'Admin MV Heustreu')
  );

  try {
    $conn = new PDO("mysql:host=$hostdb; dbname=$namedb", $userdb, $passdb);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET CHARACTER SET utf8");

    // Prepared statement to prevent SQL injection!
    $sql = "INSERT INTO `contact` (`name`, `mail`, `subject`, `message`) VALUES (:name, :mail, :subject, :message)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
      ':name' => $name,
      ':mail' => $mailadd,
      ':subject' => $subject,
      ':message' => $message
    ]);

    $conn = null;
  } catch (PDOException $e) {
    // Don't expose DB errors to the client
    error_log("Contact form DB error: " . $e->getMessage());
    echo "Database error";
    exit;
  }

  if (sendMail(json_encode($json))) {
    echo "success";
  } else {
    echo "Mail send failed";
  }
}