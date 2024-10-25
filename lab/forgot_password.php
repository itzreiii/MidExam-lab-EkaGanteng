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
            
            $reset_link = "https://darkslategray-woodpecker-113089.hostingersite.com/reset_password.php?token=$reset_token";
            // $reset_link = "http://localhost/webprog/webprog-lab/lab/reset_password.php?token=$reset_token";
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
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-700 text-center mb-6">Forgot Password</h1>

        <?php if ($error) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>
        <?php if ($success) echo "<p class='text-green-500 text-center mb-4'>$success</p>"; ?>

        <form method="POST" action="" class="space-y-4">
            <input type="email" name="email" placeholder="Email" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <button type="submit"
                class="w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">Send Password Reset Link</button>
        </form>
    </div>
</body>
</html>
