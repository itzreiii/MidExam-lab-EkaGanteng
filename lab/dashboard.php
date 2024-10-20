<?php
require_once 'config.php';
require_once 'functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Create a new to-do list
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_list'])) {
    $list_title = sanitize_input($_POST['list_title']);
    $query = "INSERT INTO todo_lists (user_id, title) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $list_title);
    mysqli_stmt_execute($stmt);
}

// Delete a to-do list
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_list'])) {
    $list_id = sanitize_input($_POST['list_id']);
    $query = "DELETE FROM todo_lists WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $list_id, $user_id);
    mysqli_stmt_execute($stmt);
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
</head>
<body class="bg-gray-100">
    <!-- Include the header -->
    <?php include 'header.php'; ?>

    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold text-gray-700 mb-4">Create a New To-Do List</h2>
        <form method="POST" action="" class="mb-6 bg-white p-4 rounded-lg shadow-md">
            <input type="text" name="list_title" placeholder="List Title" required
                class="w-full p-3 border border-gray-300 rounded-lg mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" name="create_list" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-200">Create List</button>
        </form>

        <h2 class="text-2xl font-bold text-gray-700 mb-4">Your To-Do Lists</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($todo_lists as $list): ?>
                <div class="todo-list bg-white rounded-lg shadow-lg overflow-hidden transition-transform transform hover:scale-105">
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($list['title']); ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?php echo $list['completed_count']; ?>/<?php echo $list['task_count']; ?> completed</p>
                        <div class="flex justify-between items-center mt-4">
                            <form method="POST" action="" class="flex-1 mr-2">
                                <input type="hidden" name="list_id" value="<?php echo $list['id']; ?>">
                                <button type="submit" name="delete_list" class="w-full text-red-500 font-semibold py-1 px-3 border border-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-200" onclick="return confirm('Are you sure you want to delete this list?')">Delete List</button>
                            </form>
                            <a href="task_management.php?list_id=<?php echo $list['id']; ?>" class="bg-blue-500 text-white font-semibold py-1 px-3 rounded-lg shadow hover:bg-blue-600 transition duration-200">Manage Tasks</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
