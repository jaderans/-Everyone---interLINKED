<?php
session_start();
include_once 'interlinkedDB.php';

$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$userId = $_SESSION['user_id'] ?? null; // Logged in user
$error = [];
$successMessage = "";
$amount = ""; // Default empty

// POST: save projId and freelancerId to session, and process payment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['projId'] ?? null;
    $freeId = $_POST['freelancerId'] ?? null;
    $amount = $_POST['amount'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPass = $_POST['comfirmPass'] ?? '';
    $payDate = date('Y-m-d');

    // Save IDs to session
    if ($id) $_SESSION['projId'] = $id;
    if ($freeId) $_SESSION['freelancerId'] = $freeId;

    // Fetch project info
    $stmt = $slave_con->prepare("SELECT * FROM `projects` WHERE `PRO_ID` = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();

    if (!$result) {
        $error[] = "Project not found.";
    } else {
        $proID = $result['PRO_ID'];
    }

    // Fetch freelancer info
    $stmt = $slave_con->prepare("SELECT * FROM `user` WHERE `USER_ID` = ?");
    $stmt->execute([$freeId]);
    $user = $stmt->fetch();

    if (!$user) {
        $error[] = "Freelancer user not found.";
    }

    // Fetch logged-in user's password for verification
    $conPass = $slave_con->prepare("SELECT USER_PASSWORD FROM user WHERE USER_ID = ?");
    $conPass->execute([$userId]);
    $res = $conPass->fetch(PDO::FETCH_ASSOC);
    $userPass = $res['USER_PASSWORD'] ?? '';

    // Validate inputs
    if (empty($amount) || empty($password) || empty($confirmPass)) {
        $error[] = "Amount and password fields are required.";
    }
    if ($password !== $confirmPass) {
        $error[] = "Password and Confirm Password do not match.";
    }
    if ($password !== $userPass) {
        $error[] = "Invalid password.";
    }
    if (!is_numeric($amount) || $amount <= 0) {
        $error[] = "Amount must be a positive number.";
    }

    // Insert payment if no errors
    if (empty($error)) {
        $stmt = $master_con->prepare("
            INSERT INTO payment (
                USER_ID, PRO_ID,PAY_STATUS, PAY_AMOUNT, PAY_DATE
            ) VALUES (
                :userId, :proId, :payStatus, :payAmount, :payDate
            )
        ");

        $success = $stmt->execute([
            ':userId' => $freeId,
            ':proId' => $proID,
            ':payStatus' => "Completed",
            ':payAmount' => (int)$amount,
            ':payDate' => $payDate
        ]);

        if ($success) {
            $successMessage = "Payment recorded successfully.";
            $amount = ''; // Clear form amount after success
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

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment</title>
</head>
<body>
<div class="details">
    <h1>PAYMENT</h1>
    <p><?= htmlspecialchars($freeId ?? '') ?></p>
    <h3>User: <?= isset($user) ? htmlspecialchars($user['USER_FSTNAME'] . ' ' . $user['USER_LSTNAME']) : 'N/A' ?></h3>
    <h3>Title: <?= isset($result) ? htmlspecialchars($result['PRO_TITLE']) : 'N/A' ?></h3>
</div>

<?php if ($successMessage): ?>
    <p style="color:green;"><?= htmlspecialchars($successMessage) ?></p>
<?php endif; ?>

<form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
    <label for="amount">Amount*</label>
    <input type="number" name="amount" placeholder="Amount" value="<?= htmlspecialchars($amount) ?>" required>

    <label for="password">Password*</label>
    <input type="password" name="password" placeholder="Password" required>

    <label for="comfirmPass">Confirm Password*</label>
    <input type="password" name="comfirmPass" placeholder="Confirm Password" required>

    <p style="color: red;">
        <?php foreach ($error as $err) {
            echo htmlspecialchars($err) . "<br>";
        } ?>
    </p>

    <!-- Keep hidden fields so POST sends these if needed -->
    <input type="hidden" name="projId" value="<?= htmlspecialchars($id ?? '') ?>">
    <input type="hidden" name="freelancerId" value="<?= htmlspecialchars($freeId ?? '') ?>">

    <input type="submit" value="Pay">
    <button><a href="adminPay.php">Back</a></button>
</form>
</body>
</html>
