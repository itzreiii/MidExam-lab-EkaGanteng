<?php
require_once 'config.php';
require_once 'functions.php';
include 'header.php';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Profile Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-8 sm:px-10">
                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <!-- Current Avatar Display -->
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-lg">
                            <img 
                                src="<?php echo !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url']) : 'default-avatar.png'; ?>" 
                                alt="Profile Avatar"
                                class="w-full h-full object-cover"
                            >
                        </div>
                    </div>
                    <div class="text-center sm:text-left">
                        <h1 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($user['username']); ?></h1>
                        <p class="text-blue-100"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <form method="POST" action="" enctype="multipart/form-data" class="p-6 sm:p-10 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username Field -->
                    <div class="space-y-2">
                        <label for="username" class="text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($user['username']); ?>" 
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50"
                        >
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-envelope mr-2 text-blue-500"></i>
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($user['email']); ?>" 
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50"
                        >
                    </div>

                    <!-- Avatar Upload -->
                    <div class="space-y-2">
                        <label for="avatar" class="text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-image mr-2 text-blue-500"></i>
                            Update Avatar
                        </label>
                        <div class="relative">
                            <input 
                                type="file" 
                                id="avatar" 
                                name="avatar"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            >
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label for="new_password" class="text-sm font-medium text-gray-700 flex items-center">
                            <i class="fas fa-lock mr-2 text-blue-500"></i>
                            New Password
                        </label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50"
                            placeholder="Leave blank to keep current password"
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 rounded-lg font-medium hover:from-blue-600 hover:to-indigo-700 transition duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-md">
                        <i class="fas fa-save mr-2"></i>
                        Update Profile
                    </button>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mt-4 rounded">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <p class="text-green-700"><?php echo $success; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mt-4 rounded">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <p class="text-red-700"><?php echo $error; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
