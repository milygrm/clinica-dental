<?php
require 'includes/config.php';
requiere_login();
require 'includes/SimplePDF.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM pacientes WHERE id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { http_response_code(404); exit('Paciente no encontrado.'); }

$st = db()->prepare('SELECT a.*, pr.nombre AS procedimiento
                     FROM atenciones a JOIN procedimientos pr ON pr.id = a.procedimiento_id
                     WHERE a.paciente_id = ? ORDER BY a.fecha ASC, a.id ASC');
$st->execute([$id]);
$atenciones = $st->fetchAll();

$sexos = ['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro'];
$TEAL = [13, 124, 143]; $DARK = [30, 40, 45]; $GRAY = [107, 122, 130]; $LIGHT = [240, 246, 248]; $LINE = [215, 224, 228];

$pdf = new SimplePDF();
$ML = 40; $MR = 40; $CW = $pdf->w - $ML - $MR; $BOTTOM = $pdf->h - 55;

function cabecera(SimplePDF $pdf, array $c, bool $primera): float {
    $pdf->rect(0, 0, $pdf->w, 70, $c['TEAL']);
    $pdf->text(40, 33, CLINICA_NOMBRE, 17, true, [255, 255, 255]);
    $pdf->text(40, 52, 'Historia clínica odontológica', 10.5, false, [220, 242, 246]);
    $pdf->text(40, 33, 'N.º ' . str_pad((string)$c['id'], 5, '0', STR_PAD_LEFT), 10, true, [255, 255, 255], 'R', $pdf->w - 80);
    return 92;
}
$ctx = ['TEAL' => $TEAL, 'id' => $p['id']];

$pdf->addPage();
$y = cabecera($pdf, $ctx, true);

// ---- Datos del paciente ----
$pdf->text($ML, $y, 'DATOS DEL PACIENTE', 10, true, $TEAL);
$y += 6;
$pdf->line($ML, $y, $ML + $CW, $y, $TEAL, 1);
$y += 16;

$campos = [
    ['Nombre completo', $p['nombres'] . ' ' . $p['apellidos']],
    ['Cédula', $p['cedula']],
    ['Fecha de nacimiento', date('d/m/Y', strtotime($p['fecha_nacimiento'])) . ' (' . edad($p['fecha_nacimiento']) . ' años)'],
    ['Sexo', $sexos[$p['sexo']] ?? ''],
    ['Teléfono', $p['telefono'] ?: '—'],
    ['Correo electrónico', $p['email'] ?: '—'],
];
$colW = $CW / 2;
foreach (array_chunk($campos, 2) as $fila) {
    foreach ($fila as $i => [$lbl, $val]) {
        $x = $ML + $i * $colW;
        $pdf->text($x, $y, mb_strtoupper($lbl), 7.5, true, $GRAY);
        $lineas = $pdf->wrap($val, $colW - 12, 10);
        $pdf->text($x, $y + 12, $lineas[0], 10, false, $DARK);
    }
    $y += 32;
}
// Dirección, alergias, antecedentes (ancho completo)
foreach ([['Dirección', $p['direccion']], ['Alergias', $p['alergias']], ['Antecedentes médicos', $p['antecedentes']]] as [$lbl, $val]) {
    $lineas = $pdf->wrap($val ?: '—', $CW, 10);
    $alto = 12 + count($lineas) * 13 + 6;
    if ($y + $alto > $BOTTOM) { $pdf->addPage(); $y = cabecera($pdf, $ctx, false); }
    $pdf->text($ML, $y, mb_strtoupper($lbl), 7.5, true, $GRAY);
    $yy = $y + 12;
    foreach ($lineas as $l) { $pdf->text($ML, $yy, $l, 10, false, $DARK); $yy += 13; }
    $y = $yy + 6;
}

// ---- Tabla de atenciones ----
$y += 8;
$cols = [ // x relativo, ancho, título
    ['x' => 0,   'w' => 62,  't' => 'Fecha'],
    ['x' => 62,  'w' => 120, 't' => 'Procedimiento'],
    ['x' => 182, 'w' => 40,  't' => 'Pieza'],
    ['x' => 222, 'w' => 223, 't' => 'Diagnóstico / observaciones'],
    ['x' => 445, 'w' => 70,  't' => 'Costo'],
];
function encabezadoTabla(SimplePDF $pdf, array $cols, float $y, float $ML, float $CW, array $TEAL): float {
    $pdf->rect($ML, $y, $CW, 20, $TEAL);
    foreach ($cols as $c) {
        $al = $c['t'] === 'Costo' ? 'R' : 'L';
        $pdf->text($ML + $c['x'] + 5, $y + 13.5, $c['t'], 8.5, true, [255, 255, 255], $al, $c['w'] - 10);
    }
    return $y + 20;
}

if ($y + 60 > $BOTTOM) { $pdf->addPage(); $y = cabecera($pdf, $ctx, false); }
$pdf->text($ML, $y, 'HISTORIAL DE ATENCIONES', 10, true, $TEAL);
$y += 6;
$pdf->line($ML, $y, $ML + $CW, $y, $TEAL, 1);
$y += 12;
$y = encabezadoTabla($pdf, $cols, $y, $ML, $CW, $TEAL);

$total = 0.0;
if (!$atenciones) {
    $pdf->text($ML, $y + 22, 'Este paciente aún no tiene atenciones registradas.', 10, false, $GRAY);
    $y += 40;
}
foreach ($atenciones as $i => $a) {
    $total += (float)$a['costo'];
    $detalle = trim(implode("\n", array_filter([
        $a['diagnostico'] ? 'Dx: ' . $a['diagnostico'] : '',
        $a['observaciones'] ? 'Obs: ' . $a['observaciones'] : '',
    ])));
    $lDet  = $pdf->wrap($detalle !== '' ? $detalle : '—', $cols[3]['w'] - 10, 8.5);
    $lProc = $pdf->wrap($a['procedimiento'], $cols[1]['w'] - 10, 9, true);
    $filas = max(count($lDet), count($lProc), 1);
    $alto = $filas * 11.5 + 10;

    if ($y + $alto > $BOTTOM) {
        $pdf->addPage();
        $y = cabecera($pdf, $ctx, false);
        $y = encabezadoTabla($pdf, $cols, $y, $ML, $CW, $TEAL);
    }
    if ($i % 2 === 0) $pdf->rect($ML, $y, $CW, $alto, $LIGHT);

    $ty = $y + 13;
    $pdf->text($ML + $cols[0]['x'] + 5, $ty, date('d/m/Y', strtotime($a['fecha'])), 9, false, $DARK);
    foreach ($lProc as $k => $l) $pdf->text($ML + $cols[1]['x'] + 5, $ty + $k * 11.5, $l, 9, true, $DARK);
    $pdf->text($ML + $cols[2]['x'] + 5, $ty, $a['pieza_dental'] ?: '—', 9, false, $DARK);
    foreach ($lDet as $k => $l) $pdf->text($ML + $cols[3]['x'] + 5, $ty + $k * 11.5, $l, 8.5, false, $DARK);
    $pdf->text($ML + $cols[4]['x'] + 5, $ty, '$' . number_format((float)$a['costo'], 2), 9, false, $DARK, 'R', $cols[4]['w'] - 10);
    $y += $alto;
    $pdf->line($ML, $y, $ML + $CW, $y, $LINE, 0.4);
}

// Total
if ($atenciones) {
    if ($y + 30 > $BOTTOM) { $pdf->addPage(); $y = cabecera($pdf, $ctx, false); }
    $y += 8;
    $pdf->text($ML, $y + 8, count($atenciones) . ' atención(es) registrada(s)', 9, false, $GRAY);
    $pdf->text($ML + 300, $y + 8, 'TOTAL', 10, true, $DARK);
    $pdf->text($ML + $cols[4]['x'] + 5, $y + 8, '$' . number_format($total, 2), 11, true, $TEAL, 'R', $cols[4]['w'] - 10);
    $y += 22;
}

// Firma
if ($y + 70 > $BOTTOM) { $pdf->addPage(); $y = cabecera($pdf, $ctx, false); }
$y += 40;
$pdf->line($ML + 300, $y, $ML + $CW, $y, $DARK, 0.6);
$pdf->text($ML + 300, $y + 13, 'Firma y sello del odontólogo', 8.5, false, $GRAY, 'C', $CW - 300);

// Pie de página en todas las hojas
$totalPag = $pdf->pageCount();
for ($i = 0; $i < $totalPag; $i++) {
    $pdf->setPage($i);
    $pdf->line($ML, $pdf->h - 40, $ML + $CW, $pdf->h - 40, $LINE, 0.5);
    $pdf->text($ML, $pdf->h - 26, 'Documento confidencial · Generado el ' . date('d/m/Y H:i'), 8, false, $GRAY);
    $pdf->text($ML, $pdf->h - 26, 'Página ' . ($i + 1) . ' de ' . $totalPag, 8, false, $GRAY, 'R', $CW);
}

$nombreArchivo = 'historia_clinica_' . preg_replace('/[^a-z0-9]+/i', '_', $p['cedula']) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
echo $pdf->output();
