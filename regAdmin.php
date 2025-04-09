<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Admin</title>
    <link rel="icon" type="image/x-icon" href="imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="FormSignStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
</head>
<body>

<div class="topvar">
    <div class="logo">
        <img src="imgs/inl2Logo.png" alt="">
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
                                <input type="text" id="firstName" name="firstName" placeholder="First Name">
                            </div>
                            <div>
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="lastName" placeholder="Last Name">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="" placeholder="Email">
                            </div>
                            <div>
                                <label for="birthday">Birthday</label>
                                <input type="date" id="birthday" name="birthday">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="Phone Number">
                            </div>
                            <div>
                                <label for="type">Type</label>
                                <input type="text" id="type" name="type" value="" placeholder="Type">
                            </div>
                        </div>
                        <button type="submit" name="action" value="next">Next ►</button>
                        <button type="button" value="goBack" onclick="window.location.href='signIn.php';">◄ Go Back</button>
                    </form>
                    <div>

</body>
</html>
