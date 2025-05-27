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
      AND PRO_STATUS IN ('Pending', 'Submitted', 'Ongoing', 'Completed', 'Cancelled') 
    GROUP BY PRO_STATUS
");
$pro->execute([$id]);
$results = $pro->fetchAll(PDO::FETCH_ASSOC);


// Initialize all statuses to 0
$statuses = [
    'Pending' => 0,
    'Submitted' => 0,
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
$submitted = $statuses['Submitted'];
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
            <div id="myChart" style="width:100%; max-width:400px; height:400px;" class="card-chart"></div>
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
                <h1><i class="fa-solid fa-pencil"></i> SUBMITTED</h1>
                <div class="card-content">
                    <h1><?=$submitted?></h1>
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

    let chart;
    let data;
    let options;

    function drawChart() {
        data = google.visualization.arrayToDataTable([
            ['Commission', 'Mhl'],
            ['Submitted', <?= $submitted ?>],
            ['Ongoing', <?= $ongoing ?>],
            ['Pending', <?= $pending ?>],
            ['Completed', <?= $completed ?>],
            ['Cancelled', <?= $cancelled ?>]
        ]);

        // Get container dimensions
        const container = document.getElementById('myChart');
        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;

        options = {
            title: 'Tasks',
            width: containerWidth,
            height: containerHeight,
            legend: {
                position: containerWidth < 400 ? 'bottom' : 'right',
                alignment: 'center',
                textStyle: {
                    fontSize: containerWidth < 400 ? 10 : 12
                }
            },
            chartArea: {
                left: containerWidth < 400 ? 10 : 20,
                top: containerWidth < 400 ? 30 : 40,
                width: containerWidth < 400 ? '85%' : '70%',
                height: containerWidth < 400 ? '60%' : '70%'
            },
            colors: ['#81b7e5', '#cb8a76', '#60b981', '#f4a460', '#ff6b6b'],
            pieHole: 0,
            titleTextStyle: {
                fontSize: containerWidth < 400 ? 14 : 16
            },
            // Make chart responsive
            responsive: true
        };

        chart = new google.visualization.PieChart(container);
        chart.draw(data, options);
    }

    // Redraw chart on window resize
    function resizeChart() {
        if (chart && data && options) {
            drawChart();
        }
    }

    // Debounce function to limit resize calls
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Add event listeners for resize
    window.addEventListener('resize', debounce(resizeChart, 250));

    // Optional: Also listen for orientation change on mobile
    window.addEventListener('orientationchange', function() {
        setTimeout(resizeChart, 500);
    });
</script>

</body>
</html>
