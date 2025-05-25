<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Circle Image</title>
    <style>
        .top-right {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 20px;
            padding: 20px;
        }

        .profile {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid black;
        }

        .profile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .name {
            color: #292524;
        }

        .name a {
            color: #292524;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="top-right">
    <div class="profile">
        <img src="https://via.placeholder.com/300x400" alt="Profile Image">
    </div>
    <div class="name">
        <a href="#"><h4 style="font-weight: 700;">John Doe</h4></a>
        <p style="font-size: 12px">Freelancer</p>
    </div>
</div>
</body>
</html>
