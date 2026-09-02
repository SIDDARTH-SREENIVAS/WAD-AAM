<?php
/**
 * Course Feedback Management System
 * Database Connection (MySQL / phpMyAdmin) & Helper Functions
 */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Standardized JSON API Response
 */
function jsonResponse($success, $payload = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    if ($success) {
        echo json_encode([
            'success' => true,
            'data' => $payload
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => is_string($payload) ? $payload : ($payload['message'] ?? 'An error occurred'),
            'error' => $payload
        ]);
    }
    exit;
}

/**
 * Parse JSON or Form Input
 */
function getJsonInput() {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $data = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }
    }
    return $_POST;
}

/**
 * PDO Database Connection
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    // Default phpMyAdmin / MySQL connection settings
    $host    = '127.0.0.1';        // Database server host
    $db      = 'course_feedback';  // Database name in phpMyAdmin
    $user    = 'root';             // Default phpMyAdmin username
    $pass    = '';                 // Default phpMyAdmin password (empty in XAMPP, 'root' in MAMP)
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        jsonResponse(false, "Database Connection Error: " . $e->getMessage(), 500);
    }
}
?>
