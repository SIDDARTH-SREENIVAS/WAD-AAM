<?php
/**
 * Course Feedback Management System
 * Action: Submit Course Feedback (Student Only)
 */

require_once __DIR__ . '/db_connect.php';

// Security check: Must be a logged in student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    jsonResponse(false, 'Unauthorized. Only students can submit course feedback.', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', 405);
}

$user_id = $_SESSION['user_id'];
$input = getJsonInput();

$course_id = intval($input['course_id'] ?? 0);
$rating = intval($input['rating'] ?? 0);
$comments = trim($input['comments'] ?? '');

if ($course_id <= 0 || $rating < 1 || $rating > 5 || empty($comments)) {
    jsonResponse(false, 'All fields are required and star rating must be between 1 and 5.', 400);
}

$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("INSERT INTO feedback (student_id, course_id, rating, comments) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $course_id, $rating, $comments]);

    jsonResponse(true, ['message' => 'Feedback submitted successfully!'], 201);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResponse(false, 'You have already submitted feedback for this course.', 400);
    }
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
