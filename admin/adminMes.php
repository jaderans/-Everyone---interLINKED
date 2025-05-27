<?php
session_start();
include('interlinkedDB.php');
$conn = connectToDatabase();
include_once 'checkIfSet.php';
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);


// Default search
$search = $_GET['search'] ?? '';
$userName = $_SESSION['userName'];
$id = $_SESSION['user_id'];


$userName = $_SESSION['userName'];
$stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_ID = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_NAME'] = $res['USER_NAME'];
}

$userNameProfile = $_SESSION['USER_NAME'];


function fetchUsers($conn, $type, $search = '') {
    // Determine if we want applicants or non-applicants
    $typeCondition = $type === 'Applicant' ? "USER_TYPE = 'Applicant'" : "USER_TYPE != 'Applicant'";

    // Start building the query
    $query = "SELECT * FROM user WHERE $typeCondition";

    // Add search filter if provided
    if (!empty($search)) {
        $query .= " AND (USER_FSTNAME LIKE :search OR USER_LSTNAME LIKE :search OR USER_EMAIL LIKE :search)";
    }

    $stmt = $conn->prepare($query);

    // Bind parameters
    if (!empty($search)) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }

    return $stmt;
}

$selectedUser = null;

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE USER_ID = ?");
    $stmt->execute([$_GET['id']]);
    $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

$usersResult = fetchUsers($conn, 'User', $search);
$applicantsResult = fetchUsers($conn, 'Applicant', $search);

$name = $_SESSION['userName'];


//-------------------------------------------------------------------------Message
$filter = $_GET['filter'] ?? 'received';

$stmt = $slave_con->prepare("SELECT * FROM email WHERE EM_RECIPIENT_ID = ?");
$stmt->execute([$id]);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($res as $ressult) {
    $_SESSION['EM_RECIPIENT_ID'] = $ressult['EM_RECIPIENT_ID'];
}
$resShow = $_SESSION['EM_RECIPIENT_ID'];

if ($filter === 'sent') {
    $stmt = $slave_con->prepare("
        SELECT email.*, user.USER_NAME
        FROM email
        INNER JOIN user ON email.EM_RECEPIENT = user.USER_NAME
        WHERE email.USER_ID = :user_id
        ORDER BY email.EM_ID DESC
    ");
    $stmt->execute(['user_id' => $id]);
} else {
    $stmt = $slave_con->prepare("
        SELECT email.*, user.USER_NAME
        FROM email
        INNER JOIN user ON email.USER_ID = user.USER_ID
        WHERE email.EM_RECIPIENT_ID = :user
        ORDER BY email.EM_ID DESC
    ");
    $stmt->execute(['user' => $resShow]);
}
$result = $stmt->fetchAll();



$inputRecipient = $inputSubject = $inputMessage = '';
$error = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = $_POST['keyword'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];


    $hasError = false;

    if (empty($recipient)) {
        $error[] = "Recipient name is required";
        $hasError = true;
    }

    $stmt = $slave_con->prepare("SELECT * FROM `user` WHERE USER_NAME = :recipient");
    $stmt->execute(['recipient' => $recipient]);
    $recipientUser = $stmt->fetchAll();

    foreach ($recipientUser as $res) {
        $_SESSION['RES_ID'] = $res['USER_ID'];
        $_SESSION['RES_NAME'] = $res['USER_NAME'];
    }
    $resID = $_SESSION['RES_ID'];
    $resName = $_SESSION['RES_NAME'];

    if (!$recipientUser) {
        $error[] = "User does not exist";
        $hasError = true;
    }

    if (empty($subject)) {
        $error[] = "Subject is required";
        $hasError = true;
    }
    if (empty($message)) {
        $error[] = "Message is required";
        $hasError = true;
    }

    if ($hasError) {
        $inputRecipient = htmlspecialchars($recipient);
        $inputSubject = htmlspecialchars($subject);
        $inputMessage = htmlspecialchars($message);
    } else {
        $stmt = $master_con->prepare("INSERT INTO email(USER_ID, EM_SUBJECT, EM_COMP, EM_RECEPIENT, EM_DATE, EM_STATUS, EM_RECIPIENT_ID)
        VALUES (:user_id, :em_subject, :em_comp, :em_recipient, CURRENT_TIMESTAMP, 'Unread',:resUser)");
        $stmt->bindParam(':user_id', $id);
        $stmt->bindParam(':em_subject', $subject);
        $stmt->bindParam(':em_comp', $message);
        $stmt->bindParam(':em_recipient', $recipient);
        $stmt->bindParam(':resUser', $resID);
        $result = $stmt->execute();


        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | interLINKED</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="email.css">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFaviconwhite.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="topvar2">
            <div class="logo">
                <img src="../../imgs/inl2LogoWhite.png" alt="Logo">
            </div>
        </div>
        <ul class="side-content">
            <li><a href="adminDash.php"><i class="fas fa-database"></i> Dashboard</a></li>
            <li><a href="adminProj.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
            <li><a href="adminPay.php"><i class="fas fa-dollar-sign"></i> Salary</a></li>
            <li><a href="adminUser.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="adminNotif.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="adminMes.php"><i class="fas fa-envelope"></i> Message</a></li>
            <li><a href="adminProf.php"><i class="fas fa-user"></i> Profile</a></li>
            <div class="btm-content">
                <button class="logout-button" onclick="window.location.href='../loginSignup/logIn.php';">
                    <i class="fas fa-sign-out"></i> Log Out
                </button>
            </div>
        </ul>
    </div>

    <!-- Navbar -->
    <div class="navbar">
        <div class="topvar">
            <div class="navtitle">
                <h1>Admin | </h1>
                <p>MESSAGES</p>
            </div>
            <div class="navprofile">
                <div class="name">
                    <h4><?=$name?></h4>
                </div>
                <div class="profile">
                    <img src="../../imgs/profile.png" alt="Admin Profile">
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="email">
            <div class="messages">
                <h1>Messages</h1>
<!--                <p>--><?php //=$id?><!--</p>-->
                <div class="message-filter">
                    <form method="get" action="">
                        <button type="submit" name="filter" value="received" class="btn-edit">Received</button>
                        <button type="submit" name="filter" value="sent" class="btn-edit">Sent</button>
                    </form>

                </div>

                <div class="message-content">
                    <?php foreach ($result as $res) {?>
                        <div class="msg">
                            <h4><?= $filter === 'sent' ? 'To' : 'From' ?>: <?=$res['USER_NAME']?></h4>
                            <h4>Subject: <?=$res['EM_SUBJECT']?></h4>
<!--                            <h4>Test ID: --><?php //=$res['EM_ID']?><!--</h4>-->
                            <p>Message: <?=$res['EM_COMP']?></p>
                            <p><?=$res['EM_DATE']?></p>

                            <form action="admin-delete-message.php" method="post" class="form-delete"
                                  onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                <button class="btn-delete" name="message-id" value="<?= $res['EM_ID'] ?>"><i class="fa-solid fa-trash fa-1.5xl" <i class="fa-solid fa-trash" style="color: #9f3535;"></i></i></button>
                            </form>
                        </div>
                    <?php }?>

                </div>
            </div>
            <div class="compose">
                <h1>Compose</h1>
                <div class="message-area">
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" class="message-form" enctype="multipart/form-data">
                        <div class="info">
                            <label for="">To </label>
                            <input type="text" name="keyword" placeholder="Admin" onkeyup="search(this.value)" value="<?= htmlspecialchars($inputRecipient) ?>" autocomplete="off"><br>
                            <label for="">Subject </label>
                            <input type="text" name="subject" placeholder="Add Subject" value="<?= htmlspecialchars($inputSubject) ?>" autocomplete="off"><br>
                            <div id="search-results" class="result"></div>
                        </div>


                        <div class="type">
                            <label for="">Message</label>
                            <textarea name="message" placeholder="Type here..."><?= htmlspecialchars($inputMessage) ?></textarea><br>
                        </div>


                        <div class="info">
                            <button class="send" type="submit" name="action" value="login"><i class="fa-regular fa-paper-plane"></i> Send</button>
                            <span style="color: red">
                                <?php
                                foreach ($error as $error) {
                                    echo $error . "<br>";
                                }
                                ?>
                            </span>

                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>


    <script>
        function search(input) {
            const resultBox = document.getElementById("search-results");

            if (input.length === 0) {
                resultBox.style.display = "none"; // Hide if empty
                resultBox.innerHTML = "";
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    if (this.responseText.trim() !== "") {
                        resultBox.innerHTML = this.responseText;
                        resultBox.style.display = "block"; // Show if has content
                    } else {
                        resultBox.innerHTML = "No results found.";
                        resultBox.style.display = "block";
                    }
                }
            };

            xhr.open("GET", "search.php?keyword=" + encodeURIComponent(input), true);
            xhr.send();
        }


        function selectUser(userName) {
            document.querySelector('input[name="keyword"]').value = userName;
            document.getElementById("search-results").innerHTML = "";
            document.getElementById("search-results").style.display = "none";
        }
    </script>
</body>
</html>
