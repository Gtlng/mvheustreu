<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


function sendMail($json){

$data = json_decode($json);

// Instantiation and passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
    $mail->SMTPDebug = 0;
    $mail->isSMTP();						// Enable SMTP
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
    
    //get login information from file
    require ('../configs/credentials_mail.php');

    //Recipients
    $mail->setFrom('test@gtlng.com', 'Test-Mailer');
    
foreach($data->to as $recipient){
    	$mail->addAddress($recipient->mail, $recipient->name);
    }
    // $mail->addAddress('ellen@example.com');               // Name is optional
    $mail->addReplyTo($data->from, $data->fromname);
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    // Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $data->subject;
    $mail->Body    = $data->body;
    $mail->AltBody = $data->altbody;

    if(empty($mail->Body)) $mail->Body = ' ';

    $mail->send();
    return true;
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    return false;
}
}
