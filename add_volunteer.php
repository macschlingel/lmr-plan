<?php
include_once 'auth/auth.php';
requireAdmin(); // Ensure only admins can access this page
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash('defaultpassword', PASSWORD_BCRYPT); // Set a default password

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO volunteers (name, email, role, password) VALUES (:name, :email, :role, :password)");

    try {
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':password' => $password
        ]);
        header('Location: manage_volunteers.php'); // Redirect back to the management page
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}