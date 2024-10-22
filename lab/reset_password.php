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
                $success = "Password has been reset. You can now <a href='login.php'>login</a>.";
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
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-700 text-center mb-6">Reset Password</h1>

        <?php if ($error) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>
        <?php if ($success) echo "<p class='text-green-500 text-center mb-4'>$success</p>"; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" class="space-y-4">
            <input type="password" name="password" placeholder="New Password" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <input type="password" name="confirm_password" placeholder="Confirm Password" required
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">

            <button type="submit"
                class="w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
