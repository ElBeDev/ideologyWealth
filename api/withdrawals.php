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

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// GET - Get user withdrawals
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
            FROM withdrawals 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$user_id]);
        $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'withdrawals' => $withdrawals
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// POST - Create new withdrawal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $amount = $input['amount'] ?? null;
    $method = $input['method'] ?? null;
    $account_details = $input['account_details'] ?? '';
    $notes = $input['notes'] ?? '';
    
    if (!$amount || !$method || !$account_details) {
        echo json_encode([
            'success' => false,
            'message' => 'Amount, method and account details are required'
        ]);
        exit;
    }
    
    if (!is_numeric($amount) || $amount < 10) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid amount. Minimum withdrawal is $10.00'
        ]);
        exit;
    }
    
    try {
        // Check user balance
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate charges (2% fee)
        $charge = $amount * 0.02;
        $final_amount = $amount + $charge; // Total to deduct from balance
        
        if (!$user || $user['balance'] < $final_amount) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient balance. You need $' . number_format($final_amount, 2) . ' (including 2% fee)'
            ]);
            exit;
        }
        
        // Generate transaction ID
        $trx = 'WTH' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
        
        $gateways = [
            'bank_transfer' => 'Bank Transfer',
            'paypal' => 'PayPal',
            'crypto' => 'Cryptocurrency',
            'check' => 'Check'
        ];
        $gateway = $gateways[$method] ?? 'Unknown';
        
        // Insert withdrawal
        $stmt = $pdo->prepare("
            INSERT INTO withdrawals (
                user_id, 
                trx, 
                gateway, 
                amount, 
                charge, 
                final_amount, 
                status,
                account_details,
                notes,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $trx,
            $gateway,
            $amount,
            $charge,
            $final_amount,
            $account_details,
            $notes
        ]);
        
        $withdrawal_id = $pdo->lastInsertId();
        
        // Deduct from user balance (deduct the total amount including charge)
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$final_amount, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully',
            'withdrawal' => [
                'id' => $withdrawal_id,
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
