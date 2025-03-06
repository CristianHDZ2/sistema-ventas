<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Verificar si se recibieron IDs para eliminar
if (!isset($_POST['ids']) || !is_array($_POST['ids']) || empty($_POST['ids'])) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'No se seleccionaron facturas para eliminar'
    ];
    
    // Redirigir de vuelta a la lista
    header('Location: index.php');
    exit;
}

// Obtener la fecha para redireccionar después
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');

// Contador de facturas eliminadas y errores
$eliminadas = 0;
$errores = 0;
$mensajes_error = [];

// Intentar eliminar cada factura seleccionada
foreach ($_POST['ids'] as $id) {
    $id = intval($id);
    
    try {
        $resultado = eliminarFactura($id);
        
        if ($resultado) {
            $eliminadas++;
        } else {
            $errores++;
            $mensajes_error[] = "No se pudo eliminar la factura ID: $id";
        }
    } catch (PDOException $e) {
        $errores++;
        $mensajes_error[] = "Error al eliminar la factura ID: $id - " . $e->getMessage();
    }
}

// Preparar mensaje de resultado
if ($eliminadas > 0 && $errores == 0) {
    // Todo fue bien
    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'mensaje' => "Se han eliminado $eliminadas facturas correctamente"
    ];
} elseif ($eliminadas > 0 && $errores > 0) {
    // Algunas se eliminaron, otras no
    $_SESSION['alerta'] = [
        'tipo' => 'warning',
        'mensaje' => "Se eliminaron $eliminadas facturas, pero hubo $errores errores"
    ];
    
    // Agregar detalles de los errores
    if (!empty($mensajes_error)) {
        $_SESSION['alerta']['detalles'] = $mensajes_error;
    }
} else {
    // Ninguna se eliminó
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => "No se pudo eliminar ninguna factura. Ocurrieron $errores errores"
    ];
    
    // Agregar detalles de los errores
    if (!empty($mensajes_error)) {
        $_SESSION['alerta']['detalles'] = $mensajes_error;
    }
}

// Redirigir a la lista de facturas
header("Location: index.php?fecha=$fecha");
exit;
?>