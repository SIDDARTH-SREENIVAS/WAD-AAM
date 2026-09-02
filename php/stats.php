<?php
/**
 * Course Feedback Management System
 * Admin Analytics & Statistics API
 */

require_once __DIR__ . '/config.php';

DB::init();

// Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(false, 'Unauthorized. Admin privileges required.', 403);
}

$allCourses = DB::getCourses();
$allFeedbacks = DB::getFeedbacks(); // All feedbacks

$totalCourses = count($allCourses);
$totalFeedbacks = count($allFeedbacks);
$averageRating = 0;

if ($totalFeedbacks > 0) {
    $sum = array_sum(array_column($allFeedbacks, 'rating'));
    $averageRating = round($sum / $totalFeedbacks, 1);
}

// Course feedback counts
$courseCounts = [];
foreach ($allFeedbacks as $fb) {
    $cid = $fb['course_id'];
    $courseCounts[$cid] = ($courseCounts[$cid] ?? 0) + 1;
}

jsonResponse(true, [
    'stats' => [
        'total_courses' => $totalCourses,
        'total_feedbacks' => $totalFeedbacks,
        'average_rating' => $averageRating,
        'course_feedback_counts' => $courseCounts
    ]
]);
?>
