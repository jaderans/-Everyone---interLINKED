<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="icon" type="image/x-icon" href="imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>
<div>
    <div class="rectangle2"></div>
</div>

<div class="container2">
    <div class="content2">
        <h1>Ready to be linked? <br> SIGN IN WITH US!</h1>
        <p class="credentials">please enter your credentials</p>
        <form2 class="form"  method="POST">
            <label for="userEmail"> Email*</label><br>
            <input type="text" id="userEmail" name="email" required placeholder="Email"><br>

            <label for="clientType"> Are you a...</label><br>
            <select name="type" id="clientType">
                <option value="Client">Client</option>
                <option value="Freelancer">Freelancer</option>
                <option value="Admin">Admin</option>
            </select> <br><br>

            <input type="submit" value="Sign In">
            <input type="submit" value="◄ Go Back"><br>
        </form2>
    </div>
</div>

<?php

?>

</body>
</html>


