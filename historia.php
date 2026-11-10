<?php
require 'includes/config.php';
requiere_login();
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM pacientes WHERE id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { header('Location: pacientes.php'); exit; }

$titulo = 'Historia clínica'; $activo = 'pacientes'; $scripts = ['historia.js'];
$sexos = ['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro'];
include 'includes/layout.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="pacientes.php">Pacientes</a></li>
  <li class="breadcrumb-item active">Historia clínica</li>
</ol></nav>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?= e($p['nombres'] . ' ' . $p['apellidos']) ?></h1>
  <div class="mt-2 mt-md-0">
    <a class="btn btn-outline-danger" target="_blank" href="historia_pdf.php?id=<?= $p['id'] ?>"><i class="bi bi-file-earmark-pdf"></i> Generar PDF</a>
    <button class="btn btn-primary" id="btnNuevaAt"><i class="bi bi-plus-lg"></i> Registrar atención</button>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header fw-semibold">Datos del paciente</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3"><div class="dato-lbl">Cédula</div><div><?= e($p['cedula']) ?></div></div>
      <div class="col-md-3"><div class="dato-lbl">Edad</div><div><?= edad($p['fecha_nacimiento']) ?> años (<?= date('d/m/Y', strtotime($p['fecha_nacimiento'])) ?>)</div></div>
      <div class="col-md-3"><div class="dato-lbl">Sexo</div><div><?= e($sexos[$p['sexo']] ?? '') ?></div></div>
      <div class="col-md-3"><div class="dato-lbl">Teléfono</div><div><?= e($p['telefono'] ?: '—') ?></div></div>
      <div class="col-md-6"><div class="dato-lbl">Correo</div><div><?= e($p['email'] ?: '—') ?></div></div>
      <div class="col-md-6"><div class="dato-lbl">Dirección</div><div><?= e($p['direccion'] ?: '—') ?></div></div>
      <div class="col-md-6"><div class="dato-lbl">Alergias</div><div><?= $p['alergias'] ? '<span class="badge text-bg-warning">'.e($p['alergias']).'</span>' : '—' ?></div></div>
      <div class="col-md-6"><div class="dato-lbl">Antecedentes médicos</div><div><?= e($p['antecedentes'] ?: '—') ?></div></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="fw-semibold">Historial de atenciones</span>
    <span class="text-muted small">Total: <strong id="totalCosto">$0.00</strong></span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Fecha</th><th>Procedimiento</th><th>Pieza</th><th>Diagnóstico / observaciones</th><th class="text-end">Costo</th><th class="text-end">Acciones</th></tr></thead>
      <tbody id="tbodyAt"><tr><td colspan="6" class="text-muted">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalAt" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="formAt" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalAt">Registrar atención</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="aId">
        <input type="hidden" name="paciente_id" id="aPac" value="<?= $p['id'] ?>">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Procedimiento *</label><select class="form-select" name="procedimiento_id" id="aProc" required></select></div>
          <div class="col-md-3"><label class="form-label">Fecha *</label><input type="date" class="form-control" name="fecha" id="aFecha" required></div>
          <div class="col-md-3"><label class="form-label">Pieza dental</label><input class="form-control" name="pieza_dental" id="aPieza" maxlength="20" placeholder="Ej.: 36, 48"></div>
          <div class="col-12"><label class="form-label">Diagnóstico</label><textarea class="form-control" rows="2" name="diagnostico" id="aDiag"></textarea></div>
          <div class="col-12"><label class="form-label">Observaciones / indicaciones</label><textarea class="form-control" rows="2" name="observaciones" id="aObs"></textarea></div>
          <div class="col-md-4"><label class="form-label">Costo (USD)</label>
            <div class="input-group"><span class="input-group-text">$</span><input type="number" step="0.01" min="0" class="form-control" name="costo" id="aCosto" value="0"></div></div>
        </div>
        <div id="errAt" class="alert alert-danger py-2 mt-3 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>window.PACIENTE_ID = <?= (int)$p['id'] ?>;</script>
<?php include 'includes/footer.php'; ?>
