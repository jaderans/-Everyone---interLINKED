<?php
session_start();
include_once 'interlinkedDB.php';

$master_con = connectToDatabase(3306);

$errors = [];
$success = "";

$userId = $_SESSION['USER_ID'] ?? null;
if (!$userId) {
    $errors[] = "User not logged in.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$errors) {
    // Save POST data to session for sticky form
    $_SESSION['bnk_name'] = $_POST['bnk_name'] ?? '';
    $_SESSION['bnk_acc_num'] = $_POST['bnk_acc_num'] ?? '';
    $_SESSION['bnk_user_name'] = $_POST['bnk_user_name'] ?? '';
    $_SESSION['bnk_password'] =  $_POST['bnk_password'] ?? '';
    $_SESSION['bnk_cvv'] = $_POST['bnk_cvv'] ?? '';
    $_SESSION['bnk_exp_date'] = $_POST['bnk_exp_date'] ?? '';

    $bnkName = $_SESSION['bnk_name'];
    $bnkAccNum = $_SESSION['bnk_acc_num'];
    $bnkUserName = $_SESSION['bnk_user_name'];
    $bnkPassword = $_SESSION['bnk_password'];
    $bnkCVV = $_SESSION['bnk_cvv'];
    $bnkExpDate = $_SESSION['bnk_exp_date'];

    // Check if user already has a bank account
    $checkStmt = $master_con->prepare("SELECT COUNT(*) FROM bank WHERE USER_ID = :user_id");
    $checkStmt->execute([':user_id' => $userId]);
    $existingCount = $checkStmt->fetchColumn();

    if ($existingCount > 0) {
        $errors[] = "You already have a bank account registered. Only one bank account is allowed per user.";
    } else {
        // Validate inputs
        if (empty($bnkName) || empty($bnkAccNum) || empty($bnkUserName) || empty($bnkPassword) || empty($bnkCVV) || empty($bnkExpDate)) {
            $errors[] = "All fields are required.";
        }

        $today = date('Y-m-d');
        if ($bnkExpDate < $today) {
            $errors[] = "Make sure to enter a valid card expiration date.";
        }

        // Insert if no errors
        if (empty($errors)) {
            $stmt = $master_con->prepare("
                INSERT INTO bank (BNK_NAME, BNK_ACC_NUM, BNK_USER_NAME, BNK_PASSWORD, BNK_CVV, BNK_EXP_DATE, USER_ID)
                VALUES (:bnk_name, :bnk_acc_num, :bnk_user_name, :bnk_password, :bnk_cvv, :bnk_exp_date, :user_id)
            ");
            $successExec = $stmt->execute([
                ':bnk_name' => $bnkName,
                ':bnk_acc_num' => $bnkAccNum,
                ':bnk_user_name' => $bnkUserName,
                ':bnk_password' => $bnkPassword,
                ':bnk_cvv' => $bnkCVV,
                ':bnk_exp_date' => $bnkExpDate,
                ':user_id' => $userId
            ]);

            if ($successExec) {
                $success = "Bank record added successfully.";
                // Clear session values after success
                unset($_SESSION['bnk_name'], $_SESSION['bnk_acc_num'], $_SESSION['bnk_user_name'], $_SESSION['bnk_password'], $_SESSION['bnk_cvv'], $_SESSION['bnk_exp_date']);
            } else {
                $errors[] = "Database insertion failed.";
            }
        }
    }
}

// For sticky form outside POST or when errors
$bnkName = $_SESSION['bnk_name'] ?? '';
$bnkAccNum = $_SESSION['bnk_acc_num'] ?? '';
$bnkUserName = $_SESSION['bnk_user_name'] ?? '';
$bnkPassword = $_SESSION['bnk_password'] ?? '';
$bnkCVV = $_SESSION['bnk_cvv'] ?? '';
$bnkExpDate = $_SESSION['bnk_exp_date'] ?? '';

// Disable form if user already has a bank account
$disableForm = false;
if ($userId) {
    $checkStmt = $master_con->prepare("SELECT COUNT(*) FROM bank WHERE USER_ID = :user_id");
    $checkStmt->execute([':user_id' => $userId]);
    if ($checkStmt->fetchColumn() > 0) {
        $disableForm = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Bank Info</title>
</head>
<body>
<h2>Bank Information Form</h2>

<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color:green;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($disableForm): ?>
    <p>You already have a bank account registered. You cannot add another.</p>
<?php else: ?>
    <form method="post" action="">
        <label>Bank Name:</label><br>
        <input type="text" name="bnk_name" value="<?= htmlspecialchars($bnkName) ?>"><br>

        <label>Bank Account Number:</label><br>
        <input type="text" name="bnk_acc_num" value="<?= htmlspecialchars($bnkAccNum) ?>"><br>

        <label>Bank User Name:</label><br>
        <input type="text" name="bnk_user_name" value="<?= htmlspecialchars($bnkUserName) ?>"><br>

        <label>Bank Password:</label><br>
        <input type="password" name="bnk_password" value="<?= htmlspecialchars($bnkPassword) ?>"><br>

        <label>CVV:</label><br>
        <input type="text" name="bnk_cvv" value="<?= htmlspecialchars($bnkCVV) ?>"><br>

        <label>Expiration Date (YYYY-MM-DD):</label><br>
        <input type="date" name="bnk_exp_date" value="<?= htmlspecialchars($bnkExpDate) ?>"><br><br>

        <input type="submit" value="Submit">

    </form>
<?php endif; ?>
<button><a href="salary.php">Back</a></button>

</body>
</html>
