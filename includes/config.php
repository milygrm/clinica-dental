<?php
// =====================================================
// CONFIGURACIÓN: edite estos datos con los de su hosting
// (InfinityFree: panel -> MySQL Databases)
// =====================================================
define('DB_HOST', 'sql204.infinityfree.com');
define('DB_NAME', 'if0_43399442_12345clinica');
define('DB_USER', 'if0_43399442');
define('DB_PASS', '30809669eR');

define('CLINICA_NOMBRE', 'Consultorio Odontológico');

date_default_timezone_set('America/Caracas');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function requiere_login(): void {
    if (empty($_SESSION['uid'])) {
        header('Location: index.php');
        exit;
    }
}

function edad(string $fecha): int {
    return (int)(new DateTime($fecha))->diff(new DateTime('today'))->y;
}
