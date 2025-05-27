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
                <h2><?php //=$user?></h2>
                <h2><?php //=$id?></h2>
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
            <div class="card">
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
    const projectData = {
        submitted: <?= $submitted ?>,
        pending: <?= $pending ?>,
        ongoing: <?= $ongoing ?>,
        completed: <?= $completed ?>,
        cancelled: <?= $cancelled ?>
    };

    // Update current date/time
    function updateDateTime() {
        const now = new Date();
        const formatted = now.toLocaleString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
        document.getElementById('currentDateTime').textContent = formatted;
    }

    // Update card counts
    function updateCardCounts() {
        document.getElementById('submittedCount').textContent = projectData.submitted;
        document.getElementById('pendingCount').textContent = projectData.pending;
        document.getElementById('ongoingCount').textContent = projectData.ongoing;
        document.getElementById('completedCount').textContent = projectData.completed;
        document.getElementById('cancelledCount').textContent = projectData.cancelled;
    }

    // Google Charts
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    let chart;
    let chartData;
    let chartOptions;
    let isChartDrawn = false;

    function drawChart() {
        try {
            const container = document.getElementById('myChart');
            const loadingDiv = document.getElementById('chartLoading');

            if (!container) {
                console.error('Chart container not found');
                return;
            }

            // Check if we have any data
            const totalTasks = projectData.submitted + projectData.pending +
                projectData.ongoing + projectData.completed + projectData.cancelled;

            if (totalTasks === 0) {
                loadingDiv.style.display = 'none';
                container.innerHTML = '<div class="no-data-message">No project data available</div>';
                container.style.display = 'flex';
                container.style.alignItems = 'center';
                container.style.justifyContent = 'center';
                return;
            }

            // Prepare chart data
            chartData = google.visualization.arrayToDataTable([
                ['Status', 'Count'],
                ['Submitted', projectData.submitted],
                ['Pending', projectData.pending],
                ['Ongoing', projectData.ongoing],
                ['Completed', projectData.completed],
                ['Cancelled', projectData.cancelled]
            ]);

            // Calculate container dimensions
            const containerRect = container.parentElement.getBoundingClientRect();
            const containerWidth = Math.max(containerRect.width - 40, 300);
            const containerHeight = Math.max(containerRect.height - 40, 300);

            // Chart options
            chartOptions = {
                title: 'Project Status Overview',
                titleTextStyle: {
                    color: '#1f4c4b',
                    fontSize: 18,
                    bold: true
                },
                width: containerWidth,
                height: containerHeight,
                backgroundColor: 'transparent',
                colors: ['#81b7e5', '#cb8a76', '#60b981', '#f4a460', '#ff6b6b'],
                pieHole: 0, // Make it a donut chart
                pieSliceTextStyle: {
                    color: 'white',
                    fontSize: 12,
                    bold: true
                },
                legend: {
                    position: containerWidth < 500 ? 'bottom' : 'right',
                    alignment: 'center',
                    textStyle: {
                        color: '#1f4c4b',
                        fontSize: containerWidth < 500 ? 11 : 13
                    }
                },
                chartArea: {
                    left: containerWidth < 500 ? 20 : 40,
                    top: containerWidth < 500 ? 40 : 50,
                    width: containerWidth < 500 ? '90%' : '75%',
                    height: containerWidth < 500 ? '70%' : '75%'
                },
                tooltip: {
                    textStyle: {
                        color: '#1f4c4b',
                        fontSize: 13
                    },
                    showColorCode: true
                },
                pieSliceText: 'value',
                sliceVisibilityThreshold: 0 // Show all slices even if value is 0
            };

            // Create and draw chart
            chart = new google.visualization.PieChart(container);

            // Add event listener for chart selection
            google.visualization.events.addListener(chart, 'select', function() {
                const selection = chart.getSelection();
                if (selection.length > 0) {
                    const row = selection[0].row;
                    const status = chartData.getValue(row, 0);
                    console.log('Selected status:', status);
                    // You can add navigation logic here
                }
            });

            chart.draw(chartData, chartOptions);

            // Hide loading and show chart
            loadingDiv.style.display = 'none';
            container.style.display = 'block';
            isChartDrawn = true;

        } catch (error) {
            console.error('Error drawing chart:', error);
            document.getElementById('chartLoading').innerHTML =
                '<div style="color: #e74c3c;"><i class="fa-solid fa-exclamation-triangle"></i> Chart loading failed</div>';
        }
    }

    // Responsive chart redraw
    function resizeChart() {
        if (isChartDrawn && chart && chartData && chartOptions) {
            const container = document.getElementById('myChart');
            if (container && container.style.display !== 'none') {
                drawChart();
            }
        }
    }

    // Debounce function for resize events
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

    // Initialize everything
    function init() {
        updateDateTime();
        updateCardCounts();

        // Update date/time every minute
        setInterval(updateDateTime, 60000);
    }

    // Event listeners
    window.addEventListener('load', init);
    window.addEventListener('resize', debounce(resizeChart, 300));
    window.addEventListener('orientationchange', function() {
        setTimeout(resizeChart, 500);
    });

    // Handle visibility change (when tab becomes active again)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && isChartDrawn) {
            setTimeout(resizeChart, 100);
        }
    });
</script>

</body>
</html>
