<?php
include('interlinkedDB.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE USER_ID = $id");
    $user = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['USER_ID'];
    $fname = $_POST['USER_FSTNAME'];
    $lname = $_POST['USER_LSTNAME'];
    $email = $_POST['USER_EMAIL'];
    $type = $_POST['USER_TYPE'];
    $contact = $_POST['USER_CONTACT'];
    $birthday = $_POST['USER_BIRTHDAY'];
    $username = $_POST['USER_NAME'];

    $updateQuery = "UPDATE user SET 
        USER_FSTNAME='$fname',
        USER_LSTNAME='$lname',
        USER_EMAIL='$email',
        USER_TYPE='$type',
        USER_CONTACT='$contact',
        USER_BIRTHDAY='$birthday',
        USER_NAME='$username'
        WHERE USER_ID=$id";

    mysqli_query($conn, $updateQuery);
    echo "<script>window.location.href='adminUser.php';</script>";
    exit;
}
?>

<form method="POST">
    <input type="hidden" name="USER_ID" value="<?= $user['USER_ID'] ?>">
    <label>First Name:</label><br><input type="text" name="USER_FSTNAME" value="<?= $user['USER_FSTNAME'] ?>"><br>
    <label>Last Name:</label><br><input type="text" name="USER_LSTNAME" value="<?= $user['USER_LSTNAME'] ?>"><br>
    <label>Email:</label><br><input type="email" name="USER_EMAIL" value="<?= $user['USER_EMAIL'] ?>"><br>
    <label>Type:</label><br><input type="text" name="USER_TYPE" value="<?= $user['USER_TYPE'] ?>"><br>
    <label>Contact:</label><br><input type="text" name="USER_CONTACT" value="<?= $user['USER_CONTACT'] ?>"><br>
    <label>Birthday:</label><br><input type="date" name="USER_BIRTHDAY" value="<?= $user['USER_BIRTHDAY'] ?>"><br>
    <label>Username:</label><br><input type="text" name="USER_NAME" value="<?= $user['USER_NAME'] ?>"><br><br>
    <button type="submit">Update</button>
</form>
