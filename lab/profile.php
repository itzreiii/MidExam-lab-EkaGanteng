
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-lg bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-gray-700 text-center mb-6">User Profile</h1>

        <?php if ($error): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <p class="text-green-500 text-center mb-4"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                    class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                    class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (optional)</label>
                <input type="password" id="new_password" name="new_password"
                    class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>

            <button type="submit"
                class="w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">
                Update Profile
            </button>
        </form>

        <div class="mt-6 flex justify-between">
            <a href="dashboard.php" class="w-1/2 mr-1">
                <button class="w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition duration-200">
                    Dashboard
                </button>
            </a>
            <a href="logout.php" class="w-1/2 ml-1">
                <button class="w-full bg-red-500 text-white py-3 rounded-lg font-medium hover:bg-red-600 transition duration-200">
                    Logout
                </button>
            </a>
        </div>
    </div>
</body>
</html>

