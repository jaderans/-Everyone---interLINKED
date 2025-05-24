<?php
include 'freelancer-navbar-template.php' ;
include_once 'interlinkedDB.php';
include_once 'SecurityCheck.php';

$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$user = $_SESSION['userName'];

$stmt = $slave_con->prepare("SELECT * FROM user where USER_NAME = ?");
$stmt->execute([$user]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}
$id = $_SESSION['USER_ID'];

$pro = $slave_con->prepare("
    SELECT 
        PRO_STATUS,
        COUNT(*) AS count 
    FROM projects 
    WHERE USER_ID = ? 
      AND PRO_STATUS IN ('Pending', 'Working', 'Ongoing', 'Completed', 'Cancelled') 
    GROUP BY PRO_STATUS
");
$pro->execute([$id]);
$results = $pro->fetchAll(PDO::FETCH_ASSOC);


// Initialize all statuses to 0
$statuses = [
    'Pending' => 0,
    'Working' => 0,
    'Ongoing' => 0,
    'Completed' => 0,
    'Cancelled' => 0
];

// Fill in values from the query
foreach ($results as $row) {
    $statuses[$row['PRO_STATUS']] = $row['count'];
}

// Now you can use:
$pending = $statuses['Pending'];
$working = $statuses['Working'];
$ongoing = $statuses['Ongoing'];
$completed = $statuses['Completed'];
$cancelled = $statuses['Cancelled'];





?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="freelancer-style.css">
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <title>Dashboard</title>
</head>
<body>



<div class="container-dashboard">
    <div class="inner">
        <a href="freelancer-project-page.php" class="card-redirect">
            <div id="myChart" style="width:100%; max-width:400px; height:400px;" class="card"></div>
        </a>

        <div class="content-card">
            <div class="header-section">
                <h1 style="color: #1f4c4b">DASHBOARD</h1>
<!--                <h2>--><?php //=$user?><!--</h2>-->
<!--                <h2>--><?php //=$id?><!--</h2>-->
                <h2>My Commissions</h2>
                <h3 style="margin-top: 5px"><?= date("d/m/y H:i") ?></h3>
            </div>
            <a href="salary.php" class="card-redirect">
                <div class="salary">
                    <div class="sal-details">
                        <p>SALARY DETAILS</p>
                        <h1>₱ 100,000.09</h1>
                        <h3>Available Earnings</h3>
                    </div>

                </div>

            </a>

            <div class="check">
                <a href="freelancer-project-page.php">Check project</a>
            </div>
        </div>


    </div>

    <a href="freelancer-project-page.php" class="card-redirect">
        <div class="card-container">
            <div class="card" ">
                <h1><i class="fa-solid fa-pencil"></i> WORKING</h1>
                <div class="card-content">
                    <h1><?=$working?></h1>
                    <h2 class="label">Tasks</h2>
                </div>
            </div>
            <div class="card">
                <h1><i class="fa-solid fa-hourglass-half"></i> PENDING</h1>
                <div class="card-content">
                    <h1><?=$pending?></h1>
                    <h2 class="label">Tasks</h2>
                </div>
            </div>
            <div class="card">
                <h1><i class="fa-solid fa-check fa-l"></i>COMPLETED</h1>
                <div class="card-content">
                    <h1><?=$completed?></h1>
                    <h2 class="label">Tasks</h2>
                </div>
            </div>
            <div class="card">
                <h1><i class="fa-solid fa-xmark"></i> CANCELLED</h1>
                <div class="card-content">
                    <h1><?=$cancelled?></h1>
                    <h2 class="label">Tasks</h2>
                </div>
            </div>
        </div>
    </a>
</div>


<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        const data = google.visualization.arrayToDataTable([
            ['Commission', 'Mhl'],
            ['Working', <?= $working ?>],
            ['Ongoing', <?= $ongoing ?>],
            ['Pending', <?= $pending ?>],
            ['Completed', <?= $completed ?>],
            ['Cancelled', <?= $cancelled ?>]
        ]);

        const options = {
            title: 'Tasks',
            width: 400,    // Control chart size here
            height: 400,
            legend: {
                position: 'bottom',   // Move labels to bottom
                alignment: 'center',
                textStyle: { fontSize: 14 }
            },
            chartArea: {
                left: 0,
                top: 20,
                width: '100%',
                height: '60%'
            },
            colors: ['#81b7e5', '#cb8a76', '#60b981'],
            pieHole: 1,   // Optional: set to 0.4 if you want a donut chart
        };

        const chart = new google.visualization.PieChart(document.getElementById('myChart'));
        chart.draw(data, options);
    }
</script>

</body>
</html>
