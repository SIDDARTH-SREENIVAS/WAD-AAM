<?php
/**
 * Course Feedback Management System
 * Action: Get All Courses
 */

require_once __DIR__ . '/db_connect.php';

$pdo = getDBConnection();

try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY course_code ASC");
    $courses = $stmt->fetchAll();
    jsonResponse(true, ['courses' => $courses]);
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
