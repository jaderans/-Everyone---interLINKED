<?php
session_start();
include('interlinkedDB.php');
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$error = [];
$saved = [];

function clean_text($data) {
    return htmlspecialchars(trim($data));
}

if (isset($_POST['user_id']) && !isset($_POST['action'])) {
    // FIRST time clicking Edit
    $id = $_POST['user_id'];
    $_SESSION['user_id'] = $id;

    $stmt = $slave_con->prepare("SELECT * FROM user WHERE user_id = :id");
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

} elseif (isset($_POST['action']) && $_POST['action'] == "update") {
    // When clicking UPDATE
    $id = $_SESSION['user_id'] ?? null;

    if ($id !== null) {
        $userName = clean_text($_POST["userName"] ?? '');
        $firstName = clean_text($_POST["firstName"] ?? '');
        $lastName = clean_text($_POST["lastName"] ?? '');
        $email = clean_text($_POST["email"] ?? '');
        $bday = clean_text($_POST["bDay"] ?? '');
        $phone = clean_text($_POST["phone"] ?? '');
        $type = clean_text($_POST["type"] ?? '');
        $pass = trim($_POST["pass"] ?? '');
        $conPass = trim($_POST["conPass"] ?? '');
        $oldPass = trim($_POST["oldPass"] ?? '');
        $img = null;
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            // Validate file type (optional)
            $fileTmpPath = $_FILES['img']['tmp_name'];
            $fileType = mime_content_type($fileTmpPath);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($fileType, $allowedTypes)) {
                // Read file content as binary
                $img = file_get_contents($fileTmpPath);
            } else {
                $error[] = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
                $hasError = true;
            }
        }


        $hasError = false;

        // Validation
        if (empty($userName)) {
            $error[] = "Username cannot be empty.<br>";
            $hasError = true;
        } else {
            // Check if username exists for another user
            $stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_NAME = :userName AND USER_ID != :id");
            $stmt->execute(['userName' => $userName, 'id' => $id]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $error[] = "A user already exists with this username.";
                $hasError = true;
            }
        }

        if (empty($firstName)) {
            $error[] = "First Name cannot be empty.<br>";
            $hasError = true;
        }
        if (empty($lastName)) {
            $error[] = "Last Name cannot be empty.<br>";
            $hasError = true;
        }
        if (empty($email)) {
            $error[] = "Email cannot be empty.<br>";
            $hasError = true;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = "Invalid email format.<br>";
            $hasError = true;
        } else{
            $stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_EMAIL = :email AND USER_ID != :id");
            $stmt->execute(['email' => $email, 'id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $error[] = "A user already exists with this email.";
                $hasError = true;
            }
        }
        if (empty($bday)) {
            $error[] = "Birthday cannot be empty.<br>";
            $hasError = true;
        }

        if (empty($oldPass)) {
            $error[] = "Old Password cannot be empty.<br>";
            $hasError = true;
        }

        if (empty($phone)) {
            $error[] = "Phone number cannot be empty.<br>";
            $hasError = true;
        } else {
            // Check if phone number is valid (only digits, optional + at start, and 10-15 digits length)
            if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
                $error[] = "Please enter a valid phone number (only digits, 10-15 characters, optional + at start).<br>";
                $hasError = true;
            } else {
                // Check if phone number already exists for another user
                $stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_CONTACT = :phone AND USER_ID != :id");
                $stmt->execute(['phone' => $phone, 'id' => $id]);
                $existingPhone = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingPhone) {
                    $error[] = "A user already exists with this phone number.";
                    $hasError = true;
                }
            }
        }



        // Fetch current user data
        $stmtCurrent = $slave_con->prepare("SELECT * FROM user WHERE user_id = :id");
        $stmtCurrent->execute(['id' => $id]);
        $currentUser = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
        $redirectToLogin = false;

        if (!empty($pass) || !empty($conPass) || !empty($oldPass)) {
            if (empty($oldPass)) {
                $error[] = "Old Password is required to change your password.<br>";
                $hasError = true;
            } elseif ($pass !== $conPass) {
                $error[] = "Passwords do not match.<br>";
                $hasError = true;
            } elseif ($currentUser['USER_PASSWORD'] !== $oldPass) {
                $error[] = "Old password is incorrect.<br>";
                $hasError = true;
            }
        }


        if (!$hasError) {
            // Check if username was changed
            if ($currentUser && $userName !== $currentUser['USER_NAME']) {
                $redirectToLogin = true;
            }

            // Update basic details
            $stmt = $master_con->prepare("UPDATE `user`
            SET USER_NAME = :userName, 
                USER_FSTNAME = :firstName, 
                USER_LSTNAME = :lastName, 
                USER_BIRTHDAY = :bday, 
                USER_CONTACT = :contact, 
                USER_EMAIL = :email,
                USER_TYPE = :type,
                USER_IMG = COALESCE(:image, USER_IMG)
            WHERE USER_ID = :id");

            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':firstName', $firstName);
            $stmt->bindParam(':lastName', $lastName);
            $stmt->bindParam(':bday', $bday);
            $stmt->bindParam(':contact', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':id', $id);

            if ($img !== null) {
                $stmt->bindParam(':image', $img, PDO::PARAM_LOB);
            } else {
                // Bind NULL to :image to keep existing image in DB
                $null = null;
                $stmt->bindParam(':image', $null, PDO::PARAM_NULL);
            }
            $stmt->execute();


            // Password update if requested
            if (!empty($pass) && !empty($oldPass)) {
                if ($pass !== $conPass) {
                    $error[] = "Passwords do not match.<br>";
                } else {
                    // Check if old password is correct
                    if ($currentUser && $currentUser['USER_PASSWORD'] !== $oldPass) {
                        $error[] = "Old password is incorrect.<br>";
                    } else {
                        // Proceed with updating the password
                        $stmtPass = $master_con->prepare("UPDATE `user` SET USER_PASSWORD = :pass WHERE USER_ID = :id");
                        $stmtPass->bindParam(':pass', $pass);
                        $stmtPass->bindParam(':id', $id);
                        $stmtPass->execute();

                        $redirectToLogin = true;
                    }
                }
            }

            if (empty($error)) {
                if ($redirectToLogin) {
                    header('Location: ../loginSignUp/login.php');
                    exit();
                } else {
                    // Stay on the page after update if no need to re-login
                    $saved[] = "Changes Saved Successfully!";
                }
            }
        }

        // Reload user data to refill form if errors
        $stmt = $slave_con->prepare("SELECT * FROM user WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | INTERLINKED</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="freelancer-FormSignStyle.css">
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
        <h1>Edit Profile</h1>
        <p class="credentials">Please edit your credentials</p>
        <div class="form-container">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                <div class="form-group">
                    <div>
                        <label for="file">Update Profile</label>
                        <input class="attach" type="file" id="img" name="img" accept="image/jpg">
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="userName">User Name</label>
                        <input type="text" id="userName" name="userName" value="<?= $result['USER_NAME'] ?>">
                    </div>
                    <div>
                        <label for="type">Type</label>
                        <input type="text" id="type" name="type" value="<?= $result['USER_TYPE']?>" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" value="<?= $result['USER_FSTNAME']?>" >
                    </div>
                    <div>
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="<?= $result['USER_LSTNAME'] ?>" >
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= $result['USER_EMAIL'] ?>" >
                    </div>
                    <div>
                        <label for="bDay">Birthday</label>
                        <input type="date" id="bDay" name="bDay" value="<?= $result['USER_BIRTHDAY'] ?>" >
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?= $result['USER_CONTACT'] ?>" >
                    </div>
                    <div>
                        <label for="oldPass">Old Password*</label>
                        <input type="password" id="oldPass" name="oldPass" placeholder="Old Password">
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <div>
                        <label for="newPass">Change Password</label>
                        <input type="password" id="newPass" name="pass" placeholder="New Password (leave blank if no change)">
                    </div>
                    <div>
                        <label for="conPass">Confirm Password</label>
                        <input type="password" id="conPass" name="conPass" placeholder="Confirm New Password">
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" name="action" value="update">Update</button>
                    <button type="button" onclick="window.location.href='freelancer-profile-page.php';" value="goBack">◄ Go Back</button><br>
                    <span style="color: red"><?php
                        foreach ($error as $errorMsg) {
                            echo $errorMsg;
                        }
                        ?></span>
                    <span style="color: #8cb660"><?php
                        foreach ($saved as $saveMsg) {
                            echo $saveMsg;
                        }
                        ?></span>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
