<?php
require 'db.php';
require 'auth.php';

$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT * FROM emails WHERE user_id=$user_id ORDER BY id DESC");
?>

<link rel="stylesheet" href="assets/style.css">

<div class="topbar">📧 Inbox</div>

<div class="container">

    <div class="sidebar">
        <a href="compose.php">✉ Compose</a>
        <a href="inbox.php">📥 Inbox</a>
        <a href="check_chain.php">⛓ Blockchain</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main">

        <h2>Your Emails</h2>

        <?php while($row = $result->fetch_assoc()) { ?>

            <div class="email-card">
                <b><?= $row['subject'] ?></b>
                <p><?= $row['message'] ?></p>
                <small><?= $row['recipient'] ?></small>
            </div>

        <?php } ?>

    </div>

</div>