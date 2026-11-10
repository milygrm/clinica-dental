<?php
require __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'Sesión expirada.']); exit;
}

try {
    $pdo = db();
    $q = fn($sql) => $pdo->query($sql)->fetchColumn();

    $data = [
        'pacientes'      => (int)$q('SELECT COUNT(*) FROM pacientes'),
        'atenciones'     => (int)$q('SELECT COUNT(*) FROM atenciones'),
        'atenciones_mes' => (int)$q("SELECT COUNT(*) FROM atenciones WHERE DATE_FORMAT(fecha,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')"),
        'ingresos_mes'   => (float)$q("SELECT COALESCE(SUM(costo),0) FROM atenciones WHERE DATE_FORMAT(fecha,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')"),
        'por_procedimiento' => $pdo->query(
            'SELECT pr.nombre, COUNT(a.id) AS total
             FROM procedimientos pr LEFT JOIN atenciones a ON a.procedimiento_id = pr.id
             GROUP BY pr.id, pr.nombre ORDER BY total DESC, pr.nombre LIMIT 8')->fetchAll(),
        'recientes' => $pdo->query(
            'SELECT a.id, a.fecha, a.paciente_id, CONCAT(p.nombres," ",p.apellidos) AS paciente, pr.nombre AS procedimiento, a.costo
             FROM atenciones a
             JOIN pacientes p ON p.id = a.paciente_id
             JOIN procedimientos pr ON pr.id = a.procedimiento_id
             ORDER BY a.fecha DESC, a.id DESC LIMIT 6')->fetchAll(),
    ];
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $ex) {
    echo json_encode(['ok' => false, 'msg' => 'Error del servidor: ' . $ex->getMessage()]);
}
