<!-- functions.php -->
<?php
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($location) {
    header("Location: $location");
    exit();
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}
?>