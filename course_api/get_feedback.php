<?php
/**
 * Course Feedback Management System
 * Action: Get Feedback Reviews (with Course and Student joins)
 */

require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized. Please log in.', 401);
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$pdo = getDBConnection();

$sql = "SELECT f.*, c.course_code, c.course_name, c.instructor, u.username as student_name 
        FROM feedback f
        JOIN courses c ON f.course_id = c.id
        JOIN users u ON f.student_id = u.id";

$where = [];
$params = [];

// If student requests reviews, or ?my_feedback=1 is set
if ($user_role === 'student' || isset($_GET['my_feedback'])) {
    $where[] = "f.student_id = ?";
    $params[] = $user_id;
} elseif (!empty($_GET['student_id'])) {
    $where[] = "f.student_id = ?";
    $params[] = intval($_GET['student_id']);
}

// Search filter
if (!empty($_GET['search'])) {
    $search = '%' . trim($_GET['search']) . '%';
    $where[] = "(c.course_code LIKE ? OR c.course_name LIKE ? OR c.instructor LIKE ? OR f.comments LIKE ? OR u.username LIKE ?)";
    $params = array_merge($params, [$search, $search, $search, $search, $search]);
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY f.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $feedbacks = $stmt->fetchAll();

    jsonResponse(true, ['feedbacks' => $feedbacks]);
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>
