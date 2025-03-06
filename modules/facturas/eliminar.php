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

// Intentar eliminar la factura
try {
    $resultado = eliminarFactura($id);
    
    if ($resultado) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensaje' => 'La factura ha sido eliminada correctamente'
        ];
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'No se pudo eliminar la factura'
        ];
    }
} catch (PDOException $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Error al eliminar la factura: ' . $e->getMessage()
    ];
}

// Redirigir a la lista de facturas
header('Location: index.php');
exit;
?>