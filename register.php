<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $linkedin = cleanInput($_POST['linkedin']);
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos obligatorios deben estar completos.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Este email ya está registrado.';
        } else {
            // Procesar imagen si se subió
            $imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $imagen = uploadImage($_FILES['imagen']);
                if (!$imagen) {
                    $error = 'Error al subir la imagen. Verifica el formato y tamaño.';
                }
            }
            
            if (empty($error)) {
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, imagen, linkedin, role) VALUES (?, ?, ?, ?, ?, 'user')");
                
                if ($stmt->execute([$nombre, $email, $hashed_password, $imagen, $linkedin])) {
                    $success = 'Registro exitoso. Ya puedes iniciar sesión.';
                } else {
                    $error = 'Error al registrar el usuario.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - TalentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Logo a la izquierda -->
            <a class="navbar-brand" href="index.php">
                TalentHub
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">Iniciar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Registro de Usuario
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <br>
                                <a href="login.php" class="btn btn-primary btn-sm mt-2">Ir a Iniciar Sesión</a>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nombre completo *
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Contraseña *
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">Mínimo 6 caracteres</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmar contraseña *
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="linkedin" class="form-label">
                                    <i class="fab fa-linkedin me-2"></i>Perfil de LinkedIn
                                </label>
                                <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                       value="<?php echo isset($_POST['linkedin']) ? htmlspecialchars($_POST['linkedin']) : ''; ?>" 
                                       placeholder="https://linkedin.com/in/tu-perfil">
                            </div>

                            <div class="mb-4">
                                <label for="imagen" class="form-label">
                                    <i class="fas fa-image me-2"></i>Foto de perfil
                                </label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                <div class="form-text">Formatos: JPG, PNG, GIF. Máximo 5MB</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Registrarse
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">¿Ya tienes una cuenta?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 