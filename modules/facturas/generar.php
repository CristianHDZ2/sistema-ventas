<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Fecha por defecto (hoy)
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
$min_factura = isset($_POST['min_factura']) ? floatval($_POST['min_factura']) : 5.00;
$max_factura = isset($_POST['max_factura']) ? floatval($_POST['max_factura']) : 60.00;

// Obtener ventas del día
$ventas_productos = [];
$errores = [];
$mensaje_exito = '';

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener ventas totales del día por producto
    $ventas_general = reporteVentasPorProducto($fecha, $fecha, 2);
    $ventas_big_cola = reporteVentasPorProducto($fecha, $fecha, 1);
    
    // Combinar todas las ventas
    $ventas_productos = array_merge($ventas_general, $ventas_big_cola);
    
    // Verificar si hay ventas para el día seleccionado
    if (empty($ventas_productos)) {
        $errores[] = "No hay ventas registradas para la fecha seleccionada.";
    } else {
        // Verificar si ya existen facturas para esa fecha
        $facturas_existentes = obtenerFacturasPorFecha($fecha);
        if (!empty($facturas_existentes)) {
            $errores[] = "Ya existen facturas generadas para esta fecha. Elimínelas primero si desea regenerarlas.";
        } else {
            // Generar facturas
            try {
                $resultado = generarFacturas($fecha, $ventas_productos, $min_factura, $max_factura);
                if ($resultado['exito']) {
                    $mensaje_exito = "Se han generado " . $resultado['total_facturas'] . " facturas correctamente.";
                    
                    // Redireccionar a la lista de facturas con un mensaje de éxito
                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => $mensaje_exito
                    ];
                    
                    header('Location: index.php?fecha=' . $fecha);
                    exit;
                } else {
                    $errores[] = "Error al generar las facturas: " . $resultado['mensaje'];
                }
            } catch (Exception $e) {
                $errores[] = "Error: " . $e->getMessage();
            }
        }
    }
}

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Generar Facturas</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success">
            <?php echo $mensaje_exito; ?>
        </div>
        <?php endif; ?>
        
        <form action="generar.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha de Ventas</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>" required>
                    <div class="form-text">Seleccione la fecha de las ventas para generar facturas.</div>
                </div>
                
                <div class="col-md-4">
                    <label for="min_factura" class="form-label">Monto Mínimo de Factura ($)</label>
                    <input type="number" class="form-control" id="min_factura" name="min_factura" step="0.01" min="1" value="<?php echo $min_factura; ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label for="max_factura" class="form-label">Monto Máximo de Factura ($)</label>
                    <input type="number" class="form-control" id="max_factura" name="max_factura" step="0.01" min="5" value="<?php echo $max_factura; ?>" required>
                </div>
            </div>
            
            <p class="alert alert-info">
                <i class="bi bi-info-circle"></i> Este proceso generará automáticamente facturas con montos aleatorios entre los valores mínimo y máximo especificados, distribuyendo los productos vendidos en el día seleccionado. El total de las facturas coincidirá con las ventas reales del día.
            </p>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-receipt"></i> Generar Facturas
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>