<?php
/**
 * Script de inicialización del sistema
 * Ejecutar este script una vez después de subir los archivos al servidor
 */

// Directorios a crear
$directories = [
    'uploads/images/',
    'uploads/themes/',
    'uploads/webp/'
];

// Crear cada directorio si no existe
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Directorio creado: $dir<br>";
            
            // Crear archivo .htaccess para protección
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($dir . '.htaccess', $htaccess_content);
            echo "Archivo .htaccess creado en: $dir<br>";
            
        } else {
            echo "Error al crear directorio: $dir<br>";
        }
    } else {
        echo "El directorio ya existe: $dir<br>";
    }
}

// Verificar permisos de escritura
echo "<h2>Verificación de permisos:</h2>";
foreach ($directories as $dir) {
    if (is_writable($dir)) {
        echo "El directorio $dir tiene permisos de escritura ✓<br>";
    } else {
        echo "El directorio $dir NO tiene permisos de escritura ✗<br>";
        echo "Ejecuta: chmod 755 $dir<br>";
    }
}

// Crear archivo index.html en cada directorio para protección
$index_content = "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden</h1></body></html>";

foreach ($directories as $dir) {
    file_put_contents($dir . 'index.html', $index_content);
    echo "Archivo index.html creado en: $dir<br>";
}

echo "<h2>Inicialización completada. Ahora puedes usar el sistema.</h2>";
echo "<p><a href='login.php'>Ir al login</a></p>";
?>