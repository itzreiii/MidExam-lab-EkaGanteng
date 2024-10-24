<?php
require_once 'config.php';
require_once 'functions.php';

error_reporting(E_ERROR);

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$show_forgot_password = false; // Flag to show "Forgot your password?" link

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Both username and password are required.";
    } else {
        $query = "SELECT id, username, password, account_activation_hash FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && $user['account_activation_hash'] === null) {
            if (verify_password($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                echo "<script>document.getElementById('loginForm').reset();</script>"; // Clear form after successful login
                redirect('dashboard.php');
            } else {
                $error = "Invalid username or password.";
                $show_forgot_password = true;  // Show "Forgot password" after wrong attempt
            }
        } elseif ($user['account_activation_hash'] != null) {
            $error = "Please verify your account first.";
        } else {
            $error = "Invalid username or password.";
            $show_forgot_password = true;  // Show "Forgot password" if the user doesn't exist or wrong credentials
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Optional: Clear the form fields on page load
        window.onload = function() {
            document.getElementById("loginForm").reset();
        };
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-gray-700 text-center mb-6">Login</h1>

        <?php if ($error) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>

        <form id="loginForm" method="POST" action="" class="space-y-4">
            <input type="text" name="username" placeholder="Username" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <input type="password" name="password" placeholder="Password" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <button type="submit"
                class="w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">Login</button>
        </form>

        <p class="text-center text-gray-600 mt-4">Don't have an account? 
            <a href="register.php" class="text-blue-500 hover:underline">Register</a>
        </p>

        <?php if ($show_forgot_password): ?>
            <p class="text-center text-red-600 mt-4">
                <a href="forgot_password.php" class="text-blue-500 hover:underline">Forgot your password?</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
