<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'header.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];

// Create a new to-do list
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_list'])) {
    $list_title = sanitize_input($_POST['list_title']);
    $query = "INSERT INTO todo_lists (user_id, title) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $list_title);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "New todo list created successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to create new list.";
    }
    redirect($_SERVER['PHP_SELF']);
}

// Delete a to-do list
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_list'])) {
    $list_id = sanitize_input($_POST['list_id']);
    $query = "DELETE FROM todo_lists WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $list_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "List deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete list.";
    }
    redirect($_SERVER['PHP_SELF']);
}

// Fetch user's to-do lists with task counts
$query = "SELECT l.*, 
          (SELECT COUNT(*) FROM tasks t WHERE t.list_id = l.id) as task_count,
          (SELECT COUNT(*) FROM tasks t WHERE t.list_id = l.id AND t.completed = 1) as completed_count
          FROM todo_lists l WHERE l.user_id = ? ORDER BY l.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$todo_lists = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Online To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .todo-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .todo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .progress-bar {
            transition: width 0.3s ease-in-out;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .alert {
            animation: slideIn 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">

   

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-medium">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']); 
                    unset($_SESSION['success_message']);
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-medium">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']); 
                    unset($_SESSION['error_message']);
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Create New List Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-plus-circle text-blue-500 mr-2"></i>
                Create New Todo List
            </h2>
            <form method="POST" action="" class="flex flex-col sm:flex-row gap-4">
                <input type="text" name="list_title" required
                    placeholder="Enter list title..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200"
                >
                <button type="submit" name="create_list" 
                    class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>
                    Create List
                </button>
            </form>
        </div>

        <!-- Todo Lists Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($todo_lists as $list): ?>
                <?php 
                    $completion_percentage = $list['task_count'] > 0 
                        ? ($list['completed_count'] / $list['task_count']) * 100 
                        : 0;
                ?>
                <div class="todo-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                <?php echo htmlspecialchars($list['title']); ?>
                            </h3>
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="list_id" value="<?php echo $list['id']; ?>">
                                <button type="submit" name="delete_list" 
                                    class="text-gray-400 hover:text-red-500 transition-colors duration-200"
                                    onclick="return confirm('Are you sure you want to delete this list?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                                <span>
                                    <i class="fas fa-tasks mr-1"></i>
                                    Progress
                                </span>
                                <span><?php echo $list['completed_count']; ?>/<?php echo $list['task_count']; ?> tasks</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="progress-bar bg-blue-500 rounded-full h-2"
                                     style="width: <?php echo $completion_percentage; ?>%">
                                </div>
                            </div>
                        </div>

                        <a href="task_management.php?list_id=<?php echo $list['id']; ?>" 
                           class="block w-full text-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-tasks mr-2"></i>
                            Manage Tasks
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($todo_lists)): ?>
            <div class="text-center py-12">
                <i class="fas fa-clipboard-list text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600">You don't have any todo lists yet. Create one to get started!</p>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Auto-hide alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 3000);
            });
        });
    </script>
</body>
</html>