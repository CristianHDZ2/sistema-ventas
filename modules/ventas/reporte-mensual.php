<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Mes y año por defecto (mes actual)
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : intval(date('Y'));

// Calcular fechas de inicio y fin del mes
$fecha_inicio = sprintf("%04d-%02d-01", $ano, $mes);
$fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

// Obtener ventas del mes
$ventas_general = reporteVentasPorProducto($fecha_inicio, $fecha_fin, 2); // Productos de categoría "Otros" (ID 2)
$ventas_big_cola = reporteVentasPorProducto($fecha_inicio, $fecha_fin, 1); // Productos de categoría "Big Cola" (ID 1)

// Obtener ventas por ruta
$ventas_por_ruta = reporteVentasPorRuta($fecha_inicio, $fecha_fin);

// Cálculo de totales
$total_general = 0;
$total_big_cola = 0;

foreach ($ventas_general as $venta) {
    $total_general += $venta['total_ventas'];
}

foreach ($ventas_big_cola as $venta) {
    $total_big_cola += $venta['total_ventas'];
}

// Nombres de los meses para mostrar
$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Reporte Mensual de Ventas</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="../../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al inicio
        </a>
        <button onclick="window.print()" class="btn btn-primary ms-2">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <form action="reporte-mensual.php" method="GET" class="d-flex">
                    <div class="input-group">
                        <span class="input-group-text">Mes</span>
                        <select class="form-select" name="mes">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($i == $mes) ? 'selected' : ''; ?>>
                                    <?php echo $nombres_meses[$i]; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <span class="input-group-text">Año</span>
                        <select class="form-select" name="ano">
                            <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($i == $ano) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <h5>Periodo: <?php echo $nombres_meses[$mes] . ' ' . $ano; ?></h5>
                <p class="mb-0 text-muted">Del <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> al <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></p>
            </div>
        </div>
        
        <div class="reporte-header">
            <h2 class="mb-0">Resumen de Ventas del Mes</h2>
        </div>
        
        <!-- Resumen por ruta -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h4>Ventas por Ruta</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Ruta</th>
                                <th class="text-end">Total Ventas</th>
                                <th class="text-end">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ventas_por_ruta) || array_sum(array_column($ventas_por_ruta, 'total_ventas')) == 0): ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay ventas registradas para este mes</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($ventas_por_ruta as $ruta): ?>
                                <?php if ($ruta['total_ventas'] > 0): ?>
                                <tr <?php echo ($ruta['es_big_cola'] == 1) ? 'class="table-info"' : ''; ?>>
                                    <td>
                                        <?php echo htmlspecialchars($ruta['nombre']); ?>
                                        <?php if ($ruta['es_big_cola'] == 1): ?>
                                            <span class="badge bg-primary">Big Cola</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($ruta['total_ventas']); ?></td>
                                    <td class="text-end">$<?php echo number_format($ruta['total_monto'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <tr class="table-dark fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-end"><?php echo number_format(array_sum(array_column($ventas_por_ruta, 'total_ventas'))); ?></td>
                                    <td class="text-end">$<?php echo number_format(array_sum(array_column($ventas_por_ruta, 'total_monto')), 2); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Productos de Big Cola -->
        <div class="big-cola-section">
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4>Productos Big Cola</h4>
                </div>
                <div class="col-md-4 text-end">
                    <h5>Total: $<?php echo number_format($total_big_cola, 2); ?></h5>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-detalles">
                    <thead class="table-primary">
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Precio Unit.</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventas_big_cola)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay ventas de productos Big Cola para este mes</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($ventas_big_cola as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td class="text-end">$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($producto['total_vendido']); ?></td>
                                <td class="text-end">$<?php echo number_format($producto['total_ventas'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary fw-bold">
                                <td colspan="2">TOTAL</td>
                                <td class="text-end"><?php echo number_format(array_sum(array_column($ventas_big_cola, 'total_vendido'))); ?></td>
                                <td class="text-end">$<?php echo number_format($total_big_cola, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Productos Generales -->
        <div class="general-section mt-4">
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4>Productos Generales</h4>
                </div>
                <div class="col-md-4 text-end">
                    <h5>Total: $<?php echo number_format($total_general, 2); ?></h5>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-detalles">
                    <thead class="table-secondary">
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Precio Unit.</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventas_general)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay ventas de productos generales para este mes</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($ventas_general as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td class="text-end">$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($producto['total_vendido']); ?></td>
                                <td class="text-end">$<?php echo number_format($producto['total_ventas'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-secondary fw-bold">
                                <td colspan="2">TOTAL</td>
                                <td class="text-end"><?php echo number_format(array_sum(array_column($ventas_general, 'total_vendido'))); ?></td>
                                <td class="text-end">$<?php echo number_format($total_general, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Total General -->
        <div class="row mt-4">
            <div class="col-md-8 offset-md-4">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">TOTAL VENTAS DEL MES:</h3>
                            <h3 class="mb-0">$<?php echo number_format($total_general + $total_big_cola, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos para impresión -->
<style>
@media print {
    .navbar, footer, .btn, form {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-body {
        padding: 0 !important;
    }
    
    h1 {
        font-size: 1.5rem !important;
        text-align: center !important;
        margin-bottom: 20px !important;
    }
    
    .container {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    body {
        font-size: 12px !important;
    }
    
    .table {
        font-size: 11px !important;
    }
    
    .big-cola-section, .general-section {
        padding: 10px !important;
        margin-bottom: 15px !important;
    }
    
    h4 {
        font-size: 14px !important;
    }
    
    h5 {
        font-size: 12px !important;
    }
}
</style>

<?php
// Incluir footer
include '../../includes/footer.php';
?>