<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Get avatar URL from session or set default
$avatar_url = isset($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'img/user.jpg';
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
?>
<header class="bg-blue-600 text-white shadow-md py-3">
    <div class="container mx-auto flex justify-between items-center px-6">
        <!-- Left: User Welcome & Avatar -->
        <div class="flex items-center space-x-6">
            <img src="<?php echo $avatar_url; ?>" alt="User Avatar" class="w-14 h-14 rounded-full mx-auto mb-0">
            <h1 class="text-lg font-bold">Welcome, <?php echo $username; ?>!</h1>
        </div>

        <!-- Right: Navigation for large screens -->
        <nav class="hidden md:flex space-x-4">
            <a href="profile.php" class="hover:underline">Profile</a>
            <a href="logout.php" class="hover:underline">Logout</a>
        </nav>

        <!-- Mobile Menu Button -->
        <div class="md:hidden">
            <button id="mobile-menu-button" class="text-white focus:outline-none" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu (hidden by default) -->
    <nav id="mobile-menu" class="md:hidden bg-blue-500 px-4 py-2 hidden">
        <a href="profile.php" class="block py-2 text-white hover:underline">Profile</a>
        <a href="logout.php" class="block py-2 text-white hover:underline">Logout</a>
    </nav>
</header>
