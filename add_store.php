<?php
require_once 'auth/auth.php';
requireAdmin(); // Ensure only admins can access this page
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO stores (name) VALUES (:name)");

    try {
        $stmt->execute([':name' => $name]);
        header('Location: manage_stores.php'); // Redirect back to the management page
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}