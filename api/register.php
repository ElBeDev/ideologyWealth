<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
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
$city = trim($input['city'] ?? '');
$state = trim($input['state'] ?? '');
$zip = trim($input['zip'] ?? '');
$address = trim($input['address'] ?? '');
$country_code = trim($input['country_code'] ?? 'MX');

// Validate input
if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password) || empty($mobile)) {
    jsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email'], 400);
}

// Validate password length
if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
}

// Validate password match
if ($password !== $confirm_password) {
    jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
}

// Validate username (alphanumeric, 4-40 chars)
if (!preg_match('/^[a-zA-Z0-9]{4,40}$/', $username)) {
    jsonResponse(['success' => false, 'message' => 'Username must contain only letters and numbers (4-40 characters)'], 400);
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Username is already taken'], 409);
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email is already registered'], 409);
    }

    // Generate account number
    $account_number = generateAccountNumber();

    // Hash password
    $password_hash = hashPassword($password);

    // Insert new user - status=0 requires admin activation
    $stmt = $pdo->prepare("
        INSERT INTO users (
            account_number, firstname, lastname, username, email, 
            country_code, mobile, city, state, zip, address, password, balance, status, 
            ev, sv, kycv, created_at, updated_at
        ) VALUES (
            :account_number, :firstname, :lastname, :username, :email,
            :country_code, :mobile, :city, :state, :zip, :address, :password, 0.00, 0,
            0, 0, 0, NOW(), NOW()
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
        'city' => $city,
        'state' => $state,
        'zip' => $zip,
        'address' => $address,
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
        'message' => 'Registration successful! Your account is pending administrator approval. You will be notified once activated.',
        'pending_activation' => true,
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
    jsonResponse(['success' => false, 'message' => 'Error processing registration'], 500);
}
