<?php
include_once 'auth/auth.php';
requireAdmin(); // Ensure only admins can access this page
include 'config/db.php';

$db = getDB();
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new volunteer
    if (isset($_POST['add_volunteer'])) {
        $name = $_POST['name'];
        $color = $_POST['color'];
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0; // Check if is_admin is set
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO volunteers (name, color, is_admin, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $color, $isAdmin, $email, $password]);
        $message = 'Volunteer added successfully!';
    }

    // Edit existing volunteer
    if (isset($_POST['edit_volunteer'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $color = $_POST['color'];
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0; // Check if is_admin is set
        $email = $_POST['email'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE volunteers SET name = ?, color = ?, is_admin = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $color, $isAdmin, $email, $password, $id]);
        } else {
            $stmt = $db->prepare("UPDATE volunteers SET name = ?, color = ?, is_admin = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $color, $isAdmin, $email, $id]);
        }

        $message = 'Volunteer updated successfully!';
    }

    // Delete volunteer
    if (isset($_POST['delete_volunteer'])) {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM volunteers WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Volunteer deleted successfully!';
    }
}

// Fetch volunteers for display
$stmt = $db->query("SELECT * FROM volunteers ORDER BY name");
$volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; // Include header
?>

<div class="container mt-4">
    <h1 class="mb-4 text-center">Manage Volunteers</h1>
</div>

<?php if ($message): ?>
<div class="container mt-4">
    <div class="alert alert-success text-center">
        <?= htmlspecialchars($message) ?>
    </div>
</div>
<?php endif; ?>

<!-- Add Volunteer Form -->
<div class="container mt-4">
    <div class="container my-5">
        <form method="POST">
            <div class="form-floating mb-3">
                <label for="name">Name</label>
                <input class="form-control" id="name" name="name" type="text" placeholder="Name" required />
                <div class="invalid-feedback">Name is required.</div>
            </div>
            <div class="form-floating mb-3">
                <label for="email">Email Address</label>
                <input class="form-control" id="email" name="email" type="email" placeholder="Email Address" required />
                <div class="invalid-feedback">Email Address is required.</div>
            </div>
            <div class="form-floating mb-3">
                <label for="password">Password</label>
                <input class="form-control" id="password" name="password" type="password" placeholder="Password" required />
                <div class="invalid-feedback">Password is required.</div>
            </div>
            <div class="mb-3">
                <label for="color" class="form-label d-block">Color</label>
                <input type="color" class="form-control form-control-color" id="color" name="color" value="#<?php echo(str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT)) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label d-block">Admin</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" id="is_admin" type="checkbox" name="is_admin" />
                    <label class="form-check-label" for="is_admin"></label>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" name="add_volunteer" class="btn btn-primary w-100">Add Volunteer</button>
            </div>
        </form> 
    </div>
</div>

<!-- Volunteer List -->
<div class="row justify-content-center">
    <div class="col-md-12 col-lg-10">
        <h2 class="mb-3 text-center">Current Volunteers</h2>
        <table class="table table-hover table-bordered shadow">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Color</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($volunteers as $volunteer): ?>
                    <tr>
                        <td><?= htmlspecialchars($volunteer['name']) ?></td>
                        <td><?= htmlspecialchars($volunteer['email']) ?></td>
                        <td><span class="badge" style="background-color: <?= htmlspecialchars($volunteer['color']) ?>;">&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
                        <td><?= ($volunteer['role'] == "admin") ? 'Admin' : 'Volunteer' ?></td>
                        <td>
                            <!-- Edit Volunteer Form -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $volunteer['id'] ?>">
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal-<?= $volunteer['id'] ?>">Edit</button>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal-<?= $volunteer['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel-<?= $volunteer['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel-<?= $volunteer['id'] ?>">Edit Volunteer</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="name-<?= $volunteer['id'] ?>" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="name-<?= $volunteer['id'] ?>" name="name" value="<?= htmlspecialchars($volunteer['name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email-<?= $volunteer['id'] ?>" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email-<?= $volunteer['id'] ?>" name="email" value="<?= htmlspecialchars($volunteer['email']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password-<?= $volunteer['id'] ?>" class="form-label">Password (Leave blank to keep current)</label>
                                                    <input type="password" class="form-control" id="password-<?= $volunteer['id'] ?>" name="password">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="color-<?= $volunteer['id'] ?>" class="form-label">Color</label>
                                                    <input type="color" class="form-control form-control-color" id="color-<?= $volunteer['id'] ?>" name="color" value="<?= htmlspecialchars($volunteer['color']) ?>" required>
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="is_admin-<?= $volunteer['id'] ?>" name="is_admin" <?= $volunteer['role'] == 'admin' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_admin-<?= $volunteer['id'] ?>">Admin</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="edit_volunteer" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Delete Volunteer Form -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $volunteer['id'] ?>">
                                <button type="submit" name="delete_volunteer" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this volunteer?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; // Include footer ?>