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

// Obtener la información del producto
$producto = obtenerProductoPorId($id);

if (!$producto) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Producto no encontrado'
    ];
    header('Location: index.php');
    exit;
}

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    
    // Validar datos
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre del producto es obligatorio";
    }
    
    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor que cero";
    }
    
    if ($categoria_id <= 0) {
        $errores[] = "Debes seleccionar una categoría válida";
    }
    
    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // Actualizar el producto
            $result = actualizarProducto($id, $nombre, $precio, $categoria_id);
            
            if ($result) {
                // Producto actualizado con éxito, redirigir a la lista de productos
                $_SESSION['alerta'] = [
                    'tipo' => 'success',
                    'mensaje' => 'El producto se ha actualizado correctamente'
                ];
                
                header('Location: index.php');
                exit;
            } else {
                $errores[] = "No se pudo actualizar el producto";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar el producto: " . $e->getMessage();
        }
    }
}

// Obtener las categorías para el formulario
$categorias = obtenerCategorias();

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Editar Producto</h1>
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
                <label for="nombre" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required 
                    value="<?php echo htmlspecialchars($_POST['nombre'] ?? $producto['nombre']); ?>">
            </div>
            
            <div class="mb-3">
                <label for="precio" class="form-label">Precio</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0.01" required 
                        value="<?php echo htmlspecialchars($_POST['precio'] ?? $producto['precio']); ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select class="form-select" id="categoria_id" name="categoria_id" required>
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>" 
                        <?php echo ((isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['id']) || 
                            (!isset($_POST['categoria_id']) && $producto['categoria_id'] == $categoria['id'])) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>