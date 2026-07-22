<?php
/**
 * ONE-TIME SETUP SCRIPT — CREATE TEST USERS
 * Run once via browser: https://yoursite.com/api/setup_test_users.php?key=SETUP_2024
 * DELETE THIS FILE after running it.
 */

// Simple security key to prevent unauthorized access
$setup_key = $_GET['key'] ?? '';
if ($setup_key !== 'SETUP_2024') {
    die(json_encode(['error' => 'Unauthorized. Provide ?key=SETUP_2024']));
}

require_once 'config.php';
header('Content-Type: application/json');

$results = [];

// ─── 1. ADMIN USER (hardcoded in admin_login.php) ───────────────────────────
// Admin credentials are hardcoded, no DB entry needed.
$results['admin'] = [
    'status'   => 'ready (hardcoded)',
    'username' => 'admin',
    'password' => 'admin123',
    'note'     => 'Login at /admin/login.html'
];

// ─── 2. REGULAR TEST USER ────────────────────────────────────────────────────
$test_username  = 'testuser';
$test_password  = 'Test1234!';
$test_email     = 'testuser@ideologywealthadvisors.com';
$test_firstname = 'Test';
$test_lastname  = 'User';
$test_account   = 'IWA-TEST-001';
$test_balance   = '10000.00000000';

// Check if already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$test_username, $test_email]);
$existing = $stmt->fetch();

if ($existing) {
    $results['testuser'] = [
        'status'   => 'already exists',
        'username' => $test_username,
        'password' => $test_password,
        'note'     => 'User was already in the database'
    ];
} else {
    $hashed = password_hash($test_password, PASSWORD_BCRYPT);
    $now    = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO users 
            (account_number, firstname, lastname, username, email,
             balance, password, status, ev, sv, ts, tv,
             created_at, updated_at)
        VALUES 
            (?, ?, ?, ?, ?,
             ?, ?, 1, 1, 1, 0, 1,
             ?, ?)
    ");

    $stmt->execute([
        $test_account,
        $test_firstname,
        $test_lastname,
        $test_username,
        $test_email,
        $test_balance,
        $hashed,
        $now,
        $now
    ]);

    $results['testuser'] = [
        'status'        => 'created successfully',
        'id'            => $pdo->lastInsertId(),
        'username'      => $test_username,
        'password'      => $test_password,
        'email'         => $test_email,
        'account'       => $test_account,
        'balance'       => '$10,000.00',
        'note'          => 'Login at /login.html'
    ];
}

// ─── SUMMARY ─────────────────────────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'message' => 'Setup complete. DELETE THIS FILE NOW: api/setup_test_users.php',
    'users'   => $results
], JSON_PRETTY_PRINT);
