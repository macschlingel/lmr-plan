<?php
// reset_password_action.php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        die("Passwords do not match.");
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database and remove the token
    $db = getDB();
    $stmt = $db->prepare("UPDATE volunteers SET password = :password, reset_token = NULL, reset_expires = NULL WHERE reset_token = :token");
    $stmt->execute([
        ':password' => $hashedPassword,
        ':token' => $token
    ]);

    echo "Your password has been reset successfully.";
}