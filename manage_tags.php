<?php
include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';

// Handle form submission for adding/updating tags
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    if (isset($_POST['add_tag'])) {
        // Add new tag
        $name = $_POST['name'];
        $color = $_POST['color'];

        $stmt = $db->prepare("INSERT INTO tags (name, color) VALUES (:name, :color)");
        $stmt->execute([':name' => $name, ':color' => $color]);
    } elseif (isset($_POST['edit_tag'])) {
        // Edit existing tag
        $id = $_POST['id'];
        $name = $_POST['name'];
        $color = $_POST['color'];

        $stmt = $db->prepare("UPDATE tags SET name = :name, color = :color WHERE id = :id");
        $stmt->execute([':name' => $name, ':color' => $color, ':id' => $id]);
    } elseif (isset($_POST['delete_tag'])) {
        // Delete tag
        $id = $_POST['id'];

        $stmt = $db->prepare("DELETE FROM tags WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}

// Fetch all tags
$db = getDB();
$tagsStmt = $db->query("SELECT * FROM tags ORDER BY name");
$tags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);

include_once 'header.php';
?>

<h1 class="mb-4">Manage Tags</h1>

<!-- Tag Form -->
<div class="mb-4">
    <form id="tag-form" method="POST">
        <input type="hidden" name="id" id="tag-id">
        <div class="form-group">
            <label for="name">Tag Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="color">Tag Color</label>
            <input type="color" class="form-control" id="color" name="color" value="#ffffff" required>
        </div>
        <button type="submit" name="add_tag" class="btn btn-primary">Add Tag</button>
        <button type="submit" name="edit_tag" class="btn btn-success" style="display: none;" id="edit-button">Save Changes</button>
        <button type="button" class="btn btn-secondary" style="display: none;" id="cancel-button" onclick="resetForm()">Cancel</button>
    </form>
</div>

<!-- Tags List -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Color</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tags as $tag): ?>
        <tr>
            <td><?= htmlspecialchars($tag['name']); ?></td>
            <td>
                <div style="width: 30px; height: 30px; background-color: <?= htmlspecialchars($tag['color']); ?>;"></div>
            </td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editTag(<?= $tag['id']; ?>, '<?= htmlspecialchars($tag['name']); ?>', '<?= htmlspecialchars($tag['color']); ?>')">Edit</button>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?= $tag['id']; ?>">
                    <button type="submit" name="delete_tag" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tag?');">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// JavaScript function to handle editing a tag
function editTag(id, name, color) {
    document.getElementById('tag-id').value = id;
    document.getElementById('name').value = name;
    document.getElementById('color').value = color;
    document.querySelector('[name="add_tag"]').style.display = 'none';
    document.getElementById('edit-button').style.display = 'inline-block';
    document.getElementById('cancel-button').style.display = 'inline-block';
}

// Reset the form to its initial state
function resetForm() {
    document.getElementById('tag-id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('color').value = '#ffffff';
    document.querySelector('[name="add_tag"]').style.display = 'inline-block';
    document.getElementById('edit-button').style.display = 'none';
    document.getElementById('cancel-button').style.display = 'none';
}
</script>

<?php include_once 'footer.php';