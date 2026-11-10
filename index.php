<?php
require 'includes/config.php';
if (!empty($_SESSION['uid'])) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Iniciar sesión · <?= e(CLINICA_NOMBRE) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">
  <div class="login-card card shadow-lg border-0">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="login-logo"><i class="bi bi-heart-pulse-fill"></i></div>
        <h1 class="h4 mt-3 mb-1"><?= e(CLINICA_NOMBRE) ?></h1>
        <p class="text-muted mb-0">Historia clínica odontológica</p>
      </div>
      <form id="formLogin" novalidate>
        <div class="mb-3">
          <label class="form-label" for="usuario">Usuario</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="usuario" name="usuario" required autofocus autocomplete="username">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="clave">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="clave" name="clave" required autocomplete="current-password">
          </div>
        </div>
        <div id="loginError" class="alert alert-danger py-2 d-none"></div>
        <button class="btn btn-primary w-100 py-2" type="submit" id="btnLogin">Ingresar</button>
      </form>
    </div>
  </div>

<script>
document.getElementById('formLogin').addEventListener('submit', async (ev) => {
  ev.preventDefault();
  const err = document.getElementById('loginError');
  const btn = document.getElementById('btnLogin');
  err.classList.add('d-none');
  btn.disabled = true; btn.textContent = 'Verificando...';
  try {
    const r = await fetch('api/auth.php', { method: 'POST', body: new FormData(ev.target) });
    const j = await r.json();
    if (j.ok) { window.location = 'dashboard.php'; return; }
    err.textContent = j.msg || 'No se pudo iniciar sesión.';
    err.classList.remove('d-none');
  } catch (e) {
    err.textContent = 'Error de conexión con el servidor.';
    err.classList.remove('d-none');
  }
  btn.disabled = false; btn.textContent = 'Ingresar';
});
</script>
</body>
</html>
