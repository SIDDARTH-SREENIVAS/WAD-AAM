<?php
/**
 * Course Feedback Management System
 * Action: Delete Feedback (Author Student Only)
 */

require_once __DIR__ . '/db_connect.php';

// Security check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized. Please log in.', 401);
}

$user_id = $_SESSION['user_id'];
$input = getJsonInput();
$id = intval($_GET['id'] ?? ($input['id'] ?? 0));

if ($id <= 0) {
    jsonResponse(false, 'Invalid feedback record ID.', 400);
}

$pdo = getDBConnection();

try {
    // Check record and ownership
    $stmt = $pdo->prepare("SELECT * FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    $feedback = $stmt->fetch();

    if (!$feedback) {
        jsonResponse(false, 'Feedback record not found.', 404);
    }

    // Only the student author is allowed to delete
    if (intval($feedback['student_id']) !== intval($user_id)) {
        jsonResponse(false, 'Unauthorized. You can only delete your own feedback.', 403);
    }

    $deleteStmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $deleteStmt->execute([$id]);

    jsonResponse(true, ['message' => 'Feedback deleted successfully!']);
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
