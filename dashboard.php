<?php
require 'auth.php';
require 'db.php';
require 'blockchain.php';

$blockchain = new Blockchain();

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM emails WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalEmails = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$chainCount = count($blockchain->getChain());
$isValid = $blockchain->validateChain();
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Your PHPMailer SMTP system with blockchain audit tracking.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Sent Mails</h3>
                <p><?php echo $totalEmails; ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Blockchain Blocks</h3>
                <p><?php echo $chainCount; ?></p>
            </div>

            <div class="stat-card">
                <h3>Chain Status</h3>
                <p><?php echo $isValid ? "Valid" : "Tampered"; ?></p>
            </div>
        </div>
    </main>
</div>

</div>
</body>
</html>
