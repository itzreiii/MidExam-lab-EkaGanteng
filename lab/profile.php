
<!-- profile.php -->
<?php
require_once 'config.php';
require_once 'functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user information
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $new_password = sanitize_input($_POST['new_password']);

    if (empty($username) || empty($email)) {
        $error = "Username and email are required.";
    } else {
        $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['username'] = $username;
            $success = "Profile updated successfully.";

            if (!empty($new_password)) {
                $hashed_password = hash_password($new_password);
                $query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                mysqli_stmt_execute($stmt);
                $success .= " Password updated.";
            }
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Online To-Do List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>User Profile</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>

    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>

    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="new_password">New Password (leave blank to keep current password):</label>
        <input type="password" id="new_password" name="new_password">

        <button type="submit">Update Profile</button>
    </form>
</body>
</html>