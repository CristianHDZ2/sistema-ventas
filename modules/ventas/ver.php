<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'ID de venta no válido'
    ];
    header('Location: listar.php');
    exit;
}

$id = intval($_GET['id']);

// Obtener información de la venta
$venta = obtenerVentaPorId($id);

if (!$venta) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Venta no encontrada'
    ];
    header('Location: listar.php');
    exit;
}

// Obtener detalles de la venta
$detalles = obtenerDetallesVenta($id);

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Detalles de Venta #<?php echo $id; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="listar.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a la lista
        </a>
        <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-warning ms-2">
            <i class="bi bi-pencil"></i> Editar
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
                <h5>Información de la Venta</h5>
                <table class="table table-borderless">
                    <tr>
                        <th width="120">Fecha:</th>
                        <td><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></td>
                    </tr>
                    <tr>
                        <th>Ruta:</th>
                        <td>
                            <?php echo htmlspecialchars($venta['ruta_nombre']); ?>
                            <?php if (isset($venta['es_big_cola']) && $venta['es_big_cola'] == 1): ?>
                                <span class="badge bg-primary">Big Cola</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Total:</th>
                        <td class="fw-bold">$<?php echo number_format($venta['total'], 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <h5 class="mt-4 mb-3">Productos</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th class="text-end">Precio Unitario</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($detalles)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay detalles disponibles</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($detalles as $detalle): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                            <td class="text-end">$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                            <td class="text-end"><?php echo number_format($detalle['cantidad']); ?></td>
                            <td class="text-end">$<?php echo number_format($detalle['subtotal'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-dark fw-bold">
                            <td colspan="3" class="text-end">TOTAL</td>
                            <td class="text-end">$<?php echo number_format($venta['total'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Estilos para impresión -->
<style>
@media print {
    .navbar, footer, .btn {
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
}
</style>

<?php
// Incluir footer
include '../../includes/footer.php';
?>