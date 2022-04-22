<?php
require_once "mail.php";
require_once "../configs/credentials_db.php";

$input = json_decode(file_get_contents('php://input'));

//form fields from webpage contact form
if ($input) {

  $name = $input->name;
  $mailadd = $input->mail;
  $subject = $input->subject;
  $message = $input->message;

  //build json for mail function
  $json = new stdClass();
  $json->from = $mailadd;
  $json->fromname = $name;
  $json->subject = $subject;
  $json->altbody = "";
  $json->body = "Neue Nachricht vom Kontaktformular der Webseite.\n\nAbsender: " . $name . " (" . $mailadd . ")\nBetreff: " . $subject . "\n\n" . $message;
  $json->to = array(
    array('mail' => 'vorstand@mv-heustreu.de', 'name' => 'Vorstand MV Heustreu'),
//  array('mail' => 'manger_lorenz@web.de', 'name' => 'Lorenz Manger')
  );
  $json->bcc = array(
    array('mail' => 'admin@mv-heustreu.de', 'name' => 'Admin MV Heustreu')
  );

  try {
    // Connect and create the PDO object
    $conn = new PDO("mysql:host=$hostdb; dbname=$namedb", $userdb, $passdb);
    $conn->exec("SET CHARACTER SET utf8");      // Sets encoding UTF-8

    // Define an insert query
    $sql = "INSERT INTO `contact` (`name`, `mail`, `subject`, `message`)
    VALUES
      ('$name', '$mailadd', '$subject', '$message')";
    $count = $conn->exec($sql);

    $conn = null;        // Disconnect
  } catch (PDOException $e) {
    echo $e->getMessage();
  }

  // If data added ($count not false) displays the number of rows added
  if ($count !== false) {
    if (sendMail(json_encode($json))) {
      echo "success";
    } else {
      echo "Mail send failed";
    }
  }
}
