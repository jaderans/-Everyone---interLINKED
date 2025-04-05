<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>interLINKED</title>
    <link rel="icon" type="image/x-icon" href="imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>

<div >
    <div class="rectangle"></div>
    <div class="bgimage"></div>
    <div class="logInTitle">
        <p>WHERE CLIENTS MEET TALENT,</p>
        <H1>EFFORTLESSLY!</H1>
    </div>
</div>

<div class="container">
    <div class="content">
        <h1>Welcome back</h1>
        <p class="credentials">please enter your credentials</p>
        <form class="form"  method="POST">
            <label for="userName"> Username*</label><br>
            <input type="text" id="userName" name="user" placeholder="Username"><br>

            <label for="userEmail"> Email*</label><br>
            <input type="text" id="userEmail" name="email" placeholder="Email"><br>

            <label for="userPass"> Password*</label><br>
            <input type="password" id="userPass" name="pass" placeholder="Password"><br>

            <label for="clientType"> Are you a...</label><br>
            <select name="type" id="clientType">
                <option value="Client">Client</option>
                <option value="Freelancer">Freelancer</option>
                <option value="Admin">Admin</option>
            </select> <br><br>

            <button type="submit" name="action" value="login">Log In</button>
            <button type="submit" name="action" value="signIn">Sign In</button>
        </form>
    </div>
</div>
<div>
    <img class="imageHeader" alt="headerTitle" src="imgs/inl2Logo.png">
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == "login") {
            // Ensure all required fields are filled
            if (!empty($_POST['user']) && !empty($_POST['email']) && !empty($_POST['pass']) && !empty($_POST['type'])) {
                $userType = $_POST['type']; // Get selected user type

                // Redirect based on user type
                if ($userType == "Client") {
                    header("Location: clientHome.php");
                } elseif ($userType == "Freelancer") {
                    header("Location: freelancer-dashboard-page.php");
                } elseif ($userType == "Admin") {
                    header("Location: AdminDash.php");
                }
                exit();
            }
        } elseif ($_POST['action'] == "signIn") {
            header("Location: signIn.php");
            exit();
        }
    }
}
?>

</body>
</html>


