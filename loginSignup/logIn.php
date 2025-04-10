<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>interLINKED</title>
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>

<?php
$nameMsg = $emailMsg = $passMsg = "";
$userName = $email = $pass = $userType = "";

function clean_text($text) {
    return htmlspecialchars(trim($text));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userName = clean_text($_POST['user']);
    $email = clean_text($_POST['email']);
    $userType = clean_text($_POST['type']);
    $password = clean_text($_POST['pass']);

    if (isset($_POST['action'])) {
        if ($_POST['action'] == "Log In") {
            if (empty($userName)) {
                $nameMsg = "Username is required <br>";
            }

            if (empty($email)) {
                $emailMsg = "Email is required <br>";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailMsg = "Input a valid email address <br>";
            }

            if (empty($password)) {
                $passMsg = "Password is required <br>";
            }

            if (empty($nameMsg) && empty($emailMsg) && empty($passMsg) && !empty($userType)) {
                if ($userType == "Client") {
                    header("Location: clientHome.php");
                } elseif ($userType == "Freelancer") {
                    header("Location: ../freelancer/freelancer-dashboard-page.php");
                } elseif ($userType == "Admin") {
                    header("Location: AdminDash.php");
                }
                exit();
            }
        } elseif ($_POST['action'] == "Sign In") {
            header("Location: signIn.php");
            exit();
        }
    }
}
?>

<div>
    <div class="rectangle"></div>
    <div class="bgimage"></div>
    <div class="logInTitle">
        <p>WHERE CLIENTS MEET TALENT,</p>
        <h1>EFFORTLESSLY!</h1>
    </div>
</div>

<div class="container">
    <div class="content">
        <h1>Welcome back</h1>
        <p class="credentials">please enter your credentials</p>
        <form class="form" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <label for="userName">Username* </label><br>
            <input type="text" id="userName" name="user" placeholder="Username" value="<?= $userName ?>"><br>
            <span class="spanWarning "><?= $nameMsg ?></span>

            <label for="userEmail">Email*</label><br>
            <input type="text" id="userEmail" name="email" placeholder="Email" value="<?= $email ?>"><br>
            <span class="spanWarning"><?= $emailMsg ?></span>

            <label for="userPass">Password*</label><br>
            <input type="password" id="userPass" name="pass" placeholder="Password"><br>
            <span class="spanWarning"><?= $passMsg ?></span>

            <label for="clientType">Are you a...</label> <br>
            <select name="type" id="clientType">
                <option value="Client" <?= $userType == "Client" ? 'selected' : '' ?>>Client</option>
                <option value="Freelancer" <?= $userType == "Freelancer" ? 'selected' : '' ?>>Freelancer</option>
                <option value="Admin" <?= $userType == "Admin" ? 'selected' : '' ?>>Admin</option>
            </select><br><br>

            <input type="submit" name="action" value="Log In">
            <input type="submit" name="action" value="Sign In">
        </form>
    </div>
</div>

<div>
    <img class="imageHeader" alt="headerTitle" src="../imgs/inl2Logo.png">
</div>

</body>
</html>
