<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Bio</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="authenticate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
<?php
?>

<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>
<div class="container">
    <p class="subtext">6/8</p>
    <h1>Write a short bio for your profile.</h1>
    <p class="subtext">Tell us a bit about yourself.</p>
    <div class="category-specialty-container">
        <div class="bio-field">
            <label for="bio">Bio for your profile</label>
            <textarea id="bio" name="bio" class="jobTitle" placeholder="Write something about yourself..." maxlength="450" rows="10" required></textarea>
        </div>
    </div>
</div>


<div class="botvar">
    <div class="botconrow">
        <input type="submit" name="action" value="Back">
        <input type="submit" name="action" value="Next">
    </div>

</div>

</body>
</html>
