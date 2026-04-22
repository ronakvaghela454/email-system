<?php
require 'auth.php';
require 'db.php';
require 'blockchain.php';

$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $find = $conn->prepare("SELECT attachment FROM emails WHERE id = ? AND user_id = ?");
    $find->bind_param("ii", $id, $userId);
    $find->execute();
    $result = $find->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['attachment'])) {
            $filePath = __DIR__ . "/" . $row['attachment'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $conn->prepare("DELETE FROM emails WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();

        $blockchain = new Blockchain();
        $blockchain->addBlock([
            "action" => "delete_permanently",
            "email_id" => $id,
            "user_id" => $userId,
            "time" => date("Y-m-d H:i:s")
        ]);
    }
}

header("Location: trash.php");
exit();
?>