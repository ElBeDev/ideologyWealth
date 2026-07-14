<?php
require_once 'config.php';

header('Content-Type: application/json');
// Allow credentials — must specify exact origin, not wildcard
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Authenticate via PHP session OR token header/param
$user_id = null;

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
} else {
    // Fallback: token from header or GET param
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? $_GET['token'] ?? null;
    if ($token) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE auth_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) {
            $user_id = $row['id'];
        }
    }
}

if (!$user_id) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    // Get user data
    $stmt = $pdo->prepare("
        SELECT id, username, email, firstname, lastname, balance, 
               account_number, country_code, mobile, address, city, state, zip,
               created_at, concept, dividends, cdt, investment_balance, status, ev, sv,
               bank_account_number, bank_beneficiary, bank_routing, bank_swift,
               bank_intermediary, bank_address
        FROM users 
        WHERE id = :id 
        LIMIT 1
    ");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
    }

    // Parse address: stored as JSON object or plain string
    if (!empty($user['address'])) {
        $decoded = json_decode($user['address'], true);
        if (is_array($decoded)) {
            // Legacy JSON format — extract the inner address string
            $user['address'] = $decoded['address'] ?? '';
        }
        // else: already a plain string, keep as-is
    }

    jsonResponse([
        'success' => true,
        'user' => $user
    ]);

} catch (Exception $e) {
    error_log("Get user error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error al obtener datos del usuario'], 500);
}
