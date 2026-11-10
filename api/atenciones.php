<?php
require __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'Sesión expirada.']); exit;
}

function out($arr) { echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

$accion = $_REQUEST['accion'] ?? '';

try {
    $pdo = db();
    switch ($accion) {
        case 'listar':
            $st = $pdo->prepare('SELECT a.id, a.fecha, a.pieza_dental, a.diagnostico, a.observaciones, a.costo,
                                        a.procedimiento_id, pr.nombre AS procedimiento
                                 FROM atenciones a JOIN procedimientos pr ON pr.id = a.procedimiento_id
                                 WHERE a.paciente_id = ? ORDER BY a.fecha DESC, a.id DESC');
            $st->execute([(int)($_GET['paciente_id'] ?? 0)]);
            out(['ok' => true, 'data' => $st->fetchAll()]);

        case 'obtener':
            $st = $pdo->prepare('SELECT * FROM atenciones WHERE id = ?');
            $st->execute([(int)($_GET['id'] ?? 0)]);
            $a = $st->fetch();
            out($a ? ['ok' => true, 'data' => $a] : ['ok' => false, 'msg' => 'No encontrada.']);

        case 'guardar':
            $id    = (int)($_POST['id'] ?? 0);
            $pac   = (int)($_POST['paciente_id'] ?? 0);
            $proc  = (int)($_POST['procedimiento_id'] ?? 0);
            $fecha = trim($_POST['fecha'] ?? '');
            $pieza = trim($_POST['pieza_dental'] ?? '');
            $diag  = trim($_POST['diagnostico'] ?? '');
            $obs   = trim($_POST['observaciones'] ?? '');
            $costo = str_replace(',', '.', trim($_POST['costo'] ?? '0'));

            if (!$pac || !$proc || $fecha === '') out(['ok' => false, 'msg' => 'Procedimiento y fecha son obligatorios.']);
            if (!DateTime::createFromFormat('Y-m-d', $fecha)) out(['ok' => false, 'msg' => 'Fecha inválida.']);
            if (!is_numeric($costo) || $costo < 0) out(['ok' => false, 'msg' => 'Costo inválido.']);

            if ($id > 0) {
                $pdo->prepare('UPDATE atenciones SET procedimiento_id=?, fecha=?, pieza_dental=?, diagnostico=?, observaciones=?, costo=? WHERE id=? AND paciente_id=?')
                    ->execute([$proc, $fecha, $pieza, $diag, $obs, $costo, $id, $pac]);
                out(['ok' => true, 'msg' => 'Atención actualizada.']);
            }
            $pdo->prepare('INSERT INTO atenciones (paciente_id, procedimiento_id, fecha, pieza_dental, diagnostico, observaciones, costo) VALUES (?,?,?,?,?,?,?)')
                ->execute([$pac, $proc, $fecha, $pieza, $diag, $obs, $costo]);
            out(['ok' => true, 'msg' => 'Atención registrada en la historia clínica.']);

        case 'eliminar':
            $pdo->prepare('DELETE FROM atenciones WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
            out(['ok' => true, 'msg' => 'Atención eliminada.']);

        default:
            out(['ok' => false, 'msg' => 'Acción no válida.']);
    }
} catch (Throwable $ex) {
    out(['ok' => false, 'msg' => 'Error del servidor: ' . $ex->getMessage()]);
}
