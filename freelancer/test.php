<?php
session_start();
include_once 'interlinkedDB.php';

////code to display
//$stmt = $conn->prepare("SELECT * FROM user where USER_NAME =:name");
//$stmt->execute(array("name" => $name));
//$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo $_SESSION['user_name'];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<table>
<thead>
<tr>
    <th>Name</th>
    <th>EMAIL</th>
    <th>ID</th>
    <th>PASSWORD</th>

</tr>
</thead>
<tbody>
<?php foreach ($result as $res) { ?>
    <tr>
        <td><?=$res['USER_NAME']?></td>
        <td><?=$res['USER_EMAIL']?></td>
        <td><?=$res['USER_ID']?></td>
        <td><?=$res['USER_PASSWORD']?></td>

    </tr>
<?php } ?>
</tbody>
</table>
</body>
</html>


