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

// Obtener categorías para el select
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();

// Alta de vacante
if (isset($_POST['add_vacante'])) {
    $titulo = cleanInput($_POST['titulo']);
    $descripcion = cleanInput($_POST['descripcion']);
    $salario = floatval($_POST['salario']);
    $ubicacion = cleanInput($_POST['ubicacion']);
    $tipo_contrato = cleanInput($_POST['tipo_contrato']);
    $experiencia = cleanInput($_POST['experiencia']);
    $categoria_id = intval($_POST['categoria_id']);
    $usuario_id = $_SESSION['user_id'];
    if (empty($titulo) || empty($descripcion) || !$categoria_id) {
        $error = 'Completa todos los campos obligatorios.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO vacantes (titulo, descripcion, salario, ubicacion, tipo_contrato, experiencia, categoria_id, usuario_id, activa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        if ($stmt->execute([$titulo, $descripcion, $salario, $ubicacion, $tipo_contrato, $experiencia, $categoria_id, $usuario_id])) {
            $success = 'Vacante creada correctamente.';
        } else {
            $error = 'Error al crear la vacante.';
        }
    }
}

// Activar/desactivar vacante
if (isset($_POST['toggle_vacante'])) {
    $id = (int)$_POST['vacante_id'];
    $activa = (int)$_POST['activa'];
    $stmt = $pdo->prepare("UPDATE vacantes SET activa = ? WHERE id = ?");
    if ($stmt->execute([$activa, $id])) {
        $success = 'Estado de la vacante actualizado.';
    } else {
        $error = 'Error al actualizar el estado.';
    }
}

// Obtener todas las vacantes
$stmt = $pdo->query("SELECT v.*, c.nombre as categoria_nombre FROM vacantes v JOIN categorias c ON v.categoria_id = c.id ORDER BY v.fecha_creacion DESC");
$vacantes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Vacantes - Admin TalentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-briefcase me-2"></i>Gestionar Vacantes</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plus me-2"></i>Agregar nueva vacante
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="titulo" class="form-control mb-2" placeholder="Título de la vacante" required>
                        <textarea name="descripcion" class="form-control mb-2" placeholder="Descripción" rows="3" required></textarea>
                        <input type="number" name="salario" class="form-control mb-2" placeholder="Salario" min="0" step="100" required>
                        <input type="text" name="ubicacion" class="form-control mb-2" placeholder="Ubicación">
                    </div>
                    <div class="col-md-6">
                        <select name="tipo_contrato" class="form-select mb-2">
                            <option value="Tiempo completo">Tiempo completo</option>
                            <option value="Tiempo parcial">Tiempo parcial</option>
                            <option value="Contrato">Contrato</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                        <input type="text" name="experiencia" class="form-control mb-2" placeholder="Experiencia requerida">
                        <select name="categoria_id" class="form-select mb-2" required>
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="add_vacante" class="btn btn-primary w-100 mt-2">
                            <i class="fas fa-plus me-2"></i>Agregar Vacante
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-list me-2"></i>Listado de vacantes
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Salario</th>
                            <th>Activa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vacantes as $v): ?>
                        <tr>
                            <td><?php echo $v['id']; ?></td>
                            <td><?php echo htmlspecialchars($v['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($v['categoria_nombre']); ?></td>
                            <td><?php echo formatSalary($v['salario']); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="vacante_id" value="<?php echo $v['id']; ?>">
                                    <input type="hidden" name="activa" value="<?php echo $v['activa'] ? 0 : 1; ?>">
                                    <button type="submit" name="toggle_vacante" class="btn btn-sm <?php echo $v['activa'] ? 'btn-success' : 'btn-secondary'; ?>">
                                        <?php echo $v['activa'] ? 'Activa' : 'Inactiva'; ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <!-- Aquí se podría agregar edición avanzada o eliminación -->
                            </td>
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