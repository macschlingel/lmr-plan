<?php
include 'header.php'; // Include header for Bootstrap and other necessary files
include 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['password'];
    $db = getDB();

    // Check if the token is valid and not expired
    $stmt = $db->prepare('SELECT * FROM volunteers WHERE reset_token = ? AND reset_token_expiry > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update the password and clear the reset token
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE volunteers SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?');
        $stmt->execute([$hashedPassword, $token]);

        $message = 'Your password has been reset successfully. You can now <a href=\'login.php\'>login</a>.';
    } else {
        $message = 'Invalid or expired reset link.';
    }
} elseif (isset($_GET['token'])) {
    // Ensure the token is passed via GET
    $token = $_GET['token'];
} else {
    header('Location: reset_password.php');
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Reset Your Password</h3>
                    <?php if ($message): ?>
                        <div class="alert alert-info text-center">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!isset($message) || empty($message)): ?>
                    <form method="POST" action="reset_password_form.php">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter a new password" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Include footer ?>