<?php
include_once 'auth/auth.php';
requireAdmin();
include 'config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';
$volunteerId = $data['volunteer_id'] ?? null;
$storeId = $data['store_id'] ?? null;
$date = $data['date'] ?? null;

// Validate inputs
if (!$volunteerId || !$storeId || !$date) {
    echo json_encode(['success' => false, 'message' => 'Invalid input: Missing required fields']);
    exit();
}

$db = getDB();

try {
    if ($action === 'save') {
        // Check if assignment already exists
        $stmt = $db->prepare("SELECT * FROM assignments WHERE volunteer_id = :volunteer_id AND store_id = :store_id AND date = :date");
        $stmt->execute([
            ':volunteer_id' => $volunteerId,
            ':store_id' => $storeId,
            ':date' => $date
        ]);

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Assignment already exists']);
            exit();
        }

        // Insert the new assignment
        $stmt = $db->prepare("INSERT INTO assignments (volunteer_id, store_id, date) VALUES (:volunteer_id, :store_id, :date)");
        $stmt->execute([
            ':volunteer_id' => $volunteerId,
            ':store_id' => $storeId,
            ':date' => $date
        ]);
        echo json_encode(['success' => true, 'message' => 'Assignment saved successfully']);
    } elseif ($action === 'remove') {
        // Remove the assignment
        $stmt = $db->prepare("DELETE FROM assignments WHERE volunteer_id = :volunteer_id AND store_id = :store_id AND date = :date");
        $stmt->execute([
            ':volunteer_id' => $volunteerId,
            ':store_id' => $storeId,
            ':date' => $date
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Assignment removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No assignment found to remove']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}