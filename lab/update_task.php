<?php
require_once 'config.php'; // File koneksi ke database
require_once 'functions.php'; // File yang berisi fungsi tambahan seperti sanitize_input

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = sanitize_input($_POST['task_id']);
    $is_completed = sanitize_input($_POST['completed']);

    // Pastikan hanya task dari user yang terautentikasi yang bisa diupdate
    $user_id = $_SESSION['user_id'];

    // Update status 'completed' pada task yang terkait
    $query = "UPDATE tasks 
              JOIN todo_lists ON tasks.list_id = todo_lists.id 
              SET tasks.completed = ? 
              WHERE tasks.id = ? AND todo_lists.user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iii', $is_completed, $task_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
