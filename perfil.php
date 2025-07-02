<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Procesar actualización de foto de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_foto'])) {
    if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] == 0) {
        $nueva_foto = uploadImage($_FILES['nueva_foto']);
        if ($nueva_foto) {
            // Eliminar foto anterior si existe
            $stmt = $pdo->prepare("SELECT imagen FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $usuario_actual = $stmt->fetch();
            
            if ($usuario_actual['imagen']) {
                deleteProfileImage($usuario_actual['imagen']);
            }
            
            // Actualizar en la base de datos
            $stmt = $pdo->prepare("UPDATE usuarios SET imagen = ? WHERE id = ?");
            if ($stmt->execute([$nueva_foto, $_SESSION['user_id']])) {
                $_SESSION['user_image'] = $nueva_foto;
                $success = 'Foto de perfil actualizada exitosamente.';
            } else {
                $error = 'Error al actualizar la foto de perfil.';
            }
        } else {
            $error = 'Error al subir la imagen. Verifica el formato y tamaño.';
        }
    } else {
        $error = 'Por favor, selecciona una imagen.';
    }
}

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

// Obtener candidaturas del usuario
$stmt = $pdo->prepare("
    SELECT c.*, v.titulo as vacante_titulo, v.salario, v.ubicacion, cat.nombre as categoria_nombre
    FROM candidaturas c 
    JOIN vacantes v ON c.vacante_id = v.id 
    JOIN categorias cat ON v.categoria_id = cat.id 
    WHERE c.candidato_id = ? 
    ORDER BY c.fecha_aplicacion DESC
");
$stmt->execute([$_SESSION['user_id']]);
$candidaturas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - TalentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Logo a la izquierda -->
            <a class="navbar-brand" href="index.php">
                TalentHub
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Enlaces a la derecha -->
                <ul class="navbar-nav ms-auto">
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Panel Admin</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="perfil.php">Mi Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Perfil del usuario -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Mi Perfil</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($usuario['imagen']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($usuario['imagen']); ?>" 
                                 alt="Foto de perfil" class="profile-image mb-3">
                        <?php else: ?>
                            <div class="profile-image mb-3 bg-secondary d-flex align-items-center justify-content-center mx-auto">
                                <i class="fas fa-user text-white" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h5><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
                        <p class="text-muted mb-3">
                            <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($usuario['email']); ?>
                        </p>
                        
                        <?php if ($usuario['linkedin']): ?>
                            <a href="<?php echo htmlspecialchars($usuario['linkedin']); ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm mb-3">
                                <i class="fab fa-linkedin me-2"></i>Ver LinkedIn
                            </a>
                        <?php endif; ?>
                        
                        <!-- Formulario para actualizar foto -->
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-sm btn-cambiar-foto" data-bs-toggle="modal" data-bs-target="#actualizarFotoModal">
                                <i class="fas fa-camera me-2"></i>Cambiar Foto
                            </button>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Miembro desde: <?php echo formatDate($usuario['fecha_registro']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mis candidaturas -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Mis Candidaturas
                            <span class="badge bg-light text-dark ms-2"><?php echo count($candidaturas); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($candidaturas)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No tienes candidaturas aún</h5>
                                <p class="text-muted">Explora las vacantes disponibles y aplica a las que te interesen.</p>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Ver Vacantes
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($candidaturas as $candidatura): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="candidatura-card">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($candidatura['vacante_titulo']); ?></h6>
                                                <span class="badge bg-<?php echo getCandidaturaStatus($candidatura['estado']); ?>">
                                                    <?php echo htmlspecialchars($candidatura['estado']); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo htmlspecialchars($candidatura['categoria_nombre']); ?>
                                            </p>
                                            
                                            <div class="row text-muted small mb-2">
                                                <div class="col-6">
                                                    <i class="fas fa-money-bill-wave me-1"></i>
                                                    <?php echo formatSalary($candidatura['salario']); ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($candidatura['ubicacion']); ?>
                                                </div>
                                            </div>
                                            
                                            <div class="text-muted small">
                                                <i class="fas fa-calendar me-1"></i>
                                                Aplicaste el: <?php echo formatDate($candidatura['fecha_aplicacion']); ?>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <a href="vacante.php?id=<?php echo $candidatura['vacante_id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>Ver Vacante
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para actualizar foto de perfil -->
    <div class="modal fade" id="actualizarFotoModal" tabindex="-1" aria-labelledby="actualizarFotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="actualizarFotoModalLabel">
                        <i class="fas fa-camera me-2"></i>Actualizar Foto de Perfil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nueva_foto" class="form-label">
                                <i class="fas fa-image me-2"></i>Seleccionar nueva imagen
                            </label>
                            <input type="file" class="form-control" id="nueva_foto" name="nueva_foto" accept="image/*" required>
                            <div class="form-text">
                                Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Consejo:</strong> Para mejores resultados, usa una imagen cuadrada de al menos 200x200 píxeles.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" name="actualizar_foto" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Actualizar Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mejorar la experiencia de usuario al seleccionar imagen
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('nueva_foto');
            const modal = document.getElementById('actualizarFotoModal');
            
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        // Mostrar información del archivo seleccionado
                        const fileInfo = document.createElement('div');
                        fileInfo.className = 'alert alert-info mt-2';
                        fileInfo.innerHTML = `
                            <i class="fas fa-file-image me-2"></i>
                            <strong>Archivo seleccionado:</strong> ${file.name}<br>
                            <small>Tamaño: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                        `;
                        
                        // Remover información anterior si existe
                        const existingInfo = this.parentNode.querySelector('.alert');
                        if (existingInfo) {
                            existingInfo.remove();
                        }
                        
                        this.parentNode.appendChild(fileInfo);
                    }
                });
            }
            
            // Limpiar información cuando se cierre el modal
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function() {
                    const fileInfo = this.querySelector('.alert');
                    if (fileInfo) {
                        fileInfo.remove();
                    }
                    if (fileInput) {
                        fileInput.value = '';
                    }
                });
            }
        });
    </script>
</body>
</html> 