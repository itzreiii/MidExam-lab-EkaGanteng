<?php

$token = $_GET["token"];

$token_hash = hash("sha256", $token);

$mysqli = require __DIR__ . "\config.php";

$sql = "SELECT * FROM users
        WHERE account_activation_hash = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user === null) {
    die("token not found");
}

$sql = "UPDATE users
        SET account_activation_hash = NULL
        WHERE id = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $user["id"]);

$stmt->execute();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account activated</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>

    <h1>Account acivated</h1>

    <p>Akun berhasil di aktivasi! Silahkan 
        <a href="login.php">login</a>
        ke akun anda.
    </p>

</body>
</html>