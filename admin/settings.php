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

// Obtener configuración actual
$settings = getSettings();

// Procesar actualización de configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $system_name = trim($_POST['system_name']);
    $whatsapp_number = trim($_POST['whatsapp_number']);
    
    if (empty($system_name)) {
        $error = "El nombre del sistema es obligatorio";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Procesar logo si se subió
        $logo_name = $settings['logo'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            
            // Crear directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            $file_type = $_FILES['logo']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = "Tipo de archivo no permitido. Solo se permiten JPEG, PNG, GIF y SVG.";
            } else {
                $logo_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logo_name = 'logo.' . $logo_ext;
                $upload_file = $upload_dir . $logo_name;
                
                // Eliminar logo anterior si existe
                if (!empty($settings['logo']) && file_exists('../' . $settings['logo'])) {
                    unlink('../' . $settings['logo']);
                }
                
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_file)) {
                    $error = "Error al subir el logo. Verifique los permisos del directorio uploads/";
                }
            }
        }
        
        if (empty($error)) {
            $query = "UPDATE settings SET system_name = :system_name, logo = :logo, whatsapp_number = :whatsapp_number";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':system_name', $system_name);
            $stmt->bindParam(':logo', $logo_name);
            $stmt->bindParam(':whatsapp_number', $whatsapp_number);
            
            if ($stmt->execute()) {
                $success = "Configuración actualizada correctamente";
                // Actualizar variable de configuración
                $settings = getSettings();
            } else {
                $error = "Error al actualizar la configuración";
            }
        }
    }
}

$page_title = "Configuración del Sistema";
require_once '../includes/headera.php';
?>

<div class="container">
    <h1><i class="fas fa-cogs"></i> Configuración del Sistema</h1>
    
    <?php if ($error): ?>
    <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="admin-form">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="system_name">Nombre del Sistema:</label>
                <input type="text" id="system_name" name="system_name" required 
                       value="<?php echo $settings['system_name']; ?>">
            </div>
            
            <div class="form-group">
                <label for="whatsapp_number">Número de WhatsApp:</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" 
                       value="<?php echo $settings['whatsapp_number']; ?>" 
                       placeholder="Incluir código de país (ej: +521234567890)">
                <small>Este es el número al que los clientes enviarán mensajes de WhatsApp</small>
            </div>
            
            <div class="form-group">
                <label for="logo">Logo del Sistema:</label>
                <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/svg+xml">
                <small>Formatos permitidos: JPEG, PNG, GIF, SVG. Tamaño máximo: 2MB</small>
                
                <?php if (!empty($settings['logo']) && file_exists('../' . $settings['logo'])): ?>
                <div class="current-logo">
                    <p>Logo actual:</p>
                    <img src="../<?php echo $settings['logo']; ?>" alt="Logo actual" style="max-width: 200px; max-height: 100px; object-fit: contain;">
                    <p><small><?php echo $settings['logo']; ?></small></p>
                </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Configuración</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>