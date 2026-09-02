<?php
/**
 * Course Feedback Management System
 * Action: Delete Course (Admin Only)
 */

require_once __DIR__ . '/db_connect.php';

// Security check: Must be an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    jsonResponse(false, 'Unauthorized. Administrator access required.', 403);
}

$input = getJsonInput();
$id = intval($_GET['id'] ?? ($input['id'] ?? 0));

if ($id <= 0) {
    jsonResponse(false, 'Invalid course ID specified.', 400);
}

$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(true, ['message' => 'Course and related feedback deleted successfully!']);
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
