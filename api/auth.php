<?php
require __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']); exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$clave   = $_POST['clave'] ?? '';

if ($usuario === '' || $clave === '') {
    echo json_encode(['ok' => false, 'msg' => 'Ingrese usuario y contraseña.']); exit;
}

try {
    $st = db()->prepare('SELECT id, nombre, clave FROM usuarios WHERE usuario = ?');
    $st->execute([$usuario]);
    $u = $st->fetch();
    if ($u && password_verify($clave, $u['clave'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        $_SESSION['nombre'] = $u['nombre'];
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Usuario o contraseña incorrectos.']);
    }
} catch (Throwable $ex) {
    echo json_encode(['ok' => false, 'msg' => 'Error de base de datos. Revise includes/config.php.']);
}
