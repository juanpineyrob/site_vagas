<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

// Obtener ID de la vacante
$vacante_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vacante_id) {
    header('Location: index.php');
    exit();
}

// Obtener datos de la vacante
$stmt = $pdo->prepare("
    SELECT v.*, c.nombre as categoria_nombre, u.nombre as empresa_nombre, u.linkedin as empresa_linkedin
    FROM vacantes v 
    JOIN categorias c ON v.categoria_id = c.id 
    JOIN usuarios u ON v.usuario_id = u.id 
    WHERE v.id = ? AND v.activa = 1
");
$stmt->execute([$vacante_id]);
$vacante = $stmt->fetch();

if (!$vacante) {
    header('Location: index.php');
    exit();
}

// Procesar aplicación si el usuario está logueado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aplicar'])) {
    if (!isLoggedIn()) {
        $error = 'Debes iniciar sesión para aplicar a esta vacante.';
    } else {
        $candidato_id = $_SESSION['user_id'];
        
        // Verificar si ya aplicó
        if (hasApplied($pdo, $vacante_id, $candidato_id)) {
            $error = 'Ya has aplicado a esta vacante.';
        } else {
            // Insertar candidatura
            $stmt = $pdo->prepare("INSERT INTO candidaturas (vacante_id, candidato_id) VALUES (?, ?)");
            if ($stmt->execute([$vacante_id, $candidato_id])) {
                $success = '¡Tu aplicación ha sido enviada exitosamente!';
            } else {
                $error = 'Error al enviar la aplicación.';
            }
        }
    }
}

// Verificar si el usuario actual ya aplicó
$ya_aplico = false;
if (isLoggedIn()) {
    $ya_aplico = hasApplied($pdo, $vacante_id, $_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vacante['titulo']); ?> - TalentHub</title>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Panel Admin</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="perfil.php">Mi Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Header de la vacante -->
        <div class="vacante-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($vacante['categoria_nombre']); ?></span>
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($vacante['titulo']); ?></h1>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-building me-2 text-muted"></i>
                        <span class="text-muted"><?php echo htmlspecialchars($vacante['empresa_nombre']); ?></span>
                        <?php if ($vacante['empresa_linkedin']): ?>
                            <a href="<?php echo htmlspecialchars($vacante['empresa_linkedin']); ?>" target="_blank" class="ms-2">
                                <i class="fab fa-linkedin text-primary"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            <span class="text-muted"><?php echo htmlspecialchars($vacante['ubicacion']); ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock me-2 text-muted"></i>
                            <span class="text-muted"><?php echo htmlspecialchars($vacante['tipo_contrato']); ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar me-2 text-muted"></i>
                            <span class="text-muted"><?php echo formatDate($vacante['fecha_creacion']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="text-success fw-bold fs-4 mb-3">
                        <?php echo formatSalary($vacante['salario']); ?>
                    </div>
                    <?php if (isLoggedIn() && $_SESSION['user_role'] != 'admin'): ?>
                        <?php if ($ya_aplico): ?>
                            <button class="btn btn-success btn-lg" disabled>
                                <i class="fas fa-check me-2"></i>Ya aplicaste
                            </button>
                        <?php else: ?>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="aplicar" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Aplicar ahora
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Inicia sesión para aplicar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Información detallada -->
        <div class="row">
            <div class="col-lg-8">
                <div class="vacante-info">
                    <h4><i class="fas fa-file-alt me-2"></i>Descripción del puesto</h4>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($vacante['descripcion'])); ?></p>
                </div>

                <?php if ($vacante['experiencia']): ?>
                <div class="vacante-info">
                    <h4><i class="fas fa-briefcase me-2"></i>Experiencia requerida</h4>
                    <p><?php echo htmlspecialchars($vacante['experiencia']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del puesto</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong><i class="fas fa-money-bill-wave me-2"></i>Salario:</strong>
                            <div class="text-success fw-bold"><?php echo formatSalary($vacante['salario']); ?></div>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-map-marker-alt me-2"></i>Ubicación:</strong>
                            <div><?php echo htmlspecialchars($vacante['ubicacion']); ?></div>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-clock me-2"></i>Tipo de contrato:</strong>
                            <div><?php echo htmlspecialchars($vacante['tipo_contrato']); ?></div>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-tag me-2"></i>Categoría:</strong>
                            <div><?php echo htmlspecialchars($vacante['categoria_nombre']); ?></div>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-calendar me-2"></i>Fecha de publicación:</strong>
                            <div><?php echo formatDate($vacante['fecha_creacion']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Empresa</h5>
                    </div>
                    <div class="card-body text-center">
                        <h6><?php echo htmlspecialchars($vacante['empresa_nombre']); ?></h6>
                        <?php if ($vacante['empresa_linkedin']): ?>
                            <a href="<?php echo htmlspecialchars($vacante['empresa_linkedin']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-linkedin me-2"></i>Ver en LinkedIn
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a las vacantes
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 