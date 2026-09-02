<?php
/**
 * Course Feedback Management System
 * Action: Get Admin Dashboard Statistics (Admin Only)
 */

require_once __DIR__ . '/db_connect.php';

// Security check: Must be an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(false, 'Unauthorized. Administrator access required.', 403);
}

$pdo = getDBConnection();

try {
    // 1. Total Courses Count
    $coursesCountStmt = $pdo->query("SELECT COUNT(*) as total_courses FROM courses");
    $totalCourses = $coursesCountStmt->fetch()['total_courses'] ?? 0;

    // 2. Total Feedbacks Count & Average Rating
    $feedbackStatsStmt = $pdo->query("SELECT COUNT(*) as total_feedbacks, AVG(rating) as avg_rating FROM feedback");
    $feedbackStats = $feedbackStatsStmt->fetch();

    $totalFeedbacks = $feedbackStats['total_feedbacks'] ?? 0;
    $averageRating = $feedbackStats['avg_rating'] !== null ? round((float)$feedbackStats['avg_rating'], 1) : 0;

    jsonResponse(true, [
        'stats' => [
            'total_courses' => (int)$totalCourses,
            'total_feedbacks' => (int)$totalFeedbacks,
            'average_rating' => (float)$averageRating
        ]
    ]);
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
