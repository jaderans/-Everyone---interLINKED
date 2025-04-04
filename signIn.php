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
            <input type="text" id="userEmail" name="email" placeholder="Email"><br>

            <label for="clientType"> Are you a...</label><br>
            <select name="type" id="clientType">
                <option value="Client">Client</option>
                <option value="Freelancer">Freelancer</option>
                <option value="Admin">Admin</option>
            </select> <br><br>

            <button type="submit" name="action" value="signIn">Sign In</button>
            <button type="submit" name="action" value="goBack">◄ Go Back</button>
        </form>
    </div>
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {

        // Go back should not depend on filled input
        if ($_POST['action'] == "goBack") {
            header("Location: logIn.php");
            exit();
        }

        // Only validate inputs if the user is signing in
        if ($_POST['action'] == "signIn") {
            if (!empty($_POST['email']) && !empty($_POST['type'])) {
                $userType = $_POST['type'];

                if ($userType == "Client") {
                    header("Location: FormSignClient.php");
                } elseif ($userType == "Freelancer") {
                    header("Location: FormSignFreelancer.php");
                } elseif ($userType == "Admin") {
                    header("Location: FormSignAdmin.php");
                }
                exit();
            }
        }
    }
}
?>

</body>
</html>
