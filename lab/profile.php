<?php
require_once 'config.php';
require_once 'functions.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user information
$query = "SELECT username, email, avatar_url FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $new_password = sanitize_input($_POST['new_password']);
    $avatar = $_FILES['avatar'];

    if (empty($username) || empty($email)) {
        $error = "Username and email are required.";
    } else {
        // Update user information
        $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['username'] = $username;
            $success = "Profile updated successfully.";

            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = hash_password($new_password);
                $query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                mysqli_stmt_execute($stmt);
                $success .= " Password updated.";
            }

            // Handle avatar upload if provided
            if (!empty($avatar['name'])) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (in_array($avatar['type'], $allowed_types)) {
                    $upload_dir = 'uploads/avatars/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
                    }

                    $file_name = time() . '_' . basename($avatar['name']);
                    $target_file = $upload_dir . $file_name;

                    if (move_uploaded_file($avatar['tmp_name'], $target_file)) {
                        // Update the user's avatar URL in the database
                        $query = "UPDATE users SET avatar_url = ? WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "si", $target_file, $user_id);
                        if (mysqli_stmt_execute($stmt)) {
                            // Update session with new avatar URL
                            $_SESSION['avatar_url'] = $target_file;
                            $success .= " Avatar updated.";
                        } else {
                            $error = "Failed to update avatar in database.";
                        }
                    } else {
                        $error = "Failed to upload avatar.";
                    }
                } else {
                    $error = "Invalid file type for avatar. Allowed types: JPEG, PNG, GIF.";
                }
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
<body class="bg-gray-100">
<header class="bg-blue-600 text-white shadow-md py-3">
    <div class="container mx-auto flex justify-between items-center px-6">
        <div class="flex items-center space-x-4">
            <img src="<?php echo $_SESSION['avatar_url'] ?? 'img/user.jpg'; ?>" alt="User Avatar" class="w-12 h-12 rounded-full">
            <h1 class="text-lg font-bold">Welcome, <?php echo $_SESSION['username']; ?>!</h1>
        </div>
        <nav class="hidden md:flex space-x-4">
            <a href="dashboard.php" class="hover:underline">Dashboard</a>
            <a href="logout.php" class="hover:underline">Logout</a>
        </nav>
        <div class="md:hidden">
            <button id="mobile-menu-button" class="text-white focus:outline-none" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
        </div>
    </div>
    <nav id="mobile-menu" class="md:hidden bg-blue-500 px-4 py-2 hidden">
        <a href="profile.php" class="block py-2 text-white hover:underline">Profile</a>
        <a href="logout.php" class="block py-2 text-white hover:underline">Logout</a>
    </nav>
</header>

<div class="container mx-auto p-6">
    <form method="POST" action="" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-8 space-y-4">
        <h2 class="text-2xl font-bold text-gray-800">Update Your Profile</h2>
        
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
            <label for="avatar" class="block text-sm font-medium text-gray-700">Avatar (optional)</label>
            <input type="file" id="avatar" name="avatar"
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

        <?php if ($success): ?>
            <p class="text-green-600 mt-4"><?php echo $success; ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="text-red-600 mt-4"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
</div>

<script>
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    mobileMenuButton.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>
</body>
</html>
