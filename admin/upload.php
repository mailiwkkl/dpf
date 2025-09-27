<?php
require_once '../config.php';
require_once '../functions.php';

// Verificar si el usuario está logueado y es administrador
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Obtener todas las temáticas
$themes = getAllThemes();

// Obtener el theme_id de la URL si está presente
$selected_theme_id = isset($_GET['theme_id']) ? intval($_GET['theme_id']) : '';

// Verificar si el theme_id existe en la base de datos
if ($selected_theme_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $check_theme_query = "SELECT id FROM themes WHERE id = :theme_id";
    $check_theme_stmt = $db->prepare($check_theme_query);
    $check_theme_stmt->bindParam(':theme_id', $selected_theme_id);
    $check_theme_stmt->execute();
    
    if ($check_theme_stmt->rowCount() === 0) {
        $selected_theme_id = ''; // Si no existe, limpiar el valor
    }
}

// Procesar subida de imágenes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    $theme_id = $_POST['theme_id'];
    $title = trim($_POST['title']);
    
    if (empty($theme_id) || empty($title)) {
        $error = "La temática y el título son obligatorios";
    } elseif (count($_FILES['images']['name']) == 0) {
        $error = "Debe seleccionar al menos una imagen";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $upload_count = 0;
        $error_count = 0;
        $error_messages = [];
        
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                // Validar tipo de archivo
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['images']['type'][$key];
                
                if (!in_array($file_type, $allowed_types)) {
                    $error_messages[] = "Archivo {$name}: Tipo no permitido";
                    $error_count++;
                    continue;
                }
                
                // Validar tamaño
                if ($_FILES['images']['size'][$key] > MAX_IMAGE_SIZE) {
                    $error_messages[] = "Archivo {$name}: Tamaño excede el límite de 5MB";
                    $error_count++;
                    continue;
                }
                
                // Generar nombre único
                $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_ext;
                $upload_file = '../' . UPLOAD_DIR . $file_name;
                
                // Mover archivo original
                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $upload_file)) {
                    // Convertir a WebP
                    $webp_file = '../' . WEBP_DIR . pathinfo($file_name, PATHINFO_FILENAME) . '.webp';
                    
                    if (convertToWebP($upload_file, $webp_file)) {
                        // Guardar en base de datos
                        $query = "INSERT INTO images (title, filename, theme_id) VALUES (:title, :filename, :theme_id)";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':title', $title);
                        
                        // CORRECCIÓN: Guardar el resultado de pathinfo en una variable primero
                        $filename_without_extension = pathinfo($file_name, PATHINFO_FILENAME);
                        $stmt->bindParam(':filename', $filename_without_extension);
                        
                        $stmt->bindParam(':theme_id', $theme_id);
                        
                        if ($stmt->execute()) {
                            $upload_count++;
                            if (file_exists($upload_file)) unlink($upload_file);
                        } else {
                            $error_messages[] = "Archivo {$name}: Error al guardar en base de datos";
                            $error_count++;
                            // Eliminar archivos subidos
                            if (file_exists($upload_file)) unlink($upload_file);
                            if (file_exists($webp_file)) unlink($webp_file);
                        }
                    } else {
                        $error_messages[] = "Archivo {$name}: Error al convertir a WebP";
                        $error_count++;
                        if (file_exists($upload_file)) unlink($upload_file);
                    }
                } else {
                    $error_messages[] = "Archivo {$name}: Error al subir el archivo";
                    $error_count++;
                }
            } elseif ($_FILES['images']['error'][$key] != UPLOAD_ERR_NO_FILE) {
                $error_messages[] = "Archivo {$name}: " . getUploadError($_FILES['images']['error'][$key]);
                $error_count++;
            }
        }
        
        if ($upload_count > 0) {
            $success = "Se subieron {$upload_count} imágenes correctamente";
            if ($error_count > 0) {
                $error = "Hubo {$error_count} errores:<br>" . implode("<br>", $error_messages);
            }
            
            // Limpiar el theme_id seleccionado después de una subida exitosa
            $selected_theme_id = '';
        } else {
            $error = "Error al subir las imágenes:<br>" . implode("<br>", $error_messages);
        }
    }
}

$page_title = "Subir Imágenes";
require_once '../includes/headera.php';
?>

<div class="container">
    <h1><i class="fas fa-cloud-upload-alt"></i> Subir Imágenes</h1>
    
    <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="admin-form">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="theme_id">Temática:</label>
                <select id="theme_id" name="theme_id" required>
                    <option value="">Seleccione una temática</option>
                    <?php foreach ($themes as $theme): ?>
                    <option value="<?php echo $theme['id']; ?>" 
                            <?php echo ($selected_theme_id == $theme['id']) ? 'selected' : ''; ?>>
                        <?php echo $theme['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($selected_theme_id): ?>
                <small style="color: #28a745;">
                    <i class="fas fa-check-circle"></i> Temática pre-seleccionada desde la galería
                </small>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="title">Título para las imágenes:</label>
                <input type="text" id="title" name="title" required 
                       placeholder="Título que se mostrará para todas las imágenes">
            </div>
            
            <div class="form-group">
                <label for="images">Seleccionar imágenes:</label>
                <input type="file" id="images" name="images[]" multiple accept="image/*" required>
                <small>Puede seleccionar múltiples imágenes (JPEG, PNG, GIF). Tamaño máximo por archivo: 5MB</small>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Subir Imágenes
                </button>
                <a href="../gallery.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la Galería
                </a>
                <?php if ($selected_theme_id): ?>
                <a href="upload.php" class="btn btn-info">
                    <i class="fas fa-times"></i> Limpiar selección
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <?php if ($selected_theme_id): ?>
    <div class="info-box" style="background-color: #e7f3ff; padding: 1rem; border-radius: 5px; margin-top: 1rem;">
        <h3><i class="fas fa-info-circle"></i> Información</h3>
        <p>Has llegado aquí desde la temática: 
            <strong>
                <?php 
                $theme_name = '';
                foreach ($themes as $theme) {
                    if ($theme['id'] == $selected_theme_id) {
                        $theme_name = $theme['name'];
                        break;
                    }
                }
                echo $theme_name;
                ?>
            </strong>
        </p>
        <p>Las imágenes que subas se asociarán automáticamente a esta temática.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<style>
.form-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1.5rem;
}

.form-buttons .btn {
    margin: 0;
}

.info-box {
    border-left: 4px solid #007bff;
}

@media (max-width: 768px) {
    .form-buttons {
        flex-direction: column;
    }
    
    .form-buttons .btn {
        width: 100%;
        text-align: center;
    }
}
</style>