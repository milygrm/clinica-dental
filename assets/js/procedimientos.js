const modalProc = new bootstrap.Modal('#modalProc');
const formProc = document.getElementById('formProc');

async function cargarProc() {
  const j = await api('api/procedimientos.php?accion=listar');
  const tb = document.getElementById('tbodyProc');
  if (!j.ok) { tb.innerHTML = `<tr><td colspan="5" class="text-danger">${esc(j.msg)}</td></tr>`; return; }
  if (!j.data.length) { tb.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay procedimientos registrados.</td></tr>'; return; }
  tb.innerHTML = j.data.map(p => `
    <tr>
      <td class="fw-semibold">${esc(p.nombre)}</td>
      <td class="text-muted small">${esc(p.descripcion || '')}</td>
      <td class="text-end">${money(p.precio)}</td>
      <td>${+p.activo ? '<span class="badge text-bg-success">Disponible</span>' : '<span class="badge text-bg-secondary">Inactivo</span>'}</td>
      <td class="text-end text-nowrap">
        <button class="btn btn-sm btn-outline-secondary" onclick="editarProc(${p.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick="eliminarProc(${p.id}, '${esc(p.nombre).replace(/'/g, "\\'")}')"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
}

document.getElementById('btnNuevoProc').addEventListener('click', () => {
  formProc.reset();
  document.getElementById('prId').value = '';
  document.getElementById('prActivo').checked = true;
  document.getElementById('tituloModalProc').textContent = 'Nuevo procedimiento';
  ocultarError('errProc');
  modalProc.show();
});

async function editarProc(id) {
  const j = await api('api/procedimientos.php?accion=obtener&id=' + id);
  if (!j.ok) { toast(j.msg, false); return; }
  const p = j.data;
  formProc.reset();
  document.getElementById('prId').value = p.id;
  document.getElementById('prNombre').value = p.nombre;
  document.getElementById('prDesc').value = p.descripcion || '';
  document.getElementById('prPrecio').value = p.precio;
  document.getElementById('prActivo').checked = +p.activo === 1;
  document.getElementById('tituloModalProc').textContent = 'Editar procedimiento';
  ocultarError('errProc');
  modalProc.show();
}

async function eliminarProc(id, nombre) {
  if (!confirm(`¿Eliminar el procedimiento "${nombre}"?`)) return;
  const j = await api('api/procedimientos.php', { method: 'POST', data: { accion: 'eliminar', id } });
  toast(j.msg, j.ok);
  if (j.ok) cargarProc();
}

formProc.addEventListener('submit', async (ev) => {
  ev.preventDefault();
  ocultarError('errProc');
  const fd = new FormData(formProc);
  fd.append('accion', 'guardar');
  const j = await api('api/procedimientos.php', { method: 'POST', data: fd });
  if (!j.ok) { mostrarError('errProc', j.msg); return; }
  modalProc.hide();
  toast(j.msg);
  cargarProc();
});

cargarProc();
