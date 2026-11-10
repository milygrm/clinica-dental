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
            $soloActivos = !empty($_GET['activos']);
            $sql = 'SELECT id, nombre, descripcion, precio, activo FROM procedimientos'
                 . ($soloActivos ? ' WHERE activo = 1' : '') . ' ORDER BY nombre';
            out(['ok' => true, 'data' => $pdo->query($sql)->fetchAll()]);

        case 'obtener':
            $st = $pdo->prepare('SELECT * FROM procedimientos WHERE id = ?');
            $st->execute([(int)($_GET['id'] ?? 0)]);
            $p = $st->fetch();
            out($p ? ['ok' => true, 'data' => $p] : ['ok' => false, 'msg' => 'No encontrado.']);

        case 'guardar':
            $id     = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $desc   = trim($_POST['descripcion'] ?? '');
            $precio = str_replace(',', '.', trim($_POST['precio'] ?? '0'));
            $activo = !empty($_POST['activo']) ? 1 : 0;
            if ($nombre === '') out(['ok' => false, 'msg' => 'El nombre es obligatorio.']);
            if (!is_numeric($precio) || $precio < 0) out(['ok' => false, 'msg' => 'Precio inválido.']);

            if ($id > 0) {
                $pdo->prepare('UPDATE procedimientos SET nombre=?, descripcion=?, precio=?, activo=? WHERE id=?')
                    ->execute([$nombre, $desc, $precio, $activo, $id]);
                out(['ok' => true, 'msg' => 'Procedimiento actualizado.']);
            }
            $pdo->prepare('INSERT INTO procedimientos (nombre, descripcion, precio, activo) VALUES (?,?,?,?)')
                ->execute([$nombre, $desc, $precio, $activo]);
            out(['ok' => true, 'msg' => 'Procedimiento registrado.']);

        case 'eliminar':
            $id = (int)($_POST['id'] ?? 0);
            $st = $pdo->prepare('SELECT COUNT(*) FROM atenciones WHERE procedimiento_id = ?');
            $st->execute([$id]);
            if ($st->fetchColumn() > 0) {
                out(['ok' => false, 'msg' => 'Tiene atenciones registradas; desactívelo en lugar de eliminarlo.']);
            }
            $pdo->prepare('DELETE FROM procedimientos WHERE id = ?')->execute([$id]);
            out(['ok' => true, 'msg' => 'Procedimiento eliminado.']);

        default:
            out(['ok' => false, 'msg' => 'Acción no válida.']);
    }
} catch (Throwable $ex) {
    out(['ok' => false, 'msg' => 'Error del servidor: ' . $ex->getMessage()]);
}
