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

// Obtener rutas y productos
$rutas = obtenerRutas(true); // Solo rutas activas
$productosBigCola = obtenerProductos(1); // Productos Big Cola
$productosGeneral = obtenerProductos(); // Todos los productos

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
            // Actualizar la venta
            $result = actualizarVenta($id, $fecha, $ruta_id, $productos);
            
            if ($result) {
                // Venta actualizada con éxito
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'La venta se ha actualizado correctamente'
                ];
                
                header('Location: listar.php');
                exit;
            } else {
                $errores[] = "No se pudo actualizar la venta";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar la venta: " . $e->getMessage();
        }
    }
}

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Editar Venta #<?php echo $id; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="listar.php" class="btn btn-secondary">
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
        
        <form action="editar.php?id=<?php echo $id; ?>" method="POST" id="form-venta">
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required 
                        value="<?php echo htmlspecialchars($_POST['fecha'] ?? $venta['fecha']); ?>">
                </div>
                
                <div class="col-md-8 mb-3">
                    <label for="ruta_id" class="form-label">Ruta</label>
                    <select class="form-select" id="ruta_id" name="ruta_id" required>
                        <option value="">Selecciona una ruta</option>
                        <?php foreach ($rutas as $ruta): ?>
                        <option value="<?php echo $ruta['id']; ?>" 
                            data-big-cola="<?php echo $ruta['es_big_cola']; ?>"
                            <?php echo ((isset($_POST['ruta_id']) && $_POST['ruta_id'] == $ruta['id']) || 
                                (!isset($_POST['ruta_id']) && $venta['ruta_id'] == $ruta['id'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ruta['nombre']); ?>
                            <?php if ($ruta['es_big_cola'] == 1): ?> (Big Cola)<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Sección de productos -->
            <h4 class="mb-3">Productos</h4>
            
            <div id="container-productos">
                <?php foreach ($detalles as $index => $detalle): ?>
                <div class="row row-producto mb-3">
                    <div class="col-md-5">
                        <select class="form-select select-producto" name="producto_id[]">
                            <option value="">Selecciona un producto</option>
                            <?php 
                            // Determinar qué lista de productos usar
                            $listaProductos = $productosGeneral;
                            foreach ($listaProductos as $producto): 
                            ?>
                            <option value="<?php echo $producto['id']; ?>" 
                                data-precio="<?php echo $producto['precio']; ?>"
                                <?php echo ($detalle['producto_id'] == $producto['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo number_format($producto['precio'], 2); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control input-precio" name="precio[]" step="0.01" min="0.01" 
                                placeholder="Precio" value="<?php echo $detalle['precio_unitario']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control input-cantidad" name="cantidad[]" min="1" 
                            placeholder="Cantidad" value="<?php echo $detalle['cantidad']; ?>">
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control input-subtotal" 
                                placeholder="Subtotal" value="<?php echo $detalle['subtotal']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <?php if ($index > 0): // No permitir eliminar el primer producto ?>
                        <button type="button" class="btn btn-danger btn-eliminar-producto">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($detalles)): // Si no hay detalles (caso raro), mostrar fila vacía ?>
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
                </div>
                <?php endif; ?>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="button" class="btn btn-outline-primary" id="btn-agregar-producto">
                        <i class="bi bi-plus-lg"></i> Agregar otro producto
                    </button>
                </div>
            </div>
            
            <!-- Total y botón de guardar -->
            <div class="row mb-3 mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="input-group">
                        <span class="input-group-text fw-bold">TOTAL $</span>
                        <input type="text" class="form-control form-control-lg" id="total" value="<?php echo $venta['total']; ?>" readonly>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Actualizar Venta
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const selectRuta = document.getElementById('ruta_id');
    const btnAgregarProducto = document.getElementById('btn-agregar-producto');
    const containerProductos = document.getElementById('container-productos');
    const templateProducto = document.getElementById('template-producto');
    const inputTotal = document.getElementById('total');
    
    // Array para mantener listas de productos ya seleccionados
    let productosSeleccionados = [];
    
    // Inicializar productos seleccionados a partir de los existentes
    const selectsProductoInicial = document.querySelectorAll('.select-producto');
    selectsProductoInicial.forEach(function(select) {
        if (select.value) {
            productosSeleccionados.push(select.value);
        }
    });
    
    // Agregar nuevo producto
    if (btnAgregarProducto && containerProductos && templateProducto) {
        btnAgregarProducto.addEventListener('click', function() {
            const template = templateProducto.content.cloneNode(true);
            
            // Eliminar productos ya seleccionados del nuevo select
            const newSelect = template.querySelector('.select-producto');
            if (newSelect) {
                for (const productoId of productosSeleccionados) {
                    const option = newSelect.querySelector(`option[value="${productoId}"]`);
                    if (option) {
                        option.remove();
                    }
                }
            }
            
            containerProductos.appendChild(template);
            
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
                const select = fila.querySelector('.select-producto');
                const productoId = select.value;
                
                // Eliminar de la lista de seleccionados
                productosSeleccionados = productosSeleccionados.filter(id => id !== productoId);
                
                fila.remove();
                calcularTotal();
                actualizarSelectsProductos();
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
            // Guardar el valor anterior para poder eliminar de la lista de seleccionados si cambia
            let valorAnterior = select.value;
            
            select.addEventListener('change', function() {
                const fila = this.closest('.row-producto');
                const option = this.options[this.selectedIndex];
                const productoId = this.value;
                const precio = option.getAttribute('data-precio');
                const inputPrecio = fila.querySelector('.input-precio');
                
                // Eliminar el producto anterior de la lista de seleccionados
                productosSeleccionados = productosSeleccionados.filter(id => id !== valorAnterior);
                
                // Agregar el nuevo producto a la lista de seleccionados
                if (productoId) {
                    productosSeleccionados.push(productoId);
                }
                
                valorAnterior = productoId;
                
                if (inputPrecio && precio) {
                    inputPrecio.value = precio;
                    calcularSubtotal(fila);
                }
                
                actualizarSelectsProductos();
            });
        });
    }
    
    function actualizarSelectsProductos() {
        // Actualizar todos los selects para eliminar productos ya seleccionados
        const selects = document.querySelectorAll('.select-producto');
        
        selects.forEach(function(select) {
            // Si es el select actualmente activo, no modificar
            if (select === document.activeElement) return;
            
            const valorActual = select.value;
            
            // Restaurar todas las opciones
            select.innerHTML = templateProducto.content.querySelector('.select-producto').innerHTML;
            
            // Eliminar opciones de productos ya seleccionados (excepto el seleccionado en este select)
            for (const productoId of productosSeleccionados) {
                if (productoId !== valorActual) {
                    const option = select.querySelector(`option[value="${productoId}"]`);
                    if (option) {
                        option.remove();
                    }
                }
            }
            
            // Restaurar el valor seleccionado
            select.value = valorActual;
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
    
    // Inicializar los subtotales y el total al cargar la página
    document.querySelectorAll('.row-producto').forEach(function(fila) {
        calcularSubtotal(fila);
    });
    
    // Actualizar selectores iniciales
    actualizarSelectsProductos();
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>