-- Crear base de datos
CREATE DATABASE IF NOT EXISTS talenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE talenthub;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    imagen VARCHAR(255),
    linkedin VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla de vacantes
CREATE TABLE vacantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    salario DECIMAL(10,2) NOT NULL,
    ubicacion VARCHAR(100),
    tipo_contrato ENUM('Tiempo completo', 'Tiempo parcial', 'Contrato', 'Freelance') DEFAULT 'Tiempo completo',
    experiencia VARCHAR(100),
    categoria_id INT NOT NULL,
    usuario_id INT NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de candidaturas
CREATE TABLE candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vacante_id INT NOT NULL,
    candidato_id INT NOT NULL,
    fecha_aplicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente', 'Revisada', 'Aceptada', 'Rechazada') DEFAULT 'Pendiente',
    FOREIGN KEY (vacante_id) REFERENCES vacantes(id),
    FOREIGN KEY (candidato_id) REFERENCES usuarios(id),
    UNIQUE KEY unique_candidatura (vacante_id, candidato_id)
);

-- Insertar categorías iniciales
INSERT INTO categorias (nombre) VALUES 
('Desarrollo Web'),
('Desarrollo Móvil'),
('Diseño Gráfico'),
('Marketing Digital'),
('Ventas'),
('Recursos Humanos'),
('Administración'),
('Contabilidad'),
('Ingeniería'),
('Educación'),
('Salud'),
('Logística'),
('Atención al Cliente'),
('Investigación'),
('Legal');

-- Insertar usuario administrador
INSERT INTO usuarios (nombre, email, password, role) VALUES 
('Administrador', 'admin@talenthub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar algunos usuarios de ejemplo
INSERT INTO usuarios (nombre, email, password, linkedin, role) VALUES 
('María González', 'maria@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://linkedin.com/company/techcorp', 'user'),
('Carlos Rodríguez', 'carlos@innovatech.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://linkedin.com/company/innovatech', 'user'),
('Ana Martínez', 'ana@digitalmarketing.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://linkedin.com/company/digitalmarketing', 'user');

-- Insertar vacantes de ejemplo
INSERT INTO vacantes (titulo, descripcion, salario, ubicacion, tipo_contrato, experiencia, categoria_id, usuario_id) VALUES 
('Desarrollador Full Stack Senior', 'Buscamos un desarrollador Full Stack con experiencia en PHP, JavaScript, MySQL y frameworks modernos. Responsabilidades incluyen desarrollo de aplicaciones web, mantenimiento de sistemas existentes y colaboración con el equipo de diseño.', 45000, 'Madrid', 'Tiempo completo', '3-5 años', 1, 2),
('Diseñador UX/UI', 'Necesitamos un diseñador creativo para crear interfaces de usuario atractivas y funcionales. Experiencia con Figma, Adobe Creative Suite y metodologías de diseño centrado en el usuario.', 35000, 'Barcelona', 'Tiempo completo', '2-4 años', 3, 2),
('Especialista en Marketing Digital', 'Responsable de estrategias de marketing digital, gestión de redes sociales, campañas publicitarias y análisis de métricas. Experiencia con Google Ads, Facebook Ads y herramientas de analytics.', 32000, 'Valencia', 'Tiempo completo', '1-3 años', 4, 3),
('Vendedor Senior', 'Buscamos un vendedor experimentado para expandir nuestro mercado. Responsabilidades incluyen prospección de clientes, presentaciones comerciales y seguimiento de ventas.', 28000, 'Sevilla', 'Tiempo completo', '2-5 años', 5, 4),
('Desarrollador React Native', 'Desarrollador móvil especializado en React Native para crear aplicaciones multiplataforma. Experiencia con JavaScript, TypeScript y APIs REST.', 40000, 'Bilbao', 'Tiempo completo', '2-4 años', 2, 2),
('Contador Público', 'Responsable de la contabilidad general, preparación de estados financieros, declaraciones fiscales y asesoramiento financiero a la empresa.', 30000, 'Málaga', 'Tiempo completo', '3-6 años', 8, 4),
('Ingeniero de Software', 'Desarrollo de software empresarial, arquitectura de sistemas, optimización de rendimiento y colaboración en proyectos de innovación tecnológica.', 50000, 'Zaragoza', 'Tiempo completo', '4-7 años', 1, 2),
('Profesor de Programación', 'Impartir clases de programación en Python, Java y desarrollo web. Preparar material didáctico y evaluar el progreso de los estudiantes.', 25000, 'Granada', 'Tiempo completo', '1-3 años', 10, 3); 