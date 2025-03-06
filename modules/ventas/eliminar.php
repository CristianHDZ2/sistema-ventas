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

// Intentar eliminar la venta
try {
    $resultado = eliminarVenta($id);
    
    if ($resultado) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensaje' => 'La venta ha sido eliminada correctamente'
        ];
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'No se pudo eliminar la venta'
        ];
    }
} catch (PDOException $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Error al eliminar la venta: ' . $e->getMessage()
    ];
}

// Redirigir a la lista de ventas
header('Location: listar.php');
exit;
?>