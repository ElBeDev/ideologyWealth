<?php
// Script para crear usuario de prueba con campo State
require_once 'config.php';

header('Content-Type: application/json');

$testUser = [
    'firstname' => 'Test',
    'lastname' => 'StateField',
    'username' => 'teststate' . time(),
    'email' => 'teststate' . time() . '@test.com',
    'password' => 'Test123456',
    'mobile' => '5551234567',
    'country_code' => 'US',
    'city' => 'Los Angeles',
    'state' => 'California',
    'zip' => '90001',
    'address' => '123 Test Street'
];

try {
    // Generate account number
    $account_number = generateAccountNumber();
    
    // Hash password
    $password_hash = hashPassword($testUser['password']);
    
    // Insert test user
    $stmt = $pdo->prepare("
        INSERT INTO users (
            account_number, firstname, lastname, username, email, 
            country_code, mobile, city, state, zip, address, password, 
            plain_password, balance, status, ev, sv, kycv, created_at, updated_at
        ) VALUES (
            :account_number, :firstname, :lastname, :username, :email,
            :country_code, :mobile, :city, :state, :zip, :address, :password,
            :plain_password, 0.00, 1, 1, 1, 1, NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        'account_number' => $account_number,
        'firstname' => $testUser['firstname'],
        'lastname' => $testUser['lastname'],
        'username' => $testUser['username'],
        'email' => $testUser['email'],
        'country_code' => $testUser['country_code'],
        'mobile' => $testUser['mobile'],
        'city' => $testUser['city'],
        'state' => $testUser['state'],
        'zip' => $testUser['zip'],
        'address' => $testUser['address'],
        'password' => $password_hash,
        'plain_password' => $testUser['password']
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Get the created user
    $stmt = $pdo->prepare("
        SELECT id, username, firstname, lastname, email, mobile, country_code,
               city, state, zip, address, balance, account_number, created_at
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $createdUser = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario de prueba creado exitosamente',
        'user' => $createdUser,
        'credentials' => [
            'username' => $testUser['username'],
            'password' => $testUser['password']
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear usuario de prueba',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
