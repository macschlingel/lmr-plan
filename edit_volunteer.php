<?php
include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';
include_once 'header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    // Fetch volunteer details for editing
    $volunteerId = $_GET['id'];
    $volunteerStmt = $db->prepare("SELECT * FROM volunteers WHERE id = :id");
    $volunteerStmt->execute([':id' => $volunteerId]);
    $volunteer = $volunteerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$volunteer) {
        echo "Volunteer not found.";
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    // Update volunteer details
    $volunteerId = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $color = $_POST['color'] ?? '#007bff'; // Default color if not set

    $updateStmt = $db->prepare("UPDATE volunteers SET name = :name, email = :email, role = :role, color = :color WHERE id = :id");

    try {
        $updateStmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':color' => $color,
            ':id' => $volunteerId
        ]);
        header('Location: manage_volunteers.php'); // Redirect back to the management page after update
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
    exit();
}
?>

<h1 class="mb-4">Edit Volunteer</h1>

<form action="edit_volunteer.php" method="post">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($volunteer['id']); ?>">
    <div class="form-group">
        <label for="name">Volunteer Name:</label>
        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($volunteer['name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($volunteer['email']); ?>" required>
    </div>
    <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="role" class="form-control">
            <option value="volunteer" <?php echo ($volunteer['role'] == 'volunteer') ? 'selected' : ''; ?>>Volunteer</option>
            <option value="admin" <?php echo ($volunteer['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
    </div>
    <div class="form-group">
        <label for="color">Color:</label>
        <input type="color" name="color" id="color" class="form-control" value="<?php echo htmlspecialchars($volunteer['color'] ?? '#007bff'); ?>">
    </div>
    <button type="submit" class="btn btn-success">Save Changes</button>
    <a href="manage_volunteers.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include_once 'footer.php'; ?>