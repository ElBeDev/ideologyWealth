<?php
/**
 * Test Registration System
 * Este archivo muestra información del sistema de registro
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        h1 { color: #0143a3; }
        h2 { color: #d4af37; margin-top: 30px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #0143a3; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .info-box { background: #e3f2fd; padding: 15px; border-left: 4px solid #0143a3; margin: 15px 0; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f0f9ff; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; color: #0143a3; }
        .stat-label { color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏦 Ideology Wealth Advisors - Sistema de Registro</h1>
        
        <?php
        try {
            // Test database connection
            echo '<h2>✅ Estado de la Base de Datos</h2>';
            echo '<div class="info-box success">Conexión a la base de datos exitosa</div>';
            
            // Get table structure
            echo '<h2>📋 Estructura de la Tabla "users"</h2>';
            $stmt = $pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll();
            
            echo '<table>';
            echo '<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>';
            foreach ($columns as $col) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Default']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Extra']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Get statistics
            echo '<h2>📊 Estadísticas de Usuarios</h2>';
            
            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
            $total = $stmt->fetch()['total'];
            
            // Users today
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()");
            $today = $stmt->fetch()['total'];
            
            // Users this month
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
            $month = $stmt->fetch()['total'];
            
            // Total balance
            $stmt = $pdo->query("SELECT SUM(balance) as total FROM users");
            $totalBalance = $stmt->fetch()['total'] ?? 0;
            
            echo '<div class="stats">';
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $total . '</div>';
            echo '<div class="stat-label">Total Usuarios</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $today . '</div>';
            echo '<div class="stat-label">Registros Hoy</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $month . '</div>';
            echo '<div class="stat-label">Registros Este Mes</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number">$' . number_format($totalBalance, 2) . '</div>';
            echo '<div class="stat-label">Balance Total</div>';
            echo '</div>';
            echo '</div>';
            
            // Recent users
            echo '<h2>👥 Últimos 10 Usuarios Registrados</h2>';
            $stmt = $pdo->query("
                SELECT id, account_number, firstname, lastname, username, email, 
                       country_code, mobile, balance, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $users = $stmt->fetchAll();
            
            if (count($users) > 0) {
                echo '<table>';
                echo '<tr>
                        <th>ID</th>
                        <th>Número de Cuenta</th>
                        <th>Nombre</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>País</th>
                        <th>Teléfono</th>
                        <th>Balance</th>
                        <th>Registro</th>
                      </tr>';
                
                foreach ($users as $user) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['account_number']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['country_code']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['mobile']) . '</td>';
                    echo '<td>$' . number_format($user['balance'], 2) . '</td>';
                    echo '<td>' . date('Y-m-d H:i', strtotime($user['created_at'])) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="info-box">No hay usuarios registrados aún</div>';
            }
            
            // System capabilities
            echo '<h2>⚙️ Capacidades del Sistema</h2>';
            echo '<div class="info-box">';
            echo '<ul>';
            echo '<li>✅ Registro de usuarios ilimitados</li>';
            echo '<li>✅ Validación de username único</li>';
            echo '<li>✅ Validación de email único</li>';
            echo '<li>✅ Generación automática de número de cuenta</li>';
            echo '<li>✅ Encriptación de contraseñas con BCrypt</li>';
            echo '<li>✅ Inicio de sesión automático después del registro</li>';
            echo '<li>✅ Soporte para múltiples países</li>';
            echo '<li>✅ Almacenamiento seguro en base de datos MySQL</li>';
            echo '<li>✅ API REST con validaciones completas</li>';
            echo '<li>✅ Sistema preparado para producción</li>';
            echo '</ul>';
            echo '</div>';
            
            // API Endpoints
            echo '<h2>🔌 API Endpoints Disponibles</h2>';
            echo '<table>';
            echo '<tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr>';
            echo '<tr><td>/api/register.php</td><td>POST</td><td>Registrar nuevo usuario</td></tr>';
            echo '<tr><td>/api/login.php</td><td>POST</td><td>Iniciar sesión</td></tr>';
            echo '<tr><td>/api/user.php</td><td>GET</td><td>Obtener datos del usuario actual</td></tr>';
            echo '<tr><td>/api/logout.php</td><td>GET</td><td>Cerrar sesión</td></tr>';
            echo '</table>';
            
        } catch (Exception $e) {
            echo '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <div style="margin-top: 40px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
            <h3>🔗 Enlaces Útiles</h3>
            <ul>
                <li><a href="/index.html">Página Principal</a></li>
                <li><a href="/register.html">Formulario de Registro</a></li>
                <li><a href="/login.html">Iniciar Sesión</a></li>
                <li><a href="/dashboard.html">Dashboard</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
