<?php
require 'includes/config.php';
$titulo = 'Procedimientos'; $activo = 'procedimientos'; $scripts = ['procedimientos.js'];
include 'includes/layout.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-0"><i class="bi bi-clipboard2-pulse"></i> Procedimientos odontológicos</h1>
    <p class="text-muted mb-0">Servicios que ofrece el médico odontólogo.</p>
  </div>
  <button class="btn btn-primary mt-2 mt-md-0" id="btnNuevoProc"><i class="bi bi-plus-lg"></i> Nuevo procedimiento</button>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Procedimiento</th><th>Descripción</th><th class="text-end">Precio</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
      <tbody id="tbodyProc"><tr><td colspan="5" class="text-muted">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalProc" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formProc" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalProc">Nuevo procedimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="prId">
        <div class="mb-3"><label class="form-label">Nombre *</label><input class="form-control" name="nombre" id="prNombre" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" rows="3" name="descripcion" id="prDesc"></textarea></div>
        <div class="mb-3"><label class="form-label">Precio referencial (USD)</label>
          <div class="input-group"><span class="input-group-text">$</span><input type="number" step="0.01" min="0" class="form-control" name="precio" id="prPrecio" value="0"></div></div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="activo" id="prActivo" value="1" checked>
          <label class="form-check-label" for="prActivo">Disponible (aparece al registrar atenciones)</label>
        </div>
        <div id="errProc" class="alert alert-danger py-2 mt-3 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
