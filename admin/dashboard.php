<?php
require_once '../config.php';
require_once '../functions.php';

// Verificar si el usuario está logueado y es administrador
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Obtener estadísticas
$database = new Database();
$db = $database->getConnection();

// Contar usuarios
$users_query = "SELECT COUNT(*) as total FROM users";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$users_count = $users_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Contar imágenes
$images_query = "SELECT COUNT(*) as total FROM images";
$images_stmt = $db->prepare($images_query);
$images_stmt->execute();
$images_count = $images_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Contar pedidos
$orders_query = "SELECT COUNT(*) as total FROM invoices";
$orders_stmt = $db->prepare($orders_query);
$orders_stmt->execute();
$orders_count = $orders_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Ingresos del mes actual
$current_month = date('m');
$current_year = date('Y');
$income_query = "SELECT SUM(total_amount) as total_income FROM invoices 
                 WHERE MONTH(order_date) = :month AND YEAR(order_date) = :year";
$income_stmt = $db->prepare($income_query);
$income_stmt->bindParam(':month', $current_month);
$income_stmt->bindParam(':year', $current_year);
$income_stmt->execute();
$income = $income_stmt->fetch(PDO::FETCH_ASSOC)['total_income'] ?? 0;

$page_title = "Panel de Administración";
require_once '../includes/headera.php';
?>

<div class="container">
    <h1><i class="fas fa-tachometer-alt"></i> Panel de Administración</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>Usuarios Registrados</h3>
            <p class="stat-number"><?php echo $users_count; ?></p>
            <a href="users.php" class="btn btn-primary"><i class="fas fa-eye"></i> Ver Usuarios</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-images"></i>
            </div>
            <h3>Imágenes en Galería</h3>
            <p class="stat-number"><?php echo $images_count; ?></p>
            <a href="upload.php" class="btn btn-primary"><i class="fas fa-upload"></i> Subir Imágenes</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3>Pedidos Totales</h3>
            <p class="stat-number"><?php echo $orders_count; ?></p>
            <a href="invoices.php" class="btn btn-primary"><i class="fas fa-list"></i> Ver Pedidos</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h3>Ingresos del Mes</h3>
            <p class="stat-number">$<?php echo number_format($income, 2); ?></p>
            <a href="stats.php" class="btn btn-primary"><i class="fas fa-chart-bar"></i> Ver Estadísticas</a>
        </div>
    </div>
    
    <div class="admin-actions">
        <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
        <div class="action-buttons">
            <a href="themes.php" class="btn btn-primary"><i class="fas fa-palette"></i> Gestionar Temáticas</a>
            <a href="upload.php" class="btn btn-primary"><i class="fas fa-cloud-upload-alt"></i> Subir Imágenes</a>
            <a href="invoices.php" class="btn btn-primary"><i class="fas fa-file-invoice"></i> Crear Pedido</a>
            <a href="settings.php" class="btn btn-primary"><i class="fas fa-cogs"></i> Configuración</a>
        </div>
    </div>
    
    <!-- Sección de favoritos por usuarios -->
    <div class="favorites-by-users">
        <h2><i class="fas fa-heart"></i> Favoritos por Usuarios</h2>
        <form method="GET" action="" class="filter-form">
            <div class="form-group">
                <label for="user_id"><i class="fas fa-filter"></i> Filtrar por ID de Usuario:</label>
                <input type="text" id="user_id" name="user_id" placeholder="Ingrese ID de usuario"
                       value="<?php echo isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpiar</a>
        </form>
        
        <?php
        // Obtener favoritos filtrados
        $user_filter = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        $favorites_query = "SELECT f.*, u.name as user_name, i.title as image_title, i.filename as image_filename, 
                           t.name as theme_name 
                           FROM favorites f 
                           JOIN users u ON f.user_id = u.id 
                           JOIN images i ON f.image_id = i.id 
                           JOIN themes t ON i.theme_id = t.id";
        
        if (!empty($user_filter)) {
            $favorites_query .= " WHERE f.user_id = :user_id";
        }
        
        $favorites_query .= " ORDER BY f.added_date DESC LIMIT 10";
        
        $favorites_stmt = $db->prepare($favorites_query);
        if (!empty($user_filter)) {
            $favorites_stmt->bindParam(':user_id', $user_filter);
        }
        $favorites_stmt->execute();
        $favorites = $favorites_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="favorites-list">
            <h3><i class="fas fa-list"></i> Últimos 10 favoritos <?php echo !empty($user_filter) ? "del usuario ID: $user_filter" : ""; ?></h3>
            
            <?php if (empty($favorites)): ?>
            <div class="empty-state">
                <p><i class="fas fa-inbox"></i> No se encontraron favoritos.</p>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Usuario</th>
                        <th><i class="fas fa-image"></i> Imagen</th>
                        <th><i class="fas fa-tag"></i> Temática</th>
                        <th><i class="fas fa-calendar"></i> Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($favorites as $favorite): ?>
                    <tr>
                        <td><?php echo $favorite['user_name'] . " (ID: " . $favorite['user_id'] . ")"; ?></td>
                        <td>
                            <div class="favorite-image-container">
                                <img src="../uploads/webp/<?php echo $favorite['image_filename']; ?>.webp" 
                                     alt="<?php echo $favorite['image_title']; ?>"
                                     class="favorite-image"
                                     onclick="openImageModal('../uploads/webp/<?php echo $favorite['image_filename']; ?>.webp')"
                                     style="cursor: pointer; max-width: 80px; height: auto; border-radius: 4px;">
                                <div class="image-title"><?php echo $favorite['image_title']; ?></div>
                            </div>
                        </td>
                        <td><?php echo $favorite['theme_name']; ?></td>
                        <td><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($favorite['added_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para imagen ampliada -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img id="modalImage" src="" alt="">
    </div>
</div>

<script>
// Funcionalidad para el modal de imágenes
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        const modalImg = document.getElementById('modalImage');
        const closeBtn = modal.querySelector('.close');
        
        // Cerrar modal
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }
});

// Función para abrir imagen en modal
function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    if (modal && modalImg) {
        modal.style.display = 'block';
        modalImg.src = imageSrc;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>