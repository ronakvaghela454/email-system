<?php
require 'auth.php';
require 'db.php';

$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE emails 
                            SET is_starred = CASE WHEN is_starred = 1 THEN 0 ELSE 1 END
                            WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
}

$back = $_SERVER['HTTP_REFERER'] ?? 'sent.php';
header("Location: " . $back);
exit();
?>