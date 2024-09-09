<?php

include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';

function isAjaxRequest() {
    return isset($_GET['is_ajax']) || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAjaxRequest()) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unexpected error occurred'];

    try {
        $db = getDB();

        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $response['message'] = 'Invalid JSON input';
            echo json_encode($response);
            exit();
        }

        if (isset($data['assign_volunteer'])) {
            $date = $data['date'];
            $storeId = $data['store_id'];
            $volunteerId = $data['volunteer_id'];

            $stmt = $db->prepare("INSERT INTO assignments (date, store_id, volunteer_id) VALUES (:date, :store_id, :volunteer_id)");
            $stmt->execute([':date' => $date, ':store_id' => $storeId, ':volunteer_id' => $volunteerId]);

            $response['success'] = true;
            $response['message'] = 'Volunteer assigned successfully';
            echo json_encode($response);
            exit();
        }

        if (isset($data['remove_assignment'])) {
            $date = $data['date'];
            $storeId = $data['store_id'];
            $volunteerId = $data['volunteer_id'];

            $checkStmt = $db->prepare("SELECT * FROM assignments WHERE date = :date AND store_id = :store_id AND volunteer_id = :volunteer_id");
            $checkStmt->execute([':date' => $date, ':store_id' => $storeId, ':volunteer_id' => $volunteerId]);

            if ($checkStmt->rowCount() > 0) {
                $stmt = $db->prepare("DELETE FROM assignments WHERE date = :date AND store_id = :store_id AND volunteer_id = :volunteer_id");
                $stmt->execute([':date' => $date, ':store_id' => $storeId, ':volunteer_id' => $volunteerId]);

                $response['success'] = true;
                $response['message'] = 'Assignment removed successfully';
            } else {
                $response['message'] = 'No assignment found to remove';
            }
            echo json_encode($response);
            exit();
        }

        if (isset($data['add_next_month'])) {
            $lastDisplayedMonth = $data['last_displayed_month'];
            $newMonth = (new DateTime($lastDisplayedMonth))->modify('+1 month')->format('Y-m'); // Format to 'YYYY-MM'

            // Check if the new month already exists in the table
            $checkMonthStmt = $db->prepare("SELECT * FROM added_months WHERE month = :month");
            $checkMonthStmt->execute([':month' => $newMonth]);

            if ($checkMonthStmt->rowCount() > 0) {
                $response['message'] = 'Month already added';
                echo json_encode($response);
                exit();
            }

            // Insert the new month into the added_months table
            $addMonthStmt = $db->prepare("INSERT INTO added_months (month) VALUES (:month)");
            $addMonthStmt->execute([':month' => $newMonth]);

            $dates = [];
            $currentDate = new DateTime("first day of $newMonth");
            $endDate = (new DateTime("last day of $newMonth"))->modify('+1 day');
            while ($currentDate < $endDate) {
                $dates[] = $currentDate->format('Y-m-d');
                $currentDate->modify('+1 day');
            }

            $storesStmt = $db->query("SELECT * FROM stores ORDER BY name, location");
            $stores = $storesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $tagsStmt = $db->query("SELECT * FROM tags ORDER BY name");
            $tags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $assignmentsStmt = $db->query("
                SELECT a.*, v.name as volunteer_name, v.color as volunteer_color 
                FROM assignments a 
                JOIN volunteers v ON a.volunteer_id = v.id
            ");
            $assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);

            ob_start();
            $currentMonth = new DateTime("first day of $newMonth");
            include 'month_template.php';
            $response['html'] = ob_get_clean();
            $response['success'] = true;

            echo json_encode($response);
            exit();
        }

    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        $response['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response);
        exit();
    }
}

// Render the initial month
include_once 'header.php';

$db = getDB();
$storesStmt = $db->query("SELECT * FROM stores ORDER BY name, location");
$stores = $storesStmt->fetchAll(PDO::FETCH_ASSOC);

$tagsStmt = $db->query("SELECT * FROM tags ORDER BY name");
$tags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);

$volunteersStmt = $db->query("SELECT * FROM volunteers ORDER BY name");
$volunteers = $volunteersStmt->fetchAll(PDO::FETCH_ASSOC);

$assignmentsStmt = $db->query("
    SELECT a.*, v.name as volunteer_name, v.color as volunteer_color 
    FROM assignments a 
    JOIN volunteers v ON a.volunteer_id = v.id
");
$assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve added months from the database
$addedMonthsStmt = $db->query("SELECT month FROM added_months ORDER BY month");
$addedMonths = $addedMonthsStmt->fetchAll(PDO::FETCH_COLUMN);

$currentMonth = new DateTime('first day of this month');
?>

<h1 class="mb-4">Edit Plan</h1>

<div class="volunteers mb-4">
    <h2>Volunteers</h2>
    <div id="volunteer-list" class="d-flex flex-wrap">
        <?php foreach ($volunteers as $volunteer): ?>
            <div class="p-2 m-1 text-white rounded-pill draggable-item" 
                 style="background-color: <?= htmlspecialchars($volunteer['color']); ?>; cursor: pointer;" 
                 data-volunteer-id="<?= $volunteer['id']; ?>" 
                 draggable="true">
                <?= htmlspecialchars($volunteer['name']); ?>
                <button class="delete-assignment-btn" style="display: none;">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="tags-toggle mb-2">
    <button id="toggle-tags" class="btn btn-outline-secondary">
        ▼ Tags
    </button>
</div>

<div id="tags-bar" class="tags mb-4" style="display: none;">
    <h2>Tags</h2>
    <div id="tag-list" class="d-flex flex-wrap">
        <?php foreach ($tags as $tag): ?>
            <div class="p-2 m-1 text-white rounded-pill draggable-item" 
                 style="background-color: <?= htmlspecialchars($tag['color']); ?>; cursor: pointer;" 
                 data-tag-id="<?= $tag['id']; ?>" 
                 draggable="true">
                <?= htmlspecialchars($tag['name']); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="schedule">
    <?php 
    include 'month_template.php'; 
    foreach ($addedMonths as $month) {
        $currentMonth = new DateTime("first day of $month");
        include 'month_template.php'; 
    }
    ?>
</div>

<div class="mt-3">
    <button id="add-next-month" class="btn btn-primary">Add Next Month</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="js/schedule.js"></script>

<?php include_once 'footer.php'; ?>