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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <nav>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>

    <h2>Create a New To-Do List</h2>
    <form method="POST" action="">
        <input type="text" name="list_title" placeholder="List Title" required>
        <button type="submit" name="create_list">Create List</button>
    </form>

    <h2>Your To-Do Lists</h2>
    <?php foreach ($todo_lists as $list): ?>
        <div class="todo-list">
            <h3><?php echo htmlspecialchars($list['title']); ?> (<?php echo $list['completed_count']; ?>/<?php echo $list['task_count']; ?> completed)</h3>
            <form method="POST" action="">
                <input type="hidden" name="list_id" value="<?php echo $list['id']; ?>">
                <button type="submit" name="delete_list" onclick="return confirm('Are you sure you want to delete this list?')">Delete List</button>
            </form>
            <a href="task_management.php?list_id=<?php echo $list['id']; ?>" class="button">Manage Tasks</a>
        </div>
    <?php endforeach; ?>
</body>
</html>