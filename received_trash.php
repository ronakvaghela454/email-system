<?php
require 'auth.php';
require 'db.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * FROM received_emails
    WHERE user_id = ? AND folder = 'trash'
    ORDER BY id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

function previewTrashText(string $text, int $limit = 180): string
{
    $text = trim($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . "...";
}
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Received Trash</h1>
            <p>Received emails moved to trash.</p>
        </div>

        <div class="mail-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="mail-card">
                        <div class="mail-top">
                            <h3><?php echo htmlspecialchars($row['subject'] ?: '(No Subject)'); ?></h3>
                            <span><?php echo htmlspecialchars($row['received_at']); ?></span>
                        </div>

                        <p class="mail-recipient">From: <?php echo htmlspecialchars($row['sender_email']); ?></p>
                        <p class="mail-message"><?php echo nl2br(htmlspecialchars(previewTrashText($row['message']))); ?></p>

                        <div class="mail-actions">
                            <a class="action-btn restore" href="restore_received.php?id=<?php echo (int)$row['id']; ?>">Restore</a>
                            <a class="action-btn delete"
                               href="delete_received.php?id=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Delete this received email permanently?');">
                                Delete Permanently
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Received trash is empty.</div>
            <?php endif; ?>
        </div>
    </main>
</div>

</div>
</body>
</html>