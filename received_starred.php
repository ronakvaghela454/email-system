<?php
require 'auth.php';
require 'db.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * FROM received_emails
    WHERE user_id = ? AND is_starred = 1 AND folder = 'inbox'
    ORDER BY id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<div class="main-layout">
<?php include 'includes/sidebar.php'; ?>

<main class="content">
    <h1>⭐ Starred Emails</h1>

    <div class="mail-list">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="mail-card">
                <h3>⭐ <?php echo htmlspecialchars($row['subject']); ?></h3>
                <p>From: <?php echo htmlspecialchars($row['sender_email']); ?></p>
                <p><?php echo nl2br(htmlspecialchars(substr($row['message'],0,200))); ?>...</p>

                <a class="action-btn star"
                   href="toggle_received_star.php?id=<?php echo (int)$row['id']; ?>">
                    Unstar
                </a>
            </div>
        <?php endwhile; ?>
    </div>

</main>
</div>