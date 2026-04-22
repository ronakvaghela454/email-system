<?php
require 'auth.php';
require 'db.php';
require 'blockchain.php';

$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE received_emails SET folder = 'inbox' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();

    $blockchain = new Blockchain();
    $blockchain->addBlock([
        "action" => "restore_received_mail",
        "received_email_id" => $id,
        "user_id" => $userId,
        "time" => date("Y-m-d H:i:s")
    ]);
}

header("Location: received_trash.php");
exit();
?>