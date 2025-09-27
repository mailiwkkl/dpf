<?php
require_once 'config.php';
require_once 'functions.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Obtener notificaciones del usuario
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar notificaciones no leídas
$unread_count = 0;
foreach ($notifications as $notification) {
    if (!$notification['is_read']) {
        $unread_count++;
    }
}

// Marcar notificaciones como leídas
if (!empty($notifications) && $unread_count > 0) {
    $update_query = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $update_stmt->execute();
    
    // Actualizar la variable de sesión para el contador
    $_SESSION['unread_notifications'] = 0;
}

$page_title = "Notificaciones";
require_once 'includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-bell"></i> Mis Notificaciones</h1>
    
    <?php if ($unread_count > 0): ?>
    <div class="notification-alert">
        <p><i class="fas fa-info-circle"></i> Tienes <?php echo $unread_count; ?> notificación(es) nueva(s).</p>
    </div>
    <?php endif; ?>
    
    <?php if (empty($notifications)): ?>
    <div class="empty-state">
        <p><i class="fas fa-inbox"></i> No tienes notificaciones.</p>
    </div>
    <?php else: ?>
    <div class="notifications-list">
        <?php foreach ($notifications as $notification): ?>
        <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
            <div class="notification-icon">
                <?php if (!$notification['is_read']): ?>
                <i class="fas fa-circle new-indicator"></i>
                <?php else: ?>
                <i class="fas fa-check-circle read-indicator"></i>
                <?php endif; ?>
            </div>
            <div class="notification-content">
                <p><?php echo $notification['message']; ?></p>
                <small><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></small>
                
                <?php if (!empty($notification['invoice_id'])): ?>
                <div class="notification-actions">
                    <a href="orders.php?search=<?php echo urlencode($notification['invoice_id']); ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver Pedido
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>