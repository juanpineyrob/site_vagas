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

// Obtener todas las vacantes para el filtro
$stmt = $pdo->query("SELECT v.id, v.titulo, c.nombre as categoria_nombre FROM vacantes v JOIN categorias c ON v.categoria_id = c.id ORDER BY v.titulo");
$vacantes = $stmt->fetchAll();

// Obtener candidatos por vacante
$vacante_id = isset($_GET['vacante_id']) ? (int)$_GET['vacante_id'] : 0;
$candidatos = [];

if ($vacante_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nombre, u.email, u.imagen, u.linkedin, v.titulo as vacante_titulo
        FROM candidaturas c 
        JOIN usuarios u ON c.candidato_id = u.id 
        JOIN vacantes v ON c.vacante_id = v.id 
        WHERE c.vacante_id = ? 
        ORDER BY c.fecha_aplicacion DESC
    ");
    $stmt->execute([$vacante_id]);
    $candidatos = $stmt->fetchAll();
}

// Actualizar estado de candidatura
if (isset($_POST['update_estado'])) {
    $candidatura_id = (int)$_POST['candidatura_id'];
    $estado = cleanInput($_POST['estado']);
    
    $stmt = $pdo->prepare("UPDATE candidaturas SET estado = ? WHERE id = ?");
    if ($stmt->execute([$estado, $candidatura_id])) {
        $success = 'Estado de la candidatura actualizado correctamente.';
        // Recargar candidatos
        if ($vacante_id) {
            $stmt = $pdo->prepare("
                SELECT c.*, u.nombre, u.email, u.imagen, u.linkedin, v.titulo as vacante_titulo
                FROM candidaturas c 
                JOIN usuarios u ON c.candidato_id = u.id 
                JOIN vacantes v ON c.vacante_id = v.id 
                WHERE c.vacante_id = ? 
                ORDER BY c.fecha_aplicacion DESC
            ");
            $stmt->execute([$vacante_id]);
            $candidatos = $stmt->fetchAll();
        }
    } else {
        $error = 'Error al actualizar el estado.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Candidatos - Admin TalentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-user-tie me-2"></i>Gestionar Candidatos</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Filtro de vacantes -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-filter me-2"></i>Seleccionar Vacante
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <select name="vacante_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Selecciona una vacante para ver candidatos</option>
                            <?php foreach ($vacantes as $v): ?>
                                <option value="<?php echo $v['id']; ?>" 
                                        <?php echo ($vacante_id == $v['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($v['titulo']); ?> 
                                    (<?php echo htmlspecialchars($v['categoria_nombre']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Buscar Candidatos
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($vacante_id && !empty($candidatos)): ?>
            <!-- Lista de candidatos -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users me-2"></i>Candidatos para: <?php echo htmlspecialchars($candidatos[0]['vacante_titulo']); ?>
                    <span class="badge bg-light text-dark ms-2"><?php echo count($candidatos); ?> candidatos</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($candidatos as $candidato): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="candidatura-card">
                                    <div class="d-flex align-items-center mb-3">
                                        <?php if ($candidato['imagen']): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($candidato['imagen']); ?>" 
                                                 alt="Foto" class="profile-image-small me-3">
                                        <?php else: ?>
                                            <div class="profile-image-small me-3 bg-secondary d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($candidato['nombre']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($candidato['email']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Fecha de aplicación:</strong><br>
                                        <small class="text-muted">
                                            <?php echo formatDate($candidato['fecha_aplicacion']); ?>
                                        </small>
                                    </div>

                                    <?php if ($candidato['linkedin']): ?>
                                        <div class="mb-3">
                                            <a href="<?php echo htmlspecialchars($candidato['linkedin']); ?>" 
                                               target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fab fa-linkedin me-1"></i>Ver LinkedIn
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <strong>Estado actual:</strong>
                                        <span class="badge bg-<?php echo getCandidaturaStatus($candidato['estado']); ?> ms-2">
                                            <?php echo htmlspecialchars($candidato['estado']); ?>
                                        </span>
                                    </div>

                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="candidatura_id" value="<?php echo $candidato['id']; ?>">
                                        <select name="estado" class="form-select form-select-sm">
                                            <option value="Pendiente" <?php echo ($candidato['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="Revisada" <?php echo ($candidato['estado'] == 'Revisada') ? 'selected' : ''; ?>>Revisada</option>
                                            <option value="Aceptada" <?php echo ($candidato['estado'] == 'Aceptada') ? 'selected' : ''; ?>>Aceptada</option>
                                            <option value="Rechazada" <?php echo ($candidato['estado'] == 'Rechazada') ? 'selected' : ''; ?>>Rechazada</option>
                                        </select>
                                        <button type="submit" name="update_estado" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($vacante_id): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                No hay candidatos para esta vacante.
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al panel
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 