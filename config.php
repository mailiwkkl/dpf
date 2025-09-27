<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'galeria_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Galería');
define('BASE_URL', 'http://localhost/p1/');

// Configuración de imágenes
define('UPLOAD_DIR', 'uploads/images/');
define('THEMES_DIR', 'uploads/themes/');
define('WEBP_DIR', 'uploads/webp/');
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('IMAGE_QUALITY', 80);
define('IMAGE_HEIGHT', 350);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Habilitar reporte de errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para generar URLs absolutas
if (!function_exists('base_url')) {
    function base_url($path = '') {
        return BASE_URL . ltrim($path, '/');
    }
}

// Función para verificar y crear directorios si no existen
if (!function_exists('ensure_directories_exist')) {
    function ensure_directories_exist() {
        $directories = [UPLOAD_DIR, THEMES_DIR, WEBP_DIR];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
                // Crear archivo .htaccess para protección
                file_put_contents($dir . '.htaccess', "Order deny,allow\nDeny from all");
                // Crear archivo index.html para protección
                file_put_contents($dir . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden</h1></body></html>');
            }
        }
    }
}

// Asegurar que los directorios existan al incluir config.php
ensure_directories_exist();
?>