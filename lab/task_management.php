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
    $task_id = intval($_POST['task_id']);
    $query = "UPDATE tasks SET completed = NOT completed WHERE id = ? AND list_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $task_id, $list_id);
    mysqli_stmt_execute($stmt);
}

// Delete a task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = intval($_POST['task_id']);
    $query = "DELETE FROM tasks WHERE id = ? AND list_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $task_id, $list_id);
    mysqli_stmt_execute($stmt);
}

// Initialize search and filter parameters
$search_term = isset($_GET['search']) ? trim(sanitize_input($_GET['search'])) : '';
$filter = isset($_GET['filter']) ? sanitize_input($_GET['filter']) : 'all';

// Build the base query
$query = "SELECT * FROM tasks WHERE list_id = ?";
$params = [$list_id];
$types = "i";

// Add search condition if search term is provided
if (!empty($search_term)) {
    $query .= " AND title LIKE ?";
    $params[] = "%{$search_term}%";
    $types .= "s";
}

// Add filter condition
switch ($filter) {
    case 'completed':
        $query .= " AND completed = 1";
        break;
    case 'incomplete':
        $query .= " AND completed = 0";
        break;
    // 'all' doesn't need additional conditions
}

// Add ordering
$query .= " ORDER BY created_at DESC";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    // Only bind parameters if we have any
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // Handle query preparation error
    $tasks = [];
    error_log("Failed to prepare statement: " . mysqli_error($conn));
}

$avatar_url = 'img/tasks.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - <?php echo htmlspecialchars($list['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .nav-link {
            position: relative;
            padding: 5px 0;
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .nav-link::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .nav-link:hover {
            color: rgba(255, 255, 255, 0.9);
        }
        .nav-link:hover::after,
        .nav-link:focus::after {
            transform: scaleX(1);
        }
        .task-card {
            transition: all 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-2px);
        }
        .checkbox-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .custom-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid #4F46E5;
            border-radius: 4px;
            transition: all 0.2s ease;
            position: relative;
        }
        .custom-checkbox::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            color: white;
            transition: transform 0.2s ease;
        }
        .task-completed {
            background-color: #4F46E5;
        }
        .task-completed::after {
            transform: translate(-50%, -50%) scale(1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <header class="bg-blue-600 text-white shadow-md py-3">
        <div class="container mx-auto flex justify-between items-center px-6">
            <div class="flex items-center space-x-6">
                <img src="<?php echo $avatar_url; ?>" alt="User Avatar" class="w-12 h-12 square-full mx-auto mb-0">
                <h1 class="text-lg font-bold">Task Management for "<?php echo htmlspecialchars($list['title']); ?>"</h1>
            </div>
            <nav class="hidden md:flex space-x-4">
                <a href="dashboard.php" class=" nav-link">Dashboard</a>
                <a href="profile.php" class=" nav-link">Profile</a>
                <a href="logout.php" class=" nav-link">Logout</a>
            </nav>
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-white focus:outline-none" aria-expanded="false" aria-controls="mobile-menu">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </div>
        <nav id="mobile-menu" class="md:hidden bg-blue-500 px-4 py-2 hidden">
            <a href="dashboard.php" class="block py-2 text-white  nav-link">Dashboard</a>
            <a href="profile.php" class="block py-2 text-white  nav-link">Profile</a>
            <a href="logout.php" class="block py-2 text-white  nav-link">Logout</a>
        </nav>
    </header>

    <div class="container mx-auto p-4 md:p-6">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-plus-circle text-blue-500 mr-2"></i>
                Add New Task
            </h2>
            <form method="POST" action="" class="flex flex-col sm:flex-row gap-4">
                <input type="text" name="task_title" required
                    placeholder="What needs to be done?"
                    class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                >
                <button type="submit" name="add_task" 
                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i>Add Task
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <i class="fas fa-search text-blue-500 mr-2"></i>
        Search & Filter
    </h2>
    <form method="GET" action="" class="flex flex-col sm:flex-row gap-4">
        <!-- Add hidden input for list_id -->
        <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
        
        <input type="text" 
               name="search" 
               value="<?php echo htmlspecialchars($search_term); ?>"
               placeholder="Search tasks..."
               class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
        
        <select name="filter"
                class="px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
            <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All</option>
            <option value="completed" <?php echo $filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="incomplete" <?php echo $filter == 'incomplete' ? 'selected' : ''; ?>>Incomplete</option>
        </select>
        
        <button type="submit"
                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-search mr-2"></i>Search
        </button>
    </form>
</div>

        <?php foreach ($tasks as $task): ?>
            <div class="bg-white rounded-lg shadow-md p-4 mb-4 task-card">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="checkbox-wrapper">
                            <input type="checkbox"
                                data-task-id="<?php echo $task['id']; ?>"
                                class="toggle-checkbox hidden"
                                <?php echo $task['completed'] ? 'checked' : ''; ?>>
                            <div class="custom-checkbox <?php echo $task['completed'] ? 'task-completed' : ''; ?>"></div>
                        </div>
                        <h3 class="text-lg font-medium <?php echo $task['completed'] ? 'line-through text-gray-400' : 'text-gray-800'; ?>">
                            <?php echo htmlspecialchars($task['title']); ?>
                        </h3>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                        <button type="submit" name="delete_task"
                            class="text-red-500 hover:text-red-700 transition-colors duration-200 focus:outline-none">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });

        // Custom checkbox functionality
        document.querySelectorAll('.checkbox-wrapper').forEach(function(wrapper) {
            const checkbox = wrapper.querySelector('.toggle-checkbox');
            const customCheckbox = wrapper.querySelector('.custom-checkbox');
            const taskTitle = wrapper.closest('.task-card').querySelector('h3');

            wrapper.addEventListener('click', function() {
                const isChecked = !checkbox.checked;
                checkbox.checked = isChecked;

                // Toggle task completion styles
                customCheckbox.classList.toggle('task-completed', isChecked);
                taskTitle.classList.toggle('line-through', isChecked);
                taskTitle.classList.toggle('text-gray-400', isChecked);
                taskTitle.classList.toggle('text-gray-800', !isChecked);

                // Send update to server
                const formData = new FormData();
                formData.append('task_id', checkbox.getAttribute('data-task-id'));
                formData.append('toggle_task', '1');

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.ok ? console.log('Task status updated') : console.error('Update failed'))
                .catch(error => console.error('Error:', error));
            });
        });

        // Add fade-in animation to task cards
        document.querySelectorAll('.task-card').forEach((card, index) => {
            card.style.opacity = '0';
            setTimeout(() => {
                card.classList.add('fade-in');
            }, index * 100);
        });
    </script>
</body>
</html>
