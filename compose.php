<?php
require 'auth.php';
require 'db.php';
require 'blockchain.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$blockchain = new Blockchain();
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION['user_id'];
    $recipient = trim($_POST["recipient"]);
    $subject = trim($_POST["subject"]);
    $mailBody = trim($_POST["message"]);
    $attachmentPath = null;

    if ($recipient === "" || $subject === "" || $mailBody === "") {
        $message = "Please fill all required fields.";
        $messageType = "error";
    } else {
        if (!empty($_FILES["attachment"]["name"])) {
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $safeName = time() . "_" . basename($_FILES["attachment"]["name"]);
            $targetPath = $uploadDir . $safeName;

            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetPath)) {
                $attachmentPath = "uploads/" . $safeName;
            }
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ronaktech69@gmail.com';
            $mail->Password   = 'vyaxxdfpkmahrxgx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('ronaktech69@gmail.com', 'SMTP Mailer System');
            $mail->addAddress($recipient);

            if ($attachmentPath) {
                $mail->addAttachment(__DIR__ . "/" . $attachmentPath);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br(htmlspecialchars($mailBody));
            $mail->AltBody = $mailBody;

            $mail->send();

            $stmt = $conn->prepare("INSERT INTO emails (user_id, recipient, subject, message, attachment, smtp_status) VALUES (?, ?, ?, ?, ?, 'sent')");
            $stmt->bind_param("issss", $userId, $recipient, $subject, $mailBody, $attachmentPath);
            $stmt->execute();

            $blockchain->addBlock([
                "user_id" => $userId,
                "recipient" => $recipient,
                "subject" => $subject,
                "message_hash" => hash("sha256", $mailBody),
                "attachment" => $attachmentPath,
                "smtp_status" => "sent"
            ]);

            $message = "Email sent successfully and saved to blockchain log.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Mailer Error: " . $mail->ErrorInfo;
            $messageType = "error";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Compose Mail</h1>
            <p>Send emails through SMTP and store an audit hash in blockchain.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data" class="main-form">
                <label>Recipient Email</label>
                <input type="email" name="recipient" required>

                <label>Subject</label>
                <input type="text" name="subject" required>

                <label>Message</label>
                <textarea name="message" rows="8" required></textarea>

                <label>Attachment (optional)</label>
                <input type="file" name="attachment">

                <button type="submit">Send Email</button>
            </form>
        </div>
    </main>
</div>

</div>
</body>
</html>