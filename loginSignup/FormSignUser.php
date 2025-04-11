<?php
session_start();
include('interlinkedDB.php');

function clean_text($text) {
    return htmlspecialchars(trim($text));
}

$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$userType = isset($_SESSION['type']) ? $_SESSION['type'] : '';
$password = isset($_POST['pass']) ? clean_text($_POST['pass']) : '';
$confirmPassword = isset($_POST['conPass']) ? clean_text($_POST['conPass']) : '';
$phone = isset($_POST['phone']) ? clean_text($_POST['phone']) : '';
$firstName = isset($_POST['firstName']) ? clean_text($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? clean_text($_POST['lastName']) : '';
$birthday = isset($_POST['bDay']) ? clean_text($_POST['bDay']) : '';

$fstNameMsg = $lstNameMsg = $passMsg = $conpassMsg = $phoneMsg = $userErr = $bdayMsg = "";

if (isset($_GET['reset'])) {
    session_unset();
    session_destroy();
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    $_SESSION["email"] = $email;
    $_SESSION["type"] = isset($_POST['type']) ? clean_text($_POST['type']) : $userType;
    $userType = $_SESSION["type"];

    if ($_POST['action'] == "next") {
        if (empty($firstName)) {
            $fstNameMsg = "First name is required <br>";
        }
        if (empty($lastName)) {
            $lstNameMsg = "Last name is required <br>";
        }

        if (empty($password)) {
            $passMsg = "Password is required <br>";}
////        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{1,}$/', $password)) {
//            $passMsg = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character <br>";
//        }

        if (empty($confirmPassword)) {
            $conpassMsg = "Confirm your password <br>";
        } elseif ($password !== $confirmPassword) {
            $conpassMsg = "Passwords do not match <br>";
        }

        if (empty($birthday)) {
            $bdayMsg = "Birthday is required <br>";
        }

        if (empty($phone)) {
            $phoneMsg = "Phone number is required <br>";}
//        elseif (!preg_match('/^\+?\d{11}$/', $phone)) {
//            $phoneMsg = "Enter a valid phone number (11 digits, optional +) <br>";
//        }

        if (empty($fstNameMsg) && empty($lstNameMsg) && empty($passMsg) && empty($conpassMsg) && empty($phoneMsg) && empty($bdayMsg)) {

            // Generate user ID
            $prefix = [
                "Admin" => "AD",
                "Client" => "CL",
                "Freelancer" => "FR"
            ];
            $userIdPrefix = $prefix[$userType] ?? "US";

            $query = "SELECT USER_ID FROM USER WHERE USER_TYPE = ? ORDER BY USER_ID DESC LIMIT 1";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("s", $userType);
                $stmt->execute();
                $stmt->bind_result($lastUserId);
                $stmt->fetch();
                $stmt->close();

                // Extract the numeric part from USER_ID after the prefix
                if ($lastUserId) {
                    $numericPart = (int) filter_var($lastUserId, FILTER_SANITIZE_NUMBER_INT);
                    $newUserIdNumber = $numericPart + 1;
                } else {
                    $newUserIdNumber = 250000;
                }

                $userId = $userIdPrefix . $newUserIdNumber;
            } else {
                $userId = $userIdPrefix . '250000';
            }

            $birthday = date("Y/m/d", strtotime($birthday));

            $sql = "INSERT INTO USER (USER_ID, USER_EMAIL, USER_TYPE, USER_FSTNAME, USER_LSTNAME, USER_BIRTHDAY, USER_CONTACT, USER_PASSWORD) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // secure password storage

                $stmt->bind_param("ssssssss", $userId, $email, $userType, $firstName, $lastName, $birthday, $phone, $hashedPassword);

                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $userId; // optional: store logged-in user
                    if ($userType === "Client") {
                        header("Location: loginSignup/index.php");
                    } elseif ($userType === "Admin") {
                        header("Location: loginSignup/index.php");
                    } elseif ($userType === "Freelancer") {
                        header("Location: loginSignup/index.php");
                    } else {
                        header("Location: FormSignUser.php");
                    }
                    exit();
                } else {
                    $userErr = "Something went wrong while creating your account.";
                }
                $stmt->close();
            }
        }
    }
}
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
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
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
