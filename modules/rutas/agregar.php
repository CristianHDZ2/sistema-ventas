<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $es_big_cola = isset($_POST['es_big_cola']) ? 1 : 0;
    
    // Validar datos
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre de la ruta es obligatorio";
    }
    
    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // Agregar la ruta
            $result = agregarRuta($nombre, $es_big_cola);
            
            if ($result) {
                // Ruta agregada con éxito, redirigir a la lista de rutas
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'La ruta se ha agregado correctamente'
                ];
                
                header('Location: index.php');
                exit;
            } else {
                $errores[] = "No se pudo agregar la ruta";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al agregar la ruta: " . $e->getMessage();
        }
    }
}

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Agregar Ruta</h1>
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
        
        <form action="agregar.php" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Ruta</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="es_big_cola" name="es_big_cola" <?php echo (isset($_POST['es_big_cola'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="es_big_cola">¿Es ruta de Big Cola?</label>
                <div class="form-text">Si marca esta opción, la ruta solo mostrará productos de Big Cola para ventas.</div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Ruta
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>