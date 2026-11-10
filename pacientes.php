<?php
require 'includes/config.php';
$titulo = 'Pacientes'; $activo = 'pacientes'; $scripts = ['pacientes.js'];
include 'includes/layout.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><i class="bi bi-people"></i> Pacientes</h1>
  <button class="btn btn-primary mt-2 mt-md-0" id="btnNuevo"><i class="bi bi-person-plus"></i> Nuevo paciente</button>
</div>

<div class="card">
  <div class="card-body">
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="search" id="buscar" class="form-control" placeholder="Buscar por cédula, nombre o apellido...">
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Cédula</th><th>Paciente</th><th>Edad</th><th>Teléfono</th><th class="text-center">Atenciones</th><th class="text-end">Acciones</th></tr></thead>
        <tbody id="tbodyPacientes"><tr><td colspan="6" class="text-muted">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="formPaciente" novalidate>
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalPaciente">Nuevo paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="pId">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Cédula *</label><input class="form-control" name="cedula" id="pCedula" required maxlength="20"></div>
          <div class="col-md-4"><label class="form-label">Nombres *</label><input class="form-control" name="nombres" id="pNombres" required maxlength="80"></div>
          <div class="col-md-4"><label class="form-label">Apellidos *</label><input class="form-control" name="apellidos" id="pApellidos" required maxlength="80"></div>
          <div class="col-md-4"><label class="form-label">Fecha de nacimiento *</label><input type="date" class="form-control" name="fecha_nacimiento" id="pFecha" required max="<?= date('Y-m-d') ?>"></div>
          <div class="col-md-4"><label class="form-label">Sexo</label>
            <select class="form-select" name="sexo" id="pSexo"><option value="M">Masculino</option><option value="F">Femenino</option><option value="O">Otro</option></select></div>
          <div class="col-md-4"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" id="pTelefono" maxlength="30"></div>
          <div class="col-md-6"><label class="form-label">Correo electrónico</label><input type="email" class="form-control" name="email" id="pEmail" maxlength="120"></div>
          <div class="col-md-6"><label class="form-label">Dirección</label><input class="form-control" name="direccion" id="pDireccion" maxlength="255"></div>
          <div class="col-md-6"><label class="form-label">Alergias</label><textarea class="form-control" rows="2" name="alergias" id="pAlergias" placeholder="Ej.: penicilina, látex, anestesia..."></textarea></div>
          <div class="col-md-6"><label class="form-label">Antecedentes médicos</label><textarea class="form-control" rows="2" name="antecedentes" id="pAntecedentes" placeholder="Ej.: diabetes, hipertensión, embarazo..."></textarea></div>
        </div>
        <div id="errPaciente" class="alert alert-danger py-2 mt-3 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
