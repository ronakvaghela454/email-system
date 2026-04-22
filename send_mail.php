<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';
require 'db.php';
require 'auth.php';
require 'blockchain.php';

$blockchain = new Blockchain();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $filePath = null;

    if (!empty($_FILES['attachment']['name'])) {
        $filePath = "upload/" . $_FILES['attachment']['name'];
        move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath);
    }

    // SMTP
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ronaktech69@gmail.com';
    $mail->Password = 'vyaxxdfpkmahrxgx';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('ronaktech69@gmail.com', 'System');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;

    if ($filePath) {
        $mail->addAttachment($filePath);
    }

    $mail->send();

    // DB SAVE
    $conn->query("INSERT INTO emails (user_id,email,subject,message,attachment)
    VALUES ($user_id,'$email','$subject','$message','$filePath')");

    // BLOCKCHAIN
    $blockchain->addBlock([
        "email" => $email,
        "subject" => $subject,
        "time" => date("Y-m-d H:i:s")
    ]);

    echo "<script>alert('Email Sent'); window.location='inbox.php';</script>";
}
?>

<form method="POST" enctype="multipart/form-data">
    <h2>Compose Email</h2>
    <input name="email" placeholder="To"><br><br>
    <input name="subject" placeholder="Subject"><br><br>
    <textarea name="message" placeholder="Message"></textarea><br><br>
    <input type="file" name="attachment"><br><br>
    <button>Send</button>
</form>