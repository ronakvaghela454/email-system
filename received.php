<?php
require 'auth.php';
require 'db.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT *
    FROM received_emails
    WHERE user_id = ? AND folder = 'inbox'
    ORDER BY id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$msg = $_GET['msg'] ?? '';

function previewText(string $text, int $limit = 220): string
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
            <h1>Received Emails</h1>
            <p>Emails imported from your real mailbox through IMAP.</p>
        </div>

        <div style="margin-bottom: 18px;">
            <a class="action-btn restore" href="fetch_inbox.php">Sync Inbox</a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div class="mail-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="mail-card <?php echo ((int)$row['is_read'] === 1) ? 'read-mail' : 'unread-mail'; ?>">

                        <div class="mail-top">
                            <h3>
                                <?php if ((int)$row['is_starred'] === 1): ?>
                                    ⭐
                                <?php endif; ?>
                                <?php echo htmlspecialchars($row['subject'] ?: '(No Subject)'); ?>
                            </h3>

                            <span>
                                <?php echo htmlspecialchars($row['received_at']); ?>
                            </span>
                        </div>

                        <p class="mail-recipient">
                            From: <?php echo htmlspecialchars($row['sender_email']); ?>
                        </p>

                        <p class="mail-message">
                            <?php echo nl2br(htmlspecialchars(previewText($row['message']))); ?>
                        </p>

                        <div class="mail-actions">
                            <a class="action-btn restore"
                               href="view_received.php?id=<?php echo (int)$row['id']; ?>">
                                Open
                            </a>

                            <a class="action-btn star"
                               href="toggle_received_star.php?id=<?php echo (int)$row['id']; ?>">
                                <?php echo ((int)$row['is_starred'] === 1) ? 'Unstar' : 'Star'; ?>
                            </a>

                            <a class="action-btn trash"
                               href="move_received_to_trash.php?id=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Move this received email to trash?');">
                                Move to Trash
                            </a>
                        </div>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    No received emails yet. Click “Sync Inbox”.
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</div>
</body>
</html>