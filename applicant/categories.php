<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Category</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="authenticate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>


<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>
<div class="container">
    <p class="subtext">1/8</p>
    <h1>What kind of work you are applying for?</h1>
    <p class="subtext">Don't worry you can edit it later.</p>
    <div class="category-specialty-container">
        <div class="category-col">
            <div class="category-header">Select 1 category</div>
            <ul class="category-list" id="categoryList">
                <li>3D Modelling</li>
                <li>Illustration</li>
                <li>Photo Manipulation</li>
                <li>Layout Design</li>
                <li>UI/UX Design</li>
            </ul>
        </div>
        <div class="specialty-col">
            <div class="specialty-header">Now, select your Skills</div>
            <ul class="specialty-list" id="specialtyList">
            </ul>
            <a href="#" class="clear-selections" id="clearSelections">✕ Clear selections</a>
        </div>
    </div>
</div>

<div class="botvar">
    <div class="botconright">
        <input type="submit" name="action" value="Next">
    </div>
</div>

<script src="script.js"></script>

</body>
</html>
