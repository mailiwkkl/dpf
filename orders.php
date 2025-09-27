<?php
require_once 'config.php';
require_once 'functions.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$order = null;
$is_admin = isAdmin();

// Obtener configuración para WhatsApp
$settings = getSettings();


// Buscar pedido si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_order'])) {
    $order_code = trim($_POST['order_code']);
    
    if (!empty($order_code)) {
        $database = new Database();
        $db = $database->getConnection();
        
        // Consulta diferente para admin vs cliente
        if ($is_admin) {
            // Admin puede buscar cualquier pedido
            $query = "SELECT * FROM invoices WHERE invoice_id = :invoice_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':invoice_id', $order_code);
        } else {
            // Cliente solo puede buscar sus propios pedidos
            $query = "SELECT * FROM invoices WHERE invoice_id = :invoice_id AND client_id = :client_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':invoice_id', $order_code);
            $stmt->bindParam(':client_id', $_SESSION['user_id']);
        }
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Obtener detalles del pedido
            $details_query = "SELECT * FROM invoice_details WHERE invoice_id = :invoice_id";
            $details_stmt = $db->prepare($details_query);
            $details_stmt->bindParam(':invoice_id', $order_code);
            $details_stmt->execute();
            $order_details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $order['details'] = $order_details;
            

        } else {
            $error = "No se encontró ningún pedido con ese código";
        }
    } else {
        $error = "Por favor ingrese un código de pedido";
    }
}

// También buscar si se pasa por URL (desde notificaciones)
if (isset($_GET['search'])) {
    $order_code = trim($_GET['search']);
    
    if (!empty($order_code)) {
        $database = new Database();
        $db = $database->getConnection();
        
        // Consulta diferente para admin vs cliente
        if ($is_admin) {
            $query = "SELECT * FROM invoices WHERE invoice_id = :invoice_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':invoice_id', $order_code);
        } else {
            $query = "SELECT * FROM invoices WHERE invoice_id = :invoice_id AND client_id = :client_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':invoice_id', $order_code);
           $stmt->bindParam(':client_id', $_SESSION['user_id']);
        }
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Obtener detalles del pedido
            $details_query = "SELECT * FROM invoice_details WHERE invoice_id = :invoice_id";
            $details_stmt = $db->prepare($details_query);
            $details_stmt->bindParam(':invoice_id', $order_code);
            $details_stmt->execute();
            $order_details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $order['details'] = $order_details;
            

        }
    }
}

$page_title = $is_admin ? "Búsqueda de Pedidos" : "Mis Pedidos";
require_once 'includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-receipt"></i> <?php echo $is_admin ? "Búsqueda de Pedidos" : "Mis Pedidos"; ?></h1>
    
    <div class="search-order">
        <h2><i class="fas fa-search"></i> Buscar Pedido</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="order_code">Código de Pedido:</label>
                <input type="text" id="order_code" name="order_code" required 
                       placeholder="Ingrese el código de pedido (ej: F_15_2023)"
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <?php if ($is_admin): ?>
                <small>Como administrador, puedes buscar cualquier pedido del sistema.</small>
                <?php else: ?>
                <small>Ingrese el código que le proporcionó el administrador.</small>
                <?php endif; ?>
            </div>
            <button type="submit" name="search_order" class="btn btn-primary"><i class="fas fa-search"></i> Buscar Pedido</button>
        </form>
        
        <?php if ($error): ?>
        <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
    </div>
    
    <?php if ($order): ?>
    <div class="order-details">
        <h2><i class="fas fa-file-invoice"></i> Detalles del Pedido</h2>
        
        <?php if ($is_admin && isset($order['client_name'])): ?>
        <div class="order-info">
            <p><strong>Cliente:</strong> <?php echo $order['client_name']; ?></p>
            <p><strong>Teléfono:</strong> <?php echo $order['phone']; ?></p>
            <p><strong>Dirección:</strong> <?php echo $order['address']; ?></p>
        </div>
        <?php endif; ?>
        
        <div class="order-info">
            <p><strong><i class="fas fa-hashtag"></i> Código:</strong> <?php echo $order['invoice_id']; ?></p>
            <p><strong><i class="fas fa-calendar-alt"></i> Fecha de Pedido:</strong> <?php echo date('d/m/Y', strtotime($order['order_date'])); ?></p>
            <p><strong><i class="fas fa-truck"></i> Fecha de Entrega:</strong> <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></p>
            <p><strong><i class="fas fa-tasks"></i> Estado:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo $order['status']; ?></span></p>
            <p><strong><i class="fas fa-money-bill-wave"></i> Importe Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong><i class="fas fa-credit-card"></i> Pagado:</strong> $<?php echo number_format($order['paid_amount'], 2); ?></p>
            <p><strong><i class="fas fa-home"></i> Domicilio:</strong> <?php echo $order['delivery']; ?></p>
        </div>
        
        <h3><i class="fas fa-list-ul"></i> Detalles de los Servicios</h3>
        <table class="order-items">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Descuento</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['details'] as $detail): ?>
                <tr>
                    <td><?php echo $detail['description']; ?></td>
                    <td>$<?php echo number_format($detail['price'], 2); ?></td>
                    <td><?php echo $detail['quantity']; ?></td>
                    <td>$<?php echo number_format($detail['discount'], 2); ?></td>
                    <td>$<?php echo number_format($detail['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($is_admin): ?>
        <div class="admin-actions" style="margin-top: 20px;">
            <a href="admin/invoices.php?edit=<?php echo $order['invoice_id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar Pedido
            </a>
        </div>
        
<div class="whatsapp-section">
    <h2>Contactar por WhatsApp</h2>
    <p>Puedes enviar un mensaje al cliente con los datos de la factura.</p>
    
    <?php if (!empty($order['phone'])): 
        // Crear el mensaje para WhatsApp con emojis
        $message = "¡Hola Bendiciones! 🌟 Somos Digital-Print-Fiesta 🥳 Aquí están los datos de su factura:\n\n";
        $message .= "📅 Fecha de Pedido: " . date('d/m/Y', strtotime($order['order_date'])) . "\n";
        $message .= "🚚 Fecha de Entrega: " . date('d/m/Y', strtotime($order['delivery_date'])) . "\n";
        $message .= "📊 Estado: " . $order['status'] . "\n";
        $message .= "💰 Importe Total: $" . number_format($order['total_amount'], 2) . "\n";
        $message .= "💵 Pagado: $" . number_format($order['paid_amount'], 2) . "\n";
        $message .= "🏠 Domicilio: " . $order['delivery'] . "\n";
        $message .= "📋 ID de Factura: " . $order['invoice_id'] . "\n\n";
        $message .= "¡Gracias por su preferencia! 😊";
        
        // Codificar el mensaje para URL
        $encodedMessage = urlencode($message);
        $whatsappUrl = "https://wa.me/{$order['phone']}?text={$encodedMessage}";
    ?>
    
    <div class="order-info">
        <p><span class="emoji">📅</span> <strong>Fecha de Pedido:</strong> <?php echo date('d/m/Y', strtotime($order['order_date'])); ?></p>
        <p><span class="emoji">🚚</span> <strong>Fecha de Entrega:</strong> <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></p>
        <p><span class="emoji">📊</span> <strong>Estado:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo $order['status']; ?></span></p>
        <p><span class="emoji">💰</span> <strong>Importe Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
        <p><span class="emoji">💵</span> <strong>Pagado:</strong> $<?php echo number_format($order['paid_amount'], 2); ?></p>
        <p><span class="emoji">🏠</span> <strong>Domicilio:</strong> <?php echo $order['delivery']; ?></p>
        <p><span class="emoji">📋</span> <strong>ID de Factura:</strong> <?php echo $order['invoice_id']; ?></p>
    </div>
    
    <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="btn btn-whatsapp">
        💬 Enviar mensaje por WhatsApp
    </a>
    
    <?php else: ?>
    <p class="error-message">❌ El número de WhatsApp no está configurado. Contacte al administrador.</p>
    <?php endif; ?>
</div>

<style>
.emoji {
    font-size: 1.2em;
    margin-right: 5px;
    vertical-align: middle;
}

.btn-whatsapp {
    display: inline-block;
    background-color: #25D366;
    color: white;
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    margin-top: 15px;
    transition: background-color 0.3s;
}

.btn-whatsapp:hover {
    background-color: #128C7E;
}

.order-info {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin: 15px 0;
}

.error-message {
    color: #ff3860;
    background-color: #ffebee;
    padding: 10px;
    border-radius: 5px;
}

.status-completado {
    color: #23d160;
    font-weight: bold;
}

.status-pendiente {
    color: #ffdd57;
    font-weight: bold;
}

.status-procesando {
    color: #209cee;
    font-weight: bold;
}
</style>

    </div>
        
        
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>





