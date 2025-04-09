<?php
session_start();

$nameMsg = $emailMsg = $passMsg = "";
$userName = $email = $pass = $userType = "";

// Helper function to sanitize input
function clean_text($text) {
    return htmlspecialchars(trim($text));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $email = isset($_POST['email']) ? clean_text($_POST['email']) : '';
        $userType = isset($_POST['type']) ? clean_text($_POST['type']) : '';

        $_SESSION["email"] = $email;
        $_SESSION["type"] = $userType;

        if ($_POST['action'] == "goBack") {
            header("Location: logIn.php");
            exit();
        }

        if ($_POST['action'] == "signIn") {
            // Validate email
            if (empty($email)) {
                $emailMsg = "Email is required <br>";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailMsg = "Input a valid email address <br>";
            }

            // Redirect if validation passed
            if (empty($emailMsg) && !empty($userType)) {
                header("Location: FormSignUser.php");
                exit();
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
    <link rel="icon" type="image/x-icon" href="imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>
<div>
    <div class="rectangle2"></div>
    <img class="iconBg" alt="icon" src="imgs/inlIconWhite.png">
</div>

<div class="container2">
    <div class="content2">
        <h1>Ready to be linked? <br> SIGN IN WITH US!</h1>
        <p class="credentials">Please enter your credentials</p>
        <form class="form" method="POST">
            <label for="userEmail"> Email*</label><br>
            <input type="text" id="userEmail" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>"><br>
            <span class="spanWarning"><?= $emailMsg ?></span>

            <label for="clientType"> Are you a...</label><br>
            <select name="type" id="clientType">
                <option value="Client" <?= ($userType == "Client") ? "selected" : "" ?>>Client</option>
                <option value="Freelancer" <?= ($userType == "Freelancer") ? "selected" : "" ?>>Freelancer</option>
                <option value="Admin" <?= ($userType == "Admin") ? "selected" : "" ?>>Admin</option>
            </select> <br><br>

            <input type="submit" name="action" value="Sign In">
            <button type="submit" name="action" value="goBack">◄ Go Back</button>
        </form>
    </div>
</div>

</body>
</html>
