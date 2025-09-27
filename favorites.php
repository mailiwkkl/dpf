<?php
require_once 'config.php';
require_once 'functions.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Obtener favoritos del usuario
$favorites = getUserFavorites($_SESSION['user_id']);

// Obtener configuración para WhatsApp
$settings = getSettings();

// Procesar eliminación de favoritos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_favorite'])) {
    $image_id = $_POST['image_id'];
    toggleFavorite($_SESSION['user_id'], $image_id);
    
    // Redirigir para evitar reenvío del formulario
    header('Location: favorites.php');
    exit();
}

$page_title = "Mis Favoritos";
require_once 'includes/header.php';
?>

<div class="container">
    <h1>Mis Favoritos</h1>
    
    <?php if (empty($favorites)): ?>
    <div class="empty-state">
        <p>No tienes imágenes favoritas aún.</p>
        <a href="gallery.php" class="btn btn-primary">Explorar Galería</a>
    </div>
    <?php else: ?>
    <div class="favorites-grid">
        <?php foreach ($favorites as $favorite): ?>
        <div class="favorite-item">
            <img src="uploads/webp/<?php echo $favorite['filename']; ?>.webp" alt="<?php echo $favorite['title']; ?>">
            <div class="favorite-info">
                <h3><?php echo $favorite['title']; ?></h3>
                <p>Temática: <?php echo $favorite['theme_name']; ?></p>
                <form method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $favorite['id']; ?>">
                    <button type="submit" name="remove_favorite" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="whatsapp-section">
        <h2>Contactar por WhatsApp</h2>
        <p>Puedes enviar un mensaje al administrador con tus favoritos seleccionados.</p>
        <?php if (!empty($settings['whatsapp_number'])): ?>
        <a href="https://wa.me/<?php echo $settings['whatsapp_number']; ?>?text=Hola estoy interesado en algunos de sus trabajos por favor verifique el código <?php echo $_SESSION['user_id']; ?>" 
           target="_blank" class="btn btn-whatsapp">
            Enviar mensaje por WhatsApp
        </a>
        <?php else: ?>
        <p class="error-message">El número de WhatsApp no está configurado. Contacte al administrador.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>