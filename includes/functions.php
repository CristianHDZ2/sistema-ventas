<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../config/db.php';

/**
 * Obtiene todos los productos
 */
function obtenerProductos($categoria_id = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            JOIN categorias c ON p.categoria_id = c.id";
    
    if ($categoria_id !== null) {
        $sql .= " WHERE p.categoria_id = :categoria_id";
    }
    
    $sql .= " ORDER BY p.nombre ASC";
    
    $stmt = $conn->prepare($sql);
    
    if ($categoria_id !== null) {
        $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene un producto por su ID
 */
function obtenerProductoPorId($id) {
    global $conn;
    
    $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Agregar un nuevo producto
 */
function agregarProducto($nombre, $precio, $categoria_id) {
    global $conn;
    
    $sql = "INSERT INTO productos (nombre, precio, categoria_id) 
            VALUES (:nombre, :precio, :categoria_id)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Actualizar un producto existente
 */
function actualizarProducto($id, $nombre, $precio, $categoria_id) {
    global $conn;
    
    $sql = "UPDATE productos 
            SET nombre = :nombre, precio = :precio, categoria_id = :categoria_id 
            WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Eliminar un producto
 */
function eliminarProducto($id) {
    global $conn;
    
    $sql = "DELETE FROM productos WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Obtiene todas las categorías
 */
function obtenerCategorias() {
    global $conn;
    
    $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene todas las rutas
 */
function obtenerRutas($soloActivas = false, $soloBigCola = null) {
    global $conn;
    
    $sql = "SELECT * FROM rutas";
    $where = [];
    
    if ($soloActivas) {
        $where[] = "activa = 1";
    }
    
    if ($soloBigCola !== null) {
        $where[] = "es_big_cola = " . ($soloBigCola ? "1" : "0");
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " ORDER BY nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene una ruta por su ID
 */
function obtenerRutaPorId($id) {
    global $conn;
    
    $sql = "SELECT * FROM rutas WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Agregar una nueva ruta
 */
function agregarRuta($nombre, $es_big_cola) {
    global $conn;
    
    $sql = "INSERT INTO rutas (nombre, es_big_cola) VALUES (:nombre, :es_big_cola)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':es_big_cola', $es_big_cola, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Actualizar una ruta existente
 */
function actualizarRuta($id, $nombre, $es_big_cola, $activa) {
    global $conn;
    
    $sql = "UPDATE rutas 
            SET nombre = :nombre, es_big_cola = :es_big_cola, activa = :activa 
            WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':es_big_cola', $es_big_cola, PDO::PARAM_INT);
    $stmt->bindParam(':activa', $activa, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Eliminar una ruta
 */
function eliminarRuta($id) {
    global $conn;
    
    $sql = "DELETE FROM rutas WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    return $stmt->execute();
}

/**
 * Registrar una venta
 */
function registrarVenta($fecha, $ruta_id, $productos) {
    global $conn;
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // Calcular el total de la venta
        $total = 0;
        foreach ($productos as $producto) {
            $total += $producto['cantidad'] * $producto['precio'];
        }
        
        // Insertar la venta
        $sql = "INSERT INTO ventas (fecha, ruta_id, total) VALUES (:fecha, :ruta_id, :total)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':ruta_id', $ruta_id);
        $stmt->bindParam(':total', $total);
        $stmt->execute();
        
        // Obtener el ID de la venta insertada
        $venta_id = $conn->lastInsertId();
        
        // Insertar los detalles de la venta
        $sql = "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
        
        $stmt = $conn->prepare($sql);
        
        foreach ($productos as $producto) {
            $producto_id = $producto['id'];
            $cantidad = $producto['cantidad'];
            $precio = $producto['precio'];
            $subtotal = $cantidad * $precio;
            
            $stmt->bindParam(':venta_id', $venta_id);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':precio_unitario', $precio);
            $stmt->bindParam(':subtotal', $subtotal);
            $stmt->execute();
        }
        
        // Confirmar la transacción
        $conn->commit();
        
        return $venta_id;
        
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        echo "Error al registrar la venta: " . $e->getMessage();
        return false;
    }
}

/**
 * Obtener ventas por fecha
 */
function obtenerVentasPorFecha($fecha_inicio, $fecha_fin) {
    global $conn;
    
    $sql = "SELECT v.*, r.nombre as ruta_nombre, r.es_big_cola 
            FROM ventas v 
            JOIN rutas r ON v.ruta_id = r.id 
            WHERE v.fecha BETWEEN :fecha_inicio AND :fecha_fin 
            ORDER BY v.fecha DESC, r.nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener detalles de una venta
 */
function obtenerDetallesVenta($venta_id) {
    global $conn;
    
    $sql = "SELECT dv.*, p.nombre as producto_nombre 
            FROM detalle_ventas dv 
            JOIN productos p ON dv.producto_id = p.id 
            WHERE dv.venta_id = :venta_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':venta_id', $venta_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener reporte de ventas por producto en un rango de fechas
 */
function reporteVentasPorProducto($fecha_inicio, $fecha_fin, $categoria_id = null) {
    global $conn;
    
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.precio,
                SUM(dv.cantidad) as total_vendido,
                SUM(dv.subtotal) as total_ventas,
                c.nombre as categoria
            FROM 
                detalle_ventas dv
            JOIN 
                ventas v ON dv.venta_id = v.id
            JOIN 
                productos p ON dv.producto_id = p.id
            JOIN 
                categorias c ON p.categoria_id = c.id
            WHERE 
                v.fecha BETWEEN :fecha_inicio AND :fecha_fin";
    
    if ($categoria_id !== null) {
        $sql .= " AND p.categoria_id = :categoria_id";
    }
    
    $sql .= " GROUP BY p.id, p.nombre, p.precio, c.nombre
              ORDER BY p.nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    
    if ($categoria_id !== null) {
        $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener reporte de ventas por ruta en un rango de fechas
 */
function reporteVentasPorRuta($fecha_inicio, $fecha_fin) {
    global $conn;
    
    $sql = "SELECT 
                r.id,
                r.nombre,
                r.es_big_cola,
                COUNT(v.id) as total_ventas,
                SUM(v.total) as total_monto
            FROM 
                rutas r
            LEFT JOIN 
                ventas v ON r.id = v.ruta_id AND v.fecha BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY 
                r.id, r.nombre, r.es_big_cola
            ORDER BY 
                r.nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener una venta específica por su ID
 */
function obtenerVentaPorId($id) {
    global $conn;
    
    $sql = "SELECT v.*, r.nombre as ruta_nombre 
            FROM ventas v 
            JOIN rutas r ON v.ruta_id = r.id 
            WHERE v.id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Eliminar una venta y sus detalles
 */
function eliminarVenta($id) {
    global $conn;
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // Eliminar los detalles de la venta primero (restricción de clave foránea)
        $sql = "DELETE FROM detalle_ventas WHERE venta_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Ahora eliminar la venta
        $sql = "DELETE FROM ventas WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Confirmar transacción
        $conn->commit();
        
        return true;
    } catch (PDOException $e) {
        // En caso de error, revertir cambios
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Actualizar una venta existente
 */
function actualizarVenta($id, $fecha, $ruta_id, $productos) {
    global $conn;
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // Calcular el nuevo total
        $total = 0;
        foreach ($productos as $producto) {
            $total += $producto['cantidad'] * $producto['precio'];
        }
        
        // Actualizar la información básica de la venta
        $sql = "UPDATE ventas SET fecha = :fecha, ruta_id = :ruta_id, total = :total WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':ruta_id', $ruta_id);
        $stmt->bindParam(':total', $total);
        $stmt->execute();
        
        // Eliminar los detalles existentes
        $sql = "DELETE FROM detalle_ventas WHERE venta_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Insertar los nuevos detalles
        $sql = "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
        
        $stmt = $conn->prepare($sql);
        
        foreach ($productos as $producto) {
            $producto_id = $producto['id'];
            $cantidad = $producto['cantidad'];
            $precio = $producto['precio'];
            $subtotal = $cantidad * $precio;
            
            $stmt->bindParam(':venta_id', $id);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':precio_unitario', $precio);
            $stmt->bindParam(':subtotal', $subtotal);
            $stmt->execute();
        }
        
        // Confirmar transacción
        $conn->commit();
        
        return true;
    } catch (PDOException $e) {
        // En caso de error, revertir cambios
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Generar facturas a partir de las ventas diarias
 */
function generarFacturas($fecha, $ventas_productos, $min_factura = 5.00, $max_factura = 45.00) {
    global $conn;
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // Verificar que hay ventas para la fecha seleccionada
        if (empty($ventas_productos)) {
            return [
                'exito' => false,
                'mensaje' => 'No hay ventas registradas para la fecha seleccionada.'
            ];
        }
        
        // Calcular el total de ventas del día
        $total_ventas = 0;
        $productos_disponibles = [];
        $agua_caida_cielo_id = null;
        
        foreach ($ventas_productos as $producto) {
            $total_ventas += $producto['total_ventas'];
            
            // Guardar los productos con su cantidad vendida
            $productos_disponibles[$producto['id']] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad_restante' => $producto['total_vendido'],
                'min_cantidad' => 1 // cantidad mínima predeterminada
            ];
            
            // Identificar el producto "Agua Caída del Cielo"
            if (strpos($producto['nombre'], 'Agua "Caída del Cielo"') !== false) {
                $agua_caida_cielo_id = $producto['id'];
                $productos_disponibles[$producto['id']]['min_cantidad'] = 3; // Mínimo 3 unidades
            }
        }
        // Generar las facturas hasta cubrir el total de ventas
        $facturas = [];
        $total_generado = 0;
        $numero_factura_base = date('Ymd', strtotime($fecha));
        $contador_factura = 1;
        
        while ($total_generado < $total_ventas) {
            // Verificar si quedan productos disponibles con cantidades suficientes
            $hay_productos_disponibles = false;
            foreach ($productos_disponibles as $prod) {
                if ($prod['cantidad_restante'] >= $prod['min_cantidad']) {
                    $hay_productos_disponibles = true;
                    break;
                }
            }
            
            if (!$hay_productos_disponibles) {
                break;
            }
            
            // Definir el monto objetivo para esta factura (entre min_factura y max_factura)
            $monto_disponible = $total_ventas - $total_generado;
            $monto_objetivo = min($max_factura, $monto_disponible);
            
            if ($monto_objetivo < $min_factura && $monto_disponible > 0) {
                $monto_objetivo = $monto_disponible; // Ajustar la última factura
            } else if ($monto_objetivo >= $min_factura) {
                $monto_objetivo = mt_rand($min_factura * 100, min($max_factura * 100, $monto_objetivo * 100)) / 100;
            }
            
            // Si ya no hay monto disponible, salir del ciclo
            if ($monto_objetivo <= 0) {
                break;
            }
            
            // Generar un número de factura
            $numero_factura = $numero_factura_base . '-' . str_pad($contador_factura, 3, '0', STR_PAD_LEFT);
            
            // Crear la factura
            $sql = "INSERT INTO facturas (fecha, numero_factura, total) VALUES (:fecha, :numero_factura, :total)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':numero_factura', $numero_factura);
            $stmt->bindValue(':total', 0); // Se actualizará después con el total real
            $stmt->execute();
            
            $factura_id = $conn->lastInsertId();
            
            // Agregar productos a la factura
            $detalles_factura = [];
            $total_factura = 0;
            $productos_en_factura = []; // Productos ya agregados a esta factura
            // Primero, tratar de agregar Agua Caída del Cielo si está disponible y en cantidad suficiente
            if ($agua_caida_cielo_id && isset($productos_disponibles[$agua_caida_cielo_id]) && 
                $productos_disponibles[$agua_caida_cielo_id]['cantidad_restante'] >= 3) {
                
                $producto = $productos_disponibles[$agua_caida_cielo_id];
                $cantidad = max(3, min(mt_rand(3, 10), $producto['cantidad_restante']));
                $subtotal = $cantidad * $producto['precio'];
                
                // Si el subtotal excede el monto objetivo, ajustar pero manteniendo al menos 3 unidades
                if ($subtotal > $monto_objetivo) {
                    $cantidad = max(3, floor($monto_objetivo / $producto['precio']));
                    $subtotal = $cantidad * $producto['precio'];
                }
                
                if ($total_factura + $subtotal <= $monto_objetivo) {
                    $detalles_factura[] = [
                        'producto_id' => $producto['id'],
                        'cantidad' => $cantidad,
                        'precio' => $producto['precio'],
                        'subtotal' => $subtotal
                    ];
                    
                    $total_factura += $subtotal;
                    $productos_disponibles[$agua_caida_cielo_id]['cantidad_restante'] -= $cantidad;
                    $productos_en_factura[$agua_caida_cielo_id] = true; // Marcar como ya agregado
                }
            }
            
            // Agregar otros productos hasta llegar al monto objetivo o agotar productos
            $intentos = 0;
            $max_intentos = 100; // Evitar bucles infinitos
            
            while ($total_factura < $monto_objetivo && $intentos < $max_intentos) {
                $intentos++;
                
                // Filtrar productos que aún no se han agregado a esta factura y tienen suficiente cantidad
                $productos_disponibles_filtrados = [];
                foreach ($productos_disponibles as $id => $prod) {
                    if (!isset($productos_en_factura[$id]) && $prod['cantidad_restante'] >= $prod['min_cantidad']) {
                        $productos_disponibles_filtrados[$id] = $prod;
                    }
                }
                
                if (empty($productos_disponibles_filtrados)) {
                    break; // No quedan productos disponibles para esta factura
                }
                
                // Seleccionar un producto aleatorio de los disponibles
                $producto_ids = array_keys($productos_disponibles_filtrados);
                $producto_key = $producto_ids[array_rand($producto_ids)];
                $producto = $productos_disponibles_filtrados[$producto_key];
                
                // Verificar si queda espacio en la factura para este producto
                $espacio_disponible = $monto_objetivo - $total_factura;
                if ($espacio_disponible < $producto['precio'] * $producto['min_cantidad']) {
                    // No hay espacio para la cantidad mínima de este producto
                    $productos_en_factura[$producto_key] = true; // Marcar como no disponible para esta factura
                    continue;
                }
                
                // Determinar cuántas unidades agregar (entre min_cantidad y el máximo posible)
                $max_unidades = min(
                    floor($espacio_disponible / $producto['precio']),
                    $producto['cantidad_restante']
                );
                
                if ($max_unidades < $producto['min_cantidad']) {
                    // No se pueden agregar suficientes unidades
                    $productos_en_factura[$producto_key] = true; // Marcar como no disponible para esta factura
                    continue;
                }
                
                // Seleccionar una cantidad aleatoria respetando el mínimo
                $cantidad = mt_rand($producto['min_cantidad'], max($producto['min_cantidad'], $max_unidades));
                $subtotal = $cantidad * $producto['precio'];
                
                // Agregar a los detalles de la factura
                $detalles_factura[] = [
                    'producto_id' => $producto['id'],
                    'cantidad' => $cantidad,
                    'precio' => $producto['precio'],
                    'subtotal' => $subtotal
                ];
                
                $total_factura += $subtotal;
                
                // Actualizar la cantidad restante del producto
                $productos_disponibles[$producto_key]['cantidad_restante'] -= $cantidad;
                $productos_en_factura[$producto_key] = true; // Marcar como ya agregado a esta factura
            }
            // Si no se pudo agregar ningún producto a la factura, salir del ciclo
            if (empty($detalles_factura)) {
                // Eliminar la factura vacía
                $sql = "DELETE FROM facturas WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $factura_id);
                $stmt->execute();
                break;
            }
            
            // Insertar los detalles de la factura
            $sql = "INSERT INTO detalle_facturas (factura_id, producto_id, cantidad, precio_unitario, subtotal) 
                    VALUES (:factura_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
            
            $stmt = $conn->prepare($sql);
            
            foreach ($detalles_factura as $detalle) {
                $producto_id = $detalle['producto_id'];
                $cantidad = $detalle['cantidad'];
                $precio = $detalle['precio'];
                $subtotal = $detalle['subtotal'];
                
                $stmt->bindParam(':factura_id', $factura_id);
                $stmt->bindParam(':producto_id', $producto_id);
                $stmt->bindParam(':cantidad', $cantidad);
                $stmt->bindParam(':precio_unitario', $precio);
                $stmt->bindParam(':subtotal', $subtotal);
                $stmt->execute();
            }
            
            // Actualizar la factura con el total real
            $sql = "UPDATE facturas SET total = :total WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $factura_id);
            $stmt->bindParam(':total', $total_factura);
            $stmt->execute();
            
            $total_generado += $total_factura;
            $contador_factura++;
            
            $facturas[] = [
                'id' => $factura_id,
                'numero' => $numero_factura,
                'total' => $total_factura
            ];
        }
        
        // Verificar si quedaron productos sin distribuir
        $productos_pendientes = [];
        foreach ($productos_disponibles as $prod_id => $prod) {
            if ($prod['cantidad_restante'] > 0) {
                $productos_pendientes[$prod_id] = $prod;
            }
        }
        
        // Si quedaron productos sin distribuir, crear facturas adicionales
        if (!empty($productos_pendientes)) {
            while (!empty($productos_pendientes)) {
                // Generar un número de factura
                $numero_factura = $numero_factura_base . '-' . str_pad($contador_factura, 3, '0', STR_PAD_LEFT);
                
                // Crear la factura
                $sql = "INSERT INTO facturas (fecha, numero_factura, total) VALUES (:fecha, :numero_factura, :total)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fecha', $fecha);
                $stmt->bindParam(':numero_factura', $numero_factura);
                $stmt->bindValue(':total', 0); // Se actualizará después con el total real
                $stmt->execute();
                
                $factura_id = $conn->lastInsertId();
                
                // Agregar productos a la factura
                $detalles_factura = [];
                $total_factura = 0;
                $productos_en_factura = []; // Productos ya agregados a esta factura
                
                // Intentar agregar todos los productos pendientes, respetando el monto máximo
                $monto_objetivo = min($max_factura, array_sum(array_map(function($p) {
                    return $p['cantidad_restante'] * $p['precio'];
                }, $productos_pendientes)));
                
                foreach ($productos_pendientes as $prod_id => $prod) {
                    if ($prod['cantidad_restante'] <= 0) continue;
                    
                    $espacio_disponible = $monto_objetivo - $total_factura;
                    $cantidad_posible = min(floor($espacio_disponible / $prod['precio']), $prod['cantidad_restante']);
                    
                    if ($cantidad_posible < $prod['min_cantidad']) {
                        continue; // No se pueden agregar suficientes unidades
                    }
                    
                    $cantidad = min($cantidad_posible, $prod['cantidad_restante']);
                    $subtotal = $cantidad * $prod['precio'];
                    
                    if ($total_factura + $subtotal <= $monto_objetivo) {
                        $detalles_factura[] = [
                            'producto_id' => $prod['id'],
                            'cantidad' => $cantidad,
                            'precio' => $prod['precio'],
                            'subtotal' => $subtotal
                        ];
                        
                        $total_factura += $subtotal;
                        $productos_pendientes[$prod_id]['cantidad_restante'] -= $cantidad;
                        $productos_en_factura[$prod_id] = true;
                        
                        if ($productos_pendientes[$prod_id]['cantidad_restante'] <= 0) {
                            unset($productos_pendientes[$prod_id]);
                        }
                    }
                    
                    // Si la factura está llena, salir del bucle
                    if ($total_factura >= $monto_objetivo) {
                        break;
                    }
                }
                
                // Si no se pudo agregar ningún producto a la factura, salir del ciclo
                if (empty($detalles_factura)) {
                    // Eliminar la factura vacía
                    $sql = "DELETE FROM facturas WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $factura_id);
                    $stmt->execute();
                    break;
                }
                
                // Insertar los detalles de la factura
                $sql = "INSERT INTO detalle_facturas (factura_id, producto_id, cantidad, precio_unitario, subtotal) 
                        VALUES (:factura_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
                
                $stmt = $conn->prepare($sql);
                
                foreach ($detalles_factura as $detalle) {
                    $producto_id = $detalle['producto_id'];
                    $cantidad = $detalle['cantidad'];
                    $precio = $detalle['precio'];
                    $subtotal = $detalle['subtotal'];
                    
                    $stmt->bindParam(':factura_id', $factura_id);
                    $stmt->bindParam(':producto_id', $producto_id);
                    $stmt->bindParam(':cantidad', $cantidad);
                    $stmt->bindParam(':precio_unitario', $precio);
                    $stmt->bindParam(':subtotal', $subtotal);
                    $stmt->execute();
                }
                
                // Actualizar la factura con el total real
                $sql = "UPDATE facturas SET total = :total WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $factura_id);
                $stmt->bindParam(':total', $total_factura);
                $stmt->execute();
                
                $total_generado += $total_factura;
                $contador_factura++;
                
                $facturas[] = [
                    'id' => $factura_id,
                    'numero' => $numero_factura,
                    'total' => $total_factura
                ];
            }
        }
        
        // Confirmar la transacción
        $conn->commit();
        
        return [
            'exito' => true,
            'total_facturas' => count($facturas),
            'total_generado' => $total_generado,
            'facturas' => $facturas
        ];
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollBack();
        return [
            'exito' => false,
            'mensaje' => 'Error al generar facturas: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtener facturas por fecha
 */
function obtenerFacturasPorFecha($fecha) {
    global $conn;
    
    $sql = "SELECT * FROM facturas WHERE fecha = :fecha ORDER BY id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener una factura por su ID
 */
function obtenerFacturaPorId($id) {
    global $conn;
    
    $sql = "SELECT * FROM facturas WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtener detalles de una factura
 */
function obtenerDetallesFactura($factura_id) {
    global $conn;
    
    $sql = "SELECT df.*, p.nombre as producto_nombre 
            FROM detalle_facturas df 
            JOIN productos p ON df.producto_id = p.id 
            WHERE df.factura_id = :factura_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':factura_id', $factura_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Eliminar una factura y sus detalles
 */
function eliminarFactura($id) {
    global $conn;
    
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        // Eliminar los detalles de la factura primero (restricción de clave foránea)
        $sql = "DELETE FROM detalle_facturas WHERE factura_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Ahora eliminar la factura
        $sql = "DELETE FROM facturas WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Confirmar transacción
        $conn->commit();
        
        return true;
    } catch (PDOException $e) {
        // En caso de error, revertir cambios
        $conn->rollBack();
        throw $e;
    }
}
?>