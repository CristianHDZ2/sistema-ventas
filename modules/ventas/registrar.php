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
    const formVenta = document.getElementById('form-venta');
    
    // Arrays para mantener listas de productos y sus selecciones
    let productosSeleccionados = [];
    let productosBigColaSeleccionados = [];
    
    // Inicialmente deshabilitar los desplegables de productos y botones de agregar
    deshabilitarSeleccionProductos(true);
    
    // Cambiar visualización según el tipo de ruta
    if (selectRuta) {
        selectRuta.addEventListener('change', function() {
            const rutaSeleccionada = this.value !== '';
            
            // Habilitar o deshabilitar controles de productos según si hay ruta seleccionada
            deshabilitarSeleccionProductos(!rutaSeleccionada);
            
            if (!rutaSeleccionada) {
                return; // No continuar si no hay ruta seleccionada
            }
            
            const esBigCola = this.options[this.selectedIndex].getAttribute('data-big-cola') === '1';
            
            if (esBigCola) {
                divProductosBigCola.style.display = 'block';
                divProductosGeneral.style.display = 'none';
                // Reset selected products when changing routes
                productosSeleccionados = [];
                productosBigColaSeleccionados = [];
                actualizarSelectsProductos();
            } else {
                divProductosBigCola.style.display = 'none';
                divProductosGeneral.style.display = 'block';
                // Reset selected products when changing routes
                productosSeleccionados = [];
                productosBigColaSeleccionados = [];
                actualizarSelectsProductos();
            }
        });
        
        // Inicializar la visualización correcta
        selectRuta.dispatchEvent(new Event('change'));
    }
    
    // Validación del formulario antes de enviar
    if (formVenta) {
        formVenta.addEventListener('submit', function(e) {
            // Verificar si hay una ruta seleccionada
            if (!selectRuta.value) {
                e.preventDefault();
                alert('Debe seleccionar una ruta antes de registrar la venta.');
                return false;
            }
            
            // Verificar si hay productos agregados
            const hayProductos = document.querySelectorAll('.row-producto').length > 0;
            const hayProductosSeleccionados = document.querySelectorAll('.select-producto option:checked[value!=""]').length > 0;
            
            if (!hayProductos || !hayProductosSeleccionados) {
                e.preventDefault();
                alert('Debe agregar al menos un producto a la venta.');
                return false;
            }
            
            // Verificar si todos los productos tienen cantidades
            const inputsCantidad = document.querySelectorAll('.input-cantidad');
            let cantidadesValidas = true;
            
            inputsCantidad.forEach(function(input) {
                if (!input.value || parseFloat(input.value) <= 0) {
                    cantidadesValidas = false;
                }
            });
            
            if (!cantidadesValidas) {
                e.preventDefault();
                alert('Todos los productos deben tener cantidades válidas.');
                return false;
            }
            
            // Si todo está bien, el formulario se enviará normalmente
            return true;
        });
    }
    
    // Función para deshabilitar/habilitar controles de productos
    function deshabilitarSeleccionProductos(deshabilitar) {
        // Deshabilitar/habilitar los selects de productos
        document.querySelectorAll('.select-producto').forEach(function(select) {
            select.disabled = deshabilitar;
        });
        
        // Deshabilitar/habilitar los inputs de cantidad
        document.querySelectorAll('.input-cantidad').forEach(function(input) {
            input.disabled = deshabilitar;
        });
        
        // Deshabilitar/habilitar botones de agregar producto
        if (btnAgregarProducto) btnAgregarProducto.disabled = deshabilitar;
        if (btnAgregarProductoBigCola) btnAgregarProductoBigCola.disabled = deshabilitar;
        
        // Si está deshabilitado, mostrar mensaje
        const mensajeRuta = document.getElementById('mensaje-seleccionar-ruta');
        if (deshabilitar) {
            if (!mensajeRuta) {
                const mensaje = document.createElement('div');
                mensaje.id = 'mensaje-seleccionar-ruta';
                mensaje.className = 'alert alert-warning mt-3';
                mensaje.textContent = 'Debe seleccionar una ruta antes de agregar productos.';
                
                divProductosGeneral.prepend(mensaje);
                
                const mensajeBigCola = mensaje.cloneNode(true);
                divProductosBigCola.prepend(mensajeBigCola);
            }
        } else {
            if (mensajeRuta) {
                document.querySelectorAll('#mensaje-seleccionar-ruta').forEach(function(elemento) {
                    elemento.remove();
                });
            }
        }
    }
    
    // Agregar nuevo producto general
    if (btnAgregarProducto && containerProductos && templateProducto) {
        btnAgregarProducto.addEventListener('click', function() {
            // Verificar si hay una ruta seleccionada
            if (!selectRuta.value) {
                alert('Debe seleccionar una ruta antes de agregar productos.');
                return;
            }
            
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
    
    // Agregar nuevo producto Big Cola
    if (btnAgregarProductoBigCola && containerProductosBigCola && templateProductoBigCola) {
        btnAgregarProductoBigCola.addEventListener('click', function() {
            // Verificar si hay una ruta seleccionada
            if (!selectRuta.value) {
                alert('Debe seleccionar una ruta antes de agregar productos.');
                return;
            }
            
            const template = templateProductoBigCola.content.cloneNode(true);
            
            // Eliminar productos ya seleccionados del nuevo select
            const newSelect = template.querySelector('.select-producto');
            if (newSelect) {
                for (const productoId of productosBigColaSeleccionados) {
                    const option = newSelect.querySelector(`option[value="${productoId}"]`);
                    if (option) {
                        option.remove();
                    }
                }
            }
            
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
                const select = fila.querySelector('.select-producto');
                const productoId = select.value;
                
                // Eliminar de la lista de seleccionados
                if (divProductosBigCola.style.display === 'block') {
                    productosBigColaSeleccionados = productosBigColaSeleccionados.filter(id => id !== productoId);
                } else {
                    productosSeleccionados = productosSeleccionados.filter(id => id !== productoId);
                }
                
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
                if (divProductosBigCola.style.display === 'block') {
                    productosBigColaSeleccionados = productosBigColaSeleccionados.filter(id => id !== valorAnterior);
                    
                    // Agregar el nuevo producto a la lista de seleccionados
                    if (productoId) {
                        productosBigColaSeleccionados.push(productoId);
                    }
                } else {
                    productosSeleccionados = productosSeleccionados.filter(id => id !== valorAnterior);
                    
                    // Agregar el nuevo producto a la lista de seleccionados
                    if (productoId) {
                        productosSeleccionados.push(productoId);
                    }
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
        // Actualizar selects para productos generales
        const selectsGeneral = document.querySelectorAll('#productos-general .select-producto');
        
        selectsGeneral.forEach(function(select, index) {
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
        
        // Actualizar selects para productos Big Cola
        const selectsBigCola = document.querySelectorAll('#productos-big-cola .select-producto');
        
        selectsBigCola.forEach(function(select) {
            // Si es el select actualmente activo, no modificar
            if (select === document.activeElement) return;
            
            const valorActual = select.value;
            
            // Restaurar todas las opciones
            select.innerHTML = templateProductoBigCola.content.querySelector('.select-producto').innerHTML;
            
            // Eliminar opciones de productos ya seleccionados (excepto el seleccionado en este select)
            for (const productoId of productosBigColaSeleccionados) {
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
    
    // Actualizar las listas desplegables al cargar la página
    const selectsProductoInicial = document.querySelectorAll('.select-producto');
    selectsProductoInicial.forEach(function(select) {
        if (select.value) {
            if (select.closest('#productos-big-cola')) {
                productosBigColaSeleccionados.push(select.value);
            } else {
                productosSeleccionados.push(select.value);
            }
        }
    });
    
    // Actualizar selectores iniciales
    actualizarSelectsProductos();
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>