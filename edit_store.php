<?php
include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';
include_once 'header.php';

try {
    $db = getDB();

    // Check if store_id is set and valid
    if (!isset($_GET['store_id']) || !is_numeric($_GET['store_id'])) {
        echo "Error: Missing or invalid store_id in URL.";
        exit();
    }

    $storeId = $_GET['store_id'];

    // Fetch store details
    $storeStmt = $db->prepare("SELECT * FROM stores WHERE id = :store_id");
    $storeStmt->execute([':store_id' => $storeId]);
    $store = $storeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        echo "Store not found for store_id = $storeId";
        exit();
    }

    // Fetch all tags
    $tagsStmt = $db->query("SELECT * FROM tags ORDER BY name");
    $tags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch current tags assigned to store and weekday combinations
    $storeTagsStmt = $db->prepare("
        SELECT sdt.*, t.name as tag_name, t.color as tag_color 
        FROM store_day_tags sdt 
        JOIN tags t ON sdt.tag_id = t.id 
        WHERE sdt.store_id = :store_id
    ");
    $storeTagsStmt->execute([':store_id' => $storeId]);
    $storeTags = $storeTagsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Group tags by weekday
    $tagsByWeekday = [];
    foreach ($storeTags as $storeTag) {
        $weekday = $storeTag['weekday'];
        if (!isset($tagsByWeekday[$weekday])) {
            $tagsByWeekday[$weekday] = [];
        }
        $tagsByWeekday[$weekday][] = $storeTag;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle adding a new tag
        if (isset($_POST['add_tag'])) {
            $tagName = $_POST['tag_name'];
            $tagColor = $_POST['tag_color'];
            $addTagStmt = $db->prepare("INSERT INTO tags (name, color) VALUES (:name, :color)");
            $addTagStmt->execute([':name' => $tagName, ':color' => $tagColor]);
        }

        // Handle assigning tags to store/weekday combinations
        if (isset($_POST['assign_tag'])) {
            $weekday = $_POST['weekday'];
            $tagIds = $_POST['tag_ids']; // This should be an array of tag IDs

            // Insert multiple tags for the selected store and weekday
            foreach ($tagIds as $tagId) {
                $assignTagStmt = $db->prepare("INSERT INTO store_day_tags (store_id, weekday, tag_id) VALUES (:store_id, :weekday, :tag_id)");
                $assignTagStmt->execute([':store_id' => $storeId, ':weekday' => $weekday, ':tag_id' => $tagId]);
            }
        }

        // Handle removing tags
        if (isset($_POST['remove_tag'])) {
            $removeTagId = $_POST['remove_tag_id'];
            $removeWeekday = $_POST['remove_weekday'];
            $removeTagStmt = $db->prepare("DELETE FROM store_day_tags WHERE store_id = :store_id AND weekday = :weekday AND tag_id = :tag_id");
            $removeTagStmt->execute([':store_id' => $storeId, ':weekday' => $removeWeekday, ':tag_id' => $removeTagId]);
        }

        // Refresh to reflect changes
        header("Location: edit_store.php?store_id=$storeId");
        exit();
    }
} catch (PDOException $e) {
    echo "Database query failed: " . $e->getMessage();
    exit();
}
?>

<h1>Edit Store: <?= htmlspecialchars($store['name']); ?></h1>

<!-- Section to Add Tags -->
<h2>Add New Tag</h2>
<form method="POST">
    <label for="tag_name">Tag Name:</label>
    <input type="text" name="tag_name" required>
    <label for="tag_color">Tag Color:</label>
    <input type="color" name="tag_color" required>
    <button type="submit" name="add_tag">Add Tag</button>
</form>

<!-- Section to Assign Tags to Store/Weekday Combinations -->
<h2>Assign Tags</h2>
<form method="POST">
    <label for="weekday">Weekday:</label>
    <select name="weekday">
        <option>Monday</option>
        <option>Tuesday</option>
        <option>Wednesday</option>
        <option>Thursday</option>
        <option>Friday</option>
        <option>Saturday</option>
        <option>Sunday</option>
    </select>
    <label for="tag_ids">Tags:</label>
    <select name="tag_ids[]" multiple>
        <?php foreach ($tags as $tag): ?>
            <option value="<?= $tag['id']; ?>"><?= htmlspecialchars($tag['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="assign_tag">Assign Tags</button>
</form>

<!-- Display Assigned Tags -->
<h2>Assigned Tags</h2>
<?php foreach ($tagsByWeekday as $weekday => $weekdayTags): ?>
    <h3><?= htmlspecialchars($weekday); ?></h3>
    <ul class="list-group">
        <?php foreach ($weekdayTags as $tag): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: <?= htmlspecialchars($tag['tag_color']); ?>;">
                <?= htmlspecialchars($tag['tag_name']); ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="remove_tag_id" value="<?= $tag['tag_id']; ?>">
                    <input type="hidden" name="remove_weekday" value="<?= $tag['weekday']; ?>">
                    <button type="submit" name="remove_tag" class="btn btn-sm btn-danger">Remove</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>

<?php include_once 'footer.php';