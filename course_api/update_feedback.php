<?php
/**
 * Course Feedback Management System
 * Action: Update Feedback (Author Student Only)
 */

require_once __DIR__ . '/db_connect.php';

// Security check: Must be a logged in student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    jsonResponse(false, 'Unauthorized. Only students can update feedback.', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', 405);
}

$user_id = $_SESSION['user_id'];
$input = getJsonInput();

$id = intval($input['id'] ?? 0);
$rating = intval($input['rating'] ?? 0);
$comments = trim($input['comments'] ?? '');

if ($id <= 0 || $rating < 1 || $rating > 5 || empty($comments)) {
    jsonResponse(false, 'All fields are required and star rating must be between 1 and 5.', 400);
}

$pdo = getDBConnection();

try {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    $feedback = $stmt->fetch();

    if (!$feedback) {
        jsonResponse(false, 'Feedback record not found.', 404);
    }

    if (intval($feedback['student_id']) !== intval($user_id)) {
        jsonResponse(false, 'Unauthorized to edit this review.', 403);
    }

    $updateStmt = $pdo->prepare("UPDATE feedback SET rating = ?, comments = ? WHERE id = ?");
    $updateStmt->execute([$rating, $comments, $id]);

    jsonResponse(true, ['message' => 'Feedback updated successfully!']);
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
