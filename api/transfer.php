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
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Authenticate via session OR X-Auth-Token header
$userId = null;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $userId = $_SESSION['user_id'];
} else {
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? null;
    if ($token) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE auth_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) $userId = $row['id'];
    }
}

if (!$userId) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Please log in.'], 401);
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);

$beneficiaryName = trim($input['beneficiary_name'] ?? '');
$bankName = trim($input['bank_name'] ?? '');
$accountNumber = trim($input['account_number'] ?? '');
$routingNumber = trim($input['routing_number'] ?? '');
$swiftCode = trim($input['swift_code'] ?? '');
$amount = floatval($input['amount'] ?? 0);
$purpose = trim($input['purpose'] ?? '');

// Validation
$errors = [];

if (empty($beneficiaryName)) {
    $errors[] = 'Beneficiary name is required';
}

if (empty($bankName)) {
    $errors[] = 'Bank name is required';
}

if (empty($accountNumber)) {
    $errors[] = 'Account number is required';
}

if ($amount <= 0) {
    $errors[] = 'Amount must be greater than zero';
}

if (empty($purpose)) {
    $errors[] = 'Purpose of transfer is required';
}

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => implode(', ', $errors)], 400);
}

try {
    // Check user's balance
    $stmt = $pdo->prepare("SELECT balance, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Calculate charge (you can adjust this based on your fee structure)
    $charge = $amount * 0.00; // 0% fee for now
    $finalAmount = $amount + $charge;
    
    // Check if user has sufficient balance
    if ($user['balance'] < $finalAmount) {
        jsonResponse([
            'success' => false, 
            'message' => 'Insufficient balance. Available: $' . number_format($user['balance'], 2)
        ], 400);
    }
    
    // Generate unique transaction ID
    $trx = 'TRF' . strtoupper(uniqid());

    $pdo->beginTransaction();

    // Insert transfer request
    $stmt = $pdo->prepare("
        INSERT INTO transfers (
            user_id,
            trx,
            beneficiary_name,
            bank_name,
            account_number,
            routing_number,
            swift_code,
            amount,
            charge,
            final_amount,
            purpose,
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $userId,
        $trx,
        $beneficiaryName,
        $bankName,
        $accountNumber,
        $routingNumber,
        $swiftCode,
        $amount,
        $charge,
        $finalAmount,
        $purpose
    ]);

    $transferId = $pdo->lastInsertId();

    // Deduct balance immediately (returned if admin rejects)
    $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->execute([$finalAmount, $userId]);

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => 'Transfer request submitted successfully',
        'data' => [
            'transfer_id' => $transferId,
            'trx' => $trx,
            'amount' => $amount,
            'charge' => $charge,
            'final_amount' => $finalAmount,
            'status' => 'pending'
        ]
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Transfer creation error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to process transfer. Please try again.'], 500);
}
?>
