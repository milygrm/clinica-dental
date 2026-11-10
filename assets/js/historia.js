const modalAt = new bootstrap.Modal('#modalAt');
const formAt = document.getElementById('formAt');
let procedimientos = [];

async function cargarProcedimientos() {
  const j = await api('api/procedimientos.php?accion=listar&activos=1');
  procedimientos = j.ok ? j.data : [];
  document.getElementById('aProc').innerHTML =
    '<option value="">Seleccione...</option>' +
    procedimientos.map(p => `<option value="${p.id}" data-precio="${p.precio}">${esc(p.nombre)}</option>`).join('');
}

async function cargarAtenciones() {
  const j = await api('api/atenciones.php?accion=listar&paciente_id=' + PACIENTE_ID);
  const tb = document.getElementById('tbodyAt');
  if (!j.ok) { tb.innerHTML = `<tr><td colspan="6" class="text-danger">${esc(j.msg)}</td></tr>`; return; }
  if (!j.data.length) {
    tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Este paciente aún no tiene atenciones registradas.</td></tr>';
    document.getElementById('totalCosto').textContent = money(0);
    return;
  }
  let total = 0;
  tb.innerHTML = j.data.map(a => {
    total += +a.costo;
    const detalle = [a.diagnostico, a.observaciones].filter(Boolean).map(esc).join('<br><span class="text-muted">') + (a.diagnostico && a.observaciones ? '</span>' : '');
    return `
    <tr>
      <td class="text-nowrap">${fechaES(a.fecha)}</td>
      <td class="fw-semibold">${esc(a.procedimiento)}</td>
      <td>${esc(a.pieza_dental || '—')}</td>
      <td class="small">${detalle || '—'}</td>
      <td class="text-end">${money(a.costo)}</td>
      <td class="text-end text-nowrap">
        <button class="btn btn-sm btn-outline-secondary" onclick="editarAt(${a.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick="eliminarAt(${a.id})"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`;
  }).join('');
  document.getElementById('totalCosto').textContent = money(total);
}

document.getElementById('btnNuevaAt').addEventListener('click', () => {
  formAt.reset();
  document.getElementById('aId').value = '';
  document.getElementById('aFecha').value = new Date().toISOString().slice(0, 10);
  document.getElementById('tituloModalAt').textContent = 'Registrar atención';
  ocultarError('errAt');
  modalAt.show();
});

// Al elegir procedimiento se sugiere su precio
document.getElementById('aProc').addEventListener('change', (ev) => {
  const opt = ev.target.selectedOptions[0];
  if (opt && opt.dataset.precio !== undefined && !document.getElementById('aId').value) {
    document.getElementById('aCosto').value = opt.dataset.precio;
  }
});

async function editarAt(id) {
  const j = await api('api/atenciones.php?accion=obtener&id=' + id);
  if (!j.ok) { toast(j.msg, false); return; }
  const a = j.data;
  // si el procedimiento está inactivo, agregarlo temporalmente al selector
  const sel = document.getElementById('aProc');
  if (![...sel.options].some(o => o.value == a.procedimiento_id)) {
    const pj = await api('api/procedimientos.php?accion=obtener&id=' + a.procedimiento_id);
    if (pj.ok) sel.add(new Option(pj.data.nombre + ' (inactivo)', pj.data.id));
  }
  formAt.reset();
  document.getElementById('aId').value = a.id;
  sel.value = a.procedimiento_id;
  document.getElementById('aFecha').value = a.fecha;
  document.getElementById('aPieza').value = a.pieza_dental || '';
  document.getElementById('aDiag').value = a.diagnostico || '';
  document.getElementById('aObs').value = a.observaciones || '';
  document.getElementById('aCosto').value = a.costo;
  document.getElementById('tituloModalAt').textContent = 'Editar atención';
  ocultarError('errAt');
  modalAt.show();
}

async function eliminarAt(id) {
  if (!confirm('¿Eliminar esta atención de la historia clínica?')) return;
  const j = await api('api/atenciones.php', { method: 'POST', data: { accion: 'eliminar', id } });
  toast(j.msg, j.ok);
  if (j.ok) cargarAtenciones();
}

formAt.addEventListener('submit', async (ev) => {
  ev.preventDefault();
  ocultarError('errAt');
  const fd = new FormData(formAt);
  fd.append('accion', 'guardar');
  const j = await api('api/atenciones.php', { method: 'POST', data: fd });
  if (!j.ok) { mostrarError('errAt', j.msg); return; }
  modalAt.hide();
  toast(j.msg);
  cargarAtenciones();
});

cargarProcedimientos();
cargarAtenciones();
