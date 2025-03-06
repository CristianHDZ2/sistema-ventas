<?php
  // Ruta base para los enlaces
  $base_url = '/sistema-ventas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Control de Ventas</title>
  <!-- Bootstrap CSS -->
  <link href="<?php echo $base_url; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?php echo $base_url; ?>/assets/css/styles.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="<?php echo $base_url; ?>/assets/css/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="<?php echo $base_url; ?>/">Sistema de Ventas</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>/"><i class="bi bi-house-fill"></i> Inicio</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownVentas" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-cart-fill"></i> Ventas
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdownVentas">
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/ventas/registrar.php">Registrar Venta</a></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/ventas/listar.php">Lista de Ventas</a></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/ventas/reporte-diario.php">Reporte Diario</a></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/ventas/reporte-mensual.php">Reporte Mensual</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownFacturas" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-receipt"></i> Facturas
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdownFacturas">
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/facturas/index.php">Lista de Facturas</a></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/facturas/generar.php">Generar Facturas</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownProductos" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-box-seam"></i> Productos
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdownProductos">
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/productos/index.php">Lista de Productos</a></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/productos/agregar.php">Agregar Producto</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownRutas" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-signpost-2"></i> Rutas
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdownRutas">
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/rutas/index.php">Lista de Rutas</a></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/rutas/agregar.php">Agregar Ruta</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Contenedor principal -->
  <div class="container mt-4">
    <!-- Sistema de alertas -->
    <?php if (isset($_SESSION['alerta'])): ?>
    <div class="alert alert-<?php echo $_SESSION['alerta']['tipo']; ?> alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['alerta']['mensaje']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['alerta']); ?>
    <?php endif; ?>