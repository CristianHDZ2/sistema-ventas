<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'ID de producto no válido'
    ];
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Intentar eliminar el producto
try {
    $resultado = eliminarProducto($id);
    
    if ($resultado) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensaje' => 'El producto ha sido eliminado correctamente'
        ];
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'No se pudo eliminar el producto'
        ];
    }
} catch (PDOException $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Error al eliminar el producto: ' . $e->getMessage()
    ];
}

// Redirigir a la lista de productos
header('Location: index.php');
exit;
?>