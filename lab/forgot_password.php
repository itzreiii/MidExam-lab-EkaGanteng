<?php 
require_once 'config.php';
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    
    if (empty($email)) {
        $error = "Email is required.";
    } else {
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user) {
            $reset_token = bin2hex(random_bytes(16));
            $reset_token_hash = hash("sha256", $reset_token);
            
            $query = "UPDATE users SET password_reset_hash = ?, password_reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $reset_token_hash, $user['id']);
            mysqli_stmt_execute($stmt);
            
            // Send the reset email
            $mail = require __DIR__ . "/mailer.php";
            $mail->setFrom("noreply@example.com");
            $mail->addAddress($email);
            $mail->Subject = "Password Reset";
            
            $reset_link = "http://localhost/webprog-lab/lab/reset_password.php?token=$reset_token";
            $mail->Body = <<<END
                Click <a href="$reset_link">here</a> to reset your password. This link is valid for 1 hour.
                END;
            
            try {
                $mail->send();
                $success = "An email has been sent to reset your password.";
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Online To-Do List</title>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-6 text-center">
            Reset Password
        </h1>

        <?php if ($error) echo "<p class='text-red-500 text-center mb-4 bg-red-100 p-3 rounded-lg animate__animated animate__shake'>$error</p>"; ?>
        <?php if ($success) echo "<p class='text-green-500 text-center mb-4 bg-green-100 p-3 rounded-lg animate__animated animate__fadeIn'>$success</p>"; ?>

        <form method="POST" action="" class="space-y-4">
            <div class="relative">
                <input type="email" name="email" placeholder="Enter your email address" required
                    class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white py-4 rounded-xl font-medium hover:from-blue-600 hover:to-blue-800 transition duration-300 transform hover:-translate-y-1 shadow-lg mt-6">
                Send Reset Link
            </button>
        </form>

        <div class="mt-6">
            <p class="text-center text-gray-600">
                Remember your password? 
                <a href="login.php" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition duration-200">Back to Login</a>
            </p>
        </div>

        <!-- Feature highlights -->
        <div class="mt-12 grid grid-cols-2 gap-4 text-center">
            <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
                <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <p class="text-sm text-gray-600">Secure Reset</p>
            </div>
            <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
                <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm text-gray-600">Email Verification</p>
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