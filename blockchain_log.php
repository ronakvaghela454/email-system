<?php
require 'auth.php';
require 'blockchain.php';

$blockchain = new Blockchain();
$chain = $blockchain->getChain();
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Blockchain Log</h1>
            <p>Audit trail of sent emails stored as linked blocks.</p>
        </div>

        <?php if (count($chain) > 0): ?>
            <div class="block-grid">
                <?php foreach ($chain as $block): ?>
                    <div class="block-card">
                        <h3>Block #<?php echo htmlspecialchars($block['index']); ?></h3>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($block['timestamp']); ?></p>
                        <p><strong>Previous Hash:</strong></p>
                        <div class="hash-box"><?php echo htmlspecialchars($block['previous_hash']); ?></div>

                        <p><strong>Hash:</strong></p>
                        <div class="hash-box"><?php echo htmlspecialchars($block['hash']); ?></div>

                        <p><strong>Data:</strong></p>
                        <pre><?php echo htmlspecialchars(json_encode($block['data'], JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">No blockchain records yet.</div>
        <?php endif; ?>
    </main>
</div>

</div>
</body>
</html>
