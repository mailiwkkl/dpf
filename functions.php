<?php
require_once 'db_connection.php';

// Función para redimensionar y convertir imágenes a WebP (con manejo de errores)
function convertToWebP($source, $destination, $quality = 90, $height = 400) {
    // Verificar que GD esté disponible
    if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
        return false;
    }
    
    // Verificar que el archivo fuente existe
    if (!file_exists($source)) {
        return false;
    }
    
    $info = @getimagesize($source);
    if (!$info) {
        return false;
    }
    
    $mime = $info['mime'];
    
    try {
        switch($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                break;
            case 'image/webp':
                // Si ya es WebP, copiar directamente
                return copy($source, $destination);
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        // Calcular nuevas dimensiones manteniendo la relación de aspecto
        $width = imagesx($image);
        $height_orig = imagesy($image);
        $ratio = $width / $height_orig;
        $new_width = round($height * $ratio);
        
        // Crear nueva imagen
        $new_image = imagecreatetruecolor($new_width, $height);
        
        if (!$new_image) {
            imagedestroy($image);
            return false;
        }
        
        // Preservar transparencia para PNG y GIF
        if($mime == 'image/png' || $mime == 'image/gif') {
            imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
        }
        
        // Redimensionar
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $height, $width, $height_orig);
        
        // Guardar como WebP
        $result = imagewebp($new_image, $destination, $quality);
        
        // Liberar memoria
        imagedestroy($image);
        imagedestroy($new_image);
        
        return $result;
        
    } catch (Exception $e) {
        return false;
    }
}

// Función para obtener la configuración del sistema
function getSettings() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM settings LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Función para obtener las imágenes más populares (top 10 favoritas)
function getPopularImages($limit = 10) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT i.*, COUNT(f.id) as favorite_count 
              FROM images i 
              LEFT JOIN favorites f ON i.id = f.image_id 
              GROUP BY i.id 
              ORDER BY favorite_count DESC 
              LIMIT :limit";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener imágenes por temática
function getImagesByTheme($theme_id = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($theme_id) {
        $query = "SELECT i.*, t.name as theme_name 
                  FROM images i 
                  JOIN themes t ON i.theme_id = t.id 
                  WHERE i.theme_id = :theme_id 
                  ORDER BY i.upload_date DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':theme_id', $theme_id, PDO::PARAM_INT);
    } else {
        $query = "SELECT i.*, t.name as theme_name 
                  FROM images i 
                  JOIN themes t ON i.theme_id = t.id 
                  ORDER BY i.upload_date DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener todas las temáticas
function getAllThemes() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM themes ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para verificar si una imagen es favorita del usuario
function isFavorite($user_id, $image_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id FROM favorites WHERE user_id = :user_id AND image_id = :image_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}

// Función para agregar/quitar favorito
function toggleFavorite($user_id, $image_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar si ya es favorito
    if (isFavorite($user_id, $image_id)) {
        // Eliminar de favoritos
        $query = "DELETE FROM favorites WHERE user_id = :user_id AND image_id = :image_id";
        $action = 'removed';
    } else {
        // Agregar a favoritos
        $query = "INSERT INTO favorites (user_id, image_id) VALUES (:user_id, :image_id)";
        $action = 'added';
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
    
    return $stmt->execute() ? $action : false;
}

// Función para obtener los favoritos de un usuario
function getUserFavorites($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT i.*, t.name as theme_name 
              FROM favorites f 
              JOIN images i ON f.image_id = i.id 
              JOIN themes t ON i.theme_id = t.id 
              WHERE f.user_id = :user_id 
              ORDER BY f.added_date DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para crear notificación
function createNotification($user_id, $message, $invoice_id = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO notifications (user_id, message, invoice_id) VALUES (:user_id, :message, :invoice_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':invoice_id', $invoice_id);
    
    return $stmt->execute();
}

// Función para generar ID de factura
function generateInvoiceId($invoice_db_id) {
    $year = date('Y');
    return "F_{$invoice_db_id}_{$year}";
}

// Función para registrar o actualizar cliente desde orden
function registerClientFromOrder($name, $phone, $address) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar si el teléfono ya existe
    $check_query = "SELECT id FROM users WHERE phone = :phone";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':phone', $phone);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // El teléfono ya existe, devolver el ID existente
        $existing_client = $check_stmt->fetch(PDO::FETCH_ASSOC);
        return $existing_client['id'];
    } else {
        // Crear nuevo cliente con email y contraseña fijos
        $email = generateUnregisteredClientEmail(); // Pasar la conexión
        $password = getUnregisteredClientPassword();
        
        $insert_query = "INSERT INTO users (name, phone, address, email, password, role) 
                         VALUES (:name, :phone, :address, :email, :password, 'client')";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->bindParam(':phone', $phone);
        $insert_stmt->bindParam(':address', $address);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':password', $password);
        
        if ($insert_stmt->execute()) {
            return $db->lastInsertId(); // Devolver el ID del nuevo cliente
        }
    }
    
    return false; 
}



// Función para generar email de cliente no registrado con código único
function generateUnregisteredClientEmail() {
    // Usar microtime y md5 para garantizar unicidad
    $hash = substr(md5(microtime() . rand()), 0, 6);
    return 'noregister@' . $hash . '.com';
}

// Función para obtener roles codificada de usuarios no registrados
function getUnregisteredClientPassword() {
    return password_hash('88062528109', PASSWORD_DEFAULT);
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    
    // Ajustar para que funcione tanto en la carpeta admin como en la raíz
    if (strpos($path, 'admin') !== false) {
        $path = str_replace('/admin', '', $path);
    }
    
    return $protocol . $domainName . $path . '/';
}
?>