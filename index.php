<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once 'includes/functions.php';

// Obtener fecha actual para mostrar en el dashboard
$fecha_actual = date('Y-m-d');
$primer_dia_mes = date('Y-m-01');
$ultimo_dia_mes = date('Y-m-t');

// Obtener estadísticas para el dashboard
try {
    // Ventas del día
    $ventas_dia = obtenerVentasPorFecha($fecha_actual, $fecha_actual);
    $total_ventas_dia = 0;
    foreach ($ventas_dia as $venta) {
        $total_ventas_dia += $venta['total'];
    }
    
    // Ventas del mes
    $ventas_mes = obtenerVentasPorFecha($primer_dia_mes, $ultimo_dia_mes);
    $total_ventas_mes = 0;
    foreach ($ventas_mes as $venta) {
        $total_ventas_mes += $venta['total'];
    }
    
    // Productos más vendidos del mes (top 5)
    $productos_mes = reporteVentasPorProducto($primer_dia_mes, $ultimo_dia_mes);
    usort($productos_mes, function($a, $b) {
        return $b['total_vendido'] - $a['total_vendido'];
    });
    $productos_top = array_slice($productos_mes, 0, 5);
    
    // Ventas por ruta
    $ventas_por_ruta = reporteVentasPorRuta($primer_dia_mes, $ultimo_dia_mes);
    
} catch (PDOException $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ];
}

// Incluir header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-4">Dashboard</h1>
    </div>
</div>

<!-- Tarjetas de resumen -->
<div class="row mb-5">
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="icon">
                    <i class="bi bi-calendar-day"></i>
                </div>
                <h5 class="card-title">Ventas Hoy</h5>
                <h3 class="text-primary">$<?php echo number_format($total_ventas_dia, 2); ?></h3>
                <p class="card-text"><?php echo count($ventas_dia); ?> transacciones</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="icon">
                    <i class="bi bi-calendar-month"></i>
                </div>
                <h5 class="card-title">Ventas Mes</h5>
                <h3 class="text-primary">$<?php echo number_format($total_ventas_mes, 2); ?></h3>
                <p class="card-text"><?php echo count($ventas_mes); ?> transacciones</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h5 class="card-title">Productos</h5>
                <h3 class="text-primary"><?php 
                    $productos = obtenerProductos();
                    echo count($productos); 
                ?></h3>
                <p class="card-text">
                    <?php 
                        $productos_big_cola = obtenerProductos(1);
                        echo count($productos_big_cola); 
                    ?> Big Cola
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="icon">
                    <i class="bi bi-signpost-2"></i>
                </div>
                <h5 class="card-title">Rutas</h5>
                <h3 class="text-primary"><?php 
                    $rutas = obtenerRutas(true);
                    echo count($rutas); 
                ?></h3>
                <p class="card-text">
                    <?php 
                        $rutas_big_cola = obtenerRutas(true, true);
                        echo count($rutas_big_cola); 
                    ?> Big Cola
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row mb-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="modules/ventas/registrar.php" class="btn btn-outline-primary btn-lg d-block">
                            <i class="bi bi-plus-circle"></i> Registrar Venta
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="modules/ventas/reporte-diario.php" class="btn btn-outline-primary btn-lg d-block">
                            <i class="bi bi-bar-chart"></i> Reporte Diario
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="modules/ventas/reporte-mensual.php" class="btn btn-outline-primary btn-lg d-block">
                            <i class="bi bi-graph-up"></i> Reporte Mensual
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="modules/productos/agregar.php" class="btn btn-outline-primary btn-lg d-block">
                            <i class="bi bi-box2"></i> Nuevo Producto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Productos más vendidos y Ventas por ruta -->
<div class="row">
    <!-- Productos más vendidos -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Productos más vendidos (mes actual)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos_top)): ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay ventas registradas este mes</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($productos_top as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td class="text-end"><?php echo number_format($producto['total_vendido']); ?></td>
                                    <td class="text-end">$<?php echo number_format($producto['total_ventas'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ventas por ruta -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Ventas por ruta (mes actual)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ruta</th>
                                <th class="text-end">Ventas</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ventas_por_ruta)): ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay ventas registradas este mes</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($ventas_por_ruta as $ruta): ?>
                                <tr <?php echo ($ruta['es_big_cola'] == 1) ? 'class="table-info"' : ''; ?>>
                                    <td>
                                        <?php echo htmlspecialchars($ruta['nombre']); ?>
                                        <?php if ($ruta['es_big_cola'] == 1): ?>
                                            <span class="badge bg-info">Big Cola</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($ruta['total_ventas']); ?></td>
                                    <td class="text-end">$<?php echo number_format($ruta['total_monto'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include 'includes/footer.php';
?>