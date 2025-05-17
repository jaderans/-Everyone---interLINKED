<?php
//session_start();
include 'freelancer-navbar-template.php';
include_once 'interlinkedDB.php';
$master_con = connectToDatabase(3006);
$slave_con = connectToDatabase(3007);

//$id = $_SESSION['user_id']; fix this once theres a foreign key

$id = 1;

//$stmt = $slave_con->prepare("SELECT * FROM projects where PRO_ID = ?");
//$stmt->execute([$id]);
//$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';

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
    <title>Project</title>
</head>
<body>

<div class="container">
    <div class="content">
        <div class="project-card">
            <div class="project-card-content">
                <h1>Projects</h1>
                <table style="width:100%; margin-top: 15px;" class="table">
                    <tr>
                        <th>Project Title</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Date Start</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Priority Level</th>
                        <th>Commissioned By</th>
                        <th>Edit/Update</th>
                    </tr>
                    <tr>
                        <td>NA</td>
                        <td>NA</td>
                        <td>NA</td>
                        <td>NA</td>
                        <td>NA</td>
                        <td>NA</td>
                        <td>NA</td>
                        <td>NA</td>
                        <td>
                            <form action="freelancer-edit-project.php" method="post">
                                <button class="btn-project" id="btn-edit" name="user_id" value="">Edit</button>
                            </form>
                        </td>
                    </tr>
<!--                    --><?php //foreach ($result as $project) { ?>
<!--                        -->
<!--                   --><?php //} ?>

                </table>
            </div>
        </div>
    </div>

</div>
</body>
</html>

