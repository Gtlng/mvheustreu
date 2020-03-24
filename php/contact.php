<?php

require_once "../configs/credentials.php";

$input = json_decode(file_get_contents('php://input'));

//form fields from webpage contact form
if($input){

$name = $input->name;
$mail = $input->mail;
$subject = $input->subject;
$message = $input->message;


try {
  // Connect and create the PDO object
  $conn = new PDO("mysql:host=$hostdb; dbname=$namedb", $userdb, $passdb);
  $conn->exec("SET CHARACTER SET utf8");      // Sets encoding UTF-8

  // Define an insert query
  $sql = "INSERT INTO `contact` (`name`, `mail`, `subject`, `message`)
    VALUES
      ('$name', '$mail', '$subject', '$message')";
  $count = $conn->exec($sql);

  $conn = null;        // Disconnect
}
catch(PDOException $e) {
  echo $e->getMessage();
}

// If data added ($count not false) displays the number of rows added
if($count !== false) echo "success";

}

?>
