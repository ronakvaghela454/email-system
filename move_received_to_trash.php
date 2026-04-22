<?php
require 'auth.php';
require 'db.php';
require 'blockchain.php';

$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: received.php?msg=" . urlencode("Invalid email ID."));
    exit();
}

// Check the email exists for this user
$check = $conn->prepare("
    SELECT id
    FROM received_emails
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$check->bind_param("ii", $id, $userId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows !== 1) {
    header("Location: received.php?msg=" . urlencode("Email not found."));
    exit();
}

// Update folder to trash
$update = $conn->prepare("
    UPDATE received_emails
    SET folder = 'trash'
    WHERE id = ? AND user_id = ?
");
$update->bind_param("ii", $id, $userId);

if ($update->execute()) {
    $blockchain = new Blockchain();
    $blockchain->addBlock([
        "action" => "move_received_to_trash",
        "received_email_id" => $id,
        "user_id" => $userId,
        "time" => date("Y-m-d H:i:s")
    ]);

    header("Location: received.php?msg=" . urlencode("Email moved to trash."));
    exit();
}

header("Location: received.php?msg=" . urlencode("Failed to move email to trash."));
exit();
?>