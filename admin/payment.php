<?php
session_start();
include_once 'interlinkedDB.php';

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

<style>
    body {
        background: #f5f6fa;
        font-family: 'Poppins', Arial, sans-serif;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }

    .main-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 0;
    }

    .admin-panel {
        display: flex;
        gap: 40px;
        max-width: 1200px;
        width: 100%;
        justify-content: center;
        align-items: flex-start;
    }

    .left-pane {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 32px rgba(21,96,100,0.09);
        padding: 38px 40px 32px 40px;
        max-width: 650px;
        width: 100%;
        min-width: 350px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .content {
        width: 100%;
    }

    .payment-header h2 {
        font-size: 1.35rem;
        color: #156064;
        margin-bottom: 2px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .payment-header p {
        color: #666;
        margin: 0 0 10px 0;
        font-size: 1rem;
    }

    .payment-info-section {
        margin-bottom: 14px;
    }
    .info-cards {
        display: flex;
        gap: 18px;
    }
    .info-card {
        background: #f8fafd;
        border-radius: 10px;
        flex: 1 1 0;
        padding: 18px 18px 12px 18px;
        min-width: 200px;
        box-shadow: 0 1px 4px rgba(21,96,100,0.04);
    }
    .info-card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: #156064;
        margin-bottom: 8px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 1rem;
    }
    .info-label {
        color: #444;
        font-weight: 500;
    }
    .info-value {
        color: #222;
        font-weight: 600;
    }

    .alert {
        padding: 13px 18px;
        border-radius: 8px;
        margin-bottom: 12px;
        font-size: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .alert-success {
        background: #eafbe7;
        color: #1bc47d;
        border: 1px solid #b4e3c1;
    }
    .alert-danger {
        background: #ffeaea;
        color: #e74c3c;
        border: 1px solid #f5bebe;
    }

    .payment-form-section {
        margin-top: 14px;
    }
    .payment-form .form-header h3 {
        color: #156064;
        font-size: 1.08rem;
        margin-bottom: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-row {
        display: flex;
        gap: 22px;
        margin-bottom: 12px;
    }
    .form-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .input-group {
        display: flex;
        align-items: center;
        border: 1px solid #dbe4ea;
        border-radius: 7px;
        background: #f8fafd;
        padding: 0 10px;
    }
    .input-prefix {
        color: #888;
        font-size: 1.1rem;
        margin-right: 3px;
    }
    input[type="number"], input[type="password"] {
        border: none;
        background: transparent;
        font-size: 1.1rem;
        padding: 8px 0;
        outline: none;
        width: 100%;
    }
    input[type="number"]:focus, input[type="password"]:focus {
        background: #eaf6fa;
    }
    .form-section {
        margin-top: 10px;
    }
    .security-note {
        font-size: 0.98rem;
        color: #156064;
        margin-bottom: 7px;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 16px;
        margin-top: 16px;
    }
    .cancel-btn, .submit-btn {
        padding: 9px 22px;
        border: none;
        border-radius: 7px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: background 0.18s;
    }
    .cancel-btn {
        background: #f5f6fa;
        color: #156064;
        border: 1px solid #dbe4ea;
    }
    .cancel-btn:hover {
        background: #eaf6fa;
    }
    .submit-btn {
        background: #156064;
        color: #fff;
        border: 1px solid #156064;
    }
    .submit-btn:hover {
        background: #114c4c;
    }

    /* Right pane - Payment Summary */
    #right-pane {
        min-width: 320px;
        max-width: 340px;
        width: 100%;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 32px rgba(21,96,100,0.09);
        padding: 32px 28px 22px 28px;
        margin-top: 0;
        margin-left: 0;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .payment-summary .summary-header h3 {
        color: #156064;
        font-size: 1.09rem;
        margin-bottom: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .summary-section {
        margin-bottom: 14px;
    }
    .summary-section h4 {
        color: #444;
        font-size: 1rem;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 1rem;
    }
    .summary-label {
        color: #666;
        font-weight: 500;
    }
    .summary-value {
        color: #222;
        font-weight: 600;
    }
    .status-pending-tag {
        background: #ffe066;
        color: #444;
        font-weight: 700;
        border-radius: 6px;
        padding: 1px 11px;
        font-size: 0.95rem;
    }
    .total-row .summary-label {
        font-weight: 700;
    }
    .total-row .summary-value {
        color: #156064;
        font-weight: 800;
        font-size: 1.13rem;
    }
    .security-notice {
        background: #f5f6fa;
        border-radius: 8px;
        padding: 13px 14px;
        font-size: 0.97rem;
        color: #156064;
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-top: 12px;
    }
    .security-notice i {
        font-size: 1.2rem;
        margin-top: 2px;
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .admin-panel {
            flex-direction: column;
            align-items: center;
            gap: 32px;
        }
        .left-pane, #right-pane {
            max-width: 98vw;
            min-width: 0;
            width: 100%;
        }
        .left-pane {
            padding: 28px 8vw 22px 8vw;
        }
        #right-pane {
            padding: 22px 6vw 16px 6vw;
        }
    }
    @media (max-width: 700px) {
        .main-container {
            padding: 12px 0;
        }
        .admin-panel {
            gap: 18px;
        }
        .left-pane, #right-pane {
            padding: 16px 2vw 12px 2vw;
            border-radius: 10px;
        }
        .info-cards {
            flex-direction: column;
            gap: 10px;
        }
    }

</style>

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
                                           placeholder="Password"
                                           required>
                                </div>
                                <div class="form-col">
                                    <label for="confirmPass">Confirm Password*</label>
                                    <input type="password"
                                           name="confirmPass"
                                           id="confirmPass"
                                           placeholder="Confirm Password"
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