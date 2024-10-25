<?php
require_once 'config.php';
require_once 'functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activation_token = bin2hex(random_bytes(16));
    $activation_token_hash = hash("sha256", $activation_token);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);

    // Validate input fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = hash_password($password);
        $query = "INSERT INTO users (username, email, password, account_activation_hash) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $activation_token_hash);

        if (mysqli_stmt_execute($stmt)) {
            $mail = require __DIR__ . "/mailer.php";

            $mail->setFrom("noreply@example.com");
            $mail->addAddress($email);
            $mail->Subject = "Account Activation";
            // $mail->Body = <<<END
            // Click <a href="https://darkslategray-woodpecker-113089.hostingersite.com/activate-account.php?token=$activation_token">here</a> to activate your account.
            // END;
            $mail->Body = <<<END
            Click <a href="http://localhost/webprog-lab/lab/activate-account.php?token=$activation_token">here</a> to activate your account.
            END;

            try {
                $mail->send();
                echo "<script>document.getElementById('registerForm').reset();</script>"; // Clear form after successful registration
                echo "<script>alert('Registration successful. Please check your email for verification.');</script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
                exit;
            }
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
    <div class="relative backdrop-blur-sm bg-white/90 shadow-2xl rounded-2xl p-8 w-full max-w-md transform hover:scale-105 transition-all duration-300 animate__animated animate__fadeIn">
        <!-- Logo/Icon -->
        <div class="mb-6">
            <svg class="w-16 h-16 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-6 text-center">
            Create Your Account
        </h1>

        <?php if ($error) echo "<p class='text-red-500 text-center mb-4 bg-red-100 p-3 rounded-lg'>$error</p>"; ?>

        <form id="registerForm" method="POST" action="" class="space-y-4">
            <div class="space-y-4">
                <div class="relative">
                    <input type="text" name="username" placeholder="Username" required
                        class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
                </div>

                <div class="relative">
                    <input type="email" name="email" placeholder="Email" required
                        class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
                </div>

                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="Password" required
                        onkeyup="checkPasswordStrength(this.value)"
                        class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="strength-bar" class="h-2 rounded-full transition-all duration-300"></div>
                        </div>
                        <p id="password-strength" class="text-sm mt-1 text-gray-600"></p>
                    </div>
                </div>

                <div class="relative">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required
                        onkeyup="validateConfirmPassword()"
                        class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 bg-white/50 backdrop-blur-sm">
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="confirm-password-bar" class="h-2 rounded-full transition-all duration-300"></div>
                        </div>
                        <p id="confirm-password-error" class="text-sm mt-1"></p>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white py-4 rounded-xl font-medium hover:from-blue-600 hover:to-blue-800 transition duration-300 transform hover:-translate-y-1 shadow-lg mt-6">
                Create Account
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            Already have an account? 
            <a href="login.php" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition duration-200">Login</a>
        </p>
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
        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById("password-strength");
            const strengthBar = document.getElementById("strength-bar");
            
            // Initialize variables for password criteria
            let strength = 0;
            const hasLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChars = /[^A-Za-z0-9]/.test(password);

            // Increment strength for each met criterion
            if (hasLength) strength += 1;
            if (hasUpperCase) strength += 1;
            if (hasLowerCase) strength += 1;
            if (hasNumbers) strength += 1;
            if (hasSpecialChars) strength += 1;

            // Update UI based on strength
            let strengthText = "";
            let barWidth = "";
            let barColor = "";

            switch (strength) {
                case 0:
                case 1:
                    strengthText = "Very Weak";
                    barWidth = "20%";
                    barColor = "bg-red-500";
                    break;
                case 2:
                    strengthText = "Weak";
                    barWidth = "40%";
                    barColor = "bg-orange-500";
                    break;
                case 3:
                    strengthText = "Medium";
                    barWidth = "60%";
                    barColor = "bg-yellow-500";
                    break;
                case 4:
                    strengthText = "Strong";
                    barWidth = "80%";
                    barColor = "bg-blue-500";
                    break;
                case 5:
                    strengthText = "Very Strong";
                    barWidth = "100%";
                    barColor = "bg-green-500";
                    break;
            }

            strengthIndicator.textContent = strengthText;
            strengthBar.style.width = barWidth;
            strengthBar.className = `h-2 rounded-full transition-all duration-300 ${barColor}`;
        }

        function validateConfirmPassword() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const confirmPasswordIndicator = document.getElementById("confirm-password-error");
            const confirmPasswordBar = document.getElementById("confirm-password-bar");

            if (confirmPassword === "") {
                confirmPasswordIndicator.textContent = "";
                confirmPasswordBar.style.width = "0%";
                return;
            }

            if (password !== confirmPassword) {
                confirmPasswordIndicator.textContent = "Passwords do not match";
                confirmPasswordIndicator.className = "text-sm mt-1 text-red-500";
                confirmPasswordBar.style.width = "100%";
                confirmPasswordBar.className = "h-2 rounded-full transition-all duration-300 bg-red-500";
            } else {
                confirmPasswordIndicator.textContent = "Passwords match!";
                confirmPasswordIndicator.className = "text-sm mt-1 text-green-500";
                confirmPasswordBar.style.width = "100%";
                confirmPasswordBar.className = "h-2 rounded-full transition-all duration-300 bg-green-500";
            }
        }
    </script>
</body>
</html>