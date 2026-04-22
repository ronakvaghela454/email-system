<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Mailer System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-shell">
    <header class="topbar">
        <div class="brand">SMTP PHPMailer</div>
        <?php if (isset($_SESSION['user_name'])): ?>
            <div class="topbar-right">
                <span class="user-chip"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a class="logout-btn" href="logout.php">Logout</a>
            </div>
        <?php endif; ?>
    </header>