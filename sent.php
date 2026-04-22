<?php
require 'auth.php';
require 'db.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM emails WHERE user_id = ? AND folder = 'sent' ORDER BY id DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Sent Mails</h1>
            <p>All emails sent from your SMTP mailer system.</p>
        </div>

        <div class="mail-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="mail-card">
                        <div class="mail-top">
                            <h3>
                                <?php if ((int)$row['is_starred'] === 1): ?>
                                    ⭐
                                <?php endif; ?>
                                <?php echo htmlspecialchars($row['subject']); ?>
                            </h3>
                            <span><?php echo htmlspecialchars($row['created_at']); ?></span>
                        </div>

                        <p class="mail-recipient">To: <?php echo htmlspecialchars($row['recipient']); ?></p>
                        <p class="mail-message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>

                        <?php if (!empty($row['attachment'])): ?>
                            <p class="mail-attachment">
                                Attachment:
                                <a href="<?php echo htmlspecialchars($row['attachment']); ?>" target="_blank">View File</a>
                            </p>
                        <?php endif; ?>

                        <div class="mail-actions">
                            <a class="action-btn star" href="toggle_star.php?id=<?php echo (int)$row['id']; ?>">
                                <?php echo ((int)$row['is_starred'] === 1) ? 'Unstar' : 'Star'; ?>
                            </a>

                            <a class="action-btn trash" href="move_to_trash.php?id=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Move this email to trash?');">
                                Move to Trash
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">No sent emails found yet.</div>
            <?php endif; ?>
        </div>
    </main>
</div>

</div>
</body>
</html>