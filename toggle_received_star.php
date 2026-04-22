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

// Check email exists for this user
$stmt = $conn->prepare("
    SELECT is_starred 
    FROM received_emails 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: received.php?msg=" . urlencode("Email not found."));
    exit();
}

$row = $result->fetch_assoc();

// Toggle value
$newValue = ($row['is_starred'] == 1) ? 0 : 1;

// Update DB
$update = $conn->prepare("
    UPDATE received_emails 
    SET is_starred = ? 
    WHERE id = ? AND user_id = ?
");
$update->bind_param("iii", $newValue, $id, $userId);
$update->execute();

// Blockchain log
$blockchain = new Blockchain();
$blockchain->addBlock([
    "action" => $newValue ? "star_received_email" : "unstar_received_email",
    "email_id" => $id,
    "user_id" => $userId,
    "time" => date("Y-m-d H:i:s")
]);

// Redirect back
$back = $_SERVER['HTTP_REFERER'] ?? 'received.php';
header("Location: " . $back);
exit();
?>