<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Filtros de fecha (por defecto, muestra ventas del día actual)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Obtener ventas
$ventas = obtenerVentasPorFecha($fecha_inicio, $fecha_fin);

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Lista de Ventas</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="registrar.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Registrar Nueva Venta
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
                <form action="listar.php" method="GET" class="d-flex">
                    <div class="input-group">
                        <span class="input-group-text">Desde</span>
                        <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                        <span class="input-group-text">Hasta</span>
                        <input type="date" class="form-control" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla de ventas -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Ruta</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventas)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay ventas registradas en el período seleccionado</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
    <td><?php echo $venta['id']; ?></td>
    <td><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></td>
    <td>
        <?php echo htmlspecialchars($venta['ruta_nombre']); ?>
        <?php if ($venta['es_big_cola'] == 1): ?>
            <span class="badge bg-primary">Big Cola</span>
        <?php endif; ?>
    </td>
    <td>$<?php echo number_format($venta['total'], 2); ?></td>
    <td>
        <a href="ver.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-info">
            <i class="bi bi-eye"></i> Ver
        </a>
        <a href="editar.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="eliminar.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-danger confirmar-eliminar">
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