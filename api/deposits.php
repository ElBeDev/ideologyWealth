<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// GET - Get user deposits
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                trx,
                gateway,
                amount,
                charge,
                final_amount,
                status,
                transaction_id,
                created_at,
                updated_at
            FROM deposits 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$user_id]);
        $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'deposits' => $deposits
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST - Create new deposit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $amount = $input['amount'] ?? null;
    $method = $input['method'] ?? null;
    $notes = $input['notes'] ?? '';
    
    // Validation
    if (!$amount || !$method) {
        echo json_encode([
            'success' => false,
            'message' => 'Amount and payment method are required'
        ]);
        exit;
    }
    
    if (!is_numeric($amount) || $amount < 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid amount. Minimum deposit is $1.00'
        ]);
        exit;
    }
    
    try {
        // Generate transaction ID
        $trx = 'DEP' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
        
        // Calculate charges (2% fee)
        $charge = $amount * 0.02;
        $final_amount = $amount + $charge; // Total amount including charge
        
        // Get gateway name based on method
        $gateways = [
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'paypal' => 'PayPal',
            'crypto' => 'Cryptocurrency'
        ];
        $gateway = $gateways[$method] ?? 'Unknown';
        
        // Insert deposit
        $stmt = $pdo->prepare("
            INSERT INTO deposits (
                user_id, 
                trx, 
                gateway, 
                amount, 
                charge, 
                final_amount, 
                status,
                notes,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $trx,
            $gateway,
            $amount,
            $charge,
            $final_amount,
            $notes
        ]);
        
        $deposit_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Deposit request submitted successfully',
            'deposit' => [
                'id' => $deposit_id,
                'trx' => $trx,
                'amount' => $amount,
                'charge' => $charge,
                'final_amount' => $final_amount,
                'gateway' => $gateway,
                'status' => 'pending'
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
