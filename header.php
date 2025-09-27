<?php
require_once 'config.php';
require_once '../functions.php';

// Verificar si el usuario está logueado
$is_logged_in = isLoggedIn();
$is_admin = isAdmin();
$settings = getSettings();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo $settings['system_name']; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <?php if (isset($settings['logo']) && !empty($settings['logo'])): ?>
    <link rel="icon" href="../<?php echo $settings['logo']; ?>" type="image/x-icon">
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <?php if (isset($settings['logo']) && !empty($settings['logo'])): ?>
                <img src="../<?php echo $settings['logo']; ?>" alt="<?php echo $settings['system_name']; ?>">
                <?php else: ?>
                <h1><?php echo $settings['system_name']; ?></h1>
                <?php endif; ?>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="../gallery.php">Galería</a></li>
                    <?php if ($is_logged_in): ?>
                        <li><a href="../favorites.php">Favoritos</a></li>
                        <li><a href="../orders.php">Mis Pedidos</a></li>
                        <li><a href="../notifications.php">Notificaciones</a></li>
                        <li><a href="../profile.php">Perfil</a></li>
                        <?php if ($is_admin): ?>
                            <li><a href="dashboard.php">Administración</a></li>
                        <?php endif; ?>
                        <li><a href="../logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="../login.php">Iniciar Sesión</a></li>
                        <li><a href="../register.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main-content">