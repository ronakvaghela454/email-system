<?php
require 'auth.php';
require 'db.php';

$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: received.php");
    exit();
}

// Mark as read
$update = $conn->prepare("UPDATE received_emails SET is_read = 1 WHERE id = ? AND user_id = ?");
$update->bind_param("ii", $id, $userId);
$update->execute();

// Load email
$stmt = $conn->prepare("
    SELECT * FROM received_emails
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: received.php");
    exit();
}

$email = $result->fetch_assoc();
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Email Details</h1>
            <p>Read the full imported email message.</p>
        </div>

        <div class="detail-card">
            <div class="detail-header">
                <div>
                    <h2>
                        <?php if ((int)$email['is_starred'] === 1): ?>⭐ <?php endif; ?>
                        <?php echo htmlspecialchars($email['subject'] ?: '(No Subject)'); ?>
                    </h2>
                    <p class="detail-meta"><strong>From:</strong> <?php echo htmlspecialchars($email['sender_email']); ?></p>
                    <p class="detail-meta"><strong>Received:</strong> <?php echo htmlspecialchars($email['received_at']); ?></p>
                </div>

                <div class="detail-actions">
                    <a class="action-btn star" href="toggle_received_star.php?id=<?php echo (int)$email['id']; ?>">
                        <?php echo ((int)$email['is_starred'] === 1) ? 'Unstar' : 'Star'; ?>
                    </a>

                    <a class="action-btn trash"
                       href="move_received_to_trash.php?id=<?php echo (int)$email['id']; ?>"
                       onclick="return confirm('Move this received email to trash?');">
                        Move to Trash
                    </a>
                </div>
            </div>

            <div class="detail-body">
                <?php echo nl2br(htmlspecialchars($email['message'])); ?>
            </div>

            <div class="detail-footer">
                <a class="back-link" href="received.php">← Back to Received Inbox</a>
            </div>
        </div>
    </main>
</div>

</div>
</body>
</html>