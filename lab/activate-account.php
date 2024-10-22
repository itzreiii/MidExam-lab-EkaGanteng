<?php

// Get the token from the URL
if (!isset($_GET["token"])) {
    die("Invalid activation link.");
}

$token = $_GET["token"];
$token_hash = hash("sha256", $token);

// Database connection
$mysqli = require __DIR__ . "/config.php";

// Prepare and execute the SQL statement to find the user by token
$sql = "SELECT id FROM users WHERE account_activation_hash = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    die("Invalid or expired token.");
}

// Update the user to remove the activation hash
$sql = "UPDATE users SET account_activation_hash = NULL WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user["id"]);

if ($stmt->execute()) {
    // Success: account activated
    $success = true;
} else {
    // Failed to update the account
    die("Failed to activate account. Please try again.");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Account activated</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>

    <h1>Account activated</h1>

    <?php if ($success): ?>
        <p>Your account has been successfully activated! You can now <a href="login.php">login</a>.</p>
    <?php endif; ?>

</body>
</html>
