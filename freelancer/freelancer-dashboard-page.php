<?php
include 'freelancer-navbar-template.php' ;
include_once 'interlinkedDB.php';

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
                <h1>DASHBOARD</h1>
                <h2>My Commissions</h2>
                <p><?= date("d/m/y H:i") ?></p>
            </div>

            <div class="check">
                <a href="freelancer-project-page.php">Check project</a>
            </div>
        </div>


    </div>

    <a href="freelancer-project-page.php" class="card-redirect">
        <div class="card-container">
            <div class="card" style="background-image: linear-gradient(to bottom,#e7fcf4 ,#bcd0f4,#6d79a2); ">
                <h1><i class="fa-solid fa-arrows-spin fa-xl"></i>ONGOING</h1>
                <div class="card-content">
                    <h1 class="num">4</h1>
                    <h1 class="label">Tasks</h1>
                </div>
            </div>
            <div class="card" style="background-image: linear-gradient(to bottom,#ede7c8 ,#f6d9c5,#b68383);">
                <h1><i class="fa-solid fa-clock fa-xl"></i>OVERDUE</h1>
                <div class="card-content">
                    <h1 class="num">9</h1>
                    <h1 class="label">Tasks</h1>
                </div>
            </div>
            <div class="card" style="background-image: linear-gradient(to bottom,#c8edd1 ,#a4d3cf,#99b3c9); ">
                <h1><i class="fa-solid fa-check fa-xl"></i>COMPLETED</h1>
                <div class="card-content">
                    <h1 class="num">20</h1>
                    <h1 class="label">Tasks</h1>
                </div>
            </div>
        </div>
    </a>


    <div class="inner">
        <div class="project-card-dashboard">
            <div class="project-card-content-dashboard">
                <h1>PROJECTS</h1>
                <table style="width:100%; margin-top: 15px;" class="table">
                    <tr>
                        <th>Project Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Urgency</th>
                        <th>Commissioned By</th>
                    </tr>
                    <tr>
                        <td>Placeholder</td>
                        <td>Placeholder</td>
                        <td>Placeholder</td>
                        <td>Placeholder</td>
                        <td>Placeholder</td>

                    </tr>
                </table>
            </div>

        </div>
    </div>
</div>


<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        const data = google.visualization.arrayToDataTable([
            ['Commission', 'Mhl'],
            ['Ongoing', 4],
            ['Overdue', 2],
            ['Completed', 20],
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
                height: '70%'
            },
            colors: ['#81b7e5', '#cb8a76', '#60b981'],
            pieHole: 0.4,   // Optional: set to 0.4 if you want a donut chart
        };

        const chart = new google.visualization.PieChart(document.getElementById('myChart'));
        chart.draw(data, options);
    }
</script>

</body>
</html>
