<!DOCTYPE html>
<html>
<head>
    <title>Email System</title>
</head>
<body>

<h2>Send Email</h2>

<form action="send_mail.php" method="POST">

    <input type="text" name="name" placeholder="Your Name" required><br><br>

    <input type="email" name="email" placeholder="Receiver Email" required><br><br>

    <input type="text" name="subject" placeholder="Subject" required><br><br>

    <textarea name="message" placeholder="Message" required></textarea><br><br>

    <button type="submit">Send Email</button>

</form>

</body>
</html>