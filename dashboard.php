<?php
require 'includes/config.php';
$titulo = 'Panel principal'; $activo = 'dashboard'; $scripts = ['dashboard.js'];
include 'includes/layout.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h3 mb-0">Bienvenido, <?= e($_SESSION['nombre']) ?></h1>
    <p class="text-muted mb-0"><?= e(strftime_es()) ?></p>
  </div>
  <a href="pacientes.php?nuevo=1" class="btn btn-primary mt-2 mt-md-0"><i class="bi bi-person-plus"></i> Nuevo paciente</a>
</div>

<div class="row g-3 mb-4" id="tarjetas">
  <div class="col-6 col-xl-3"><div class="stat-card"><i class="bi bi-people"></i><div><div class="stat-num" id="sPacientes">–</div><div class="stat-lbl">Pacientes</div></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><i class="bi bi-journal-medical"></i><div><div class="stat-num" id="sAtenciones">–</div><div class="stat-lbl">Atenciones totales</div></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><i class="bi bi-calendar-check"></i><div><div class="stat-num" id="sMes">–</div><div class="stat-lbl">Atenciones este mes</div></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat-card"><i class="bi bi-cash-coin"></i><div><div class="stat-num" id="sIngresos">–</div><div class="stat-lbl">Ingresos este mes</div></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card h-100"><div class="card-header fw-semibold">Procedimientos más realizados</div>
      <div class="card-body" id="porProc"><div class="text-muted">Cargando...</div></div></div>
  </div>
  <div class="col-lg-7">
    <div class="card h-100"><div class="card-header fw-semibold">Últimas atenciones</div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Fecha</th><th>Paciente</th><th>Procedimiento</th><th class="text-end">Costo</th></tr></thead>
          <tbody id="recientes"><tr><td colspan="4" class="text-muted">Cargando...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php
function strftime_es(): string {
    $dias = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $t = time();
    return ucfirst($dias[date('w',$t)]) . ', ' . date('j',$t) . ' de ' . $meses[date('n',$t)-1] . ' de ' . date('Y',$t);
}
include 'includes/footer.php';
