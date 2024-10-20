<?php
require_once 'config.php';
require_once 'functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="text-center bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h1 class="text-4xl font-bold text-blue-600 mb-6">Welcome to Online To-Do List</h1>
        <p class="text-gray-700 mb-8">Manage your tasks efficiently and stay organized!</p>
        
        <nav class="space-y-4">
            <a href="login.php" class="block bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">Login</a>
            <a href="register.php" class="block bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition duration-200">Register</a>
        </nav>
    </div>

</body>
</html>
