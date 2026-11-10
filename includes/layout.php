<?php
// Plantilla común: menú lateral + barra superior. Uso:
//   $titulo = 'Pacientes'; $activo = 'pacientes';
//   include 'includes/layout.php';  ... contenido ...  include 'includes/footer.php';
requiere_login();
$activo = $activo ?? '';
$menu = [
    'dashboard'      => ['dashboard.php',      'bi-speedometer2',  'Panel principal'],
    'pacientes'      => ['pacientes.php',      'bi-people',        'Pacientes'],
    'procedimientos' => ['procedimientos.php', 'bi-clipboard2-pulse', 'Procedimientos'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Historia Clínica') ?> · <?= e(CLINICA_NOMBRE) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark topbar d-lg-none">
  <div class="container-fluid">
    <span class="navbar-brand"><i class="bi bi-heart-pulse"></i> <?= e(CLINICA_NOMBRE) ?></span>
    <button class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"><span class="navbar-toggler-icon"></span></button>
  </div>
</nav>

<div class="d-flex">
  <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="menuLateral">
    <div class="sidebar-inner">
      <div class="brand"><i class="bi bi-heart-pulse-fill"></i><span><?= e(CLINICA_NOMBRE) ?></span></div>
      <ul class="nav flex-column mt-3">
        <?php foreach ($menu as $k => [$url, $icono, $texto]): ?>
          <li class="nav-item">
            <a class="nav-link <?= $k === $activo ? 'active' : '' ?>" href="<?= $url ?>"><i class="bi <?= $icono ?>"></i> <?= $texto ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="user-box">
        <div class="small text-white-50">Sesión iniciada como</div>
        <div class="fw-semibold"><?= e($_SESSION['nombre']) ?></div>
        <a href="logout.php" class="btn btn-sm btn-outline-light mt-2 w-100"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
      </div>
    </div>
  </aside>

  <main class="content flex-grow-1 p-3 p-lg-4">
