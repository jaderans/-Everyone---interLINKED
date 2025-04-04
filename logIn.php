<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>interLINKED</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>
<?php
?>
<div class="container">
    <div class="content">
        <h1>Ready to be linked?</h1>
        <p class="credentials">please enter your credentials</p>
        <form class="form"  method="POST">
            <label for="userName"> User name*</label><br>
            <input type="text" id="userName" name="user" required placeholder="User Name"><br>

            <label for="userEmail"> Email*</label><br>
            <input type="text" id="userEmail" name="email" required placeholder="Email"><br>

            <label for="userPass"> Password*</label><br>
            <input type="password" id="userPass" name="pass" required placeholder="Password"><br>

            <label for="clientType"> Are you a...</label><br>
            <select name="type" id="clientType">
                <option value="Client">Client</option>
                <option value="Freelancer">Freelancer</option>
                <option value="Admin">Admin</option>
            </select> <br><br>

            <input type="submit" value="Log In"><br>
            <input type="submit" value="Sign In">
        </form>
    </div>
</div>




</body>
</html>


