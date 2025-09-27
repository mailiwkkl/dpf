<?php
require_once '../config.php';
require_once '../functions.php';

// Verificar la ruta correcta para config.php
$config_path = __DIR__ . '/../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die('Error: No se puede encontrar el archivo de configuración.');
}

// Verificar si functions.php existe
$functions_path = __DIR__ . '/../functions.php';
if (file_exists($functions_path)) {
    require_once $functions_path;
} else {
    die('Error: No se puede encontrar el archivo de funciones.');
}

// Verificar si el usuario está logueado y es administrador
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Obtener todos los clientes registrados
$database = new Database();
$db = $database->getConnection();

// Obtener clientes registrados y nombres únicos de facturas
$clients_query = "SELECT id, name, phone, address FROM users WHERE role = 'client' ORDER BY name";
$clients_stmt = $db->prepare($clients_query);
$clients_stmt->execute();
$registered_clients = $clients_stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener nombres únicos de clientes de facturas (no registrados)
$invoice_clients_query = "SELECT DISTINCT client_name, phone, address 
                         FROM invoices 
                         WHERE client_id IS NULL 
                         ORDER BY client_name";
$invoice_clients_stmt = $db->prepare($invoice_clients_query);
$invoice_clients_stmt->execute();
$invoice_clients = $invoice_clients_stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear un array unificado con estructura consistente
$all_clients = array();

// Procesar clientes registrados
foreach ($registered_clients as $client) {
    $all_clients[] = array(
        'id' => $client['id'],
        'name' => $client['name'],
        'phone' => $client['phone'],
        'address' => $client['address'],
        'type' => 'registered'
    );
}

// Procesar clientes de facturas (no registrados)
foreach ($invoice_clients as $client) {
    $all_clients[] = array(
        'id' => null, // No tienen ID
        'name' => $client['client_name'],
        'phone' => $client['phone'],
        'address' => $client['address'],
        'type' => 'unregistered'
    );
}
// Ordenar alfabéticamente por nombre
usort($all_clients, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Obtener todos los estados disponibles para el filtro
$statuses_query = "SELECT DISTINCT status FROM invoices ORDER BY status";
$statuses_stmt = $db->prepare($statuses_query);
$statuses_stmt->execute();
$available_statuses = $statuses_stmt->fetchAll(PDO::FETCH_COLUMN);

// Manejar el filtro por estado
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = '';
$query_params = [];

// Construir la consulta con filtro si se aplica
if (!empty($status_filter)) {
    $where_clause = "WHERE i.status = :status";
    $query_params[':status'] = $status_filter;
}

// Obtener todas las facturas (con filtro si aplica)
$invoices_query = "SELECT i.*, COALESCE(u.name, i.client_name) as client_display_name 
                   FROM invoices i 
                   LEFT JOIN users u ON i.client_id = u.id 
                   $where_clause
                   ORDER BY i.created_at DESC";
$invoices_stmt = $db->prepare($invoices_query);

// Aplicar parámetros si hay filtro
if (!empty($status_filter)) {
    $invoices_stmt->bindParam(':status', $status_filter);
}

$invoices_stmt->execute();
$invoices = $invoices_stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar confirmación de cliente existente
if (isset($_GET['confirm_client']) && isset($_SESSION['client_confirmation'])) {
    $confirmation_data = $_SESSION['client_confirmation'];
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['client_action'])) {
            $db->beginTransaction();
            
            if ($_POST['client_action'] == 'update') {
                // Actualizar cliente existente
                $update_query = "UPDATE users SET name = :name, address = :address WHERE id = :id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':name', $confirmation_data['new_name']);
                $update_stmt->bindParam(':address', $confirmation_data['new_address']);
                $update_stmt->bindParam(':id', $confirmation_data['existing_id']);
                $update_stmt->execute();
                
                $client_id = $confirmation_data['existing_id'];
            } else {
                // Usar cliente existente sin cambios
                $client_id = $confirmation_data['existing_id'];
            }
            
            // Restaurar datos del formulario
            $_POST = $confirmation_data['form_data'];
            $_POST['client_id'] = $client_id;
            
            unset($_SESSION['client_confirmation']);
            $db->commit();
            
            // Continuar con el procesamiento normal
            // [El código se re-ejecutará con los nuevos datos]
        }
    } else {
        // Mostrar página de confirmación
        $page_title = "Confirmación de Cliente";
        require_once '../includes/headera.php';
        
        // Obtener información del cliente existente
        $existing_client_query = "SELECT name, phone, address FROM users WHERE id = :id";
        $existing_client_stmt = $db->prepare($existing_client_query);
        $existing_client_stmt->bindParam(':id', $confirmation_data['existing_id']);
        $existing_client_stmt->execute();
        $existing_client = $existing_client_stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        
        <div class="container">
            <h1><i class="fas fa-exclamation-triangle"></i> Confirmación Requerida</h1>
            
            <div class="confirmation-box">
                <p>El número de teléfono <strong><?php echo $confirmation_data['new_phone']; ?></strong> ya existe en nuestro sistema.</p>
                
                <div class="client-comparison">
                    <div class="existing-client">
                        <h3><i class="fas fa-user"></i> Cliente Existente</h3>
                        <p><strong>Nombre:</strong> <?php echo $existing_client['name']; ?></p>
                        <p><strong>Teléfono:</strong> <?php echo $existing_client['phone']; ?></p>
                        <p><strong>Dirección:</strong> <?php echo $existing_client['address']; ?></p>
                    </div>
                    
                    <div class="new-client">
                        <h3><i class="fas fa-user-plus"></i> Datos Ingresados</h3>
                        <p><strong>Nombre:</strong> <?php echo $confirmation_data['new_name']; ?></p>
                        <p><strong>Teléfono:</strong> <?php echo $confirmation_data['new_phone']; ?></p>
                        <p><strong>Dirección:</strong> <?php echo $confirmation_data['new_address']; ?></p>
                    </div>
                </div>
                
                <form method="POST" class="confirmation-form">
                    <div class="form-group">
                        <label>¿Qué desea hacer?</label>
                        <div class="radio-options">
                            <label>
                                <input type="radio" name="client_action" value="use_existing" checked>
                                Usar el cliente existente sin cambios
                            </label>
                            <label>
                                <input type="radio" name="client_action" value="update">
                                Actualizar el cliente existente con los nuevos datos
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Continuar
                        </button>
                        <a href="invoices.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        require_once '../includes/footer.php';
        exit();
    }
}
// Procesar creación/edición de factura
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = !empty($_POST['client_id']) ? $_POST['client_id'] : null;
    $client_name = trim($_POST['client_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $order_date = $_POST['order_date'];
    $delivery_date = $_POST['delivery_date'];
    $delivery = $_POST['delivery'];
    $status = $_POST['status'];
    $paid_amount = $_POST['paid_amount'] ?: 0;
    
    // Validar campos obligatorios
    if (empty($client_name) || empty($phone) || empty($address) || empty($order_date) || empty($delivery_date)) {
        $error = "Todos los campos obligatorios deben ser completados";
    } else {
        // Obtener detalles
        $descriptions = $_POST['description'] ?? [];
        $prices = $_POST['price'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $discounts = $_POST['discount'] ?? [];
        
        if (empty($descriptions) || count($descriptions) == 0) {
            $error = "Debe agregar al menos un detalle al pedido";
        } else {
            try {
                $db->beginTransaction();
                
                // Si no hay client_id seleccionado pero se ingresaron datos de cliente
                if (empty($client_id) && !empty($client_name) && !empty($phone)) {
                    // Verificar si el teléfono ya existe en usuarios registrados
                    $check_phone_query = "SELECT id FROM users WHERE phone = :phone";
                    $check_phone_stmt = $db->prepare($check_phone_query);
                    $check_phone_stmt->bindParam(':phone', $phone);
                    $check_phone_stmt->execute();
                    
                    if ($check_phone_stmt->rowCount() > 0) {
                        // Teléfono existe, preguntar si quiere usar el cliente existente
                        $existing_client = $check_phone_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Guardar en sesión para mostrar confirmación
                        $_SESSION['client_confirmation'] = [
                            'existing_id' => $existing_client['id'],
                            'new_name' => $client_name,
                            'new_phone' => $phone,
                            'new_address' => $address,
                            'form_data' => $_POST
                        ];
                        
                        $db->rollBack();
                        header('Location: invoices.php?confirm_client=1');
                        exit();
                    }
                    // Si no existe, no crear usuario, solo usar los datos en la factura
                    // client_id permanecerá null
                }
                
                // Calcular total
                $total_amount = 0;
                $details = [];
                
                foreach ($descriptions as $index => $description) {
                    if (empty($description)) continue; // Saltar descripciones vacías
                    
                    $price = floatval($prices[$index] ?? 0);
                    $quantity = intval($quantities[$index] ?? 1);
                    $discount = floatval($discounts[$index] ?? 0);
                    $amount = ($price * $quantity) - $discount;
                    
                    $total_amount += $amount;
                    
                    $details[] = [
                        'description' => $description,
                        'price' => $price,
                        'quantity' => $quantity,
                        'discount' => $discount,
                        'amount' => $amount
                    ];
                }
                
                if (empty($details)) {
                    throw new Exception("Debe agregar al menos un detalle válido al pedido");
                }
                
                if (isset($_POST['invoice_id'])) {
                    // Editar factura existente
                    $invoice_id = $_POST['invoice_id'];
                    
                    $update_query = "UPDATE invoices SET 
                                    client_id = :client_id, 
                                    client_name = :client_name,
                                    phone = :phone, 
                                    address = :address, 
                                    order_date = :order_date, 
                                    delivery_date = :delivery_date, 
                                    total_amount = :total_amount, 
                                    paid_amount = :paid_amount, 
                                    status = :status, 
                                    delivery = :delivery 
                                    WHERE invoice_id = :invoice_id";
                    
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                    $update_stmt->bindParam(':client_name', $client_name);
                    $update_stmt->bindParam(':phone', $phone);
                    $update_stmt->bindParam(':address', $address);
                    $update_stmt->bindParam(':order_date', $order_date);
                    $update_stmt->bindParam(':delivery_date', $delivery_date);
                    $update_stmt->bindParam(':total_amount', $total_amount);
                    $update_stmt->bindParam(':paid_amount', $paid_amount);
                    $update_stmt->bindParam(':status', $status);
                    $update_stmt->bindParam(':delivery', $delivery);
                    $update_stmt->bindParam(':invoice_id', $invoice_id);
                    
                    if ($update_stmt->execute()) {
                        // Eliminar detalles existentes
                        $delete_details = "DELETE FROM invoice_details WHERE invoice_id = :invoice_id";
                        $delete_stmt = $db->prepare($delete_details);
                        $delete_stmt->bindParam(':invoice_id', $invoice_id);
                        $delete_stmt->execute();
                        
                        // Insertar nuevos detalles
                        foreach ($details as $detail) {
                            $detail_query = "INSERT INTO invoice_details 
                                            (invoice_id, description, price, quantity, discount, amount) 
                                            VALUES 
                                            (:invoice_id, :description, :price, :quantity, :discount, :amount)";
                            
                            $detail_stmt = $db->prepare($detail_query);
                            $detail_stmt->bindParam(':invoice_id', $invoice_id);
                            $detail_stmt->bindParam(':description', $detail['description']);
                            $detail_stmt->bindParam(':price', $detail['price']);
                            $detail_stmt->bindParam(':quantity', $detail['quantity']);
                            $detail_stmt->bindParam(':discount', $detail['discount']);
                            $detail_stmt->bindParam(':amount', $detail['amount']);
                            $detail_stmt->execute();
                        }
                        
                        // Crear notificación para el cliente si está registrado
                        if ($client_id) {
                            $message = "Su pedido {$invoice_id} ha sido actualizado. Estado: {$status}";
                            createNotification($client_id, $message, $invoice_id);
                        }
                        
                        $db->commit();
                        $success = "Pedido actualizado correctamente";
                    } else {
                        throw new Exception("Error al actualizar el pedido: " . implode(", ", $update_stmt->errorInfo()));
                    }
                } else {
                    // Crear nueva factura
                    $insert_query = "INSERT INTO invoices 
                                    (client_id, client_name, phone, address, order_date, delivery_date, total_amount, paid_amount, status, delivery) 
                                    VALUES 
                                    (:client_id, :client_name, :phone, :address, :order_date, :delivery_date, :total_amount, :paid_amount, :status, :delivery)";
                    
                    $insert_stmt = $db->prepare($insert_query);
                    $insert_stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                    $insert_stmt->bindParam(':client_name', $client_name);
                    $insert_stmt->bindParam(':phone', $phone);
                    $insert_stmt->bindParam(':address', $address);
                    $insert_stmt->bindParam(':order_date', $order_date);
                    $insert_stmt->bindParam(':delivery_date', $delivery_date);
                    $insert_stmt->bindParam(':total_amount', $total_amount);
                    $insert_stmt->bindParam(':paid_amount', $paid_amount);
                    $insert_stmt->bindParam(':status', $status);
                    $insert_stmt->bindParam(':delivery', $delivery);
                    
                    if ($insert_stmt->execute()) {
                        $invoice_db_id = $db->lastInsertId();
                        $invoice_id = generateInvoiceId($invoice_db_id);
                        
                        // Actualizar con el ID generado
                        $update_id_query = "UPDATE invoices SET invoice_id = :invoice_id WHERE id = :id";
                        $update_id_stmt = $db->prepare($update_id_query);
                        $update_id_stmt->bindParam(':invoice_id', $invoice_id);
                        $update_id_stmt->bindParam(':id', $invoice_db_id);
                        $update_id_stmt->execute();
                        
                        // Insertar detalles
                        foreach ($details as $detail) {
                            $detail_query = "INSERT INTO invoice_details 
                                            (invoice_id, description, price, quantity, discount, amount) 
                                            VALUES 
                                            (:invoice_id, :description, :price, :quantity, :discount, :amount)";
                            
                            $detail_stmt = $db->prepare($detail_query);
                            $detail_stmt->bindParam(':invoice_id', $invoice_id);
                            $detail_stmt->bindParam(':description', $detail['description']);
                            $detail_stmt->bindParam(':price', $detail['price']);
                            $detail_stmt->bindParam(':quantity', $detail['quantity']);
                            $detail_stmt->bindParam(':discount', $detail['discount']);
                            $detail_stmt->bindParam(':amount', $detail['amount']);
                            $detail_stmt->execute();
                        }
                        
                        // Crear notificación para el cliente si está registrado
                        if ($client_id) {
                            $message = "Se ha creado un nuevo pedido para usted. Número: {$invoice_id}";
                            createNotification($client_id, $message, $invoice_id);
                        }
                        
                        $db->commit();
                        $success = "Pedido creado correctamente. Número: {$invoice_id}";
                    } else {
                        throw new Exception("Error al crear el pedido: " . implode(", ", $insert_stmt->errorInfo()));
                    }
                }
                
                header('Location: invoices.php');
                exit();
                
            } catch (Exception $e) {
                $db->rollBack();
                $error = "Error al procesar el pedido: " . $e->getMessage();
                error_log("Error en invoices.php: " . $e->getMessage());
            }
        }
    }
}

// Procesar eliminación de factura
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    try {
        $db->beginTransaction();
        
        // Eliminar detalles
        $delete_details = "DELETE FROM invoice_details WHERE invoice_id = :invoice_id";
        $delete_details_stmt = $db->prepare($delete_details);
        $delete_details_stmt->bindParam(':invoice_id', $delete_id);
        $delete_details_stmt->execute();
        
        // Eliminar factura
        $delete_invoice = "DELETE FROM invoices WHERE invoice_id = :invoice_id";
        $delete_invoice_stmt = $db->prepare($delete_invoice);
        $delete_invoice_stmt->bindParam(':invoice_id', $delete_id);
        
        if ($delete_invoice_stmt->execute()) {
            $db->commit();
            $success = "Pedido eliminado correctamente";
            header('Location: invoices.php');
            exit();
        }
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error al eliminar el pedido: " . $e->getMessage();
    }
}

// Obtener datos para edición
$edit_invoice = null;
$edit_details = [];
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    
    $edit_query = "SELECT * FROM invoices WHERE invoice_id = :invoice_id";
    $edit_stmt = $db->prepare($edit_query);
    $edit_stmt->bindParam(':invoice_id', $edit_id);
    $edit_stmt->execute();
    $edit_invoice = $edit_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($edit_invoice) {
        $details_query = "SELECT * FROM invoice_details WHERE invoice_id = :invoice_id";
        $details_stmt = $db->prepare($details_query);
        $details_stmt->bindParam(':invoice_id', $edit_id);
        $details_stmt->execute();
        $edit_details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$page_title = "Gestión de Pedidos";
require_once '../includes/headera.php';
?>

<div class="container">
    <h1><i class="fas fa-file-invoice"></i> Gestión de Pedidos</h1>
    
    <?php if ($error): ?>
    <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Botón para crear nuevo pedido -->
    <div class="action-buttons" style="margin-bottom: 20px;">
        <button type="button" class="btn btn-primary" id="createInvoiceBtn">
            <i class="fas fa-plus"></i> Crear Nuevo Pedido
        </button>
    </div>
    
    <!-- Modal para crear/editar pedido -->
    <div id="invoiceModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-<?php echo $edit_invoice ? 'edit' : 'plus'; ?>"></i> <?php echo $edit_invoice ? 'Editar' : 'Crear'; ?> Pedido</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" id="invoiceForm">
                    <?php if ($edit_invoice): ?>
                    <input type="hidden" name="invoice_id" value="<?php echo $edit_invoice['invoice_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                         <label for="client_id"><i class="fas fa-user"></i> Cliente Existente:</label>
    <select id="client_id" name="client_id">
        <option value="">Seleccione un cliente existente</option>
        <?php foreach ($all_clients as $client): 
            $client_name_val = isset($client['name']) ? $client['name'] : ($client['client_name'] ?? '');
            $client_id_value = isset($client['id']) ? $client['id'] : '';
            
            // Para clientes registrados (de la tabla users)
            if (isset($client['id'])) {
                $client_phone = $client['phone'] ?? '';
                $client_address = $client['address'] ?? '';
            } 
            // Para clientes no registrados (de la tabla invoices)
            else {
                $client_phone = $client['phone'] ?? '';
                $client_address = $client['address'] ?? '';
                // Para clientes no registrados, el valor será vacío pero con datos en los atributos
                $client_id_value = ''; // Asegurar que sea vacío para no registrados
            }
        ?>
        <option value="<?php echo $client_id_value; ?>" 
                <?php if ($edit_invoice && $edit_invoice['client_id'] == $client_id_value) echo 'selected'; ?>
                data-phone="<?php echo htmlspecialchars($client_phone); ?>" 
                data-address="<?php echo htmlspecialchars($client_address); ?>"
                data-name="<?php echo htmlspecialchars($client_name_val); ?>">
            <?php echo htmlspecialchars($client_name_val); ?>
            <?php if (isset($client['id'])): ?><?php else: ?><?php endif; ?>
        </option>
        <?php endforeach; ?>
    </select>
    <small>Seleccione un cliente existente o complete los datos manualmente</small>   
                        </div>
                        
                        <div class="form-group">
                            <label for="client_name"><i class="fas fa-user"></i> Nombre del Cliente *</label>
                            <input type="text" id="client_name" name="client_name" required 
                                   value="<?php echo $edit_invoice ? $edit_invoice['client_name'] : ''; ?>"
                                   placeholder="Nombre completo del cliente">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Teléfono *</label>
                            <input type="text" id="phone" name="phone" required 
                                   value="<?php echo $edit_invoice ? $edit_invoice['phone'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery"><i class="fas fa-truck"></i> Domicilio</label>
                            <select id="delivery" name="delivery">
                                <option value="No" <?php if ($edit_invoice && $edit_invoice['delivery'] == 'No') echo 'selected'; ?>>No</option>
                                <option value="Si" <?php if ($edit_invoice && $edit_invoice['delivery'] == 'Si') echo 'selected'; ?>>Sí</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Dirección *</label>
                        <textarea id="address" name="address" required rows="3" style="font-family: inherit; font-size: inherit;"><?php echo $edit_invoice ? $edit_invoice['address'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="order_date"><i class="fas fa-calendar-alt"></i> Fecha de Pedido *</label>
                            <input type="date" id="order_date" name="order_date" required 
                                   value="<?php echo $edit_invoice ? $edit_invoice['order_date'] : date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery_date"><i class="fas fa-calendar-check"></i> Fecha de Entrega *</label>
                            <input type="date" id="delivery_date" name="delivery_date" required 
                                   value="<?php echo $edit_invoice ? $edit_invoice['delivery_date'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status"><i class="fas fa-tasks"></i> Estado</label>
                            <select id="status" name="status">
                                <option value="Pendiente de pago" <?php if ($edit_invoice && $edit_invoice['status'] == 'Pendiente de pago') echo 'selected'; ?>>Pendiente de pago</option>
                                <option value="Pagado en espera" <?php if ($edit_invoice && $edit_invoice['status'] == 'Pagado en espera') echo 'selected'; ?>>Pagado en espera</option>
                                <option value="Pagado en proceso" <?php if ($edit_invoice && $edit_invoice['status'] == 'Pagado en proceso') echo 'selected'; ?>>Pagado en proceso</option>
                                <option value="Terminado" <?php if ($edit_invoice && $edit_invoice['status'] == 'Terminado') echo 'selected'; ?>>Terminado</option>
                                <option value="Entregado" <?php if ($edit_invoice && $edit_invoice['status'] == 'Entregado') echo 'selected'; ?>>Entregado</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="paid_amount"><i class="fas fa-money-bill-wave"></i> Pagado ($)</label>
                            <input type="number" id="paid_amount" name="paid_amount" step="0.01" 
                                   value="<?php echo $edit_invoice ? $edit_invoice['paid_amount'] : '0'; ?>">
                        </div>
                    </div>
                    
                    <h3><i class="fas fa-list"></i> Detalles del Pedido</h3>
                    <div id="invoiceDetails">
                        <?php if (!empty($edit_details)): ?>
                            <?php foreach ($edit_details as $index => $detail): ?>
                            <div class="detail-row">
                                <div class="form-group">
                                    <label>Descripción *</label>
                                    <input type="text" name="description[]" value="<?php echo $detail['description']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Precio *</label>
                                    <input type="number" name="price[]" step="0.01" value="<?php echo $detail['price']; ?>" required min="0">
                                </div>
                                <div class="form-group">
                                    <label>Cantidad *</label>
                                    <input type="number" name="quantity[]" value="<?php echo $detail['quantity']; ?>" required min="1">
                                </div>
                                <div class="form-group">
                                    <label>Descuento</label>
                                    <input type="number" name="discount[]" step="0.01" value="<?php echo $detail['discount']; ?>" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Importe</label>
                                    <input type="text" value="$<?php echo number_format($detail['amount'], 2); ?>" readonly class="amount-display">
                                </div>
                                <button type="button" class="btn btn-danger remove-detail"><i class="fas fa-trash"></i></button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="detail-row">
                            <div class="form-group">
                                <label>Descripción *</label>
                                <input type="text" name="description[]" required>
                            </div>
                            <div class="form-group">
                                <label>Precio *</label>
                                <input type="number" name="price[]" step="0.01" required min="0">
                            </div>
                            <div class="form-group">
                                <label>Cantidad *</label>
                                <input type="number" name="quantity[]" value="1" required min="1">
                            </div>
                            <div class="form-group">
                                <label>Descuento</label>
                                <input type="number" name="discount[]" step="0.01" value="0" min="0">
                            </div>
                            <div class="form-group">
                                <label>Importe</label>
                                <input type="text" value="$0.00" readonly class="amount-display">
                            </div>
                            <button type="button" class="btn btn-danger remove-detail"><i class="fas fa-trash"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="addDetail" class="btn btn-secondary"><i class="fas fa-plus"></i> Agregar Detalle</button>
                    
                    <div class="form-group">
                        <label for="total_amount"><i class="fas fa-calculator"></i> Total del Pedido</label>
                        <input type="text" id="total_amount" name="total_amount" readonly 
                               value="$<?php echo $edit_invoice ? number_format($edit_invoice['total_amount'], 2) : '0.00'; ?>">
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $edit_invoice ? 'save' : 'plus'; ?>"></i> <?php echo $edit_invoice ? 'Actualizar' : 'Crear'; ?> Pedido
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="invoices-list">
        <h2><i class="fas fa-list"></i> Lista de Pedidos</h2>
        
        <!-- Filtro por estado -->
        <div class="status-filter">
            <form method="GET" action="invoices.php" class="filter-form">
                <div class="form-group">
                    <label for="status_filter"><i class="fas fa-filter"></i> Filtrar por estado:</label>
                    <select id="status_filter" name="status" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <?php foreach ($available_statuses as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo $status_filter == $status ? 'selected' : ''; ?>>
                            <?php echo $status; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($status_filter)): ?>
                <a href="invoices.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar filtro
                </a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if (empty($invoices)): ?>
        <div class="empty-state">
            <p><i class="fas fa-inbox"></i> No hay pedidos <?php echo !empty($status_filter) ? 'con el estado "' . $status_filter . '"' : 'registrados'; ?>.</p>
        </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> Número</th>
                    <th><i class="fas fa-user"></i> Cliente</th>
                    <th><i class="fas fa-calendar-alt"></i> Fecha Entrega</th>
                    <th><i class="fas fa-dollar-sign"></i> Total</th>
                    <th><i class="fas fa-money-bill-wave"></i> Pagado</th>
                    <th><i class="fas fa-tasks"></i> Estado</th>
                    <th><i class="fas fa-cog"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td>
                        <a href="<?php echo getBaseUrl(); ?>orders.php?search=<?php echo $invoice['invoice_id']; ?>" 
                           target="_blank" 
                           title="Ver este pedido en Orders" 
                           class="order-link">
                            <i class="fas fa-external-link-alt"></i> <?php echo $invoice['invoice_id']; ?>
                        </a>
                    </td>
                    <td>
                        <?php echo $invoice['client_display_name']; ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($invoice['delivery_date'])); ?></td>
                    <td>$<?php echo number_format($invoice['total_amount'], 2); ?></td>
                    <td>$<?php echo number_format($invoice['paid_amount'], 2); ?></td>
                    <td>
                        <span class="status-<?php echo strtolower(str_replace(' ', '-', $invoice['status'])); ?>">
                            <?php echo $invoice['status']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="invoices.php?edit=<?php echo $invoice['invoice_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="invoices.php?delete=<?php echo $invoice['invoice_id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('¿Está seguro de eliminar este pedido?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('invoiceModal');
    const createBtn = document.getElementById('createInvoiceBtn');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');
const clientSelect = document.getElementById('client_id');
    const clientNameInput = document.getElementById('client_name');
    const phoneInput = document.getElementById('phone');
    const addressInput = document.getElementById('address');
    
    if (clientSelect && clientNameInput && phoneInput && addressInput) {
        clientSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const phone = selectedOption.getAttribute('data-phone') || '';
                const address = selectedOption.getAttribute('data-address') || '';
                const name = selectedOption.getAttribute('data-name') || selectedOption.text;
                
                // Solo actualizar si hay datos en los atributos
                if (phone) phoneInput.value = phone;
                if (address) addressInput.value = address;
                if (name && name !== 'Seleccione un cliente existente') {
                    clientNameInput.value = name;
                }
            }
        });
        
        // Llenar automáticamente si ya hay un cliente seleccionado al cargar la página
        if (clientSelect.value) {
            const selectedOption = clientSelect.options[clientSelect.selectedIndex];
            if (selectedOption) {
                const phone = selectedOption.getAttribute('data-phone') || '';
                const address = selectedOption.getAttribute('data-address') || '';
                const name = selectedOption.getAttribute('data-name') || selectedOption.text;
                
                if (phone) phoneInput.value = phone;
                if (address) addressInput.value = address;
                if (name && name !== 'Seleccione un cliente existente') {
                    clientNameInput.value = name;
                }
            }
        }
    }
    
    // Función para limpiar completamente el formulario
    function resetForm() {
        document.getElementById('invoiceForm').reset();
        document.getElementById('order_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('delivery_date').value = '';
        document.getElementById('total_amount').value = '$0.00';
        
        // Limpiar detalles adicionales, mantener solo uno
        const detailsContainer = document.getElementById('invoiceDetails');
        const firstDetailRow = detailsContainer.querySelector('.detail-row');
        detailsContainer.innerHTML = '';
        detailsContainer.appendChild(firstDetailRow);
        
        // Resetear el primer detalle
        const firstRow = detailsContainer.querySelector('.detail-row');
        firstRow.querySelector('input[name="description[]"]').value = '';
        firstRow.querySelector('input[name="price[]"]').value = '';
        firstRow.querySelector('input[name="quantity[]"]').value = '1';
        firstRow.querySelector('input[name="discount[]"]').value = '0';
        firstRow.querySelector('.amount-display').value = '$0.00';
        
        // Resetear campos de cliente
        clientSelect.value = '';
        clientNameInput.value = '';
        phoneInput.value = '';
        addressInput.value = '';
        clientNameInput.readOnly = false;
        phoneInput.readOnly = false;
        addressInput.readOnly = false;
        
        // Re-inicializar event listeners
        initDetailEventListeners();
    }
    
    // Abrir modal para crear nuevo pedido
    createBtn.addEventListener('click', function() {
        resetForm();
        modal.style.display = 'block';
    });
    
    // Si hay parámetro de edición en la URL, abrir modal automáticamente
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit')) {
        modal.style.display = 'block';
        
        // Configurar autocompletado para edición
        if (clientSelect.value) {
            const selectedOption = clientSelect.options[clientSelect.selectedIndex];
            if (selectedOption) {
                clientNameInput.value = selectedOption.getAttribute('data-name') || '';
                phoneInput.value = selectedOption.getAttribute('data-phone') || '';
                addressInput.value = selectedOption.getAttribute('data-address') || '';
                
                // Solo hacer campos de solo lectura si se seleccionó un cliente existente
                if (selectedOption.value) {
                    clientNameInput.readOnly = true;
                    phoneInput.readOnly = true;
                    addressInput.readOnly = true;
                }
            }
        }
    }
    
    // Cerrar modal
    function closeModal() {
        modal.style.display = 'none';
        // Limpiar parámetros de edición de la URL sin recargar
        if (urlParams.has('edit')) {
            const newUrl = window.location.pathname + (urlParams.has('status') ? '?status=' + urlParams.get('status') : '');
            window.history.replaceState({}, document.title, newUrl);
        }
    }
    
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
   
    
    // Inicializar event listeners para detalles
    function initDetailEventListeners() {
        document.querySelectorAll('.detail-row').forEach(row => {
            addDetailEventListeners(row);
        });
    }
    
    // Agregar nuevo detalle
    document.getElementById('addDetail').addEventListener('click', function() {
        const detailsContainer = document.getElementById('invoiceDetails');
        const newRow = document.createElement('div');
        newRow.className = 'detail-row';
        newRow.innerHTML = `
            <div class="form-group">
                <label>Descripción *</label>
                <input type="text" name="description[]" required>
            </div>
            <div class="form-group">
                <label>Precio *</label>
                <input type="number" name="price[]" step="0.01" required min="0">
            </div>
            <div class="form-group">
                <label>Cantidad *</label>
                <input type="number" name="quantity[]" value="1" required min="1">
            </div>
            <div class="form-group">
                <label>Descuento</label>
                <input type="number" name="discount[]" step="0.01" value="0" min="0">
            </div>
            <div class="form-group">
                <label>Importe</label>
                <input type="text" value="$0.00" readonly class="amount-display">
            </div>
            <button type="button" class="btn btn-danger remove-detail"><i class="fas fa-trash"></i></button>
        `;
        detailsContainer.appendChild(newRow);
        
        // Agregar event listeners a los nuevos campos
        addDetailEventListeners(newRow);
    });
    
    // Eliminar detalle
    function addDetailEventListeners(row) {
        const inputs = row.querySelectorAll('input[name="price[]"], input[name="quantity[]"], input[name="discount[]"]');
        inputs.forEach(input => {
            input.addEventListener('input', calculateAmount);
        });
        
        row.querySelector('.remove-detail').addEventListener('click', function() {
            // No permitir eliminar el último detalle
            if (document.querySelectorAll('.detail-row').length > 1) {
                row.remove();
                calculateTotal();
            } else {
                alert('Debe mantener al menos un detalle en el pedido');
            }
        });
    }
    
    // Calcular importe por fila
    function calculateAmount(event) {
        const row = event.target.closest('.detail-row');
        const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
        const quantity = parseInt(row.querySelector('input[name="quantity[]"]').value) || 0;
        const discount = parseFloat(row.querySelector('input[name="discount[]"]').value) || 0;
        const amount = (price * quantity) - discount;
        
        row.querySelector('.amount-display').value = '$' + amount.toFixed(2);
        calculateTotal();
    }
    
    // Calcular total general
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.detail-row').forEach(row => {
            const amountText = row.querySelector('.amount-display').value;
            const amount = parseFloat(amountText.replace('$', '')) || 0;
            total += amount;
        });
        
        document.getElementById('total_amount').value = '$' + total.toFixed(2);
    }
    
    // Inicializar event listeners para detalles existentes
    initDetailEventListeners();
    
    // Validar formulario antes de enviar
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        const detailRows = document.querySelectorAll('.detail-row');
        if (detailRows.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un detalle al pedido');
            return;
        }
        
        // Validar que cada detalle tenga descripción
        let valid = true;
        detailRows.forEach(row => {
            const description = row.querySelector('input[name="description[]"]').value.trim();
            if (!description) {
                valid = false;
                row.querySelector('input[name="description[]"]').style.borderColor = 'red';
            } else {
                row.querySelector('input[name="description[]"]').style.borderColor = '';
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Todos los detalles deben tener una descripción');
        }
    });
    
    // DEBUG: Verificar que los datos de clientes se cargaron correctamente
    console.log('Clientes cargados en el select:');
    clientSelect.querySelectorAll('option').forEach((option, index) => {
        if (index > 0) { // Saltar la primera opción "Seleccione..."
            console.log(`Opción ${index}:`, {
                value: option.value,
                text: option.text,
                phone: option.getAttribute('data-phone'),
                address: option.getAttribute('data-address'),
                name: option.getAttribute('data-name')
            });
        }
    });
});
</script>
<style>
/* Estilos para el modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    overflow: auto;
}

.modal-content {
    background-color: #fefefe;
    margin: 2% auto;
    padding: 0;
    border-radius: 8px;
    width: 95%;
    max-width: 900px;
    max-height: 95vh;
    overflow-y: auto;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    animation: modalOpen 0.3s ease;
}

@keyframes modalOpen {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    background: #007bff;
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5em;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close:hover {
    color: #ccc;
    transform: scale(1.1);
}

.modal-body {
    padding: 20px;
}

/* Estilos responsivos para el formulario */
.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.form-group {
    margin-bottom: 15px;
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: inherit;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* Estilos para los detalles del pedido */
.detail-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    margin-bottom: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #007bff;
    flex-wrap: wrap;
}

.detail-row .form-group {
    flex: 1;
    margin-bottom: 0;
    min-width: 120px;
}

.amount-display {
    background-color: #e9ecef;
    font-weight: bold;
    color: #495057;
}

.remove-detail {
    padding: 10px 12px;
    margin-bottom: 0;
    flex-shrink: 0;
}

/* Botones */
.form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9em;
    transition: all 0.3s;
    flex: 1;
    min-width: 120px;
    justify-content: center;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-primary { 
    background: #007bff; 
    color: white; 
}

.btn-danger { 
    background: #dc3545; 
    color: white; 
}

.btn-secondary { 
    background: #6c757d; 
    color: white; 
}

/* Media Queries para Responsividad - MÓVIL */
@media (max-width: 768px) {
    .modal-content {
        width: 100%;
        margin: 0;
        max-height: 100vh;
        border-radius: 0;
        height: 100vh;
    }
    
    .modal-header {
        padding: 15px;
        position: relative;
    }
    
    .modal-header h2 {
        font-size: 1.3em;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .form-group {
        min-width: 100%;
        margin-bottom: 10px;
    }
    
    .detail-row {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        padding: 10px;
    }
    
    .detail-row .form-group {
        min-width: 100%;
    }
    
    .remove-detail {
        align-self: flex-end;
        margin-top: 10px;
    }
    
    .form-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}

/* Tabletas pequeñas */
@media (max-width: 1024px) and (min-width: 769px) {
    .modal-content {
        width: 98%;
        margin: 1% auto;
        max-height: 98vh;
    }
    
    .form-group {
        min-width: 150px;
    }
    
    .detail-row .form-group {
        min-width: 100px;
    }
}

/* Móviles muy pequeños */
@media (max-width: 480px) {
    .modal-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .modal-header h2 {
        font-size: 1.2em;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 8px;
        font-size: 14px;
    }
    
    .btn {
        padding: 8px 15px;
        font-size: 0.8em;
    }
    
    .detail-row {
        padding: 8px;
    }
    
    .close {
        font-size: 24px;
        width: 25px;
        height: 25px;
    }
}

/* Estilos para scrollbar en modal */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Resto de estilos mantienen la apariencia original */
.order-link {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

.order-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

.order-link i {
    font-size: 0.8em;
    margin-right: 5px;
}

.status-pendiente-de-pago { background: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 4px; }
.status-pagado-en-espera { background: #cce5ff; color: #004085; padding: 5px 10px; border-radius: 4px; }
.status-pagado-en-proceso { background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 4px; }
.status-terminado { background: #d1ecf1; color: #0c5460; padding: 5px 10px; border-radius: 4px; }
.status-entregado { background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 4px; }

.status-filter {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-form .form-group {
    margin-bottom: 0;
}

.filter-form select {
    min-width: 200px;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    border-left: 4px solid #dc3545;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    border-left: 4px solid #28a745;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}


.confirmation-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.client-comparison {
    display: flex;
    gap: 20px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.existing-client, .new-client {
    flex: 1;
    min-width: 250px;
    padding: 15px;
    border-radius: 6px;
}

.existing-client {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.new-client {
    background: #d1ecf1;
    border-left: 4px solid #17a2b8;
}

.radio-options label {
    display: block;
    margin-bottom: 10px;
    padding: 10px;
    background: white;
    border-radius: 4px;
    cursor: pointer;
}

.radio-options label:hover {
    background: #f8f9fa;
}
</style>

<?php require_once '../includes/footer.php'; ?>