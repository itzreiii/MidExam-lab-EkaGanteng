<!-- config.php -->
<?php
define('DB_HOST', 'localhost');

// define('DB_USER', 'u979249421_todo_list_db');
// define('DB_PASS', 'Todolist94709');
// define('DB_NAME', 'u979249421_todo_list_db');

define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todo_list_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();
return $conn;
?>