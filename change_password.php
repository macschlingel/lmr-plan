
<!-- change_password.php -->
<?php
require_once 'auth/auth.php';
requireLogin();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        echo "Passwords do not match.";
        exit();
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT password FROM volunteers WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($currentPassword, $user['password'])) {
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE volunteers SET password = :password WHERE id = :id");
        $updateStmt->execute([':password' => $newPasswordHash, ':id' => $_SESSION['user_id']]);
        echo "Password updated.";
    } else {
        echo "Incorrect current password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <form action="change_password.php" method="post">
        <label for="current_password">Current Password</label>
        <input type="password" name="current_password" required>

        <label for="new_password">New Password</label>
        <input type="password" name="new_password" required>

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
