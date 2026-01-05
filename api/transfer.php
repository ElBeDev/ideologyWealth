<?php
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Please log in.'], 401);
}

$userId = $_SESSION['user_id'];

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);

$beneficiaryName = trim($input['beneficiary_name'] ?? '');
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

if (empty($accountNumber)) {
    $errors[] = 'Account number is required';
}

if (empty($routingNumber)) {
    $errors[] = 'Routing number is required';
}

if (empty($swiftCode)) {
    $errors[] = 'SWIFT code is required';
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
    
    // Insert transfer request
    $stmt = $pdo->prepare("
        INSERT INTO transfers (
            user_id, 
            trx, 
            beneficiary_name, 
            account_number, 
            routing_number, 
            swift_code,
            amount, 
            charge, 
            final_amount, 
            purpose, 
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $userId,
        $trx,
        $beneficiaryName,
        $accountNumber,
        $routingNumber,
        $swiftCode,
        $amount,
        $charge,
        $finalAmount,
        $purpose
    ]);
    
    // Get the inserted transfer
    $transferId = $pdo->lastInsertId();
    
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
    error_log("Transfer creation error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to process transfer. Please try again.'], 500);
}
?>
