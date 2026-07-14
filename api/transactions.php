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
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Auth: PHP session OR X-Auth-Token header
$user_id = null;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
} else {
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? $_GET['token'] ?? null;
    if ($token) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE auth_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) $user_id = $row['id'];
    }
}

if (!$user_id) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Please login.'], 401);
}

try {
    $transactions = [];

    // Deposits (credit)
    $stmt = $pdo->prepare("
        SELECT trx, 'Deposit' AS tx_type, 'credit' AS type,
               amount, charge, final_amount,
               CONCAT('Deposit via ', gateway) AS details,
               transaction_id AS account_number,
               status, created_at AS date
        FROM deposits
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $transactions = array_merge($transactions, $stmt->fetchAll(PDO::FETCH_ASSOC));

    // Withdrawals (debit)
    $stmt = $pdo->prepare("
        SELECT trx, 'Withdrawal' AS tx_type, 'debit' AS type,
               amount, charge, final_amount,
               CONCAT('Withdrawal via ', gateway) AS details,
               transaction_id AS account_number,
               status, created_at AS date
        FROM withdrawals
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $transactions = array_merge($transactions, $stmt->fetchAll(PDO::FETCH_ASSOC));

    // Transfers (debit)
    $stmt = $pdo->prepare("
        SELECT trx, 'Transfer' AS tx_type, 'debit' AS type,
               amount, charge, final_amount,
               CONCAT('Transfer to ', beneficiary_name, ' - ', bank_name) AS details,
               account_number,
               status, created_at AS date
        FROM transfers
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $transactions = array_merge($transactions, $stmt->fetchAll(PDO::FETCH_ASSOC));

    // Sort all by date DESC
    usort($transactions, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    jsonResponse([
        'success' => true,
        'transactions' => $transactions,
        'count' => count($transactions)
    ]);

} catch (Exception $e) {
    error_log("Get transactions error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error al obtener transacciones'], 500);
}
