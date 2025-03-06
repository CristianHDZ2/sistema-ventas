// Scripts personalizados para el sistema de ventas

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    
    // Confirmación para eliminación
    const confirmarEliminar = document.querySelectorAll('.confirmar-eliminar');
    if (confirmarEliminar) {
        confirmarEliminar.forEach(function(elemento) {
            elemento.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de que deseas eliminar este elemento?')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Filtro de productos para rutas Big Cola
    const selectRuta = document.getElementById('ruta_id');
    const divProductosBigCola = document.getElementById('productos-big-cola');
    const divProductosGeneral = document.getElementById('productos-general');
    
    if (selectRuta && divProductosBigCola && divProductosGeneral) {
        selectRuta.addEventListener('change', function() {
            const rutaOption = selectRuta.options[selectRuta.selectedIndex];
            const esBigCola = rutaOption.getAttribute('data-big-cola') === '1';
            
            if (esBigCola) {
                divProductosBigCola.style.display = 'block';
                divProductosGeneral.style.display = 'none';
            } else {
                divProductosBigCola.style.display = 'none';
                divProductosGeneral.style.display = 'block';
            }
        });
        
        // Trigger el evento change para inicializar correctamente
        selectRuta.dispatchEvent(new Event('change'));
    }
    
    // Fechas en reportes
    const selectTipoReporte = document.getElementById('tipo_reporte');
    const divDia = document.getElementById('div-dia');
    const divMes = document.getElementById('div-mes');
    const divRango = document.getElementById('div-rango');
    
    if (selectTipoReporte && divDia && divMes && divRango) {
        selectTipoReporte.addEventListener('change', function() {
            const tipoReporte = selectTipoReporte.value;
            
            divDia.style.display = 'none';
            divMes.style.display = 'none';
            divRango.style.display = 'none';
            
            if (tipoReporte === 'diario') {
                divDia.style.display = 'block';
            } else if (tipoReporte === 'mensual') {
                divMes.style.display = 'block';
            } else if (tipoReporte === 'rango') {
                divRango.style.display = 'block';
            }
        });
        
        // Trigger el evento change para inicializar correctamente
        selectTipoReporte.dispatchEvent(new Event('change'));
    }
    
    // Agregar productos dinámicamente al formulario de ventas
    const btnAgregarProducto = document.getElementById('btn-agregar-producto');
    const containerProductos = document.getElementById('container-productos');
    
    if (btnAgregarProducto && containerProductos) {
        let productoCount = 1;
        
        btnAgregarProducto.addEventListener('click', function() {
            const template = document.getElementById('template-producto').innerHTML;
            const nuevoProducto = template.replace(/\{index\}/g, productoCount);
            
            const div = document.createElement('div');
            div.innerHTML = nuevoProducto;
            containerProductos.appendChild(div.firstChild);
            
            // Actualizar eventos después de agregar el nuevo elemento
            actualizarEventosProductos();
            
            productoCount++;
        });
        
        function actualizarEventosProductos() {
            // Evento para eliminar productos
            const botonesEliminar = document.querySelectorAll('.btn-eliminar-producto');
            botonesEliminar.forEach(function(boton) {
                boton.addEventListener('click', function() {
                    const fila = this.closest('.row-producto');
                    fila.remove();
                });
            });
            
            // Evento para calcular subtotales
            const inputsCantidad = document.querySelectorAll('.input-cantidad');
            const selectsProducto = document.querySelectorAll('.select-producto');
            
            inputsCantidad.forEach(function(input) {
                input.addEventListener('change', calcularSubtotal);
            });
            
            selectsProducto.forEach(function(select) {
                select.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    const precio = option.getAttribute('data-precio');
                    const rowProducto = this.closest('.row-producto');
                    const inputPrecio = rowProducto.querySelector('.input-precio');
                    
                    if (inputPrecio && precio) {
                        inputPrecio.value = precio;
                        calcularSubtotal.call(this);
                    }
                });
            });
        }
        
        function calcularSubtotal() {
            const rowProducto = this.closest('.row-producto');
            const cantidad = parseFloat(rowProducto.querySelector('.input-cantidad').value) || 0;
            const precio = parseFloat(rowProducto.querySelector('.input-precio').value) || 0;
            const inputSubtotal = rowProducto.querySelector('.input-subtotal');
            
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
            
            const inputTotal = document.getElementById('total');
            if (inputTotal) {
                inputTotal.value = total.toFixed(2);
            }
        }
        
        // Inicializar eventos
        actualizarEventosProductos();
    }
});