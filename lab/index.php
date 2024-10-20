<!-- index.php -->
<?php
require_once 'config.php';
require_once 'functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online To-Do List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Welcome to Online To-Do List</h1>
    <nav>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</body>
</html>
