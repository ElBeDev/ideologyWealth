<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(['success' => false, 'message' => 'No autenticado'], 401);
}

try {
    $user_id = $_SESSION['user_id'];

    // Get user info to check username
    $stmt = $pdo->prepare("SELECT username, firstname, lastname FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
    }

    $transactions = [];

    // Check if this is Kurt Stoops
    $fullName = strtolower(trim($user['firstname'] . ' ' . $user['lastname']));
    $isKurtStoops = ($fullName === 'kurt stoops' || strtolower($user['username']) === 'kurt stoops');

    if ($isKurtStoops) {
        // Add the HILTON pending transaction for Kurt Stoops
        $transactions[] = [
            'id' => 'TRX281744624',
            'trx' => '#TRX281744624',
            'date' => '2025-12-30 00:00:00',
            'details' => 'Incoming Transfer - HILTON',
            'account_number' => '281744624',
            'amount' => 118042.00,
            'type' => 'credit',
            'status' => 'pending',
            'post_balance' => null,
            'description' => 'Transfer from HILTON - Account: 281744624 - PENDING/FROZEN'
        ];
    }

    // Get real transactions from database (if table exists)
    try {
        $stmt = $pdo->prepare("
            SELECT id, trx, date, details, amount, type, status, post_balance, description
            FROM transactions 
            WHERE user_id = :user_id 
            ORDER BY date DESC, id DESC
        ");
        $stmt->execute(['user_id' => $user_id]);
        $dbTransactions = $stmt->fetchAll();
        
        // Merge with existing transactions
        $transactions = array_merge($transactions, $dbTransactions);
        
    } catch (PDOException $e) {
        // Table might not exist yet, that's OK
        error_log("Transactions table query error: " . $e->getMessage());
    }

    jsonResponse([
        'success' => true,
        'transactions' => $transactions,
        'count' => count($transactions)
    ]);

} catch (Exception $e) {
    error_log("Get transactions error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error al obtener transacciones'], 500);
}
