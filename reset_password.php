<?php
require 'dbconnect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST['password']; // You should hash this!
        
        $stmt = $connection->prepare("UPDATE trainees SET password_hash = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $new_password, $token);
        
        if ($stmt->execute()) {
            echo "Password updated! You can now <a href='index.php'>Login</a>";
        }
    }
}
?>

<form method="POST">
    <input type="password" name="password" placeholder="Enter new password" required>
    <button type="submit">Update Password</button>
</form>