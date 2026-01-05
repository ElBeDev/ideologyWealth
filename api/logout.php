<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(['success' => false, 'message' => 'No autenticado'], 401);
}

// Destroy session
session_unset();
session_destroy();

jsonResponse(['success' => true, 'message' => 'Sesión cerrada exitosamente']);
