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
function reporteVentasPorProducto($fecha_inicio, $fecha_fin, $es_big_cola = null) {
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
            JOIN 
                rutas r ON v.ruta_id = r.id
            WHERE 
                v.fecha BETWEEN :fecha_inicio AND :fecha_fin";
    
    if ($es_big_cola !== null) {
        $sql .= " AND r.es_big_cola = :es_big_cola";
    }
    
    $sql .= " GROUP BY p.id, p.nombre, p.precio, c.nombre
              ORDER BY p.nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    
    if ($es_big_cola !== null) {
        $stmt->bindParam(':es_big_cola', $es_big_cola, PDO::PARAM_INT);
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
?>