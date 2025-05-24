<?php
include_once 'freelancer-navbar-template.php';

if (!isset($_SESSION['userName']) || empty($_SESSION['userName'])) {
    header("Location: ../loginSignup/login.php");  // Redirect to login or another page
    exit();
}
include_once 'interlinkedDB.php';

// Redirect if session userName is not set or empty


$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$user = $_SESSION['userName'];
$error = [];

$stmt = $slave_con->prepare("SELECT * FROM user where USER_NAME = ?");
$stmt->execute([$user]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}

$id = $_SESSION['USER_ID'];
$filter = $_GET['filter'] ?? 'received';

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
        WHERE email.EM_RECEPIENT = :user
        ORDER BY email.EM_ID DESC
    ");
    $stmt->execute(['user' => $user]);
}
$result = $stmt->fetchAll();


$inputRecipient = $inputSubject = $inputMessage = '';
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
    $recipientUser = $stmt->fetch();


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
        $stmt = $master_con->prepare("INSERT INTO email(USER_ID, EM_SUBJECT, EM_COMP, EM_RECEPIENT, EM_DATE, EM_STATUS)
        VALUES (:user_id, :em_subject, :em_comp, :em_recipient, CURRENT_TIMESTAMP, 'Unread')");
        $stmt->bindParam(':user_id', $id);
        $stmt->bindParam(':em_subject', $subject);
        $stmt->bindParam(':em_comp', $message);
        $stmt->bindParam(':em_recipient', $recipient);
        $result = $stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();


    }

}


?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="freelancer-style.css">
    <title>Message</title>
</head>
<body>


<div class="container">
    <div class="content">
        <div class="email">
            <div class="messages">
                <h1>Messages</h1>
                <div class="message-filter">
                    <form method="get" action="">
                        <button type="submit" name="filter" value="received" class="btn-edit">Received</button>
                        <button type="submit" name="filter" value="sent" class="btn-edit">Sent</button>
                    </form>
                </div>

                <div class="message-content">


                    <!--                    enclose the msg with for each later-->
                    <?php foreach ($result as $res) {?>
                        <div class="msg">
                            <h4><?= $filter === 'sent' ? 'To' : 'From' ?>: <?=$res['USER_NAME']?></h4>
                            <h4>Subject: <?=$res['EM_SUBJECT']?></h4>
                            <h4>Test ID: <?=$res['EM_ID']?></h4>
                            <p><?=$res['EM_COMP']?></p>
                            <p><?=$res['EM_DATE']?></p>

                            <form action="freelancer-delete-message.php" method="post" class="form-delete"
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

