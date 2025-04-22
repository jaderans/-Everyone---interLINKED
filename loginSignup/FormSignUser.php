<?php
session_start();
include('interlinkedDB.php');

function clean_text($text) {
    return htmlspecialchars(trim($text));
}
$userName = isset($_SESSION['userName']) ? $_SESSION['userName'] : '';
$userType = isset($_SESSION['type']) ? $_SESSION['type'] : '';
$password = isset($_POST['pass']) ? clean_text($_POST['pass']) : '';
$confirmPassword = isset($_POST['conPass']) ? clean_text($_POST['conPass']) : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
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
    $_SESSION["type"] = isset($_POST['type']) ? clean_text($_POST['type']) : $userType;
    $userType = $_SESSION["type"];

    if ($_POST['action'] == "next") {
        if (empty($firstName)) {
            $fstNameMsg = "First name is required <br>";
        }
        if (empty($lastName)) {
            $lstNameMsg = "Last name is required <br>";
        }

        if (empty($email)) {
            $emailMsg = "Email is required <br>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailMsg = "Input a valid email address <br>";
        } else {
            $stmt = $conn->prepare("SELECT * FROM user WHERE USER_EMAIL = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $emailMsg = "A user already exists with this email.";
            }
        }

        if (empty($password)) {
            $passMsg = "Password is required.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $passMsg = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
        }

        if (empty($confirmPassword)) {
            $conpassMsg = "Confirm your password <br>";
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

//                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // secure password storage

            $userIdPrefix = "25-INTL-";

            $query = "SELECT USER_ID FROM USER WHERE USER_ID LIKE ? ORDER BY CAST(SUBSTRING_INDEX(USER_ID, '-', -1) AS UNSIGNED) DESC LIMIT 1";
            if ($stmt = $conn->prepare($query)) {
                $likePattern = $userIdPrefix . "%";
                $stmt->bind_param("s", $likePattern);
                $stmt->execute();
                $stmt->bind_result($lastUserId);
                $stmt->fetch();
                $stmt->close();

                if ($lastUserId) {
                    $numericPart = (int) substr($lastUserId, strrpos($lastUserId, '-') + 1);
                    $newUserIdNumber = $numericPart + 1;
                } else {
                    $newUserIdNumber = 1;
                }
            } else {
                $newUserIdNumber = 1;
            }

            do {
                $formattedNumber = str_pad($newUserIdNumber, 5, '0', STR_PAD_LEFT);
                $userId = $userIdPrefix . $formattedNumber;

                $checkQuery = "SELECT USER_ID FROM USER WHERE USER_ID = ?";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param("s", $userId);
                $checkStmt->execute();
                $checkStmt->store_result();

                $exists = $checkStmt->num_rows > 0;
                $checkStmt->close();

                if ($exists) {
                    $newUserIdNumber++;
                }
            } while ($exists);


            $birthday = date("Y/m/d", strtotime($birthday));

            $sql = "INSERT INTO USER (USER_ID, USER_EMAIL, USER_TYPE, USER_FSTNAME, USER_LSTNAME, USER_BIRTHDAY, USER_CONTACT, USER_PASSWORD, USER_NAME) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssssssss", $userId, $email, $userType, $firstName, $lastName, $birthday, $phone, $password, $userName);

                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $userId;

                    // Generate type-based ID for specific table
                    $typePrefixes = [
                        "Client" => "CL25-",
                        "Freelancer" => "FR25-"
                    ];
                    $typeIdPrefix = $typePrefixes[$userType];

                    $typeTables = [
                        "Client" => ["table" => "CLIENT", "column" => "CL_ID"],
                        "Freelancer" => ["table" => "FREELANCER", "column" => "FR_ID"]
                    ];

                    $typeTable = $typeTables[$userType]["table"];
                    $typeColumn = $typeTables[$userType]["column"];

                    $query = "SELECT $typeColumn FROM $typeTable WHERE $typeColumn LIKE ? ORDER BY CAST(SUBSTRING_INDEX($typeColumn, '-', -1) AS UNSIGNED) DESC LIMIT 1";
                    if ($stmt = $conn->prepare($query)) {
                        $likePattern = $typeIdPrefix . "%";
                        $stmt->bind_param("s", $likePattern);
                        $stmt->execute();
                        $stmt->bind_result($lastTypeId);
                        $stmt->fetch();
                        $stmt->close();

                        if ($lastTypeId) {
                            $numericPart = (int) substr($lastTypeId, strrpos($lastTypeId, '-') + 1);
                            $newTypeIdNumber = $numericPart + 1;
                        } else {
                            $newTypeIdNumber = 1;
                        }
                    } else {
                        $newTypeIdNumber = 1;
                    }

                    $formattedNumber = str_pad($newTypeIdNumber, 5, '0', STR_PAD_LEFT);
                    $typeUserId = $typeIdPrefix . $formattedNumber;


                    if ($userType === "Client") {
                        $sql = "INSERT INTO CLIENT(CL_ID, USER_ID) VALUES (?, ?)";
                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("ss", $typeUserId, $userId);
                            $stmt->execute();
                            $stmt->close();
                        }
                        header("Location: ../client/clientHome.php");
                        exit();
                    } elseif ($userType === "Freelancer") {
                        $sql = "INSERT INTO FREELANCER(FR_ID, USER_ID) VALUES (?, ?)";
                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("ss", $typeUserId, $userId);
                            $stmt->execute();
                            $stmt->close();
                        }
                        header("Location: ../freelancer/freelancer-dashboard-page.php");
                        exit();
                    } else {
                        header("Location: FormSignUser.php");
                    }
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
