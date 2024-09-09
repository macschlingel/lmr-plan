<?php
include 'header.php'; // Include header for Bootstrap and other necessary files
include 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $db = getDB();

    // Check if the email exists
    $stmt = $db->prepare('SELECT * FROM volunteers WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a secure token and expiration date
        $token = bin2hex(random_bytes(50));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store the token and expiry in the database
        $stmt = $db->prepare('UPDATE volunteers SET reset_token = ?, reset_token_expiry = ? WHERE email = ?');
        $stmt->execute([$token, $expiry, $email]);

        // Send the reset link via email
        $resetLink = "https://www.brucker-lebensmittelretter.de/plan/reset_password_form.php?token=$token";
        $subject = 'Password Reset Request';
        $message = "Click the following link to reset your password: $resetLink";
        mail($email, $subject, $message);

        $message = 'A password reset link has been sent to your email.';
    } else {
        $message = 'No account found with that email address.';
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Reset Password</h3>
                    <?php if ($message): ?>
                        <div class="alert alert-info text-center">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="reset_password.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Include footer ?>