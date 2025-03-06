<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Fecha por defecto (hoy)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener facturas generadas para esta fecha
$facturas = obtenerFacturasPorFecha($fecha);

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Facturas</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="generar.php" class="btn btn-primary">
            <i class="bi bi-receipt"></i> Generar Facturas
        </a>
        <a href="../../index.php" class="btn btn-secondary ms-2">
            <i class="bi bi-arrow-left"></i> Volver al inicio
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-12">
                <form action="index.php" method="GET" class="d-flex">
                    <div class="input-group">
                        <span class="input-group-text">Fecha</span>
                        <input type="date" class="form-control" name="fecha" value="<?php echo $fecha; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla de facturas -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Número de Factura</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($facturas)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay facturas generadas para esta fecha</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($facturas as $factura): ?>
                        <tr>
                            <td><?php echo $factura['id']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($factura['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($factura['numero_factura']); ?></td>
                            <td>$<?php echo number_format($factura['total'], 2); ?></td>
                            <td>
                                <a href="ver.php?id=<?php echo $factura['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <a href="imprimir.php?id=<?php echo $factura['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-printer"></i> Imprimir
                                </a>
                                <a href="eliminar.php?id=<?php echo $factura['id']; ?>" class="btn btn-sm btn-danger confirmar-eliminar">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>