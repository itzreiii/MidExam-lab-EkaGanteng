<?php
require_once 'config.php';
require_once 'functions.php';

$error = '';
$success = '';

if (!isset($_GET['token'])) {
    $error = "Invalid or expired token.";
} else {
    $reset_token = $_GET['token'];
    $reset_token_hash = hash("sha256", $reset_token);
    
    $query = "SELECT id FROM users WHERE password_reset_hash = ? AND password_reset_expires > NOW()";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $reset_token_hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        $error = "Invalid or expired token.";
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $password = sanitize_input($_POST['password']);
        $confirm_password = sanitize_input($_POST['confirm_password']);
        
        if (empty($password) || empty($confirm_password)) {
            $error = "Both password fields are required.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $hashed_password = hash_password($password);
            
            $query = "UPDATE users SET password = ?, password_reset_hash = NULL, password_reset_expires = NULL WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user['id']);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Password has been reset successfully.";
            } else {
                $error = "Password reset failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Online To-Do List</title>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-6 text-center">
            Create New Password
        </h1>

        <?php if ($error) echo "<p class='text-red-500 text-center mb-4 bg-red-100 p-3 rounded-lg animate__animated animate__shake'>$error</p>"; ?>
        <?php if ($success): ?>
            <div class="text-center mb-4 space-y-4 animate__animated animate__fadeIn">
                <p class="text-green-500 bg-green-100 p-3 rounded-lg"><?php echo $success; ?></p>
                <a href="login.php" 
                   class="inline-block w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white py-4 rounded-xl font-medium hover:from-blue-600 hover:to-blue-800 transition duration-300 transform hover:-translate-y-1 shadow-lg text-center">
                    Return to Login
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="space-y-4">
                <div class="space-y-4">
                    <div class="relative">
                        <input type="password" name="password" placeholder="New Password" required
                            class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
                    </div>

                    <div class="relative">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required
                            class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white py-4 rounded-xl font-medium hover:from-blue-600 hover:to-blue-800 transition duration-300 transform hover:-translate-y-1 shadow-lg mt-6">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

        <!-- Feature highlights -->
        <div class="mt-12 grid grid-cols-2 gap-4 text-center">
            <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
                <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <p class="text-sm text-gray-600">Strong Security</p>
            </div>
            <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
                <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <p class="text-sm text-gray-600">Protected Reset</p>
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
</body>
</html>