<?php
require_once __DIR__ . '/db_connect.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    jsonResponse(true, [
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ]
    ]);
} else {
    jsonResponse(true, [
        'authenticated' => false
    ]);
}
?>
