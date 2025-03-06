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

// Obtener la información de la ruta
$ruta = obtenerRutaPorId($id);

if (!$ruta) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Ruta no encontrada'
    ];
    header('Location: index.php');
    exit;
}

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $es_big_cola = isset($_POST['es_big_cola']) ? 1 : 0;
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    // Validar datos
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre de la ruta es obligatorio";
    }
    
    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // Actualizar la ruta
            $result = actualizarRuta($id, $nombre, $es_big_cola, $activa);
            
            if ($result) {
                // Ruta actualizada con éxito, redirigir a la lista de rutas
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'La ruta se ha actualizado correctamente'
                ];
                
                header('Location: index.php');
                exit;
            } else {
                $errores[] = "No se pudo actualizar la ruta";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar la ruta: " . $e->getMessage();
        }
    }
}

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Editar Ruta</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body form-container">
        <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form action="editar.php?id=<?php echo $id; ?>" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Ruta</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required 
                    value="<?php echo htmlspecialchars($_POST['nombre'] ?? $ruta['nombre']); ?>">
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="es_big_cola" name="es_big_cola" 
                    <?php echo ((isset($_POST['es_big_cola']) && $_POST['es_big_cola']) || 
                        (!isset($_POST['es_big_cola']) && $ruta['es_big_cola'] == 1)) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="es_big_cola">¿Es ruta de Big Cola?</label>
                <div class="form-text">Si marca esta opción, la ruta solo mostrará productos de Big Cola para ventas.</div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="activa" name="activa" 
                    <?php echo ((isset($_POST['activa']) && $_POST['activa']) || 
                        (!isset($_POST['activa']) && $ruta['activa'] == 1)) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="activa">¿Ruta activa?</label>
                <div class="form-text">Desmarque esta opción para desactivar temporalmente la ruta sin eliminarla.</div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Actualizar Ruta
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>