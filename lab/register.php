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
    <script>
        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById("password-strength");
            const strengthBar = document.getElementById("strength-bar");
            let strength = "Weak";

            // Check password length
            if (password.length >= 8) {
                strength = "Strong";
                strengthBar.style.width = "100%";
                strengthBar.className = "bg-green-500";
            } else {
                strength = "Weak";
                strengthBar.style.width = "25%";
                strengthBar.className = "bg-red-500";
            }

            strengthIndicator.textContent = strength;
        }

        function validateConfirmPassword() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const confirmPasswordIndicator = document.getElementById("confirm-password-error");
            const confirmPasswordBar = document.getElementById("confirm-password-bar");

            if (password !== confirmPassword) {
                confirmPasswordIndicator.textContent = "Passwords do not match.";
                confirmPasswordIndicator.classList.add("text-red-500");
                confirmPasswordBar.style.width = "100%";
                confirmPasswordBar.className = "bg-red-500";
            } else {
                confirmPasswordIndicator.textContent = "Passwords match!";
                confirmPasswordIndicator.classList.remove("text-red-500");
                confirmPasswordIndicator.classList.add("text-green-500");
                confirmPasswordBar.style.width = "100%";
                confirmPasswordBar.className = "bg-green-500";
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-700 text-center mb-6">Register</h1>
        
        <?php if ($error) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>

        <form id="registerForm" method="POST" action="" class="space-y-4">
            <input type="text" name="username" placeholder="Username" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <input type="email" name="email" placeholder="Email" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <input type="password" id="password" name="password" placeholder="Password" required
                onkeyup="checkPasswordStrength(this.value)"
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                <div id="strength-bar" class="h-2 rounded-full"></div>
            </div>
            <p id="password-strength" class="text-sm mt-1"></p>

            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required
                onkeyup="validateConfirmPassword()"
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                <div id="confirm-password-bar" class="h-2 rounded-full"></div>
            </div>
            <p id="confirm-password-error" class="text-sm mt-1"></p>

            <button type="submit"
                class="w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">Register</button>
        </form>

        <p class="text-center text-gray-600 mt-4">Already have an account? 
            <a href="login.php" class="text-blue-500 hover:underline">Login</a>
        </p>
    </div>
</body>
</html>
