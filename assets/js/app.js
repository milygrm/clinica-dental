// Utilidades comunes (AJAX con fetch)
function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function money(n) {
  return '$' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fechaES(iso) {
  if (!iso) return '';
  const [y, m, d] = iso.split('-');
  return `${d}/${m}/${y}`;
}

function toast(msg, ok = true) {
  const el = document.getElementById('toastMsg');
  el.classList.remove('text-bg-success', 'text-bg-danger');
  el.classList.add(ok ? 'text-bg-success' : 'text-bg-danger');
  document.getElementById('toastTexto').textContent = msg;
  bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 }).show();
}

// Llamada AJAX genérica: devuelve el JSON; redirige al login si la sesión expiró.
async function api(url, { method = 'GET', data = null } = {}) {
  const opts = { method };
  if (data) opts.body = data instanceof FormData ? data : new URLSearchParams(data);
  const r = await fetch(url, opts);
  if (r.status === 401) { window.location = 'index.php'; throw new Error('Sesión expirada'); }
  return r.json();
}

function mostrarError(id, msg) {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.classList.remove('d-none');
}
function ocultarError(id) { document.getElementById(id).classList.add('d-none'); }
