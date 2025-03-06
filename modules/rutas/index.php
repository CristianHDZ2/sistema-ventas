<?php
// Iniciar sesión para mensajes de alerta
session_start();

// Incluir los archivos de funciones
require_once '../../includes/functions.php';

// Obtener la lista de rutas
$rutas = obtenerRutas();

// Incluir header
include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Lista de Rutas</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="agregar.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Agregar Ruta
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rutas)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay rutas registradas</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($rutas as $index => $ruta): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($ruta['nombre']); ?></td>
                            <td>
                                <?php if ($ruta['es_big_cola'] == 1): ?>
                                <span class="badge bg-primary">Big Cola</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">General</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ruta['activa'] == 1): ?>
                                <span class="badge bg-success">Activa</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="editar.php?id=<?php echo $ruta['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <a href="eliminar.php?id=<?php echo $ruta['id']; ?>" class="btn btn-sm btn-danger confirmar-eliminar">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>