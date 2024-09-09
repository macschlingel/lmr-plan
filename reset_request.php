<?php
include_once 'header.php'; // Include the common header
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="text-center mb-4">Reset Password</h2>
        <form action="reset_request_action.php" method="post">
            <div class="form-group">
                <label for="email">Enter your email address:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>
    </div>
</div>

<?php include_once 'footer.php'; ?> <!-- Include the common footer -->