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
            <svg class="w-20 h-20 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        </div>

        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-6">
            Welcome to Online To-Do List
        </h1>
        
        <p class="text-gray-600 mb-8 text-lg">
            Manage your tasks efficiently and stay organized with our intuitive task management system!
        </p>

        <nav class="space-y-4">
    <a href="login.php" 
       class="block bg-gradient-to-r from-blue-500 to-blue-700 text-white py-4 rounded-xl font-medium hover:from-blue-600 hover:to-blue-800 transition duration-300 transform hover:-translate-y-1 shadow-lg text-center">
        Login to Your Account
    </a>
    <a href="register.php" 
       class="block bg-gradient-to-r from-green-500 to-green-700 text-white py-4 rounded-xl font-medium hover:from-green-600 hover:to-green-800 transition duration-300 transform hover:-translate-y-1 shadow-lg text-center">
        Create New Account
    </a>
</nav>


        <!-- Features section -->
        <div class="mt-12 grid grid-cols-2 gap-4 text-center">
            <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
                <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-gray-600">Real-time Updates</p>
            </div>
            <div class="p-4 rounded-lg bg-white/50 backdrop-blur-sm">
                <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <p class="text-sm text-gray-600">Secure & Private</p>
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