(async function () {
  const j = await api('api/dashboard.php');
  if (!j.ok) { toast(j.msg, false); return; }
  const d = j.data;

  document.getElementById('sPacientes').textContent = d.pacientes;
  document.getElementById('sAtenciones').textContent = d.atenciones;
  document.getElementById('sMes').textContent = d.atenciones_mes;
  document.getElementById('sIngresos').textContent = money(d.ingresos_mes);

  const max = Math.max(1, ...d.por_procedimiento.map(x => +x.total));
  document.getElementById('porProc').innerHTML = d.por_procedimiento.map(x => `
    <div class="mb-3">
      <div class="d-flex justify-content-between small"><span>${esc(x.nombre)}</span><strong>${x.total}</strong></div>
      <div class="progress" style="height:8px"><div class="progress-bar" style="width:${(x.total / max) * 100}%"></div></div>
    </div>`).join('') || '<div class="text-muted">Sin datos todavía.</div>';

  document.getElementById('recientes').innerHTML = d.recientes.map(r => `
    <tr>
      <td>${fechaES(r.fecha)}</td>
      <td><a href="historia.php?id=${r.paciente_id}">${esc(r.paciente)}</a></td>
      <td>${esc(r.procedimiento)}</td>
      <td class="text-end">${money(r.costo)}</td>
    </tr>`).join('') || '<tr><td colspan="4" class="text-muted">Aún no hay atenciones registradas.</td></tr>';
})();
