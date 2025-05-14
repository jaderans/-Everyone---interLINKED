<?php
session_start();
include('interlinkedDB.php');

$error = [];

function clean_text($data) {
    return htmlspecialchars(trim($data));
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
        <h1>UPDATE TASK</h1>
        <p class="credentials">Please update your task</p>
        <div class="form-container">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                <div class="form-group">
                    <div>
                        <label for="userId">Project Name</label>
                        <input type="text" id="userId" name="userId" value="" readonly>
                    </div>
                    <div>
                        <label for="userName">Description</label>
                        <input type="text" id="userName" name="userName" value="" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="email">Commissioned By</label>
                        <input type="email" id="email" name="email" value="" readonly>
                    </div>
                    <div>
                        <label for="file">Submit File</label>
                        <input class="attach" type="file" id="file" name="file">
                    </div>
                </div>

                <div class="form-group">
                    <div class="cus-select">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="Ongoing">Ongoing</option>
                            <option value="Drop">Drop</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="cus-select">
                        <label for="urgent">Urgency</label>
                        <select name="urgent" id="urgent">
                            <option value="Moderate">Moderate</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Flexible">Flexible</option>
                        </select>
                    </div>
                </div>



                <div class="form-buttons">
                    <button type="submit" name="action" value="update">Update</button>
                    <button type="button" onclick="window.location.href='freelancer-project-page.php';" value="goBack">◄ Go Back</button><br>
                    <span style="color: red"><?php
                        foreach ($error as $errorMsg) {
                            echo $errorMsg;
                        }
                        ?></span>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
