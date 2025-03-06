<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Obtener rutas y productos
$rutas = obtenerRutas(true); // Solo rutas activas
$productosBigCola = obtenerProductos(1); // Productos Big Cola
$productosGeneral = obtenerProductos(); // Todos los productos

// Fecha actual para el formulario
$fecha_actual = date('Y-m-d');

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = trim($_POST['fecha'] ?? '');
    $ruta_id = intval($_POST['ruta_id'] ?? 0);
    
    // Validar datos
    $errores = [];
    
    if (empty($fecha)) {
        $errores[] = "La fecha es obligatoria";
    }
    
    if ($ruta_id <= 0) {
        $errores[] = "Debes seleccionar una ruta válida";
    }
    
    // Procesar los productos vendidos
    $productos = [];
    $tiene_productos = false;
    
    if (isset($_POST['producto_id']) && is_array($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $index => $producto_id) {
            if (!empty($producto_id) && isset($_POST['cantidad'][$index]) && $_POST['cantidad'][$index] > 0) {
                $productos[] = [
                    'id' => intval($producto_id),
                    'cantidad' => intval($_POST['cantidad'][$index]),
                    'precio' => floatval($_POST['precio'][$index])
                ];
                $tiene_productos = true;
            }
        }
    }
    
    if (!$tiene_productos) {
        $errores[] = "Debes agregar al menos un producto a la venta";
    }
    
    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // Registrar la venta
            $venta_id = registrarVenta($fecha, $ruta_id, $productos);
            
            if ($venta_id) {
                // Venta registrada con éxito
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'La venta se ha registrado correctamente'
                ];
                
                header('Location: registrar.php');
                exit;
            } else {
                $errores[] = "No se pudo registrar la venta";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al registrar la venta: " . $e->getMessage();
        }
    }
}

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Registrar Venta</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="../../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al inicio
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
        
        <form action="registrar.php" method="POST" id="form-venta">
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required 
                        value="<?php echo htmlspecialchars($_POST['fecha'] ?? $fecha_actual); ?>">
                </div>
                
                <div class="col-md-8 mb-3">
                    <label for="ruta_id" class="form-label">Ruta</label>
                    <select class="form-select" id="ruta_id" name="ruta_id" required>
                        <option value="">Selecciona una ruta</option>
                        <?php foreach ($rutas as $ruta): ?>
                        <option value="<?php echo $ruta['id']; ?>" 
                            data-big-cola="<?php echo $ruta['es_big_cola']; ?>"
                            <?php echo (isset($_POST['ruta_id']) && $_POST['ruta_id'] == $ruta['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ruta['nombre']); ?>
                            <?php if ($ruta['es_big_cola'] == 1): ?> (Big Cola)<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Sección de productos de Big Cola -->
            <div id="productos-big-cola" style="display: none;">
                <div class="big-cola-section">
                    <h4 class="mb-3">Productos Big Cola</h4>
                    
                    <div id="container-productos-big-cola">
                        <div class="row row-producto mb-3">
                            <div class="col-md-6">
                                <select class="form-select select-producto" name="producto_id[]">
                                    <option value="">Selecciona un producto</option>
                                    <?php foreach ($productosBigCola as $producto): ?>
                                    <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo number_format($producto['precio'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control input-precio" name="precio[]" step="0.01" min="0.01" placeholder="Precio" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control input-cantidad" name="cantidad[]" min="1" placeholder="Cantidad">
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control input-subtotal" placeholder="Subtotal" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-outline-primary" id="btn-agregar-producto-big-cola">
                                <i class="bi bi-plus-lg"></i> Agregar otro producto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección de productos generales -->
            <div id="productos-general">
                <div class="general-section">
                    <h4 class="mb-3">Productos</h4>
                    
                    <div id="container-productos">
                        <div class="row row-producto mb-3">
                            <div class="col-md-6">
                                <select class="form-select select-producto" name="producto_id[]">
                                    <option value="">Selecciona un producto</option>
                                    <?php foreach ($productosGeneral as $producto): ?>
                                    <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo number_format($producto['precio'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control input-precio" name="precio[]" step="0.01" min="0.01" placeholder="Precio" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control input-cantidad" name="cantidad[]" min="1" placeholder="Cantidad">
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control input-subtotal" placeholder="Subtotal" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-outline-primary" id="btn-agregar-producto">
                                <i class="bi bi-plus-lg"></i> Agregar otro producto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total y botón de guardar -->
            <div class="row mb-3 mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="input-group">
                        <span class="input-group-text fw-bold">TOTAL $</span>
                        <input type="text" class="form-control form-control-lg" id="total" readonly>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Registrar Venta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Plantilla para agregar nuevos productos -->
<template id="template-producto">
    <div class="row row-producto mb-3">
        <div class="col-md-5">
            <select class="form-select select-producto" name="producto_id[]">
                <option value="">Selecciona un producto</option>
                <?php foreach ($productosGeneral as $producto): ?>
                <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>">
                    <?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo number_format($producto['precio'], 2); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control input-precio" name="precio[]" step="0.01" min="0.01" placeholder="Precio" readonly>
            </div>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control input-cantidad" name="cantidad[]" min="1" placeholder="Cantidad">
        </div>
        <div class="col-md-2">
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control input-subtotal" placeholder="Subtotal" readonly>
            </div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-eliminar-producto">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

<!-- Plantilla para agregar nuevos productos Big Cola -->
<template id="template-producto-big-cola">
    <div class="row row-producto mb-3">
        <div class="col-md-5">
            <select class="form-select select-producto" name="producto_id[]">
                <option value="">Selecciona un producto</option>
                <?php foreach ($productosBigCola as $producto): ?>
                <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>">
                    <?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo number_format($producto['precio'], 2); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control input-precio" name="precio[]" step="0.01" min="0.01" placeholder="Precio" readonly>
            </div>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control input-cantidad" name="cantidad[]" min="1" placeholder="Cantidad">
        </div>
        <div class="col-md-2">
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control input-subtotal" placeholder="Subtotal" readonly>
            </div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-eliminar-producto">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const selectRuta = document.getElementById('ruta_id');
    const divProductosBigCola = document.getElementById('productos-big-cola');
    const divProductosGeneral = document.getElementById('productos-general');
    const btnAgregarProducto = document.getElementById('btn-agregar-producto');
    const btnAgregarProductoBigCola = document.getElementById('btn-agregar-producto-big-cola');
    const containerProductos = document.getElementById('container-productos');
    const containerProductosBigCola = document.getElementById('container-productos-big-cola');
    const templateProducto = document.getElementById('template-producto');
    const templateProductoBigCola = document.getElementById('template-producto-big-cola');
    const inputTotal = document.getElementById('total');
    
    // Cambiar visualización según el tipo de ruta
    if (selectRuta) {
        selectRuta.addEventListener('change', function() {
            const esBigCola = this.options[this.selectedIndex].getAttribute('data-big-cola') === '1';
            
            if (esBigCola) {
                divProductosBigCola.style.display = 'block';
                divProductosGeneral.style.display = 'none';
            } else {
                divProductosBigCola.style.display = 'none';
                divProductosGeneral.style.display = 'block';
            }
        });
        
        // Inicializar la visualización correcta
        selectRuta.dispatchEvent(new Event('change'));
    }
    
    // Agregar nuevo producto general
    if (btnAgregarProducto && containerProductos && templateProducto) {
        btnAgregarProducto.addEventListener('click', function() {
            const template = templateProducto.content.cloneNode(true);
            containerProductos.appendChild(template);
            
            // Actualizar eventos después de agregar el nuevo elemento
            actualizarEventosProductos();
        });
    }
    
    // Agregar nuevo producto Big Cola
    if (btnAgregarProductoBigCola && containerProductosBigCola && templateProductoBigCola) {
        btnAgregarProductoBigCola.addEventListener('click', function() {
            const template = templateProductoBigCola.content.cloneNode(true);
            containerProductosBigCola.appendChild(template);
            
            // Actualizar eventos después de agregar el nuevo elemento
            actualizarEventosProductos();
        });
    }
    
    // Inicializar eventos para los productos existentes
    actualizarEventosProductos();
    
    function actualizarEventosProductos() {
        // Evento para eliminar productos
        document.querySelectorAll('.btn-eliminar-producto').forEach(function(boton) {
            boton.addEventListener('click', function() {
                const fila = this.closest('.row-producto');
                fila.remove();
                calcularTotal();
            });
        });
        
        // Evento para calcular subtotales
        document.querySelectorAll('.input-cantidad').forEach(function(input) {
            input.addEventListener('input', function() {
                const fila = this.closest('.row-producto');
                calcularSubtotal(fila);
            });
        });
        
        // Evento para actualizar precio al cambiar producto
        document.querySelectorAll('.select-producto').forEach(function(select) {
            select.addEventListener('change', function() {
                const fila = this.closest('.row-producto');
                const option = this.options[this.selectedIndex];
                const precio = option.getAttribute('data-precio');
                const inputPrecio = fila.querySelector('.input-precio');
                
                if (inputPrecio && precio) {
                    inputPrecio.value = precio;
                    calcularSubtotal(fila);
                }
            });
        });
    }
    
    function calcularSubtotal(fila) {
        const cantidad = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
        const precio = parseFloat(fila.querySelector('.input-precio').value) || 0;
        const inputSubtotal = fila.querySelector('.input-subtotal');
        
        const subtotal = cantidad * precio;
        inputSubtotal.value = subtotal.toFixed(2);
        
        // Calcular total general
        calcularTotal();
    }
    
    function calcularTotal() {
        const subtotales = document.querySelectorAll('.input-subtotal');
        let total = 0;
        
        subtotales.forEach(function(input) {
            total += parseFloat(input.value) || 0;
        });
        
        if (inputTotal) {
            inputTotal.value = total.toFixed(2);
        }
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>