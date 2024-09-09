<?php
include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';
include_once 'header.php';

try {
    $db = getDB();

    // Fetch all stores ordered by name and location
    $storesStmt = $db->query("SELECT * FROM stores ORDER BY name, location");
    $stores = $storesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database query failed: " . $e->getMessage();
    exit();
}
?>

<h1 class="mb-4">Manage Stores</h1>

<table class="table table-bordered table-striped">
    <thead class="thead-dark">
        <tr>
            <th>Name</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stores as $store): ?>
            <tr>
                <td><?= htmlspecialchars($store['name']); ?></td>
                <td><?= htmlspecialchars($store['location']); ?></td>
                <td>
                    <!-- Link to edit store using the correct store_id parameter -->
                    <a href="edit_store.php?store_id=<?= $store['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="delete_store.php?store_id=<?= $store['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this store?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include_once 'footer.php';