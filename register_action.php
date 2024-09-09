
<?php
// register_action.php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO volunteers (username, password, role) VALUES (:username, :password, :role)");

    try {
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':role' => $role
        ]);
        echo "Registration successful!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}