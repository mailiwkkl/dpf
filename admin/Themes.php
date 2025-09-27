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

// Procesar formulario para agregar/modificar temática
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $theme_id = isset($_POST['theme_id']) ? $_POST['theme_id'] : null;
    
    if (empty($name)) {
        $error = "El nombre de la temática es obligatorio";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Procesar imagen si se subió
        $image_name = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // Verificar que los directorios existen
            $upload_dir = '../uploads/themes/up/';
            $themes_dir = '../uploads/themes/';
            
            // Crear directorios si no existen
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $error = "Error al crear el directorio de uploads. Contacte al administrador.";
                }
            }
            
            if (!file_exists($themes_dir)) {
                if (!mkdir($themes_dir, 0755, true)) {
                    $error = "Error al crear el directorio de temas. Contacte al administrador.";
                }
            }
            
            // Verificar permisos de escritura
            if (!is_writable($upload_dir) || !is_writable($themes_dir)) {
                $error = "Los directorios no tienen permisos de escritura. Contacte al administrador.";
            }
            
            if (empty($error)) {
                // Validar tipo de archivo
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['image']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    $error = "Tipo de archivo no permitido. Solo se permiten JPEG, PNG, GIF y WebP.";
                } else {
                    // Generar nombre único
                    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $original_name = uniqid() . '.' . $file_ext;
                    $upload_file = $upload_dir . $original_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                        // Verificar si GD está disponible para conversión WebP
                        if (function_exists('imagecreatefromjpeg') && function_exists('imagewebp')) {
                            // Intentar convertir a WebP
                            $webp_file_name = pathinfo($original_name, PATHINFO_FILENAME) . '.webp';
                            $webp_file_path = $themes_dir . $webp_file_name;
                            
                            if (convertToWebP($upload_file, $webp_file_path)) {
                                // Usar el WebP y eliminar el original
                                $image_name = $webp_file_name;
                                if (file_exists($upload_file)) {
                                    unlink($upload_file);
                                }
                            } else {
                                // Si falla la conversión, usar el original
                                $image_name = $original_name;
                                // Mover el archivo al directorio final
                                rename($upload_file, $themes_dir . $original_name);
                            }
                        } else {
                            // GD no disponible, usar el archivo original
                            $image_name = $original_name;
                            // Mover el archivo al directorio final
                            rename($upload_file, $themes_dir . $original_name);
                        }
                    } else {
                        $error = "Error al subir la imagen. Verifique los permisos del directorio.";
                    }
                }
            }
        } elseif ($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            // Hay un error en la subida que no es "ningún archivo seleccionado"
            $error = "Error al subir el archivo: " . getUploadError($_FILES['image']['error']);
        }
        
        if (empty($error)) {
            if ($theme_id) {
                // Actualizar temática existente
                if ($image_name) {
                    // Eliminar imagen anterior si existe
                    if (isset($edit_theme['image']) && !empty($edit_theme['image'])) {
                        $old_image_path = $themes_dir . $edit_theme['image'];
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    
                    $query = "UPDATE themes SET name = :name, image = :image WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':image', $image_name);
                } else {
                    $query = "UPDATE themes SET name = :name WHERE id = :id";
                    $stmt = $db->prepare($query);
                }
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':id', $theme_id);
            } else {
                // Crear nueva temática
                $query = "INSERT INTO themes (name, image) VALUES (:name, :image)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':image', $image_name);
            }
            
            if ($stmt->execute()) {
                $success = $theme_id ? "Temática actualizada correctamente" : "Temática creada correctamente";
                header('Location: themes.php');
                exit();
            } else {
                $error = "Error al guardar la temática en la base de datos";
            }
        }
    }
}

// Procesar eliminación de temática
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener información de la temática para eliminar su imagen
    $get_theme_query = "SELECT image FROM themes WHERE id = :id";
    $get_theme_stmt = $db->prepare($get_theme_query);
    $get_theme_stmt->bindParam(':id', $delete_id);
    $get_theme_stmt->execute();
    $theme_to_delete = $get_theme_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si la temática tiene imágenes
    $check_query = "SELECT COUNT(*) as count FROM images WHERE theme_id = :theme_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':theme_id', $delete_id);
    $check_stmt->execute();
    $image_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($image_count > 0) {
        $error = "No se puede eliminar la temática porque tiene imágenes asociadas";
    } else {
        $delete_query = "DELETE FROM themes WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $delete_id);
        
        if ($delete_stmt->execute()) {
            // Eliminar imagen asociada si existe
            if (!empty($theme_to_delete['image'])) {
                $image_path = '../uploads/themes/' . $theme_to_delete['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $success = "Temática eliminada correctamente";
            header('Location: themes.php');
            exit();
        } else {
            $error = "Error al eliminar la temática";
        }
    }
}

// Obtener temática para edición
$edit_theme = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    foreach ($themes as $theme) {
        if ($theme['id'] == $edit_id) {
            $edit_theme = $theme;
            break;
        }
    }
}

// Función para obtener mensajes de error de subida
function getUploadError($error_code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
        UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario',
        UPLOAD_ERR_PARTIAL => 'El archivo solo se subió parcialmente',
        UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco',
        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
    ];
    
    return isset($errors[$error_code]) ? $errors[$error_code] : 'Error desconocido al subir el archivo';
}

$page_title = "Gestión de Temáticas";
require_once '../includes/headera.php';
?>

<div class="container">
    <h1>Gestión de Temáticas</h1>
    
    <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="admin-form">
        <h2><?php echo isset($_GET['edit']) ? 'Editar' : 'Agregar'; ?> Temática</h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if (isset($_GET['edit'])): ?>
            <input type="hidden" name="theme_id" value="<?php echo $edit_theme['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Nombre de la Temática:</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo isset($edit_theme) ? $edit_theme['name'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="image">Imagen (opcional):</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <small>Formatos permitidos: JPEG, PNG, GIF, WebP. Se intentará convertir a WebP automáticamente.</small>
                
                <?php if (isset($edit_theme) && !empty($edit_theme['image'])): ?>
                <div class="current-image">
                    <p>Imagen actual:</p>
                    <img src="../uploads/themes/<?php echo $edit_theme['image']; ?>" alt="<?php echo $edit_theme['name']; ?>" style="max-width: 200px;">
                    <p><small><?php echo $edit_theme['image']; ?></small></p>
                </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <?php echo isset($_GET['edit']) ? 'Actualizar' : 'Agregar'; ?> Temática
            </button>
            
            <?php if (isset($_GET['edit'])): ?>
            <a href="themes.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="themes-list">
        <h2>Lista de Temáticas</h2>
        
        <?php if (empty($themes)): ?>
        <p>No hay temáticas registradas.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Imagen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($themes as $theme): ?>
                <tr>
                    <td><?php echo $theme['id']; ?></td>
                    <td><?php echo $theme['name']; ?></td>
                    <td>
                        <?php if (!empty($theme['image'])): ?>
                        <img src="../uploads/themes/<?php echo $theme['image']; ?>" alt="<?php echo $theme['name']; ?>" style="max-width: 50px; height: auto;">
                        <?php else: ?>
                        Sin imagen
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="themes.php?edit=<?php echo $theme['id']; ?>" class="btn btn-primary">Editar</a>
                        <a href="themes.php?delete=<?php echo $theme['id']; ?>" class="btn btn-danger" 
                           onclick="return confirm('¿Está seguro de eliminar esta temática?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>