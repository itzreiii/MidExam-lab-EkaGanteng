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

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
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
            Click <a href="http://localhost/uts/webprog-lab/lab/activate-account.php?token=$activation_token">here</a> to activate your account.
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
        // Optional: Clear the form fields on page load
        window.onload = function() {
            document.getElementById("registerForm").reset();
        };
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

            <input type="password" name="password" placeholder="Password" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <button type="submit"
                class="w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">Register</button>
        </form>

        <p class="text-center text-gray-600 mt-4">Already have an account? 
            <a href="login.php" class="text-blue-500 hover:underline">Login</a>
        </p>
    </div>
</body>
</html>
