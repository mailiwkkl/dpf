<?php
require_once '../config.php';
require_once '../functions.php';

// Verificar si el usuario está logueado
$is_logged_in = isLoggedIn();
$is_admin = isAdmin();
$settings = getSettings();

// Contar notificaciones no leídas
$unread_notifications = 0;
if ($is_logged_in) {
    $database = new Database();
    $db = $database->getConnection();
    
    $notif_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0";
    $notif_stmt = $db->prepare($notif_query);
    $notif_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $notif_stmt->execute();
    $unread_notifications = $notif_stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo $settings['system_name']; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (isset($settings['logo']) && !empty($settings['logo'])): ?>
    <link rel="icon" href="../uploads/<?php echo $settings['logo']; ?>" type="image/x-icon">
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <?php if (isset($settings['logo']) && !empty($settings['logo']) && file_exists('../uploads/' . $settings['logo'])): ?>
                <img src="../uploads/<?php echo $settings['logo']; ?>" alt="<?php echo $settings['system_name']; ?>">
                <?php else: ?>
                <h1><i class="fas fa-camera-retro"></i> <?php echo $settings['system_name']; ?></h1>
                <?php endif; ?>
            </div>
            
            <div class="header-title">
                <h2><?php echo $settings['system_name']; ?></h2>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="../gallery.php"><i class="fas fa-images"></i> Galería</a></li>
                    <?php if ($is_logged_in): ?>
                        <li><a href="../favorites.php"><i class="fas fa-heart"></i> Favoritos</a></li>
                        <li><a href="../orders.php"><i class="fas fa-receipt"></i> Facturas</a></li>                        <li><a href="../admin/invoices.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                        <li>
                            <a href="../notifications.php" class="notification-link">
                                <i class="fas fa-bell"></i> Notificaciones
                                <?php if ($unread_notifications > 0): ?>
                                <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="../profile.php"><i class="fas fa-user"></i> Perfil</a></li>
                        <?php if ($is_admin): ?>
                            <li><a href="dashboard.php"><i class="fas fa-cog"></i> Administración</a></li>
                        <?php endif; ?>
                        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="../login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                        <li><a href="../register.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main-content">