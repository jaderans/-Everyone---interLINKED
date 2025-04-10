<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f8; }
        header { background: #4CAF50; padding: 20px; color: white; text-align: center; }
        nav {
            background: #333;
            overflow: hidden;
        }
        nav a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            font-size: 17px;
        }
        nav a:hover {
            background: #ddd;
            color: black;
        }
        .container {
            padding: 20px;
        }
        section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
        }
        form input, form textarea, form button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>

<header>
    <h1>Client Dashboard</h1>
    <p>Welcome to your personalized space</p>
</header>

<nav>
    <a href="#home">Home</a>
    <a href="#about">About Me</a>
    <a href="#notifications">Notifications</a>
    <a href="#messages">Messages</a>
    <a href="#order">Place Order</a>
</nav>

<div class="container">

    <section id="home">
        <h2>Portfolio Showcase</h2>
        <p>Explore our featured works. Feel free to like or leave a comment!</p>
        <form method="post">
            <input type="text" name="comment" placeholder="Write a comment..." required>
            <button type="submit" name="like">Like</button>
            <button type="submit" name="comment_submit">Post Comment</button>
        </form>
    </section>

    <section id="about">
        <h2>About Me</h2>
        <p>Update your profile information here.</p>
        <form method="post">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit" name="update_details">Update Profile</button>
        </form>
    </section>

    <section id="notifications">
        <h2>Notifications</h2>
        <p>No new notifications at the moment.</p>
    </section>

    <section id="messages">
        <h2>Send a Message</h2>
        <p>Contact the admin directly through this form.</p>
        <form method="post">
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="message" rows="5" placeholder="Your message..." required></textarea>
            <button type="submit" name="send_message">Send Message</button>
        </form>
    </section>

    <section id="order">
        <h2>Place a New Project Order</h2>
        <p>Start your next project by filling out the form below.</p>
        <form method="post">
            <input type="text" name="project_title" placeholder="Project Title" required>
            <textarea name="project_details" rows="5" placeholder="Describe your project..." required></textarea>
            <input type="text" name="payment_method" placeholder="Preferred Payment Method" required>
            <button type="submit" name="submit_order">Submit Order</button>
        </form>
    </section>

</div>

</body>
</html>

<?php
if (isset($_POST['like'])) {
    echo "<script>alert('You liked the portfolio!');</script>";
}

if (isset($_POST['comment_submit'])) {
    $comment = htmlspecialchars($_POST['comment']);
    echo "<script>alert('Comment posted: $comment');</script>";
}

if (isset($_POST['update_details'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    echo "<script>alert('Profile updated: $name, $email');</script>";
}

if (isset($_POST['send_message'])) {
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    echo "<script>alert('Message sent: $subject');</script>";
}

if (isset($_POST['submit_order'])) {
    $title = htmlspecialchars($_POST['project_title']);
    echo "<script>alert('Order placed: $title');</script>";
}
?>
