<?php
require 'auth.php';
require 'db.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM emails WHERE user_id = ? AND folder = 'trash' ORDER BY id DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Trash</h1>
            <p>Emails moved to trash. You can restore or delete them permanently.</p>
        </div>

        <div class="mail-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="mail-card">
                        <div class="mail-top">
                            <h3><?php echo htmlspecialchars($row['subject']); ?></h3>
                            <span><?php echo htmlspecialchars($row['created_at']); ?></span>
                        </div>

                        <p class="mail-recipient">To: <?php echo htmlspecialchars($row['recipient']); ?></p>
                        <p class="mail-message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>

                        <div class="mail-actions">
                            <a class="action-btn restore" href="restore_mail.php?id=<?php echo (int)$row['id']; ?>">
                                Restore
                            </a>

                            <a class="action-btn delete" href="delete_mail.php?id=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Delete this email permanently? This cannot be undone.');">
                                Delete Permanently
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Trash is empty.</div>
            <?php endif; ?>
        </div>
    </main>
</div>

</div>
</body>
</html>