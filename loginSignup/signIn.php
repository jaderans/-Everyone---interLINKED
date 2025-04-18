<?php
session_start();

require('interlinkedDB.php');

$nameMsg = $emailMsg = $passMsg = "";
$userName = $email  = $pass = $userType = "";

// Helper function to sanitize input
function clean_text($text) {
    return htmlspecialchars(trim($text));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $userName = isset($_POST['userName']) ? clean_text($_POST['userName']) : '';
        $userType = isset($_POST['type']) ? clean_text($_POST['type']) : '';

        // Store in session
        $_SESSION["userName"] = $userName;
        $_SESSION["type"] = $userType;

        if ($_POST['action'] == "Next ►") {
            if (empty($userName)) {
                $nameMsg = "Username is required.";
            } else {
                $stmt = $conn->prepare("SELECT * FROM user WHERE USER_NAME = ?");
                $stmt->bind_param("s", $userName);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $nameMsg = "Username already exists. <br>";
                } else {
                    // Username is available, redirect
                    header("Location: FormSignUser.php");
                    exit();
                }

                $stmt->close();
            }
        }
    }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>
<div>
    <div class="rectangle2"></div>
    <img class="iconBg" alt="icon" src="../imgs/inlIconWhite.png">
</div>

<div class="container2">
    <div class="content2">
        <h1>Ready to be linked? <br> SIGN IN WITH US!</h1>
        <p class="credentials">Please enter your credentials</p>
        <form class="form" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <label for="userName"> Username*</label><br>
            <input type="text" id="userName" name="userName" placeholder="Username" value="<?= htmlspecialchars($userName) ?>"><br>
            <span class="spanWarning"><?= $nameMsg ?></span>

            <label for="clientType"> Are you a...</label><br>
            <select name="type" id="clientType">
                <option value="Client" <?= ($userType == "Client") ? "selected" : "" ?>>Client</option>
                <option value="Freelancer" <?= ($userType == "Freelancer") ? "selected" : "" ?>>Freelancer</option>
            </select> <br><br>

            <input type="submit" name="action" value="Next ►">
        </form>
        <button type="submit" name="action" value="goBack" onclick="window.location.href='logIn.php';">◄ Go Back</button>
    </div>
</div>

</body>
</html>
