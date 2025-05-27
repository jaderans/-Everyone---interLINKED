<?php
session_start();
require('../applicant/db_config.php');

function clean_text($text) {
    return htmlspecialchars(trim($text));
}

// Get session/user data
$userName = $_SESSION['userName'] ?? '';
$userType = $_SESSION['type'] ?? '';
$password = isset($_POST['pass']) ? clean_text($_POST['pass']) : '';
$confirmPassword = isset($_POST['conPass']) ? clean_text($_POST['conPass']) : '';
$email = $_POST['email'] ?? '';
$phone = isset($_POST['phone']) ? clean_text($_POST['phone']) : '';
$firstName = isset($_POST['firstName']) ? clean_text($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? clean_text($_POST['lastName']) : '';
$birthday = isset($_POST['bDay']) ? clean_text($_POST['bDay']) : '';

$fstNameMsg = $lstNameMsg = $emailMsg = $passMsg = $conpassMsg = $phoneMsg = $userErr = $bdayMsg = "";

if (isset($_GET['reset'])) {
    session_unset();
    session_destroy();
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $_SESSION["email"] = $email;
    $_SESSION["type"] = $_POST['type'] ?? $userType;
    $userType = $_SESSION["type"];

    if ($_POST['action'] == "next") {
        if (empty($firstName)) $fstNameMsg = "First name is required <br>";
        if (empty($lastName)) $lstNameMsg = "Last name is required <br>";

        if (empty($email)) {
            $emailMsg = "Email is required <br>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailMsg = "Invalid email format <br>";
        } else {
            $stmt = $slave_con->prepare("SELECT 1 FROM USER WHERE USER_EMAIL = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $emailMsg = "Email already exists.";
            }
        }

        if (empty($password)) {
            $passMsg = "Password is required.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $passMsg = "Password must be at least 8 characters, including upper, lower, number, and special character.";
        }

        if (empty($confirmPassword)) {
            $conpassMsg = "Please confirm your password <br>";
        } elseif ($password !== $confirmPassword) {
            $conpassMsg = "Passwords do not match <br>";
        }

        if (empty($birthday)) {
            $bdayMsg = "Birthday is required <br>";
        }

        if (empty($phone)) {
            $phoneMsg = "Phone number is required.";
        } elseif (preg_match('/[a-zA-Z]/', $phone)) {
            $phoneMsg = "Phone number must not contain letters.";
        } elseif (!preg_match('/^09\d{9}$/', $phone)) {
            $phoneMsg = "Enter a valid phone number starting with 09";
        }
        if (empty($fstNameMsg) && empty($lstNameMsg) && empty($passMsg) && empty($conpassMsg) && empty($phoneMsg) && empty($bdayMsg)) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $birthday = date("Y-m-d", strtotime($birthday));

                // Store all data in session
                $_SESSION['application_data'] = [
                    'user_email' => $email,
                    'user_type' => $userType,
                    'user_name' => $userName,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'birthday' => $birthday,
                    'contact' => $phone,
                    'password' => $hashedPassword
                ];

                // Redirect to categories
                if ($userType === "Client") {
                    header("Location: ../client/clientHome.php");
                } elseif ($userType === "Freelancer") {
                    header("Location: ../applicant/categories.php");
                } else {
                    header("Location: FormSignUser.php");
                }
                exit;
            } catch (Exception $e) {
                $userErr = "Error: " . $e->getMessage();
            }
        }
    }
}
$_SESSION['application_data']['user_email'] = $email;
$_SESSION['application_data']['user_type'] = $userType;
$_SESSION['application_data']['user_name'] = $userName;
$_SESSION['application_data']['first_name'] = $firstName;
$_SESSION['application_data']['last_name'] = $lastName;
$_SESSION['application_data']['birthday'] = $birthday;
$_SESSION['application_data']['contact'] = $phone;

?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | INTERLINKED</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="FormSignStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
<div class="rectangle2"></div>
<div class="rectangle3"></div>

<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>

<div class="container2">
    <div class="content2">
        <h1>Register</h1>
        <p class="credentials">Please enter your credentials</p>
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
                        <input type="email" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
                        <span class="spanWarning"><?= $emailMsg ?></span>
                    </div>
                    <div>
                        <label for="birthday">Birthday</label>
                        <input type="date" id="bDay" name="bDay">
                        <span class="spanWarning"><?= $bdayMsg ?></span>
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

                <div class="form-buttons">
                    <button type="submit" name="action" value="next">Sign In</button>
                    <button type="button" value="goBack" onclick="window.location.href='signIn.php?reset=true';">◄ Go Back</button>
                    <span class="spanWarning"><?= $userErr ?></span>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
