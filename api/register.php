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
$input = json_decode(file_get_contents('php://input'), true);

$firstname = trim($input['firstname'] ?? '');
$lastname = trim($input['lastname'] ?? '');
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';
$mobile = trim($input['mobile'] ?? '');
$country_code = trim($input['country_code'] ?? 'MX');

// Validate input
if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password) || empty($mobile)) {
    jsonResponse(['success' => false, 'message' => 'Todos los campos son requeridos'], 400);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Email inválido'], 400);
}

// Validate password length
if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
}

// Validate password match
if ($password !== $confirm_password) {
    jsonResponse(['success' => false, 'message' => 'Las contraseñas no coinciden'], 400);
}

// Validate username (alphanumeric, 4-40 chars)
if (!preg_match('/^[a-zA-Z0-9]{4,40}$/', $username)) {
    jsonResponse(['success' => false, 'message' => 'El usuario debe contener solo letras y números (4-40 caracteres)'], 400);
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'El nombre de usuario ya está en uso'], 409);
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'El email ya está registrado'], 409);
    }

    // Generate account number
    $account_number = generateAccountNumber();

    // Hash password
    $password_hash = hashPassword($password);

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (
            account_number, firstname, lastname, username, email, 
            country_code, mobile, password, balance, status, 
            ev, sv, kycv, created_at, updated_at
        ) VALUES (
            :account_number, :firstname, :lastname, :username, :email,
            :country_code, :mobile, :password, 0.00, 1,
            1, 1, 1, NOW(), NOW()
        )
    ");

    $stmt->execute([
        'account_number' => $account_number,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'username' => $username,
        'email' => $email,
        'country_code' => $country_code,
        'mobile' => $mobile,
        'password' => $password_hash
    ]);

    $user_id = $pdo->lastInsertId();

    // Create session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['logged_in'] = true;

    jsonResponse([
        'success' => true,
        'message' => 'Registro exitoso. Bienvenido a 1Life Financial!',
        'user' => [
            'id' => $user_id,
            'username' => $username,
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'account_number' => $account_number,
            'balance' => '0.00'
        ]
    ], 201);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error al procesar el registro'], 500);
}
