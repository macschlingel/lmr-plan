<?php
require_once 'auth/auth.php';
requireAdmin(); // Ensure only admins can access this page
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $db = getDB();
    $stmt = $db->prepare("DELETE FROM stores WHERE id = :id");

    try {
        $stmt->execute([':id' => $id]);
        header('Location: manage_stores.php'); // Redirect back to the management page
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}