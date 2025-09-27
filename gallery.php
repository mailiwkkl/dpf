<?php
require_once 'config.php';
require_once 'functions.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Obtener parámetro de búsqueda si existe
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Obtener parámetro de temática para scroll después de acción
$scroll_theme = isset($_GET['scroll_theme']) ? $_GET['scroll_theme'] : '';

// Obtener imágenes populares
$popular_images = getPopularImages(10);

// Obtener todas las temáticas con sus imágenes
$themes = getAllThemes();

// Obtener imágenes por temática con filtro de búsqueda si existe
$images_by_theme = [];
foreach ($themes as $theme) {
    $images = getImagesByTheme($theme['id']);
    
    // Aplicar filtro de búsqueda si existe - CORREGIDO
    if (!empty($search_term)) {
        $images = array_filter($images, function($image) use ($search_term, $theme) {
            // Buscar en el título de la imagen o en el nombre de la temática
            return stripos($image['title'], $search_term) !== false || 
                   stripos($theme['name'], $search_term) !== false;
        });
    }
    
    if (!empty($images)) {
        $images_by_theme[$theme['name']] = [
            'images' => $images,
            'theme_data' => $theme // Incluir todos los datos de la temática
        ];
    }
}

// Si hay término de búsqueda, también buscar en imágenes sin temática específica - CORREGIDO
if (!empty($search_term)) {
    $all_images = getImagesByTheme();
    $filtered_images = array_filter($all_images, function($image) use ($search_term, $themes) {
        // Buscar en el título de la imagen
        $title_match = stripos($image['title'], $search_term) !== false;
        
        // Buscar en el nombre de la temática
        $theme_match = false;
        foreach ($themes as $theme) {
            if ($theme['id'] == $image['theme_id']) {
                $theme_match = stripos($theme['name'], $search_term) !== false;
                break;
            }
        }
        
        return $title_match || $theme_match;
    });
    
    // Agrupar imágenes filtradas por temática
    foreach ($filtered_images as $image) {
        $theme_name = $image['theme_name'];
        if (!isset($images_by_theme[$theme_name])) {
            // Buscar datos completos de la temática
            $theme_data = null;
            foreach ($themes as $theme) {
                if ($theme['name'] === $theme_name) {
                    $theme_data = $theme;
                    break;
                }
            }
            
            if ($theme_data) {
                $images_by_theme[$theme_name] = [
                    'images' => [],
                    'theme_data' => $theme_data
                ];
            }
        }
        
        // Agregar imagen a la temática correspondiente
        if (isset($images_by_theme[$theme_name])) {
            // Verificar que la imagen no esté ya en la lista
            $image_exists = false;
            foreach ($images_by_theme[$theme_name]['images'] as $existing_image) {
                if ($existing_image['id'] == $image['id']) {
                    $image_exists = true;
                    break;
                }
            }
            
            if (!$image_exists) {
                $images_by_theme[$theme_name]['images'][] = $image;
            }
        }
    }
}

// Procesar eliminación de imagen si es admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_image']) && isAdmin()) {
    $image_id = $_POST['image_id'];
    $current_theme = $_POST['current_theme'] ?? ''; // Obtener la temática actual
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener información de la imagen para eliminar archivos
    $query = "SELECT filename FROM images WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $image_id);
    $stmt->execute();
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image) {
        // Eliminar de favoritos primero
        $delete_favorites = "DELETE FROM favorites WHERE image_id = :image_id";
        $stmt_fav = $db->prepare($delete_favorites);
        $stmt_fav->bindParam(':image_id', $image_id);
        $stmt_fav->execute();
        
        // Eliminar la imagen
        $delete_query = "DELETE FROM images WHERE id = :id";
        $stmt_del = $db->prepare($delete_query);
        $stmt_del->bindParam(':id', $image_id);
        
        if ($stmt_del->execute()) {
            // Eliminar archivos físicos
            $webp_file = 'uploads/webp/' . $image['filename'] . '.webp';
            
            if (file_exists($webp_file)) {
                unlink($webp_file);
            }
            
            $_SESSION['success'] = "Imagen eliminada correctamente";
            // Redirigir manteniendo la temática actual
            $redirect_url = 'gallery.php';
            if (!empty($current_theme)) {
                $redirect_url .= '?scroll_theme=' . urlencode($current_theme);
            }
            header('Location: ' . $redirect_url);
            exit();
        } else {
            $_SESSION['error'] = "Error al eliminar la imagen de la base de datos";
        }
    } else {
        $_SESSION['error'] = "Imagen no encontrada";
    }
    
    // Redirigir manteniendo la temática actual en caso de error
    $redirect_url = 'gallery.php';
    if (!empty($current_theme)) {
        $redirect_url .= '?scroll_theme=' . urlencode($current_theme);
    }
    header('Location: ' . $redirect_url);
    exit();
}

// Procesar favoritos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['image_id'])) {
    $image_id = $_POST['image_id'];
    $current_theme = $_POST['current_theme'] ?? ''; // Obtener la temática actual
    $action = toggleFavorite($_SESSION['user_id'], $image_id);
    
    // Redirigir para evitar reenvío del formulario, manteniendo la temática
    $redirect_url = 'gallery.php';
    if (!empty($current_theme)) {
        $redirect_url .= '?scroll_theme=' . urlencode($current_theme);
    }
    header('Location: ' . $redirect_url);
    exit();
}

// Mostrar mensajes de éxito o error
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error']);
unset($_SESSION['success']);

$page_title = "Galería";
require_once 'includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-images"></i> Galería de Imágenes</h1>
    
    <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Barra de búsqueda y botones de administración -->
    <div class="gallery-controls" style="margin-bottom: 2rem;">
        <div class="search-bar" style="margin-bottom: 1rem;">
            <form method="GET" action="gallery.php" class="search-form" style="display: flex; gap: 10px; align-items: center;">
                <div style="flex: 1;">
                    <input type="text" name="search" placeholder="Buscar por temática o título de imagen..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php if (!empty($search_term)): ?>
                <a href="gallery.php" class="btn btn-secondary" style="white-space: nowrap;">
                    <i class="fas fa-times"></i> Limpiar
                </a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if (isAdmin()): ?>
        <div class="admin-actions" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="admin/upload.php" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Imágenes a la Galería
            </a>
            <a href="admin/themes.php" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Gestionar Temáticas
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Mostrar resultados de búsqueda -->
    <?php if (!empty($search_term)): ?>
    <div class="search-results" style="margin-bottom: 2rem; padding: 1rem; background-color: #f8f9fa; border-radius: 8px;">
        <h3 style="margin-bottom: 0.5rem;">
            <i class="fas fa-search"></i> Resultados de búsqueda para: "<?php echo htmlspecialchars($search_term); ?>"
        </h3>
        <p style="color: #666; margin: 0;">
            Se encontraron <?php echo count($images_by_theme); ?> temáticas con imágenes que coinciden con su búsqueda.
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Imágenes populares -->
    <?php if (!empty($popular_images) && empty($search_term)): ?>
    <section class="gallery-section">
        <h2><i class="fas fa-fire"></i> Imágenes Populares</h2>
        <div class="image-carousel">
            <?php foreach ($popular_images as $image): ?>
            <div class="image-item">
                <img src="uploads/webp/<?php echo $image['filename']; ?>.webp" alt="<?php echo $image['title']; ?>" 
                     data-image-id="<?php echo $image['id']; ?>"
                     data-image-src="uploads/webp/<?php echo $image['filename']; ?>.webp"
                     data-image-title="<?php echo $image['title']; ?>">
                <div class="image-overlay">
                    <h3><?php echo $image['title']; ?></h3>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Imágenes por temática -->
    <?php if (!empty($images_by_theme)): ?>
        <?php foreach ($images_by_theme as $theme_name => $theme_data): 
            $images = $theme_data['images'];
            $theme = $theme_data['theme_data'];
            $theme_has_image = !empty($theme['image']) && file_exists('uploads/themes/' . $theme['image']);
            // Crear un ID único para la sección de la temática
            $theme_section_id = 'theme-' . preg_replace('/[^a-zA-Z0-9]/', '-', strtolower($theme_name));
        ?>
        <section id="<?php echo $theme_section_id; ?>" class="gallery-section">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div class="theme-title-container" style="display: flex; align-items: center; gap: 15px;">
                    <?php if ($theme_has_image): ?>
                    <!-- Mostrar imagen de la temática -->
                    <div class="theme-image-circle">
                        <img src="uploads/themes/<?php echo $theme['image']; ?>" 
                             alt="<?php echo $theme_name; ?>" 
                             class="theme-image">
                    </div>
                    <?php else: ?>
                    <!-- Mostrar icono por defecto -->
                    <div class="theme-icon-circle">
                        <i class="fas fa-tag"></i>
                    </div>
                    <?php endif; ?>
                    
                    <h2><?php echo $theme_name; ?></h2>
                </div>
                
                <?php if (isAdmin()): ?>
                <!-- Botón de acceso rápido para subir imágenes a esta temática -->
                <?php 
                // Obtener el ID de la temática actual
                $current_theme_id = $theme['id'];
                ?>
                <a href="admin/upload.php?theme_id=<?php echo $current_theme_id; ?>" 
                   class="btn btn-secondary btn-sm" 
                   style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="fas fa-plus-circle"></i> Agregar a esta temática
                </a>
                <?php endif; ?>
            </div>
            
            <div class="image-carousel">
                <?php foreach ($images as $image): ?>
                <div class="image-item">
                    <?php if (isAdmin()): ?>
                    <form method="POST" class="delete-image-form">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <input type="hidden" name="current_theme" value="<?php echo $theme_section_id; ?>">
                        <button type="submit" name="delete_image" class="btn-delete-image" onclick="return confirm('¿Está seguro de eliminar esta imagen?')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <img src="uploads/webp/<?php echo $image['filename']; ?>.webp" alt="<?php echo $image['title']; ?>" 
                         data-image-id="<?php echo $image['id']; ?>"
                         data-image-src="uploads/webp/<?php echo $image['filename']; ?>.webp"
                         data-image-title="<?php echo $image['title']; ?>">
                    <div class="image-overlay">
                        <h3><?php echo $image['title']; ?></h3>
                        <form method="POST" class="favorite-form">
                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                            <input type="hidden" name="current_theme" value="<?php echo $theme_section_id; ?>">
                            <button type="submit" class="favorite-btn <?php echo isFavorite($_SESSION['user_id'], $image['id']) ? 'favorited' : ''; ?>">
                                ♥
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    <?php else: ?>
        <?php if (!empty($search_term)): ?>
        <div class="empty-state">
            <p><i class="fas fa-search"></i> No se encontraron imágenes que coincidan con "<?php echo htmlspecialchars($search_term); ?>".</p>
            <a href="gallery.php" class="btn btn-primary">Ver todas las imágenes</a>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p><i class="fas fa-images"></i> No hay imágenes disponibles en la galería.</p>
            <?php if (isAdmin()): ?>
            <a href="admin/upload.php" class="btn btn-primary">Subir primeras imágenes</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal para imagen ampliada -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="zoom-controls">
            <button id="zoomIn" class="zoom-btn"><i class="fas fa-search-plus"></i></button>
            <button id="zoomOut" class="zoom-btn"><i class="fas fa-search-minus"></i></button>
            <button id="resetZoom" class="zoom-btn"><i class="fas fa-sync-alt"></i></button>
            <button id="fullZoom" class="zoom-btn"><i class="fas fa-expand"></i></button>
        </div>
        <div class="image-container">
            <img id="modalImage" src="" alt="">
        </div>
        <div class="modal-footer">
            <h3 id="modalTitle"></h3>
            <form method="POST" class="favorite-form-modal">
                <input type="hidden" name="image_id" id="modalImageId">
                <input type="hidden" name="current_theme" id="modalCurrentTheme">
                <button type="submit" class="favorite-btn-modal" id="modalFavoriteBtn">♥</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
/* Estilos para los controles de la galería */
.gallery-controls {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-form input {
    flex: 1;
    min-width: 250px;
}

.admin-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Estilos para resultados de búsqueda */
.search-results {
    background-color: #e8f5e9;
    border-left: 4px solid #4caf50;
}

/* Estilos para estado vacío mejorado */
.empty-state {
    text-align: center;
    padding: 3rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin: 2rem 0;
}

.empty-state p {
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    color: #666;
}

/* Estilos para los botones de administración */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

/* Estilos para las imágenes de temáticas */
.theme-title-container {
    display: flex;
    align-items: center;
    gap: 15px;
}

.theme-image-circle, .theme-icon-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 3px solid #6a1b9a;
    background: linear-gradient(135deg, #9c4dcc 0%, #6a1b9a 100%);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.theme-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.theme-icon-circle {
    background: linear-gradient(135deg, #9c4dcc 0%, #6a1b9a 100%);
    color: white;
    font-size: 2rem;
}

.theme-icon-circle i {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

/* Estilos para el modal de imagen */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    position: relative;
    margin: auto;
    padding: 20px;
    width: 90%;
    height: 90%;
    display: flex;
    flex-direction: column;
}

.close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1002;
}

.close:hover {
    color: #ccc;
}

.zoom-controls {
    position: absolute;
    top: 15px;
    left: 35px;
    z-index: 1001;
    display: flex;
    gap: 10px;
}

.zoom-btn {
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: background 0.3s;
}

.zoom-btn:hover {
    background: rgba(0,0,0,0.9);
}

.image-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#modalImage {
    max-width: 100%;
    max-height: 100%;
    transition: transform 0.3s ease;
    cursor: zoom-in;
}

#modalImage.zoomed {
    cursor: grab;
}

#modalImage.zoomed:active {
    cursor: grabbing;
}

.modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    color: white;
}

.favorite-btn-modal {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: color 0.3s;
}

.favorite-btn-modal.favorited {
    color: #ff5252;
}

.favorite-btn-modal:hover {
    color: #ff5252;
}

/* Efectos hover */
.theme-image-circle:hover, .theme-icon-circle:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .gallery-controls {
        padding: 1rem;
    }
    
    .search-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-form input {
        min-width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .admin-actions {
        flex-direction: column;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .section-header h2 {
        margin-bottom: 0.5rem;
    }
    
    .theme-title-container {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .theme-image-circle, .theme-icon-circle {
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }
    
    .theme-icon-circle {
        font-size: 1.5rem;
    }
    
    .modal-content {
        width: 95%;
        height: 95%;
        padding: 10px;
    }
    
    .close {
        top: 10px;
        right: 20px;
        font-size: 30px;
    }
    
    .zoom-controls {
        top: 10px;
        left: 20px;
    }
    
    .zoom-btn {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .theme-image-circle, .theme-icon-circle {
        width: 80px;
        height: 80px;
    }
    
    .theme-icon-circle {
        font-size: 1.2rem;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .empty-state p {
        font-size: 1rem;
    }
    
    .zoom-controls {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .zoom-btn {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}
</style>

<script>
// Script para el modal de imágenes con zoom y scroll a temática
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalImageId = document.getElementById('modalImageId');
    const modalCurrentTheme = document.getElementById('modalCurrentTheme');
    const modalFavoriteBtn = document.getElementById('modalFavoriteBtn');
    const closeBtn = document.querySelector('.close');
    const images = document.querySelectorAll('.image-item img');
    
    // Variables para el zoom
    let scale = 1;
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;
    const imageContainer = document.querySelector('.image-container');
    
    // Controles de zoom
    const zoomInBtn = document.getElementById('zoomIn');
    const zoomOutBtn = document.getElementById('zoomOut');
    const resetZoomBtn = document.getElementById('resetZoom');
    const fullZoomBtn = document.getElementById('fullZoom');
    
    // Función para aplicar zoom
    function applyZoom() {
        modalImg.style.transform = `scale(${scale})`;
        if (scale > 1) {
            modalImg.classList.add('zoomed');
            imageContainer.style.overflow = 'auto';
        } else {
            modalImg.classList.remove('zoomed');
            imageContainer.style.overflow = 'hidden';
        }
    }
    
    // Función para resetear zoom
    function resetZoom() {
        scale = 1;
        applyZoom();
        // Centrar la imagen
        imageContainer.scrollLeft = (imageContainer.scrollWidth - imageContainer.clientWidth) / 2;
        imageContainer.scrollTop = (imageContainer.scrollHeight - imageContainer.clientHeight) / 2;
    }
    
    // Eventos de zoom
    zoomInBtn.addEventListener('click', function() {
        scale += 0.2;
        applyZoom();
    });
    
    zoomOutBtn.addEventListener('click', function() {
        if (scale > 0.3) {
            scale -= 0.2;
            applyZoom();
        }
    });
    
    resetZoomBtn.addEventListener('click', resetZoom);
    
    fullZoomBtn.addEventListener('click', function() {
        // Ajustar zoom para que la imagen ocupe todo el contenedor
        const containerWidth = imageContainer.clientWidth;
        const containerHeight = imageContainer.clientHeight;
        const imgWidth = modalImg.naturalWidth;
        const imgHeight = modalImg.naturalHeight;
        
        const scaleX = containerWidth / imgWidth;
        const scaleY = containerHeight / imgHeight;
        
        scale = Math.min(scaleX, scaleY);
        applyZoom();
    });
    
    // Arrastrar imagen cuando está zoomed
    modalImg.addEventListener('mousedown', function(e) {
        if (scale > 1) {
            isDragging = true;
            startX = e.pageX - imageContainer.offsetLeft;
            startY = e.pageY - imageContainer.offsetTop;
            scrollLeft = imageContainer.scrollLeft;
            scrollTop = imageContainer.scrollTop;
            modalImg.style.cursor = 'grabbing';
        }
    });
    
    document.addEventListener('mouseup', function() {
        isDragging = false;
        if (scale > 1) {
            modalImg.style.cursor = 'grab';
        }
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - imageContainer.offsetLeft;
        const y = e.pageY - imageContainer.offsetTop;
        const walkX = (x - startX) * 2;
        const walkY = (y - startY) * 2;
        imageContainer.scrollLeft = scrollLeft - walkX;
        imageContainer.scrollTop = scrollTop - walkY;
    });
    
    // Zoom con rueda del mouse
    imageContainer.addEventListener('wheel', function(e) {
        if (e.ctrlKey) {
            e.preventDefault();
            if (e.deltaY < 0) {
                // Zoom in
                scale += 0.1;
            } else {
                // Zoom out
                if (scale > 0.3) {
                    scale -= 0.1;
                }
            }
            applyZoom();
        }
    });
    
    // Abrir modal al hacer clic en una imagen
    images.forEach(img => {
        img.addEventListener('click', function() {
            modal.style.display = 'flex';
            modalImg.src = this.getAttribute('data-image-src');
            modalTitle.textContent = this.getAttribute('data-image-title');
            modalImageId.value = this.getAttribute('data-image-id');
            
            // Obtener la temática actual del formulario más cercano
            const currentThemeInput = this.closest('.image-item').querySelector('input[name="current_theme"]');
            if (currentThemeInput) {
                modalCurrentTheme.value = currentThemeInput.value;
            }
            
            // Resetear zoom al cargar nueva imagen
            resetZoom();
            
            // Verificar si es favorito
            const favoriteForm = this.closest('.image-item').querySelector('.favorite-form');
            if (favoriteForm) {
                const isFavorited = favoriteForm.querySelector('.favorite-btn').classList.contains('favorited');
                if (isFavorited) {
                    modalFavoriteBtn.classList.add('favorited');
                } else {
                    modalFavoriteBtn.classList.remove('favorited');
                }
            }
        });
    });
    
    // Cerrar modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        resetZoom();
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            resetZoom();
        }
    });
    
    // Manejar favoritos en el modal
    document.querySelector('.favorite-form-modal').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('gallery.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            }
        });
    });
    
    // Prevenir que los clics en botones de eliminar abran el modal
    document.querySelectorAll('.btn-delete-image').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Scroll a la temática después de una acción
    <?php if (!empty($scroll_theme)): ?>
    setTimeout(function() {
        const themeSection = document.getElementById('<?php echo $scroll_theme; ?>');
        if (themeSection) {
            themeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            // Agregar efecto visual temporal
            themeSection.style.transition = 'all 0.5s ease';
            themeSection.style.boxShadow = '0 0 0 3px #6a1b9a';
            setTimeout(function() {
                themeSection.style.boxShadow = 'none';
            }, 2000);
        }
    }, 100);
    <?php endif; ?>
    
    // Mejorar la experiencia de búsqueda
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
        
        // Focus al cargar la página si hay término de búsqueda
        if (searchInput.value) {
            searchInput.focus();
            searchInput.select();
        }
    }
});
</script>