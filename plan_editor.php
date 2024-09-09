<?php

include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';

// Function to check if the request is an AJAX request by checking a custom parameter
function isAjaxRequest() {
    // Checking for the custom 'is_ajax' parameter in JSON input
    return isset($_GET['is_ajax']) || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAjaxRequest()) {
    header('Content-Type: application/json'); // Set content type to JSON for AJAX responses
    $response = ['success' => false, 'message' => 'An unexpected error occurred'];

    try {
        $db = getDB();

        // Decode JSON input if Content-Type is application/json
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $response['message'] = 'Invalid JSON input';
            echo json_encode($response);
            exit();
        }

        // Handle assign volunteer request
        if (isset($data['assign_volunteer'])) {
            $date = $data['date'];
            $storeId = $data['store_id'];
            $volunteerId = $data['volunteer_id'];

            // Insert assignment
            $stmt = $db->prepare("INSERT INTO assignments (date, store_id, volunteer_id) VALUES (:date, :store_id, :volunteer_id)");
            $stmt->execute([':date' => $date, ':store_id' => $storeId, ':volunteer_id' => $volunteerId]);

            $response['success'] = true;
            $response['message'] = 'Volunteer assigned successfully';
            echo json_encode($response);
            exit();
        }

        // Handle remove assignment request
        if (isset($data['remove_assignment'])) {
            $date = $data['date'];
            $storeId = $data['store_id'];
            $volunteerId = $data['volunteer_id'];

            // Check if the assignment exists
            $checkStmt = $db->prepare("SELECT * FROM assignments WHERE date = :date AND store_id = :store_id AND volunteer_id = :volunteer_id");
            $checkStmt->execute([':date' => $date, ':store_id' => $storeId, ':volunteer_id' => $volunteerId]);

            if ($checkStmt->rowCount() > 0) {
                // Remove assignment
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

        // Handle assign day tag request
        if (isset($data['assign_day_tag'])) {
            $date = $data['date'];
            $storeId = $data['store_id'];
            $tagId = $data['tag_id'];

            // Insert day tag assignment
            $stmt = $db->prepare("INSERT INTO day_tags (date, store_id, tag_id) VALUES (:date, :store_id, :tag_id)");
            $stmt->execute([':date' => $date, ':store_id' => $storeId, ':tag_id' => $tagId]);

            $response['success'] = true;
            $response['message'] = 'Tag assigned successfully';
            echo json_encode($response);
            exit();
        }

        // Handle remove day tag request
        if (isset($data['remove_day_tag'])) {
            $date = $data['date'];
            $storeId = $data['store_id'];

            // Remove day tag
            $stmt = $db->prepare("DELETE FROM day_tags WHERE date = :date AND store_id = :store_id");
            $stmt->execute([':date' => $date, ':store_id' => $storeId]);

            $response['success'] = true;
            $response['message'] = 'Tag removed successfully';
            echo json_encode($response);
            exit();
        }

    } catch (PDOException $e) {
        // Log the error and return a JSON error response
        error_log('Database error: ' . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        // Log any other exceptions and return a JSON error response
        error_log('Error: ' . $e->getMessage());
        $response['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response);
        exit();
    }
}

// If not an AJAX request, continue rendering the HTML page
include_once 'header.php';

try {
    $db = getDB();

    // Fetch all stores ordered by name and location
    $storesStmt = $db->query("SELECT * FROM stores ORDER BY name, location");
    $stores = $storesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all tags
    $tagsStmt = $db->query("SELECT * FROM tags ORDER BY name");
    $tags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tags assigned to stores and weekdays
    $storeTagsStmt = $db->query("
        SELECT sdt.*, t.name as tag_name, t.color as tag_color 
        FROM store_day_tags sdt 
        JOIN tags t ON sdt.tag_id = t.id
    ");
    $storeTags = $storeTagsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch tags assigned to specific days
    $dayTagsStmt = $db->query("
        SELECT dt.*, t.name as tag_name, t.color as tag_color 
        FROM day_tags dt 
        JOIN tags t ON dt.tag_id = t.id
    ");
    $dayTags = $dayTagsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all volunteers including admins
    $volunteersStmt = $db->query("SELECT * FROM volunteers WHERE role IN ('volunteer', 'admin')");
    $volunteers = $volunteersStmt->fetchAll(PDO::FETCH_ASSOC);
    $volunteerMap = [];
    foreach ($volunteers as $volunteer) {
        $volunteerMap[$volunteer['id']] = $volunteer; // Create a map of volunteer IDs to their details
    }

    // Fetch current assignments with volunteer names
    $assignmentsStmt = $db->query("
        SELECT a.*, v.name as volunteer_name, v.color as volunteer_color 
        FROM assignments a 
        JOIN volunteers v ON a.volunteer_id = v.id
    ");
    $assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the current and next month days
    $currentMonth = new DateTime('first day of this month');
    $nextMonth = new DateTime('first day of next month');

    // Generate a list of dates for the current and next month
    $dates = [];
    while ($currentMonth->format('Y-m') <= $nextMonth->format('Y-m')) {
        $dates[$currentMonth->format('Y-m')][] = $currentMonth->format('Y-m-d'); // Group dates by month
        $currentMonth->modify('+1 day');
    }

    // Function to find tag for a specific store and weekday
    function findTagForStoreAndWeekday($storeTags, $storeId, $weekday) {
        foreach ($storeTags as $tag) {
            if ($tag['store_id'] == $storeId && $tag['weekday'] == $weekday) {
                return $tag;
            }
        }
        return null;
    }

    // Function to find tag for a specific store and date
    function findTagForStoreAndDate($dayTags, $storeId, $date) {
        foreach ($dayTags as $tag) {
            if ($tag['store_id'] == $storeId && $tag['date'] == $date) {
                return $tag;
            }
        }
        return null;
    }
} catch (PDOException $e) {
    echo "Database query failed: " . $e->getMessage();
    exit();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
?>

<h1 class="mb-4">Edit Plan</h1>

<!-- Display Volunteers as Pills in a Row -->
<div class="volunteers mb-4">
    <h2>Volunteers</h2>
    <div id="volunteer-list" class="d-flex flex-wrap">
        <?php foreach ($volunteers as $volunteer): ?>
            <div class="p-2 m-1 text-white rounded-pill draggable-item" 
                 style="background-color: <?= htmlspecialchars($volunteer['color']); ?>; cursor: pointer;" 
                 data-volunteer-id="<?= $volunteer['id']; ?>" 
                 draggable="true">
                <?= htmlspecialchars($volunteer['name']); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Toggle Button for Tags -->
<div class="tags-toggle mb-2">
    <button id="toggle-tags" class="btn btn-outline-secondary">
        ▼ Tags
    </button>
</div>

<!-- Collapsible Tags Bar -->
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

<!-- Display Plan Table with Stores as Columns and Dates as Rows -->
<div class="schedule">
    <h2>Schedule</h2>
    <?php foreach ($dates as $month => $days): ?>
        <div class="month-section">
            <h3 class="month-heading"><?= date('F Y', strtotime($month)); ?></h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th style="position: sticky; left: 0; background: #343a40; color: #fff; z-index: 10;">Date</th> <!-- Sticky first column -->
                            <?php foreach ($stores as $store): ?>
                                <th style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; position: sticky; top: 0; background: #343a40; color: #fff; z-index: 10;">
                                    <?= htmlspecialchars($store['name']); ?><br>
                                    <?= htmlspecialchars($store['location']); ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($days as $date): ?>
                            <?php 
                            $dateTime = new DateTime($date);
                            $weekday = $dateTime->format('D'); // Get the abbreviated weekday name (e.g., "Mon" for Monday)
                            $formattedDate = $dateTime->format('d.m.y'); // Format date as "13.09.24"
                            $isMonday = $dateTime->format('l') === 'Monday'; // Check if the day is Monday
                            ?>
                            <tr style="<?= $isMonday ? 'background-color: #f2f2f2;' : ''; ?>"> <!-- Highlight Mondays -->
                                <td style="position: sticky; left: 0; background: #fff; z-index: 5;"><?= $weekday . '. ' . $formattedDate; ?></td> <!-- Sticky first column -->
                                <?php foreach ($stores as $store): ?>
                                    <?php 
                                    $tag = findTagForStoreAndWeekday($storeTags, $store['id'], $dateTime->format('l'));
                                    $dayTag = findTagForStoreAndDate($dayTags, $store['id'], $date);
                                    $tagColor = $dayTag ? $dayTag['tag_color'] : ($tag ? $tag['tag_color'] : '#ffffff'); // Background color logic
                                    ?>
                                    <td class="droppable" 
                                        data-store-id="<?= $store['id']; ?>" 
                                        data-date="<?= $date; ?>" 
                                        id="droppable-<?= $store['id'] . '-' . $date; ?>"
                                        style="background-color: <?= htmlspecialchars($tagColor); ?>; position: relative;">
                                        <ul class="store-list list-unstyled d-flex flex-wrap">
                                            <?php foreach ($assignments as $assignment): ?>
                                                <?php if ($assignment['store_id'] == $store['id'] && $assignment['date'] == $date): ?>
                                                    <li class="p-2 m-1 text-white rounded-pill draggable-item" 
                                                        style="background-color: <?= htmlspecialchars($assignment['volunteer_color']); ?>;" 
                                                        data-volunteer-id="<?= $assignment['volunteer_id']; ?>"
                                                        data-store-id="<?= $store['id']; ?>"
                                                        data-date="<?= $date; ?>"
                                                        draggable="true">
                                                        <?= htmlspecialchars($assignment['volunteer_name']); ?>
                                                        <button class="delete-assignment-btn">&times;</button> <!-- Button should be inside the pill -->
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                        <!-- Small "X" Button to Remove Tag -->
                                        <?php if ($dayTag): ?>
                                            <button class="btn btn-sm btn-danger remove-tag-btn" 
                                                    data-store-id="<?= $store['id']; ?>" 
                                                    data-date="<?= $date; ?>" 
                                                    style="position: absolute; top: 0; right: 0; padding: 2px 5px;">&times;</button>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Display Tag Legend for Each Month -->
            <div class="tag-legend mt-3">
                <h4>Tag Legend:</h4>
                <ul class="list-inline">
                    <?php foreach ($tags as $tag): ?>
                        <li class="list-inline-item">
                            <span class="badge" style="background-color: <?= htmlspecialchars($tag['color']); ?>;">
                                <?= htmlspecialchars($tag['name']); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<button id="save-button" class="btn btn-success mt-3">Save Changes</button>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="js/schedule.js"></script>

<?php include_once 'footer.php'; ?>