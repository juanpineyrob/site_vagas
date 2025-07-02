<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$stats = getDashboardStats($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - TalentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-cogs me-2"></i>Admin TalentHub
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Ver sitio</a>
                <a class="nav-link" href="../logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Panel de Administración</h2>
        <div class="stats-grid mb-5">
            <div class="dashboard-card text-center">
                <div class="icon text-primary"><i class="fas fa-briefcase"></i></div>
                <div class="number"><?php echo $stats['total_vacantes']; ?></div>
                <div>Total Vacantes</div>
            </div>
            <div class="dashboard-card text-center">
                <div class="icon text-success"><i class="fas fa-check-circle"></i></div>
                <div class="number"><?php echo $stats['vacantes_activas']; ?></div>
                <div>Vacantes Activas</div>
            </div>
            <div class="dashboard-card text-center">
                <div class="icon text-info"><i class="fas fa-users"></i></div>
                <div class="number"><?php echo $stats['total_candidatos']; ?></div>
                <div>Candidatos</div>
            </div>
            <div class="dashboard-card text-center">
                <div class="icon text-warning"><i class="fas fa-file-alt"></i></div>
                <div class="number"><?php echo $stats['total_candidaturas']; ?></div>
                <div>Candidaturas</div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <a href="categorias.php" class="dashboard-card d-block text-decoration-none text-dark text-center">
                    <div class="icon text-primary"><i class="fas fa-tags"></i></div>
                    <h5 class="mt-2">Gestionar Categorías</h5>
                </a>
            </div>
            <div class="col-md-4">
                <a href="vacantes.php" class="dashboard-card d-block text-decoration-none text-dark text-center">
                    <div class="icon text-success"><i class="fas fa-briefcase"></i></div>
                    <h5 class="mt-2">Gestionar Vacantes</h5>
                </a>
            </div>
            <div class="col-md-4">
                <a href="candidatos.php" class="dashboard-card d-block text-decoration-none text-dark text-center">
                    <div class="icon text-info"><i class="fas fa-user-tie"></i></div>
                    <h5 class="mt-2">Ver Candidatos</h5>
                </a>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 