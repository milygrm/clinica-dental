<?php
require __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'Sesión expirada.']); exit;
}

function out($arr) { echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

function campos(): array {
    $c = [
        'cedula'           => trim($_POST['cedula'] ?? ''),
        'nombres'          => trim($_POST['nombres'] ?? ''),
        'apellidos'        => trim($_POST['apellidos'] ?? ''),
        'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
        'sexo'             => $_POST['sexo'] ?? 'M',
        'telefono'         => trim($_POST['telefono'] ?? ''),
        'email'            => trim($_POST['email'] ?? ''),
        'direccion'        => trim($_POST['direccion'] ?? ''),
        'alergias'         => trim($_POST['alergias'] ?? ''),
        'antecedentes'     => trim($_POST['antecedentes'] ?? ''),
    ];
    if ($c['cedula'] === '' || $c['nombres'] === '' || $c['apellidos'] === '' || $c['fecha_nacimiento'] === '') {
        out(['ok' => false, 'msg' => 'Cédula, nombres, apellidos y fecha de nacimiento son obligatorios.']);
    }
    if (!in_array($c['sexo'], ['M', 'F', 'O'], true)) $c['sexo'] = 'M';
    $d = DateTime::createFromFormat('Y-m-d', $c['fecha_nacimiento']);
    if (!$d || $d > new DateTime('today')) out(['ok' => false, 'msg' => 'Fecha de nacimiento inválida.']);
    if ($c['email'] !== '' && !filter_var($c['email'], FILTER_VALIDATE_EMAIL)) out(['ok' => false, 'msg' => 'Correo electrónico inválido.']);
    return $c;
}

$accion = $_REQUEST['accion'] ?? '';

try {
    $pdo = db();
    switch ($accion) {
        case 'listar':
            $q = trim($_GET['q'] ?? '');
            $sql = 'SELECT p.id, p.cedula, p.nombres, p.apellidos, p.fecha_nacimiento, p.telefono,
                           (SELECT COUNT(*) FROM atenciones a WHERE a.paciente_id = p.id) AS atenciones
                    FROM pacientes p';
            $par = [];
            if ($q !== '') {
                $sql .= ' WHERE p.cedula LIKE ? OR p.nombres LIKE ? OR p.apellidos LIKE ?
                          OR CONCAT(p.nombres, " ", p.apellidos) LIKE ?';
                $like = "%$q%";
                $par = [$like, $like, $like, $like];
            }
            $sql .= ' ORDER BY p.apellidos, p.nombres LIMIT 200';
            $st = $pdo->prepare($sql);
            $st->execute($par);
            $rows = $st->fetchAll();
            foreach ($rows as &$r) $r['edad'] = edad($r['fecha_nacimiento']);
            out(['ok' => true, 'data' => $rows]);

        case 'obtener':
            $st = $pdo->prepare('SELECT * FROM pacientes WHERE id = ?');
            $st->execute([(int)($_GET['id'] ?? 0)]);
            $p = $st->fetch();
            out($p ? ['ok' => true, 'data' => $p] : ['ok' => false, 'msg' => 'Paciente no encontrado.']);

        case 'guardar':
            $c = campos();
            $id = (int)($_POST['id'] ?? 0);
            $dup = $pdo->prepare('SELECT id FROM pacientes WHERE cedula = ? AND id <> ?');
            $dup->execute([$c['cedula'], $id]);
            if ($dup->fetch()) out(['ok' => false, 'msg' => 'Ya existe un paciente con esa cédula.']);

            if ($id > 0) {
                $sql = 'UPDATE pacientes SET cedula=:cedula, nombres=:nombres, apellidos=:apellidos,
                        fecha_nacimiento=:fecha_nacimiento, sexo=:sexo, telefono=:telefono, email=:email,
                        direccion=:direccion, alergias=:alergias, antecedentes=:antecedentes WHERE id=:id';
                $c['id'] = $id;
                $pdo->prepare($sql)->execute($c);
                out(['ok' => true, 'msg' => 'Paciente actualizado.', 'id' => $id]);
            }
            $sql = 'INSERT INTO pacientes (cedula, nombres, apellidos, fecha_nacimiento, sexo, telefono, email, direccion, alergias, antecedentes)
                    VALUES (:cedula, :nombres, :apellidos, :fecha_nacimiento, :sexo, :telefono, :email, :direccion, :alergias, :antecedentes)';
            $pdo->prepare($sql)->execute($c);
            out(['ok' => true, 'msg' => 'Paciente registrado.', 'id' => (int)$pdo->lastInsertId()]);

        case 'eliminar':
            $pdo->prepare('DELETE FROM pacientes WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
            out(['ok' => true, 'msg' => 'Paciente eliminado junto con su historia clínica.']);

        default:
            out(['ok' => false, 'msg' => 'Acción no válida.']);
    }
} catch (Throwable $ex) {
    out(['ok' => false, 'msg' => 'Error del servidor: ' . $ex->getMessage()]);
}
