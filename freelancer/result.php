<?php

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="freelancer-style.css">
    <title>Document</title>
</head>
<body>
<div class="search-result-item" onclick="selectUser('<?= htmlspecialchars($name) ?>')" style="font-size: 13px">
<?= htmlspecialchars($name) ?> (<?= htmlspecialchars($type) ?>  <?= htmlspecialchars($fullName) ?>)
</div>
</body>
</html>


