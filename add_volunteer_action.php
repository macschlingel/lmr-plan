<?php
include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $color = $_POST['color'] ?? '#007bff'; // Default color if not set
    $password = password_hash('defaultpassword', PASSWORD_BCRYPT); // Set a default password

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO volunteers (name, email, role, color, password) VALUES (:name, :email, :role, :color, :password)");

    try {
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':color' => $color,
            ':password' => $password
        ]);
        header('Location: manage_volunteers.php'); // Redirect back to the management page
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}