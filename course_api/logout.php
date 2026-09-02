<?php
/**
 * Course Feedback Management System
 * Action: User Logout
 */

require_once __DIR__ . '/db_connect.php';

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
jsonResponse(true, ['message' => 'Logged out successfully']);
?>
