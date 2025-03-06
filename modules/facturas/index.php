<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Fecha por defecto (hoy)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener facturas generadas para esta fecha
$facturas = obtenerFacturasPorFecha($fecha);

// Calcular el total de las facturas
$total_facturas = 0;
foreach ($facturas as $factura) {
    $total_facturas += $factura['total'];
}

// Obtener el total de ventas del día para comparar
$ventas_general = reporteVentasPorProducto($fecha, $fecha, 2);
$ventas_big_cola = reporteVentasPorProducto($fecha, $fecha, 1);

$total_ventas_dia = 0;
foreach ($ventas_general as $venta) {
    $total_ventas_dia += $venta['total_ventas'];
}
foreach ($ventas_big_cola as $venta) {
    $total_ventas_dia += $venta['total_ventas'];
}

// Verificar si hay discrepancia entre ventas y facturas
$discrepancia = abs($total_ventas_dia - $total_facturas);
$hay_discrepancia = $discrepancia > 0.01; // Tolerancia de 1 centavo

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
        
        <!-- Resumen de totales -->
        <?php if (!empty($facturas)): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($fecha)); ?>
                                <br>
                                <strong>Total Facturas:</strong> <?php echo count($facturas); ?> facturas
                            </div>
                            <div>
                                <h5 class="mb-0">Total: $<?php echo number_format($total_facturas, 2); ?></h5>
                                
                                <?php if ($hay_discrepancia): ?>
                                <div class="small mt-2 text-danger">
                                    <strong>Nota:</strong> El total de ventas para esta fecha es $<?php echo number_format($total_ventas_dia, 2); ?> 
                                    (discrepancia de $<?php echo number_format($discrepancia, 2); ?>)
                                </div>
                                <?php else: ?>
                                <div class="small mt-2 text-success">
                                    <strong>Verificado:</strong> El monto coincide con el total de ventas del día
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para eliminar múltiples facturas -->
        <form action="eliminar-multiple.php" method="POST" id="form-eliminar-multiple">
            <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
            
            <?php if (!empty($facturas)): ?>
            <div class="mb-3">
                <button type="submit" class="btn btn-danger btn-sm" id="btn-eliminar-seleccionadas" disabled>
                    <i class="bi bi-trash"></i> Eliminar Seleccionadas
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-seleccionar-todas">
                    <i class="bi bi-check-all"></i> Seleccionar Todas
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-deseleccionar-todas" style="display:none;">
                    <i class="bi bi-x-lg"></i> Deseleccionar Todas
                </button>
            </div>
            <?php endif; ?>
            
            <!-- Tabla de facturas -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="40px">
                                <?php if (!empty($facturas)): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check-todas">
                                </div>
                                <?php endif; ?>
                            </th>
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
                            <td colspan="6" class="text-center">No hay facturas generadas para esta fecha</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($facturas as $factura): ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input check-factura" type="checkbox" name="ids[]" value="<?php echo $factura['id']; ?>">
                                    </div>
                                </td>
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
                    <?php if (!empty($facturas)): ?>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="4" class="text-end">TOTAL:</th>
                            <th>$<?php echo number_format($total_facturas, 2); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const formEliminarMultiple = document.getElementById('form-eliminar-multiple');
    const btnEliminarSeleccionadas = document.getElementById('btn-eliminar-seleccionadas');
    const btnSeleccionarTodas = document.getElementById('btn-seleccionar-todas');
    const btnDeseleccionarTodas = document.getElementById('btn-deseleccionar-todas');
    const checkTodas = document.getElementById('check-todas');
    const checksFactura = document.querySelectorAll('.check-factura');
    
    // Función para actualizar el estado del botón de eliminar seleccionadas
    function actualizarBotonEliminar() {
        const haySeleccionadas = Array.from(checksFactura).some(check => check.checked);
        btnEliminarSeleccionadas.disabled = !haySeleccionadas;
    }
    
    // Verificar selecciones al cambiar cualquier checkbox
    checksFactura.forEach(function(check) {
        check.addEventListener('change', function() {
            actualizarBotonEliminar();
            
            // Verificar si todas están seleccionadas
            const todasSeleccionadas = Array.from(checksFactura).every(c => c.checked);
            if (checkTodas) {
                checkTodas.checked = todasSeleccionadas;
            }
            
            // Mostrar/ocultar botones de selección
            if (btnSeleccionarTodas && btnDeseleccionarTodas) {
                if (todasSeleccionadas) {
                    btnSeleccionarTodas.style.display = 'none';
                    btnDeseleccionarTodas.style.display = 'inline-block';
                } else {
                    btnSeleccionarTodas.style.display = 'inline-block';
                    btnDeseleccionarTodas.style.display = 'none';
                }
            }
        });
    });
    
    // Seleccionar o deseleccionar todas con el checkbox del encabezado
    if (checkTodas) {
        checkTodas.addEventListener('change', function() {
            const seleccionarTodas = this.checked;
            
            checksFactura.forEach(function(check) {
                check.checked = seleccionarTodas;
            });
            
            actualizarBotonEliminar();
            
            // Mostrar/ocultar botones de selección
            if (btnSeleccionarTodas && btnDeseleccionarTodas) {
                if (seleccionarTodas) {
                    btnSeleccionarTodas.style.display = 'none';
                    btnDeseleccionarTodas.style.display = 'inline-block';
                } else {
                    btnSeleccionarTodas.style.display = 'inline-block';
                    btnDeseleccionarTodas.style.display = 'none';
                }
            }
        });
    }
    
    // Botón para seleccionar todas
    if (btnSeleccionarTodas) {
        btnSeleccionarTodas.addEventListener('click', function() {
            checksFactura.forEach(function(check) {
                check.checked = true;
            });
            
            if (checkTodas) {
                checkTodas.checked = true;
            }
            
            actualizarBotonEliminar();
            
            // Mostrar/ocultar botones
            btnSeleccionarTodas.style.display = 'none';
            btnDeseleccionarTodas.style.display = 'inline-block';
        });
    }
    
    // Botón para deseleccionar todas
    if (btnDeseleccionarTodas) {
        btnDeseleccionarTodas.addEventListener('click', function() {
            checksFactura.forEach(function(check) {
                check.checked = false;
            });
            
            if (checkTodas) {
                checkTodas.checked = false;
            }
            
            actualizarBotonEliminar();
            
            // Mostrar/ocultar botones
            btnSeleccionarTodas.style.display = 'inline-block';
            btnDeseleccionarTodas.style.display = 'none';
        });
    }
    
    // Confirmación antes de eliminar
    if (formEliminarMultiple) {
        formEliminarMultiple.addEventListener('submit', function(e) {
            const seleccionadas = Array.from(checksFactura).filter(check => check.checked).length;
            
            if (!confirm(`¿Está seguro de que desea eliminar ${seleccionadas} facturas seleccionadas?`)) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>