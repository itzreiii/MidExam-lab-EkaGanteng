<?php

require_once('./config.php');
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// $query = "SELECT l.*, 
//           (SELECT COUNT(*) FROM tasks t WHERE t.list_id = l.id) as task_count,
//           (SELECT COUNT(*) FROM tasks t WHERE t.list_id = l.id AND t.completed = 1) as completed_count
//           FROM todo_lists l WHERE l.user_id = ? ORDER BY l.created_at DESC";
// $stmt = mysqli_prepare($conn, $query);
// mysqli_stmt_bind_param($stmt, "i", $user_id);
// mysqli_stmt_execute($stmt);
// $result = mysqli_stmt_get_result($stmt);
// $todo_lists = mysqli_fetch_all($result, MYSQLI_ASSOC);


$user_id = $_SESSION['user_id'];

// Get avatar URL from session or set default
$get_avatar_query = "SELECT avatar_url FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $get_avatar_query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$temp_url = mysqli_fetch_assoc($result);
$avatar_url = $temp_url['avatar_url'];

if (!isset($avatar_url)) {
    $avatar_url = 'img/user.jpg';
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
?>
<style>
.nav-link {
    position: relative;
    padding: 5px 0; /* Optional padding for better click area */
    color: white; /* Base color */
    text-decoration: none; /* Remove underline */
    transition: color 0.3s ease; /* Smooth transition for color change */
}

.nav-link::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    height: 3px; /* Thickness of the underline */
    width: 100%; /* Full width */
    background-color: rgba(255, 255, 255, 0.7); /* Underline color */
    transform: scaleX(0); /* Start hidden */
    transition: transform 0.3s ease; /* Smooth transition for underline */
}

.nav-link:hover {
    color: rgba(255, 255, 255, 0.9); /* Change color on hover */
}

.nav-link:focus,
.nav-link:active {
    color: rgba(255, 255, 255, 0.9); /* Keep color on focus/active */
}

/* Show the underline when hovered or focused */
.nav-link:hover::after,
.nav-link:focus::after {
    transform: scaleX(1); /* Scale underline to full width */
}

</style>

<header class="bg-blue-600 text-white shadow-md py-3">
    <div class="container mx-auto flex justify-between items-center px-6">
        <!-- Left: User Welcome & Avatar -->
        <div class="flex items-center space-x-4">
            <img src="<?php echo $avatar_url; ?>" alt="User Avatar" class="w-12 h-12 rounded-full mx-auto mb-0">
            <h1 class="text-lg font-bold">Welcome, <?php echo $username; ?>!</h1>
        </div>

        <!-- Right: Navigation for large screens -->
        <nav class="hidden md:flex space-x-4">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="profile.php" class="nav-link">Profile</a>
            <a href="logout.php" class="nav-link">Logout</a>
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
        <a href="dashboard.php" class="block py-2 text-white nav-link">Dashboard</a>
        <a href="profile.php" class="block py-2 text-white nav-link">Profile</a>
        <a href="logout.php" class="block py-2 text-white nav-link">Logout</a>
    </nav>
</header>

<script>
    // Toggle mobile menu visibility
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        // Toggle 'hidden' class to show/hide the menu
        menu.classList.toggle('hidden');
        
        // Toggle the aria-expanded attribute for accessibility
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isExpanded);
    });
</script>
