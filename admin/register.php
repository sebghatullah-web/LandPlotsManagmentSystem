<?php
session_start();
require 'config/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role     = $conn->real_escape_string($_POST['role']);

    // بررسی وجود یوزر
    $check = $conn->query("SELECT * FROM tblUsers WHERE username='$username'");
    if($check->num_rows > 0){
        $error = "Username already exists!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO tblUsers (username, password, role, created_at) VALUES ('$username', '$hashedPassword', '$role', NOW())";
        if($conn->query($sql)){
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KCC | Registration</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:400px;">
    <h2 class="text-center mb-4">Register</h2>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" class="form-control" name="username" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select class="form-select" name="role" required>
                <option value="">Select Role</option>
                <option value="Admin">Admin</option>
                <option value="Sales">Sales</option>
                <option value="Viewer">Viewer</option>
            </select>
        </div>
        <button class="btn btn-success w-100">Register</button>
    </form>
    <p class="mt-2 text-center">Already have account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
