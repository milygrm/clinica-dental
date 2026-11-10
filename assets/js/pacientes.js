const modalPac = new bootstrap.Modal('#modalPaciente');
const formPac = document.getElementById('formPaciente');
let temporizador;

async function cargarPacientes() {
  const q = document.getElementById('buscar').value.trim();
  const j = await api('api/pacientes.php?accion=listar&q=' + encodeURIComponent(q));
  const tb = document.getElementById('tbodyPacientes');
  if (!j.ok) { tb.innerHTML = `<tr><td colspan="6" class="text-danger">${esc(j.msg)}</td></tr>`; return; }
  if (!j.data.length) {
    tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay pacientes para mostrar.</td></tr>';
    return;
  }
  tb.innerHTML = j.data.map(p => `
    <tr>
      <td>${esc(p.cedula)}</td>
      <td class="fw-semibold">${esc(p.apellidos)}, ${esc(p.nombres)}</td>
      <td>${p.edad}</td>
      <td>${esc(p.telefono || '—')}</td>
      <td class="text-center"><span class="badge text-bg-secondary">${p.atenciones}</span></td>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-primary" href="historia.php?id=${p.id}" title="Ver historia clínica"><i class="bi bi-journal-medical"></i> Historia</a>
        <button class="btn btn-sm btn-outline-secondary" onclick="editarPaciente(${p.id})" title="Editar"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick="eliminarPaciente(${p.id}, '${esc(p.nombres + ' ' + p.apellidos).replace(/'/g, "\\'")}')" title="Eliminar"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
}

function nuevoPaciente() {
  formPac.reset();
  document.getElementById('pId').value = '';
  document.getElementById('tituloModalPaciente').textContent = 'Nuevo paciente';
  ocultarError('errPaciente');
  modalPac.show();
}

async function editarPaciente(id) {
  const j = await api('api/pacientes.php?accion=obtener&id=' + id);
  if (!j.ok) { toast(j.msg, false); return; }
  const p = j.data;
  formPac.reset();
  document.getElementById('pId').value = p.id;
  document.getElementById('pCedula').value = p.cedula;
  document.getElementById('pNombres').value = p.nombres;
  document.getElementById('pApellidos').value = p.apellidos;
  document.getElementById('pFecha').value = p.fecha_nacimiento;
  document.getElementById('pSexo').value = p.sexo;
  document.getElementById('pTelefono').value = p.telefono || '';
  document.getElementById('pEmail').value = p.email || '';
  document.getElementById('pDireccion').value = p.direccion || '';
  document.getElementById('pAlergias').value = p.alergias || '';
  document.getElementById('pAntecedentes').value = p.antecedentes || '';
  document.getElementById('tituloModalPaciente').textContent = 'Editar paciente';
  ocultarError('errPaciente');
  modalPac.show();
}

async function eliminarPaciente(id, nombre) {
  if (!confirm(`¿Eliminar a ${nombre}?\nTambién se borrará toda su historia clínica.`)) return;
  const j = await api('api/pacientes.php', { method: 'POST', data: { accion: 'eliminar', id } });
  toast(j.msg, j.ok);
  if (j.ok) cargarPacientes();
}

formPac.addEventListener('submit', async (ev) => {
  ev.preventDefault();
  ocultarError('errPaciente');
  const fd = new FormData(formPac);
  fd.append('accion', 'guardar');
  const j = await api('api/pacientes.php', { method: 'POST', data: fd });
  if (!j.ok) { mostrarError('errPaciente', j.msg); return; }
  modalPac.hide();
  toast(j.msg);
  cargarPacientes();
});

document.getElementById('btnNuevo').addEventListener('click', nuevoPaciente);
document.getElementById('buscar').addEventListener('input', () => {
  clearTimeout(temporizador);
  temporizador = setTimeout(cargarPacientes, 250);
});

cargarPacientes();
if (new URLSearchParams(location.search).get('nuevo')) nuevoPaciente();
