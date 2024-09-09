<?php
include_once 'header.php'; // Include the common header
include 'config/db.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    echo "Invalid or missing token.";
    exit();
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM volunteers WHERE reset_token = :token AND reset_expiration > NOW()");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Invalid or expired token.";
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="text-center mb-4">Set New Password</h2>
        <form action="reset_password_action.php" method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
    </div>
</div>

<?php include_once 'footer.php'; ?> <!-- Include the common footer -->