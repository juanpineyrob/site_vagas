<?php
// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Función para subir imagen
function uploadImage($file, $targetDir = 'uploads/') {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Validaciones básicas
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Verificar tipo MIME real del archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    // Verificar extensión del archivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }
    
    // Verificar tamaño (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    // Generar nombre único para el archivo
    $fileName = time() . '_' . uniqid() . '.' . $extension;
    $targetPath = $targetDir . $fileName;
    
    // Intentar mover el archivo
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Verificar que el archivo sea realmente una imagen válida
        if (getimagesize($targetPath) !== false) {
            return $fileName;
        } else {
            // Si no es una imagen válida, eliminar el archivo
            unlink($targetPath);
            return false;
        }
    }
    
    return false;
}

// Función para formatear fecha
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Función para formatear salario
function formatSalary($salary) {
    return '$' . number_format($salary, 0, ',', '.');
}

// Función para obtener el estado de una candidatura
function getCandidaturaStatus($estado) {
    $statusClasses = [
        'Pendiente' => 'warning',
        'Revisada' => 'info',
        'Aceptada' => 'success',
        'Rechazada' => 'danger'
    ];
    
    return $statusClasses[$estado] ?? 'secondary';
}

// Función para limpiar entrada de datos
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para generar mensajes de alerta
function showAlert($message, $type = 'info') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Función para eliminar imagen de perfil de forma segura
function deleteProfileImage($imageName, $uploadDir = 'uploads/') {
    if (empty($imageName)) {
        return false;
    }
    
    $filePath = $uploadDir . $imageName;
    
    // Verificar que el archivo existe y está en el directorio correcto
    if (file_exists($filePath) && is_file($filePath)) {
        // Verificar que es una imagen
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        
        if (in_array($extension, $allowedExtensions)) {
            return unlink($filePath);
        }
    }
    
    return false;
}

// Función para verificar si un usuario ya aplicó a una vacante
function hasApplied($pdo, $vacanteId, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidaturas WHERE vacante_id = ? AND candidato_id = ?");
    $stmt->execute([$vacanteId, $userId]);
    return $stmt->fetchColumn() > 0;
}

// Función para obtener estadísticas del dashboard
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total de vacantes
    $stmt = $pdo->query("SELECT COUNT(*) FROM vacantes");
    $stats['total_vacantes'] = $stmt->fetchColumn();
    
    // Vacantes activas
    $stmt = $pdo->query("SELECT COUNT(*) FROM vacantes WHERE activa = 1");
    $stats['vacantes_activas'] = $stmt->fetchColumn();
    
    // Total de candidatos
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE role = 'user'");
    $stats['total_candidatos'] = $stmt->fetchColumn();
    
    // Total de candidaturas
    $stmt = $pdo->query("SELECT COUNT(*) FROM candidaturas");
    $stats['total_candidaturas'] = $stmt->fetchColumn();
    
    return $stats;
}
?> 