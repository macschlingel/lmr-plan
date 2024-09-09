<?php
include 'config/db.php';
require_once 'auth/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM volunteers WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php'); // Redirect to the main page
        exit();
    } else {
        echo "Invalid username or password.";
    }
}