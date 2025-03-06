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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo htmlspecialchars($factura['numero_factura']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .factura-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .factura-info h2 {
            margin: 0;
            font-size: 24px;
        }
        .factura-numero {
            text-align: right;
        }
        .factura-numero h4 {
            margin: 0;
            font-size: 16px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print();" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Imprimir Factura
            </button>
            <a href="index.php" style="display: inline-block; padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; text-decoration: none; margin-left: 10px;">
                Volver a la lista
            </a>
        </div>
        
        <div class="factura-header">
            <div class="factura-info">
                <h2>FACTURA</h2>
                <p>Big Cola Distribuidora</p>
                <p>Dirección: Av. Principal #123</p>
                <p>Teléfono: 123-456-7890</p>
            </div>
            <div class="factura-numero">
                <h4>N° <?php echo htmlspecialchars($factura['numero_factura']); ?></h4>
                <p>Fecha: <?php echo date('d/m/Y', strtotime($factura['fecha'])); ?></p>
            </div>
        </div>
        
        <hr>
        
        <h3>Detalle de Productos</h3>
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
        
        <div class="footer">
            <p>¡Gracias por su compra!</p>
        </div>
    </div>
    
    <script>
        // Auto imprimir al cargar la página
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>