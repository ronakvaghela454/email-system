<?php
require 'auth.php';
require 'blockchain.php';

$blockchain = new Blockchain();
$isValid = $blockchain->validateChain();
?>
<?php include 'includes/header.php'; ?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <h1>Blockchain Validation</h1>
            <p>Check whether the blockchain log has been changed or tampered with.</p>
        </div>

        <div class="validation-card <?php echo $isValid ? 'valid' : 'invalid'; ?>">
            <?php if ($isValid): ?>
                <h2>Blockchain is Valid</h2>
                <p>All blocks are linked correctly and hashes match.</p>
            <?php else: ?>
                <h2>Blockchain is Invalid</h2>
                <p>The chain has been tampered with or block hashes do not match.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

</div>
</body>
</html>