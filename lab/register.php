
<!-- register.php -->
<?php
require_once 'config.php';
require_once 'functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $hashed_password = hash_password($password);
        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['username'] = $username;
            redirect('dashboard.php');
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online To-Do List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Register</h1>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</body>
</html>
