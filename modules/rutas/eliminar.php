<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'ID de ruta no válido'
    ];
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Intentar eliminar la ruta
try {
    $resultado = eliminarRuta($id);
    
    if ($resultado) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensaje' => 'La ruta ha sido eliminada correctamente'
        ];
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'No se pudo eliminar la ruta'
        ];
    }
} catch (PDOException $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Error al eliminar la ruta: ' . $e->getMessage()
    ];
}

// Redirigir a la lista de rutas
header('Location: index.php');
exit;
?>