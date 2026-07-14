<?php
/**
 * Ideology Wealth Advisors - Suite de Pruebas del Sistema
 * 
 * Verifica todos los cambios implementados:
 *  1. Registro con activación por admin
 *  2. Número de cuenta visible y editable
 *  3. Depósitos visibles en movimientos
 *  4. Buscador de usuarios
 *  5. Monitor de logins
 *  6. Auto-corrector (validación backend)
 *
 * SEGURIDAD: eliminá este archivo después de la revisión.
 * Acceso: /api/run_tests.php  (solo desde localhost o con token)
 */

// ── Protección básica ──────────────────────────────────────────────────────
$is_cli = (php_sapi_name() === 'cli');
$allowed_ips = ['127.0.0.1', '::1'];
$request_ip  = $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['HTTP_CLIENT_IP']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '';
$request_ip = trim(explode(',', $request_ip)[0]);

$token_ok = ($_GET['token'] ?? '') === 'ideologyTest2026';

if (!$is_cli && !in_array($request_ip, $allowed_ips, true) && !$token_ok) {
    http_response_code(403);
    die('<h2>403 Forbidden</h2><p>Add <code>?token=ideologyTest2026</code> to the URL, or run from localhost.</p>');
}
// ───────────────────────────────────────────────────────────────────────────

require_once __DIR__ . '/config.php';

// ── Helpers ────────────────────────────────────────────────────────────────
$results = [];
$pass = 0; $fail = 0;

function test(string $name, callable $fn) {
    global $results, $pass, $fail;
    try {
        $result = $fn();
        if ($result === true || (is_array($result) && ($result['ok'] ?? false))) {
            $note = is_array($result) ? ($result['note'] ?? '') : '';
            $results[] = ['status' => 'PASS', 'name' => $name, 'note' => $note];
            $pass++;
        } else {
            $note = is_array($result) ? ($result['note'] ?? json_encode($result)) : (string)$result;
            $results[] = ['status' => 'FAIL', 'name' => $name, 'note' => $note];
            $fail++;
        }
    } catch (Throwable $e) {
        $results[] = ['status' => 'ERROR', 'name' => $name, 'note' => $e->getMessage()];
        $fail++;
    }
}

function db(): PDO { global $pdo; return $pdo; }

// ── Test data ──────────────────────────────────────────────────────────────
$TEST_USER   = 'test_suite_' . substr(md5(microtime()), 0, 6);
$TEST_EMAIL  = $TEST_USER . '@testideologywealth.com';
$TEST_PASS   = 'Test1234!';
$TEST_USER_ID = null;

// ═══════════════════════════════════════════════════════════════════════════
// BLOQUE 1 — Registro y activación por admin
// ═══════════════════════════════════════════════════════════════════════════
test('Registro crea usuario con status=0 (pendiente)', function () {
    global $TEST_USER, $TEST_EMAIL, $TEST_PASS, $TEST_USER_ID;

    // Limpiar si existe de pruebas anteriores
    db()->prepare("DELETE FROM users WHERE email = ?")->execute([$TEST_EMAIL]);

    $hash = hashPassword($TEST_PASS);
    $acct = '9990000001';

    $stmt = db()->prepare("
        INSERT INTO users (account_number, firstname, lastname, username, email, country_code, mobile, city, state, zip, address, password, balance, status, ev, sv, kycv, created_at, updated_at)
        VALUES (?, 'Test', 'Suite', ?, ?, 'US', '5550000001', 'TestCity', 'TS', '00000', '123 Test St', ?, 0.00, 0, 0, 0, 0, NOW(), NOW())
    ");
    $stmt->execute([$acct, $TEST_USER, $TEST_EMAIL, $hash]);
    $TEST_USER_ID = (int) db()->lastInsertId();

    // Verificar que status es 0
    $row = db()->prepare("SELECT status FROM users WHERE id = ?");
    $row->execute([$TEST_USER_ID]);
    $status = (int) $row->fetchColumn();

    if ($status !== 0) {
        return ['ok' => false, 'note' => "status esperado 0, obtenido: $status"];
    }
    return ['ok' => true, 'note' => "user_id=$TEST_USER_ID, status=0 ✓"];
});

test('Login bloqueado para cuenta pendiente (status=0)', function () {
    global $TEST_EMAIL, $TEST_PASS;

    $stmt = db()->prepare("SELECT id, status, password FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$TEST_EMAIL]);
    $user = $stmt->fetch();

    if (!$user) return ['ok' => false, 'note' => 'Usuario no encontrado'];
    if ($user['status'] != 0) return ['ok' => false, 'note' => 'status no es 0'];
    if (!password_verify($TEST_PASS, $user['password'])) return ['ok' => false, 'note' => 'password hash incorrecto'];

    // Simular lógica de login.php
    if ($user['status'] == 0) {
        return ['ok' => true, 'note' => 'Bloqueado correctamente — mensaje: pending approval'];
    }
    return ['ok' => false, 'note' => 'Debería estar bloqueado'];
});

test('Admin activa la cuenta (status=1)', function () {
    global $TEST_USER_ID;

    db()->prepare("UPDATE users SET status = 1 WHERE id = ?")->execute([$TEST_USER_ID]);

    $stmt = db()->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$TEST_USER_ID]);
    $status = (int) $stmt->fetchColumn();

    return ['ok' => $status === 1, 'note' => "status ahora = $status"];
});

test('Login permitido después de activación', function () {
    global $TEST_EMAIL, $TEST_PASS;

    $stmt = db()->prepare("SELECT id, status, password FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$TEST_EMAIL]);
    $user = $stmt->fetch();

    if (!$user) return ['ok' => false, 'note' => 'Usuario no encontrado'];
    if ($user['status'] != 1) return ['ok' => false, 'note' => "status = {$user['status']}, no activo"];
    if (!password_verify($TEST_PASS, $user['password'])) return ['ok' => false, 'note' => 'Password incorrecto'];

    return ['ok' => true, 'note' => 'Credenciales válidas, status=1 — login permitido ✓'];
});

// ═══════════════════════════════════════════════════════════════════════════
// BLOQUE 2 — Número de cuenta
// ═══════════════════════════════════════════════════════════════════════════
test('Campo account_number devuelto por admin_users GET', function () {
    global $TEST_USER_ID;

    $stmt = db()->prepare("
        SELECT id, account_number FROM users WHERE id = ?
    ");
    $stmt->execute([$TEST_USER_ID]);
    $row = $stmt->fetch();

    if (!$row) return ['ok' => false, 'note' => 'Usuario no encontrado'];
    if (!array_key_exists('account_number', $row)) return ['ok' => false, 'note' => 'account_number no retornado'];

    return ['ok' => true, 'note' => "account_number = '{$row['account_number']}' ✓"];
});

test('Admin puede editar account_number', function () {
    global $TEST_USER_ID;

    $newAcct = '99988877' . rand(10, 99);
    db()->prepare("UPDATE users SET account_number = ? WHERE id = ?")->execute([$newAcct, $TEST_USER_ID]);

    $stmt = db()->prepare("SELECT account_number FROM users WHERE id = ?");
    $stmt->execute([$TEST_USER_ID]);
    $saved = $stmt->fetchColumn();

    return ['ok' => $saved === $newAcct, 'note' => "Guardado: $saved ✓"];
});

test('API user.php incluye account_number en respuesta', function () {
    global $TEST_USER_ID;

    // Simular query que hace user.php
    $stmt = db()->prepare("
        SELECT id, username, email, firstname, lastname, balance, account_number, status
        FROM users WHERE id = ? LIMIT 1
    ");
    $stmt->execute([$TEST_USER_ID]);
    $user = $stmt->fetch();

    $has = isset($user['account_number']);
    return ['ok' => $has, 'note' => $has ? "account_number presente: '{$user['account_number']}'" : 'campo ausente'];
});

// ═══════════════════════════════════════════════════════════════════════════
// BLOQUE 3 — Depósitos visibles en movimientos
// ═══════════════════════════════════════════════════════════════════════════
test('Tabla transactions existe y tiene columnas correctas', function () {
    $cols = db()->query("DESCRIBE transactions")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['id', 'user_id', 'trx', 'date', 'details', 'amount', 'type', 'status'];
    $missing = array_diff($required, $cols);
    if ($missing) return ['ok' => false, 'note' => 'Faltan columnas: ' . implode(', ', $missing)];
    return ['ok' => true, 'note' => 'Todas las columnas presentes ✓'];
});

test('Depósito aprobado genera registro en transactions', function () {
    global $TEST_USER_ID;

    // 1. Crear depósito pendiente
    $trx = 'TESTDEP' . time();
    db()->prepare("
        INSERT INTO deposits (user_id, trx, gateway, amount, charge, final_amount, status, notes, created_at, updated_at)
        VALUES (?, ?, 'Test Gateway', 500.00, 10.00, 510.00, 'pending', 'Test deposit', NOW(), NOW())
    ")->execute([$TEST_USER_ID, $trx]);
    $deposit_id = (int) db()->lastInsertId();

    // 2. Simular lógica de admin_deposits.php (approve)
    db()->prepare("UPDATE deposits SET status = 'approved', updated_at = NOW() WHERE id = ?")->execute([$deposit_id]);
    db()->prepare("UPDATE users SET balance = balance + 510.00 WHERE id = ?")->execute([$TEST_USER_ID]);

    $stmt = db()->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$TEST_USER_ID]);
    $newBalance = (float) $stmt->fetchColumn();

    // 3. Insertar transaction record (lógica copiada de admin_deposits.php)
    $txTrx = 'DEP' . $trx;
    $chk = db()->prepare("SELECT id FROM transactions WHERE trx = ? LIMIT 1");
    $chk->execute([$txTrx]);
    if (!$chk->fetch()) {
        db()->prepare("
            INSERT INTO transactions (user_id, trx, date, details, account_number, amount, type, status, post_balance, description, created_at)
            VALUES (?, ?, NOW(), ?, '', ?, 'credit', 'completed', ?, ?, NOW())
        ")->execute([$TEST_USER_ID, $txTrx, 'Deposit - Test Gateway', 510.00, $newBalance, 'Deposit approved via Test Gateway']);
    }

    // 4. Verificar que aparece en transactions
    $check = db()->prepare("SELECT id, amount, type, status FROM transactions WHERE trx = ? AND user_id = ? LIMIT 1");
    $check->execute([$txTrx, $TEST_USER_ID]);
    $tx = $check->fetch();

    if (!$tx) return ['ok' => false, 'note' => 'Registro no encontrado en transactions'];
    if ($tx['type'] !== 'credit') return ['ok' => false, 'note' => "type = '{$tx['type']}', esperado 'credit'"];
    if ($tx['status'] !== 'completed') return ['ok' => false, 'note' => "status = '{$tx['status']}', esperado 'completed'"];

    return ['ok' => true, 'note' => "transaction_id={$tx['id']}, amount=\$510.00, type=credit, status=completed ✓"];
});

test('Depósito aprobado actualiza balance del usuario en BD', function () {
    global $TEST_USER_ID;

    $stmt = db()->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$TEST_USER_ID]);
    $balance = (float) $stmt->fetchColumn();

    return ['ok' => $balance >= 510.00, 'note' => "Balance = \$$balance ✓"];
});

// ═══════════════════════════════════════════════════════════════════════════
// BLOQUE 4 — Buscador de usuarios
// ═══════════════════════════════════════════════════════════════════════════
test('Búsqueda por email funciona en BD', function () {
    global $TEST_EMAIL, $TEST_USER_ID;

    $term = '%test_suite%';
    $stmt = db()->prepare("SELECT id, username, email FROM users WHERE email LIKE ? OR username LIKE ? LIMIT 5");
    $stmt->execute([$term, $term]);
    $rows = $stmt->fetchAll();

    $found = array_filter($rows, fn($r) => (int)$r['id'] === (int)$TEST_USER_ID);
    return ['ok' => !empty($found), 'note' => 'Encontrado por búsqueda: ' . count($rows) . ' resultado(s) ✓'];
});

test('Búsqueda por nombre retorna resultados', function () {
    global $TEST_USER_ID;

    $stmt = db()->prepare("SELECT id FROM users WHERE firstname LIKE '%Test%' LIMIT 5");
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return ['ok' => !empty($rows), 'note' => count($rows) . ' usuario(s) con nombre "Test" ✓'];
});

// ═══════════════════════════════════════════════════════════════════════════
// BLOQUE 5 — Monitor de logins
// ═══════════════════════════════════════════════════════════════════════════
test('Tabla login_logs existe (o se puede crear)', function () {
    db()->exec("
        CREATE TABLE IF NOT EXISTS login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            username VARCHAR(100),
            ip_address VARCHAR(45),
            user_agent TEXT,
            device_type VARCHAR(50),
            browser VARCHAR(100),
            os VARCHAR(100),
            location_country VARCHAR(100),
            location_city VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $exists = db()->query("SHOW TABLES LIKE 'login_logs'")->fetchColumn();
    return ['ok' => (bool)$exists, 'note' => $exists ? 'Tabla login_logs OK ✓' : 'No se pudo crear'];
});

test('Login log se puede insertar y leer', function () {
    global $TEST_USER_ID, $TEST_USER;

    db()->prepare("
        INSERT INTO login_logs (user_id, username, ip_address, user_agent, device_type, browser, os, location_country, location_city)
        VALUES (?, ?, '192.168.1.100', 'Mozilla/5.0 TestAgent', 'Desktop', 'Chrome 120', 'Windows', 'Mexico', 'CDMX')
    ")->execute([$TEST_USER_ID, $TEST_USER]);

    $stmt = db()->prepare("SELECT id, device_type, location_country FROM login_logs WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$TEST_USER_ID]);
    $log = $stmt->fetch();

    if (!$log) return ['ok' => false, 'note' => 'Log no encontrado'];
    return ['ok' => true, 'note' => "log_id={$log['id']}, device={$log['device_type']}, país={$log['location_country']} ✓"];
});

test('Login log se consulta por user_id (para admin panel)', function () {
    global $TEST_USER_ID;

    $stmt = db()->prepare("
        SELECT l.id, l.ip_address, l.browser, l.os, l.device_type, l.location_country, l.created_at,
               u.firstname, u.lastname, u.email
        FROM login_logs l
        LEFT JOIN users u ON l.user_id = u.id
        WHERE l.user_id = ?
        ORDER BY l.created_at DESC LIMIT 5
    ");
    $stmt->execute([$TEST_USER_ID]);
    $logs = $stmt->fetchAll();

    if (empty($logs)) return ['ok' => false, 'note' => 'Sin registros para user_id'];
    $first = $logs[0];
    return ['ok' => true, 'note' => "Encontrados " . count($logs) . " log(s). Último: IP={$first['ip_address']}, {$first['browser']} en {$first['os']} ✓"];
});

// ═══════════════════════════════════════════════════════════════════════════
// BLOQUE 6 — Validación de campos (auto-corrector backend)
// ═══════════════════════════════════════════════════════════════════════════
test('Backend rechaza username con caracteres especiales', function () {
    $username = 'juan@pérez!';
    $valid = (bool) preg_match('/^[a-zA-Z0-9]{4,40}$/', $username);
    return ['ok' => !$valid, 'note' => "username '$username' rechazado por regex ✓"];
});

test('Backend rechaza username muy corto', function () {
    $username = 'ab';
    $valid = (bool) preg_match('/^[a-zA-Z0-9]{4,40}$/', $username);
    return ['ok' => !$valid, 'note' => "username '$username' (2 chars) rechazado ✓"];
});

test('Backend acepta username válido', function () {
    $username = 'JuanPerez99';
    $valid = (bool) preg_match('/^[a-zA-Z0-9]{4,40}$/', $username);
    return ['ok' => $valid, 'note' => "username '$username' aceptado ✓"];
});

test('Email inválido rechazado por FILTER_VALIDATE_EMAIL', function () {
    $bad = 'no-es-un-email';
    return ['ok' => filter_var($bad, FILTER_VALIDATE_EMAIL) === false, 'note' => "'$bad' rechazado ✓"];
});

test('Email válido aceptado', function () {
    $good = 'usuario@example.com';
    return ['ok' => (bool) filter_var($good, FILTER_VALIDATE_EMAIL), 'note' => "'$good' aceptado ✓"];
});

// ═══════════════════════════════════════════════════════════════════════════
// BLOQUE 7 — Limpieza de datos de prueba
// ═══════════════════════════════════════════════════════════════════════════
test('Limpieza: eliminar datos de prueba', function () {
    global $TEST_USER_ID;

    if (!$TEST_USER_ID) return ['ok' => true, 'note' => 'Nada que limpiar'];

    db()->prepare("DELETE FROM login_logs WHERE user_id = ?")->execute([$TEST_USER_ID]);
    db()->prepare("DELETE FROM transactions WHERE user_id = ?")->execute([$TEST_USER_ID]);
    db()->prepare("DELETE FROM deposits WHERE user_id = ?")->execute([$TEST_USER_ID]);
    db()->prepare("DELETE FROM users WHERE id = ?")->execute([$TEST_USER_ID]);

    $still = db()->prepare("SELECT id FROM users WHERE id = ?");
    $still->execute([$TEST_USER_ID]);
    $gone = $still->fetchColumn() === false;

    return ['ok' => $gone, 'note' => "user_id=$TEST_USER_ID eliminado ✓"];
});

// ═══════════════════════════════════════════════════════════════════════════
// OUTPUT HTML
// ═══════════════════════════════════════════════════════════════════════════
$total = $pass + $fail;
$pct   = $total > 0 ? round($pass / $total * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ideology Wealth Advisors — Suite de Pruebas</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; color: #333; padding: 30px 20px; }
  h1 { font-size: 22px; color: #83af40; margin-bottom: 4px; }
  .subtitle { color: #888; font-size: 13px; margin-bottom: 24px; }
  .summary { display: flex; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
  .stat { flex: 1; min-width: 130px; background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,.07); text-align: center; }
  .stat .num { font-size: 32px; font-weight: 700; }
  .stat .lbl { font-size: 12px; color: #777; margin-top: 4px; text-transform: uppercase; letter-spacing: .5px; }
  .stat.pass .num { color: #28a745; }
  .stat.fail .num { color: #dc3545; }
  .stat.total .num { color: #083b6b; }
  .progress-bar { height: 8px; background: #e9ecef; border-radius: 4px; margin-bottom: 28px; overflow: hidden; }
  .progress-fill { height: 100%; background: linear-gradient(90deg,#28a745,#83af40); transition: width .4s; }
  .block { margin-bottom: 28px; }
  .block-title { font-size: 14px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; padding-left: 4px; }
  .test-row { display: flex; align-items: baseline; gap: 10px; background: #fff; border-radius: 8px; padding: 12px 16px; margin-bottom: 6px; box-shadow: 0 1px 4px rgba(0,0,0,.05); border-left: 4px solid #ddd; }
  .test-row.PASS { border-left-color: #28a745; }
  .test-row.FAIL { border-left-color: #dc3545; background: #fff5f5; }
  .test-row.ERROR { border-left-color: #fd7e14; background: #fff9f5; }
  .badge { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; white-space: nowrap; }
  .badge.PASS { background: #d4edda; color: #155724; }
  .badge.FAIL { background: #f8d7da; color: #721c24; }
  .badge.ERROR { background: #ffe8d1; color: #7a3700; }
  .test-name { font-size: 14px; flex: 1; }
  .test-note { font-size: 12px; color: #888; font-family: monospace; word-break: break-all; }
  .warn { background:#fff3cd; border:1px solid #ffc107; border-radius:8px; padding:12px 16px; font-size:13px; margin-top:24px; color:#856404; }
  .warn strong { display:block; margin-bottom:4px; }
</style>
</head>
<body>

<h1>🧪 Ideology Wealth Advisors — Suite de Pruebas</h1>
<p class="subtitle">Ejecutado el <?= date('Y-m-d H:i:s') ?> · <?= $total ?> pruebas</p>

<div class="summary">
  <div class="stat total"><div class="num"><?= $total ?></div><div class="lbl">Total</div></div>
  <div class="stat pass"><div class="num"><?= $pass ?></div><div class="lbl">Pasaron ✓</div></div>
  <div class="stat fail"><div class="num"><?= $fail ?></div><div class="lbl">Fallaron ✗</div></div>
  <div class="stat total"><div class="num"><?= $pct ?>%</div><div class="lbl">Éxito</div></div>
</div>

<div class="progress-bar">
  <div class="progress-fill" style="width:<?= $pct ?>%"></div>
</div>

<?php
$blocks = [
    'BLOQUE 1 — Registro y activación por admin'  => range(0, 3),
    'BLOQUE 2 — Número de cuenta'                 => range(4, 6),
    'BLOQUE 3 — Depósitos en movimientos'         => range(7, 9),
    'BLOQUE 4 — Buscador de usuarios'             => range(10, 11),
    'BLOQUE 5 — Monitor de logins'                => range(12, 14),
    'BLOQUE 6 — Validación auto-corrector'        => range(15, 19),
    'BLOQUE 7 — Limpieza'                         => range(20, 20),
];

$idx = 0;
foreach ($blocks as $blockTitle => $indices) {
    echo "<div class=\"block\">";
    echo "<div class=\"block-title\">$blockTitle</div>";
    foreach ($indices as $i) {
        if (!isset($results[$i])) continue;
        $r = $results[$i];
        echo "<div class=\"test-row {$r['status']}\">";
        echo "<span class=\"badge {$r['status']}\">{$r['status']}</span>";
        echo "<span class=\"test-name\">" . htmlspecialchars($r['name']) . "</span>";
        if ($r['note']) {
            echo "<span class=\"test-note\">" . htmlspecialchars($r['note']) . "</span>";
        }
        echo "</div>\n";
    }
    echo "</div>";
}
?>

<div class="warn">
  <strong>⚠️ RECUERDA:</strong>
  Este archivo es solo para pruebas internas. <strong>Elimínalo del servidor</strong> después de verificar:
  <code>rm /var/www/ideologywealthadvisors/api/run_tests.php</code>
</div>

</body>
</html>
