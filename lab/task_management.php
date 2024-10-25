<?php
require_once 'config.php';
require_once 'functions.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$list_id = isset($_GET['list_id']) ? intval($_GET['list_id']) : 0;

// Verify that the list belongs to the current user
$query = "SELECT * FROM todo_lists WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $list_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$list = mysqli_fetch_assoc($result);

if (!$list) {
    redirect('dashboard.php');
}

// Add a new task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $task_title = sanitize_input($_POST['task_title']);
    $query = "INSERT INTO tasks (list_id, title) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $list_id, $task_title);
    mysqli_stmt_execute($stmt);
}

// Toggle task completion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_task'])) {
    $task_id = sanitize_input($_POST['task_id']);
    $query = "UPDATE tasks SET completed = NOT completed WHERE id = ? AND list_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $task_id, $list_id);
    mysqli_stmt_execute($stmt);
}

// Delete a task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = sanitize_input($_POST['task_id']);
    $query = "DELETE FROM tasks WHERE id = ? AND list_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $task_id, $list_id);
    mysqli_stmt_execute($stmt);
}

// Search and filter functionality
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter = isset($_GET['filter']) ? sanitize_input($_GET['filter']) : 'all';

$where_clause = $search_term ? "AND title LIKE ?" : "";
$where_clause .= $filter == 'completed' ? " AND completed = 1" : ($filter == 'incomplete' ? " AND completed = 0" : "");

$query = "SELECT * FROM tasks 
          WHERE list_id = ? $where_clause
          ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $query);

if ($search_term) {
    $search_term = "%$search_term%";
    mysqli_stmt_bind_param($stmt, "is", $list_id, $search_term);
} else {
    mysqli_stmt_bind_param($stmt, "i", $list_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);


$avatar_url = 'img/tasks.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - <?php echo htmlspecialchars($list['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Header Section -->
    <header class="bg-blue-600 text-white shadow-md py-3">
    <div class="container mx-auto flex justify-between items-center px-6">
        <!-- Left: User Welcome & Avatar -->
        <div class="flex items-center space-x-6">
            <img src="<?php echo $avatar_url; ?>" alt="User Avatar" class="w-14 h-14 rounded-full mx-auto mb-0">
            <h1 class="text-lg font-bold">Task Management for "<?php echo htmlspecialchars($list['title']); ?>"</h1>
        </div>

        <!-- Right: Navigation for large screens -->
        <nav class="hidden md:flex space-x-4">
            <a href="dashboard.php" class=" nav-link">Dashboard</a>
            <a href="profile.php" class=" nav-link">Profile</a>
            <a href="logout.php" class=" nav-link">Logout</a>
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
        <a href="dashboard.php" class="block py-2 text-white  nav-link">Dashboard</a>
        <a href="profile.php" class="block py-2 text-white  nav-link">Profile</a>
        <a href="logout.php" class="block py-2 text-white  nav-link">Logout</a>
    </nav>
</header>


    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold text-gray-700 mb-4">Add a New Task</h2>
        <form method="POST" action="" class="mb-6 bg-white p-4 rounded-lg shadow-md flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
            <input type="text" name="task_title" placeholder="New task" required class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" name="add_task" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">Add Task</button>
        </form>

        <h2 class="text-2xl font-bold text-gray-700 mb-4">Search and Filter Tasks</h2>
        <form method="GET" action="" class="mb-6 bg-white p-4 rounded-lg shadow-md flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
            <input type="text" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search_term); ?>" class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="filter" class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Tasks</option>
                <option value="completed" <?php echo $filter == 'completed' ? 'selected' : ''; ?>>Completed Tasks</option>
                <option value="incomplete" <?php echo $filter == 'incomplete' ? 'selected' : ''; ?>>Incomplete Tasks</option>
            </select>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">Search</button>
        </form>

        <h2 class="text-2xl font-bold text-gray-700 mb-4">Tasks</h2>
        <!-- Tasks Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($tasks as $task): ?>
                <div class="bg-white p-4 rounded-lg shadow-md flex justify-between items-center transition duration-200 hover:shadow-lg">
                    <input type="checkbox" data-task-id="<?php echo $task['id']; ?>" class="toggle-checkbox mr-2 h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo $task['completed'] ? 'checked' : ''; ?>>
                    <span class="<?php echo $task['completed'] ? 'line-through text-gray-400' : 'text-gray-800'; ?> text-lg flex-1">
                        <?php echo htmlspecialchars($task['title']); ?>
                    </span>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                        <button type="submit" name="delete_task" class="bg-red-500 text-white py-1 px-3 rounded-lg hover:bg-red-600 transition duration-200" onclick="return confirm('Are you sure you want to delete this task?')">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>


    </div>

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

/* New styles for task cards */
.bg-white:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

button[name="delete_task"] {
    transition: background-color 0.3s ease;
}

button[name="delete_task"]:hover {
    background-color: rgba(255, 0, 0, 0.7); /* Slightly lighter red on hover */
}

.task-title {
    transition: color 0.3s ease;
}

.bg-white:hover .task-title {
    color: inherit; /* Keep the original color */
}
    

</style>

    <script>
        // Toggle mobile menu visibility
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Handle task completion toggle via AJAX and update task title style
        document.querySelectorAll('.toggle-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const taskId = this.getAttribute('data-task-id');
                const isCompleted = this.checked ? 1 : 0;
                const taskTitleElement = this.closest('div').querySelector('span');

                // Update task title style based on completion status in real-time
                if (isCompleted) {
                    taskTitleElement.classList.add('line-through', 'text-gray-400');
                    taskTitleElement.classList.remove('text-gray-800');
                } else {
                    taskTitleElement.classList.remove('line-through', 'text-gray-400');
                    taskTitleElement.classList.add('text-gray-800');
                }

                // Create form data to send to the server
                const formData = new FormData();
                formData.append('task_id', taskId);
                formData.append('completed', isCompleted);

                // Send the request to update the task status
                fetch('update_task.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to update task');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>


</body>
</html>