<?php
require_once 'config.php';

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Invalidate auth_token in DB if provided
$token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? $_GET['token'] ?? null;
if ($token) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET auth_token = NULL WHERE auth_token = ?");
        $stmt->execute([$token]);
    } catch (Exception $e) {}
}

// Destroy PHP session
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET auth_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (Exception $e) {}
}
session_unset();
session_destroy();

jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
