<?php
require_once 'config.php';
require_once 'functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$list_id = isset($_GET['list_id']) ? sanitize_input($_GET['list_id']) : null;

if (!$list_id) {
    die("List ID is required");
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
    $query = "UPDATE tasks SET completed = NOT completed WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $task_id);
    mysqli_stmt_execute($stmt);
}

// Delete a task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = sanitize_input($_POST['task_id']);
    $query = "DELETE FROM tasks WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $task_id);
    mysqli_stmt_execute($stmt);
}

// Search and filter functionality
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter = isset($_GET['filter']) ? sanitize_input($_GET['filter']) : 'all';

$where_clause = "WHERE list_id = ?";
$where_clause .= $search_term ? " AND title LIKE ?" : "";
$where_clause .= $filter == 'completed' ? " AND completed = 1" : ($filter == 'incomplete' ? " AND completed = 0" : "");

$query = "SELECT * FROM tasks $where_clause ORDER BY created_at DESC";

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

// Fetch list details
$query = "SELECT * FROM todo_lists WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $list_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$list = mysqli_fetch_assoc($result);

if (!$list) {
    die("List not found or you don't have permission to access it");
}
?>

<h2><?php echo htmlspecialchars($list['title']); ?></h2>

<h3>Search and Filter Tasks</h3>
<form method="GET" action="">
    <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
    <input type="text" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search_term); ?>">
    <select name="filter">
        <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Tasks</option>
        <option value="completed" <?php echo $filter == 'completed' ? 'selected' : ''; ?>>Completed Tasks</option>
        <option value="incomplete" <?php echo $filter == 'incomplete' ? 'selected' : ''; ?>>Incomplete Tasks</option>
    </select>
    <button type="submit">Search and Filter</button>
</form>

<form method="POST" action="">
    <input type="text" name="task_title" placeholder="New task" required>
    <button type="submit" name="add_task">Add Task</button>
</form>

<ul>
    <?php foreach ($tasks as $task): ?>
        <li>
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <input type="checkbox" name="toggle_task" onchange="this.form.submit()" <?php echo $task['completed'] ? 'checked' : ''; ?>>
            </form>
            <?php echo htmlspecialchars($task['title']); ?>
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <button type="submit" name="delete_task" onclick="return confirm('Are you sure you want to delete this task?')">Delete</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>