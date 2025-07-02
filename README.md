# Sistema de Ofertas de Empleo - TalentHub

Un sistema completo para gestionar ofertas de empleo con funcionalidades para administradores y usuarios regulares.

## Características

### Para Administradores:
- ✅ Panel de administración con estadísticas
- ✅ Gestión completa de categorías (CRUD)
- ✅ Gestión de vacantes (crear, activar/desactivar)
- ✅ Visualización de candidatos por vacante
- ✅ Gestión de estados de candidaturas

### Para Usuarios Regulares:
- ✅ Registro con foto de perfil y LinkedIn
- ✅ Inicio de sesión
- ✅ Exploración de vacantes activas
- ✅ Filtrado por categorías
- ✅ Aplicación a vacantes
- ✅ Perfil personal con historial de candidaturas

## Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- XAMPP, WAMP, o similar

## Instalación

1. **Clonar o descargar el proyecto** en tu directorio web:
   ```
   /xampp/htdocs/site_vagas/
   ```

2. **Crear la base de datos**:
   - Abre phpMyAdmin
   - Crea una nueva base de datos llamada `talenthub`
   - Importa el archivo `database.sql`

3. **Configurar la conexión**:
   - Edita `config/database.php` si necesitas cambiar las credenciales
   - Por defecto usa: usuario `root`, contraseña vacía

4. **Permisos de directorio**:
   - Crea el directorio `uploads/` en la raíz del proyecto
   - Asegúrate de que tenga permisos de escritura

5. **Acceder al sistema**:
   - Abre tu navegador y ve a `http://localhost/site_vagas/`

## Credenciales de Prueba

### Administrador:
- **Email:** admin@talenthub.com
- **Contraseña:** password

### Usuario Regular:
- **Email:** maria@techcorp.com
- **Contraseña:** password

## Estructura del Proyecto

```
site_vagas/
├── admin/
│   ├── dashboard.php      # Panel principal de administración
│   ├── categorias.php     # Gestión de categorías
│   ├── vacantes.php       # Gestión de vacantes
│   ├── candidatos.php     # Visualización de candidatos
│   └── navbar.php         # Navegación del admin
├── assets/
│   └── css/
│       └── style.css      # Estilos personalizados
├── config/
│   └── database.php       # Configuración de base de datos
├── includes/
│   └── functions.php      # Funciones auxiliares
├── uploads/               # Directorio para imágenes
├── database.sql           # Script de base de datos
├── index.php              # Página principal
├── login.php              # Inicio de sesión
├── register.php           # Registro de usuarios
├── logout.php             # Cierre de sesión
├── vacante.php            # Detalle de vacante
├── perfil.php             # Perfil de usuario
└── README.md              # Este archivo
```

## Funcionalidades Principales

### 1. Gestión de Categorías
- Crear nuevas categorías de empleo
- Editar nombres de categorías existentes
- Eliminar categorías (con validación)

### 2. Gestión de Vacantes
- Crear vacantes con información completa
- Activar/desactivar vacantes
- Asignar categorías desde un select dinámico
- Información realista: salario, ubicación, tipo de contrato

### 3. Sistema de Candidaturas
- Los usuarios pueden aplicar a vacantes activas
- Prevención de aplicaciones duplicadas
- Estados de candidatura: Pendiente, Revisada, Aceptada, Rechazada
- Visualización de candidatos con foto, email y LinkedIn

### 4. Interfaz de Usuario
- Diseño moderno y responsive con Bootstrap 5
- Navegación intuitiva
- Filtros por categoría
- Cards con efectos hover
- Iconografía con Font Awesome

## Base de Datos

El sistema incluye 4 tablas principales:

- **usuarios**: Información de usuarios y administradores
- **categorias**: Categorías de empleo
- **vacantes**: Ofertas de trabajo
- **candidaturas**: Aplicaciones de usuarios a vacantes

## Seguridad

- Contraseñas hasheadas con `password_hash()`
- Validación de entrada de datos
- Prevención de SQL injection con prepared statements
- Control de acceso basado en roles
- Sanitización de datos de salida

## Personalización

### Estilos CSS
Los estilos están en `assets/css/style.css` y incluyen:
- Gradientes modernos
- Efectos hover en cards
- Diseño responsive
- Paleta de colores personalizada

### Configuración
- Cambiar credenciales de BD en `config/database.php`
- Modificar categorías iniciales en `database.sql`
- Ajustar límites de subida de archivos en `includes/functions.php`

## Soporte

Para reportar problemas o solicitar mejoras:
1. Verifica que todos los requisitos estén cumplidos
2. Revisa los logs de error de PHP
3. Confirma que la base de datos esté correctamente configurada

## Licencia

Este proyecto es de uso libre para fines educativos y comerciales.

---

**EmpleosPro** - Conectando talento con oportunidades 