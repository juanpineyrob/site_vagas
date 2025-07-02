<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Obtener categorías para el filtro
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();

// Obtener vacantes activas
$where = "WHERE v.activa = 1";
$params = [];

if (isset($_GET['categoria']) && $_GET['categoria'] != '') {
    $where .= " AND v.categoria_id = ?";
    $params[] = $_GET['categoria'];
}

$sql = "SELECT v.*, c.nombre as categoria_nombre, u.nombre as empresa_nombre 
        FROM vacantes v 
        JOIN categorias c ON v.categoria_id = c.id 
        JOIN usuarios u ON v.usuario_id = u.id 
        $where 
        ORDER BY v.fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacantes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentHub - Sistema de Ofertas de Empleo</title>
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

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Encuentra tu trabajo ideal
                    </h1>
                    <p class="lead text-white mb-4">
                        Miles de oportunidades laborales te esperan. Conecta con las mejores empresas y encuentra tu próxima carrera profesional.
                    </p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Vacantes -->
    <div class="container my-5">
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <select name="categoria" class="form-select me-2">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id']; ?>" 
                                    <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $categoria['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <p class="text-muted mb-0">
                    <?php echo count($vacantes); ?> vacantes encontradas
                </p>
            </div>
        </div>

        <!-- Lista de Vacantes -->
        <div class="row">
            <?php if (empty($vacantes)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No se encontraron vacantes con los filtros seleccionados.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($vacantes as $vacante): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card h-100 shadow-sm hover-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($vacante['categoria_nombre']); ?></span>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($vacante['fecha_creacion'])); ?>
                                    </small>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($vacante['titulo']); ?></h5>
                                <p class="card-text text-muted mb-2">
                                    <i class="fas fa-building me-2"></i>
                                    <?php echo htmlspecialchars($vacante['empresa_nombre']); ?>
                                </p>
                                <p class="card-text">
                                    <?php echo substr(htmlspecialchars($vacante['descripcion']), 0, 150); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-success fw-bold">
                                        $<?php echo number_format($vacante['salario'], 0, ',', '.'); ?>
                                    </span>
                                    <a href="vacante.php?id=<?php echo $vacante['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 