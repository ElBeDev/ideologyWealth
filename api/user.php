<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(['success' => false, 'message' => 'No autenticado'], 401);
}

try {
    $user_id = $_SESSION['user_id'];

    // Get user data
    $stmt = $pdo->prepare("
        SELECT id, username, email, firstname, lastname, balance, 
               account_number, country_code, mobile, address, 
               created_at, concept, dividends, cdt, investment_balance, status, ev, sv,
               bank_account_number, bank_beneficiary, bank_routing, bank_swift
        FROM users 
        WHERE id = :id 
        LIMIT 1
    ");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
    }

    // Parse address JSON if exists
    if (!empty($user['address'])) {
        $user['address'] = json_decode($user['address'], true);
    }

    jsonResponse([
        'success' => true,
        'user' => $user
    ]);

} catch (Exception $e) {
    error_log("Get user error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error al obtener datos del usuario'], 500);
}
