<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Insertar categoría
if (isset($_POST['add_categoria'])) {
    $nombre = cleanInput($_POST['nombre']);
    if (empty($nombre)) {
        $error = 'El nombre de la categoría es obligatorio.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ?");
        $stmt->execute([$nombre]);
        if ($stmt->fetch()) {
            $error = 'Ya existe una categoría con ese nombre.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
            if ($stmt->execute([$nombre])) {
                $success = 'Categoría agregada correctamente.';
            } else {
                $error = 'Error al agregar la categoría.';
            }
        }
    }
}

// Editar categoría
if (isset($_POST['edit_categoria'])) {
    $id = (int)$_POST['categoria_id'];
    $nombre = cleanInput($_POST['nombre']);
    if (empty($nombre)) {
        $error = 'El nombre de la categoría es obligatorio.';
    } else {
        $stmt = $pdo->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
        if ($stmt->execute([$nombre, $id])) {
            $success = 'Categoría actualizada correctamente.';
        } else {
            $error = 'Error al actualizar la categoría.';
        }
    }
}

// Eliminar categoría
if (isset($_POST['delete_categoria'])) {
    $id = (int)$_POST['categoria_id'];
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Categoría eliminada correctamente.';
    } else {
        $error = 'Error al eliminar la categoría.';
    }
}

// Obtener todas las categorías
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Categorías - Admin TalentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-tags me-2"></i>Gestionar Categorías</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plus me-2"></i>Agregar nueva categoría
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre de la categoría" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="add_categoria" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Agregar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-list me-2"></i>Listado de categorías
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td>
                                <form method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="categoria_id" value="<?php echo $cat['id']; ?>">
                                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($cat['nombre']); ?>" class="form-control form-control-sm" required>
                                    <button type="submit" name="edit_categoria" class="btn btn-sm btn-success"><i class="fas fa-save"></i></button>
                                    <button type="submit" name="delete_categoria" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta categoría?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al panel</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 