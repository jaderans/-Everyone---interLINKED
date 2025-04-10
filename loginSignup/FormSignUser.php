<?php
session_start();

$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$userType = isset($_SESSION['type']) ? $_SESSION['type'] : '';
$password = isset($_POST['pass']) ? clean_text($_POST['pass']) : ''; // fixed input name
$phone = isset($_POST['phone']) ? clean_text($_POST['phone']) : '';
$confirmPassword = isset($_POST['conPass']) ? clean_text($_POST['conPass']) : ''; // fixed input name

$nameMsg = $emailMsg = $passMsg = $conpassMsg = $phoneMsg = $fstNameMsg = $lstNameMsg = "";
$firstName = $lastName = $pass = "";

// Fix: wrap with isset to avoid undefined index notice
$firstName = isset($_POST['firstName']) ? clean_text($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? clean_text($_POST['lastName']) : '';

function clean_text($text) {
    return htmlspecialchars(trim($text));
}

if (isset($_GET['reset'])) {
    session_unset();
    session_destroy();
    session_start();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $email = isset($email) ? clean_text($email) : '';
        $userType = isset($userType) ? clean_text($userType) : '';

        $_SESSION["email"] = $email;
        $_SESSION["type"] = $userType;

        if ($_POST['action'] == "next") {
            if (empty($firstName)) {
                $fstNameMsg = "First name is required <br>";
            }
            if (empty($lastName)) {
                $lstNameMsg = "Last name is required <br>";
            }

            if (empty($password)) {
                $passMsg = "Password is required <br>";
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                $passMsg = "Password must be at least 8 characters long, include uppercase, lowercase, number, and special character <br>";
            }

            if (empty($confirmPassword)) {
                $conpassMsg = "Enter your password again <br>";
            } elseif ($password !== $confirmPassword) {
                $conpassMsg = "Passwords do not match <br>";
            } else {
                $conpassMsg = "Passwords match <br>";
            }

            if (empty($phone)) {
                $phoneMsg = "Phone number is required <br>";
            } elseif (!preg_match('/^\+?\d{10,15}$/', $phone)) {
                $phoneMsg = "Enter a valid phone number (10–15 digits, optional +) <br>";
            } else {
                $phoneMsg = "Phone number is valid <br>";
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
    <title>Sign Up | INTERLINKED</title>
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="FormSignStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>
<div>
    <div class="rectangle2"></div>
    <div class="rectangle3"></div>
</div>

<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="">
    </div>
</div>

<div class="container2">
    <div class="content2">
        <h1>Register</h1>
        <p class="credentials">please enter your credentials</p>
        <div class="form-container">
            <form  method="POST">
                <div class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <div>
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="firstName" placeholder="First Name" value="<?= htmlspecialchars($firstName) ?>">
                                <span class="spanWarning"><?= $fstNameMsg ?></span>
                            </div>
                            <div>
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="lastName" placeholder="Last Name" value="<?= htmlspecialchars($lastName) ?>">
                                <span class="spanWarning"><?= $lstNameMsg ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
                            </div>
                            <div>
                                <label for="birthday">Birthday</label>
                                <input type="date" id="birthday" name="birthday">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="Phone Number" value="<?= htmlspecialchars($phone) ?>">
                                <span class="spanWarning"><?= $phoneMsg ?></span>
                            </div>
                            <div>
                                <label for="type">Type</label>
                                <input type="text" id="type" name="type" value="<?= htmlspecialchars($userType) ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label for="pass">Password</label>
                                <input type="password" id="pass" name="pass" placeholder="Password">
                                <span class="spanWarning"><?= $passMsg ?></span>
                            </div>
                            <div>
                                <label for="conPass">Confirm Password</label>
                                <input type="password" id="conPass" name="conPass" placeholder="Confirm Password">
                                <span class="spanWarning"><?= $conpassMsg ?></span>
                            </div>
                        </div>

                        <button type="submit" name="action" value="next">Next ►</button>
                        <button type="button" value="goBack" onclick="window.location.href='signIn.php?reset=true';">◄ Go Back</button>
                    </form>
         <div>
</body>
</html>
