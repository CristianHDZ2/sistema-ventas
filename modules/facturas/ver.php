<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'ID de factura no válido'
    ];
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Obtener información de la factura
$factura = obtenerFacturaPorId($id);

if (!$factura) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Factura no encontrada'
    ];
    header('Location: index.php');
    exit;
}

// Obtener detalles de la factura
$detalles = obtenerDetallesFactura($id);

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Factura #<?php echo htmlspecialchars($factura['numero_factura']); ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a la lista
        </a>
        <a href="imprimir.php?id=<?php echo $id; ?>" class="btn btn-primary ms-2">
            <i class="bi bi-printer"></i> Imprimir
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="factura-container p-4 border mb-4">
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2 class="mb-0">FACTURA</h2>
                    <p class="mb-0 text-muted">Big Cola Distribuidora</p>
                    <p class="mb-0 text-muted">Dirección: Av. Principal #123</p>
                    <p class="mb-0 text-muted">Teléfono: 123-456-7890</p>
                </div>
                <div class="col-md-4 text-end">
                    <h4 class="mb-0">N° <?php echo htmlspecialchars($factura['numero_factura']); ?></h4>
                    <p class="mb-0 text-muted">Fecha: <?php echo date('d/m/Y', strtotime($factura['fecha'])); ?></p>
                </div>
            </div>
            
            <hr>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Detalle de Productos</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unitario</th>
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
                                        <td class="text-center"><?php echo $detalle['cantidad']; ?></td>
                                        <td class="text-end">$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                        <td class="text-end">$<?php echo number_format($detalle['subtotal'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">TOTAL:</th>
                                    <th class="text-end">$<?php echo number_format($factura['total'], 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <p class="mb-0 text-center">¡Gracias por su compra!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>