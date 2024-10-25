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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center p-4">
    <!-- Animated background particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-96 h-96 -top-48 -left-48 bg-white/10 rounded-full mix-blend-overlay animate-blob"></div>
        <div class="absolute w-96 h-96 -bottom-48 -right-48 bg-white/10 rounded-full mix-blend-overlay animate-blob animation-delay-2000"></div>
        <div class="absolute w-96 h-96 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white/10 rounded-full mix-blend-overlay animate-blob animation-delay-4000"></div>
    </div>

    <!-- Main content -->
<div class="relative backdrop-blur-sm bg-white/90 shadow-2xl rounded-2xl p-8 w-full max-w-md animate__animated animate__fadeIn">
    <!-- Logo/Icon -->
    <div class="mb-6">
        <svg class="w-16 h-16 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
        </svg>
    </div>

    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-6 text-center">
        Welcome Back!
    </h1>

    <?php if ($error) echo "<p class='text-red-500 text-center mb-4 bg-red-100 p-3 rounded-lg animate__animated animate__shake'>$error</p>"; ?>

    <form id="loginForm" method="POST" action="" class="space-y-4">
        <div class="space-y-4">
            <div class="relative">
                <input type="text" name="username" placeholder="Username" required
                    class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
            </div>

            <div class="relative">
                <input type="password" name="password" placeholder="Password" required
                    class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
            </div>
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white py-4 rounded-xl font-medium hover:from-blue-600 hover:to-blue-800 transition duration-300 transform hover:-translate-y-1 shadow-lg mt-6">
            Sign In
        </button>
    </form>

    <div class="mt-6 space-y-2">
        <p class="text-center text-gray-600">
            Don't have an account? 
            <a href="register.php" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition duration-200">Register</a>
        </p>

        <?php if ($show_forgot_password): ?>
        <p class="text-center">
            <a href="forgot_password.php" 
               class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition duration-200">
                Forgot your password?
            </a>
        </p>
        <?php endif; ?>
    </div>

    <!-- Feature highlights -->
    <div class="mt-12 grid grid-cols-2 gap-4 text-center">
        <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
            <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <p class="text-sm text-gray-600">Secure Login</p>
        </div>
        <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
            <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <p class="text-sm text-gray-600">Protected Account</p>
        </div>
    </div>
</div>


    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>

    <script>
        // Clear the form fields on page load
        window.onload = function() {
            document.getElementById("loginForm").reset();
        };
    </script>
</body>
</html>