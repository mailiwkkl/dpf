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
            $upload_dir = '../uploads/themes/';
            $image_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $image_ext;
            $upload_file = $upload_dir . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                $error = "Error al subir la imagen";
            }
        }
        
        if (empty($error)) {
            if ($theme_id) {
                // Actualizar temática existente
                if ($image_name) {
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
                $error = "Error al guardar la temática";
            }
        }
    }
}

// Procesar eliminación de temática
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    $database = new Database();
    $db = $database->getConnection();
    
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
            $success = "Temática eliminada correctamente";
            header('Location: themes.php');
            exit();
        } else {
            $error = "Error al eliminar la temática";
        }
    }
}

$page_title = "Gestión de Temáticas";
require_once '../includes/header.php';
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
            <?php if (isset($_GET['edit'])): 
                $edit_id = $_GET['edit'];
                $edit_theme = null;
                foreach ($themes as $theme) {
                    if ($theme['id'] == $edit_id) {
                        $edit_theme = $theme;
                        break;
                    }
                }
            ?>
            <input type="hidden" name="theme_id" value="<?php echo $edit_id; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Nombre de la Temática:</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo isset($edit_theme) ? $edit_theme['name'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="image">Imagen (opcional):</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (isset($edit_theme) && !empty($edit_theme['image'])): ?>
                <div class="current-image">
                    <p>Imagen actual:</p>
                    <img src="../uploads/themes/<?php echo $edit_theme['image']; ?>" alt="<?php echo $edit_theme['name']; ?>" style="max-width: 200px;">
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
                        <img src="../uploads/themes/<?php echo $theme['image']; ?>" alt="<?php echo $theme['name']; ?>" style="max-width: 50px;">
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