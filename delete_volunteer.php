<?php
include_once 'auth/auth.php';
requireAdmin(); // Ensure only admins can access this page
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $volunteerId = $_POST['id'];

    $db = getDB();
    $stmt = $db->prepare("DELETE FROM volunteers WHERE id = :id");

    try {
        $stmt->execute([':id' => $volunteerId]);
        header('Location: manage_volunteers.php'); // Redirect back to the management page
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}