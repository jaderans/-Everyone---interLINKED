<?php
session_start();
include_once 'interlinkedDB.php';
include_once 'checkIfSet.php';

$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$userId = $_SESSION['user_id'] ?? null; // Logged in admin user
$error = [];
$successMessage = "";
$amount = ""; // Default empty
$bankId = $_POST['bankId'] ?? null;


// POST: save projId and freelancerId to session, and process payment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['projId'] ?? null;
    $freeId = $_POST['freelancerId'] ?? null;
    $amount = $_POST['amount'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirmPass'] ?? '';
    $payDate = date('Y-m-d');

    // Save IDs to session
    if ($id) $_SESSION['projId'] = $id;
    if ($freeId) $_SESSION['freelancerId'] = $freeId;

    // Fetch project info
    $proID = null;
    if ($id) {
        $stmt = $slave_con->prepare("SELECT * FROM `projects` WHERE `PRO_ID` = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result) {
            $proID = $result['PRO_ID'];
        } else {
            $error[] = "Project not found.";
        }
    }
    // Fetch freelancer info
    $stmt = $slave_con->prepare("SELECT * FROM `user` WHERE `USER_ID` = ?");
    $stmt->execute([$freeId]);
    $user = $stmt->fetch();

    if (!$user) {
        $error[] = "Freelancer user not found.";
    }

    // Fetch bank name for freelancer
    $bankStmt = $slave_con->prepare("SELECT BNK_NAME FROM bank WHERE USER_ID = ? LIMIT 1");
    $bankStmt->execute([$freeId]);
    $bankRow = $bankStmt->fetch(PDO::FETCH_ASSOC);
    $bankName = $bankRow['BNK_NAME'] ?? 'N/A';

    // Fetch logged-in admin's password for verification
    $conPass = $slave_con->prepare("SELECT USER_PASSWORD FROM user WHERE USER_ID = ?");
    $conPass->execute([$userId]);
    $res = $conPass->fetch(PDO::FETCH_ASSOC);
    $adminPass = $res['USER_PASSWORD'] ?? '';

    // Validate inputs
    if (empty($amount) || empty($password) || empty($confirmPass)) {
        $error[] = "Amount and password fields are required.";
    }
    if ($password !== $confirmPass) {
        $error[] = "Password and Confirm Password do not match.";
    }
    if (!password_verify($password, $adminPass)) {
        $error[] = "Invalid admin password.";
    }
    if (!is_numeric($amount) || $amount <= 0) {
        $error[] = "Amount must be a positive number.";
    }
    if (empty($proID)) {
        $error[] = "No valid project selected.";
    }
}

$bankId = null;
$bankStmt = $slave_con->prepare("SELECT BNK_ID, BNK_NAME FROM bank WHERE USER_ID = ? LIMIT 1");
$bankStmt->execute([$freeId]);
$bankRow = $bankStmt->fetch(PDO::FETCH_ASSOC);
if ($bankRow) {
    $bankId = $bankRow['BNK_ID'];
    $bankName = $bankRow['BNK_NAME'];
} else {
    $error[] = "Freelancer has no bank account on file.";
}

    // Insert payment if no errors
    if (empty($error)) {
        if (empty($error)) {
            $stmt = $master_con->prepare("
        INSERT INTO payment (
            USER_ID, PRO_ID, BNK_ID, PAY_STATUS, PAY_AMOUNT, PAY_DATE
        ) VALUES (
            :userId, :proId, :bankId, :payStatus, :payAmount, :payDate
        )
    ");
            $success = $stmt->execute([
                ':userId' => $freeId,
                ':proId' => $proID,
                ':bankId' => $bankId,
                ':payStatus' => "Completed",
                ':payAmount' => (int)$amount,
                ':payDate' => $payDate
            ]);

            if ($success) {
                $successMessage = "Payment recorded successfully. Redirecting to payments list...";
                $amount = ''; // Clear form amount after success

                // Output a JS redirect after 2 seconds
                echo "<script>
        setTimeout(function() {
            window.location.href = 'adminPay.php';
        }, 2000);
    </script>";
        } else {
            $error[] = "Failed to record payment. Please try again.";
        }
    }
}

// Outside POST, fetch project & user info using session if available
$id = $_SESSION['projId'] ?? null;
$freeId = $_SESSION['freelancerId'] ?? null;

if ($id) {
    $stmt = $slave_con->prepare("SELECT * FROM `projects` WHERE `PRO_ID` = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
}

if ($freeId) {
    $stmt = $slave_con->prepare("SELECT * FROM `user` WHERE `USER_ID` = ?");
    $stmt->execute([$freeId]);
    $user = $stmt->fetch();
}

$bankStmt = $slave_con->prepare("SELECT BNK_ID, BNK_NAME FROM bank WHERE USER_ID = ?");
$bankStmt->execute([$userId]);
$banks = $bankStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin info for navbar
$adminStmt = $slave_con->prepare("SELECT USER_FSTNAME, USER_LSTNAME FROM user WHERE USER_ID = ?");
$adminStmt->execute([$userId]);
$adminInfo = $adminStmt->fetch();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Processing | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-payment.css">
</head>
<body>
<!-- Main Container -->
<div class="main-container">
    <div class="admin-panel">
        <div class="left-pane">
            <!-- Payment Details Card -->
            <div class="content">
                <div class="payment-header">
                    <h2><i class="fas fa-credit-card"></i> Payment Processing</h2>
                    <p>Process payment for freelancer services</p>
                </div>

                <!-- Project and Freelancer Info -->
                <div class="payment-info-section">
                    <div class="info-cards">
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="fas fa-project-diagram"></i>
                                <h4>Project Information</h4>
                            </div>
                            <div class="info-card-body">
                                <div class="info-row">
                                    <span class="info-label">Project ID:</span>
                                    <span class="info-value"><?= htmlspecialchars($freeId ?? 'N/A') ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Project Title:</span>
                                    <span class="info-value"><?= isset($result) ? htmlspecialchars($result['PRO_TITLE']) : 'N/A' ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Bank:</span>
                                    <span class="info-value"><?= htmlspecialchars($bankName) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="fas fa-user"></i>
                                <h4>Freelancer Information</h4>
                            </div>
                            <div class="info-card-body">
                                <div class="info-row">
                                    <span class="info-label">Freelancer ID:</span>
                                    <span class="info-value"><?= htmlspecialchars($freeId ?? 'N/A') ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Freelancer Name:</span>
                                    <span class="info-value"><?= isset($user) ? htmlspecialchars($user['USER_FSTNAME'] . ' ' . $user['USER_LSTNAME']) : 'N/A' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <!-- Error Messages -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($error as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Payment Form -->
                <div class="payment-form-section">
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" class="payment-form">
                        <div class="form-header">
                            <h3><i class="fas fa-money-bill-wave"></i> Payment Details</h3>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label for="amount">Payment Amount*</label>
                                <div class="input-group">
                                    <span class="input-prefix">$</span>
                                    <input type="number"
                                           name="amount"
                                           id="amount"
                                           placeholder="0.00"
                                           value="<?= htmlspecialchars($amount) ?>"
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-shield-alt"></i> Security Verification</h4>
                            <p class="security-note">Please enter your admin password to authorize this payment.</p>

                            <div class="form-row">
                                <div class="form-col">
                                    <label for="password">Admin Password*</label>
                                    <input type="password"
                                           name="password"
                                           id="password"
                                           placeholder="Enter your admin password"
                                           required>
                                </div>
                                <div class="form-col">
                                    <label for="confirmPass">Confirm Password*</label>
                                    <input type="password"
                                           name="confirmPass"
                                           id="confirmPass"
                                           placeholder="Confirm your admin password"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="projId" value="<?= htmlspecialchars($id ?? '') ?>">
                        <input type="hidden" name="freelancerId" value="<?= htmlspecialchars($freeId ?? '') ?>">

                        <div class="form-actions">
                            <a href="adminPay.php" class="cancel-btn">
                                <i class="fas fa-arrow-left"></i> Back to Payments
                            </a>
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-credit-card"></i> Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Pane - Payment Summary -->
        <div id="right-pane">
            <div class="payment-summary">
                <div class="summary-header">
                    <h3><i class="fas fa-receipt"></i> Payment Summary</h3>
                </div>

                <div class="summary-section">
                    <h4>Transaction Details</h4>
                    <div class="summary-row">
                        <span class="summary-label">Payment Date:</span>
                        <span class="summary-value"><?= date('M d, Y') ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Status:</span>
                        <span class="summary-value status-pending-tag">Pending</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Method:</span>
                        <span class="summary-value">Bank Transfer</span>
                    </div>
                </div>

                <div class="summary-section">
                    <h4>Amount Breakdown</h4>
                    <div class="summary-row">
                        <span class="summary-label">Base Amount:</span>
                        <span class="summary-value" id="base-amount">$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Processing Fee:</span>
                        <span class="summary-value">-30%</span>
                    </div>
                    <div class="summary-row total-row">
                        <span class="summary-label">Total Amount:</span>
                        <span class="summary-value" id="total-amount">$0.00</span>
                    </div>
                </div>

                <div class="security-notice">
                    <i class="fas fa-info-circle"></i>
                    <p>This payment will be processed immediately upon confirmation. Please verify all details before proceeding.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update payment summary when amount changes
    document.getElementById('amount').addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        const fee = amount * 0.30;
        const total = amount - fee;

        document.getElementById('base-amount').textContent = '$' + amount.toFixed(2);
        document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
    });

    // Form validation
    document.querySelector('.payment-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPass = document.getElementById('confirmPass').value;

        if (password !== confirmPass) {
            e.preventDefault();
            alert('Password and Confirm Password do not match.');
            return false;
        }

        const amount = parseFloat(document.getElementById('amount').value);
        if (amount <= 0) {
            e.preventDefault();
            alert('Please enter a valid payment amount.');
            return false;
        }

        return confirm('Are you sure you want to process this payment?');
    });
</script>
</body>
</html>