<?php
require 'auth.php';
require 'db.php';
require 'blockchain.php';

$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM received_emails WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();

    $blockchain = new Blockchain();
    $blockchain->addBlock([
        "action" => "delete_received_permanently",
        "received_email_id" => $id,
        "user_id" => $userId,
        "time" => date("Y-m-d H:i:s")
    ]);
}

header("Location: received_trash.php");
exit();
?>