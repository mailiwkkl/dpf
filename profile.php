<?php
require_once 'config.php';
require_once 'functions.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Obtener información del usuario
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($new_password !== $confirm_password) {
        $error = "Las nuevas contraseñas no coinciden";
    } elseif (strlen($new_password) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "La contraseña actual es incorrecta";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = :password WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':password', $hashed_password);
        $update_stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            $success = "Contraseña cambiada exitosamente";
        } else {
            $error = "Error al cambiar la contraseña";
        }
    }
}

// Procesar actualización de información personal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    
    if (empty($name) || empty($phone) || empty($address) || empty($email)) {
        $error = "Todos los campos son obligatorios";
    } else {
        // Verificar si el email ya existe en otro usuario
        $check_query = "SELECT id FROM users WHERE email = :email AND id != :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->bindParam(':id', $_SESSION['user_id']);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = "El email ya está registrado por otro usuario";
        } else {
            $update_query = "UPDATE users SET name = :name, phone = :phone, address = :address, email = :email WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':name', $name);
            $update_stmt->bindParam(':phone', $phone);
            $update_stmt->bindParam(':address', $address);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':id', $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $success = "Información personal actualizada exitosamente";
                // Actualizar la información del usuario en la variable
                $user['name'] = $name;
                $user['phone'] = $phone;
                $user['address'] = $address;
                $user['email'] = $email;
            } else {
                $error = "Error al actualizar la información personal";
            }
        }
    }
}

$page_title = "Mi Perfil";
require_once 'includes/header.php';
?>

<div class="container">
    <h1>Mi Perfil</h1>
    
    <div class="profile-info">
        <div class="profile-header">
            <h2>Información Personal</h2>
            <button type="button" class="btn btn-secondary" onclick="openEditModal()">Editar Información</button>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <strong>Nombre:</strong> <?php echo htmlspecialchars($user['name']); ?>
            </div>
            <div class="info-item">
                <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
            </div>
            <div class="info-item">
                <strong>Teléfono:</strong> <?php echo htmlspecialchars($user['phone']); ?>
            </div>
            <div class="info-item">
                <strong>Dirección:</strong> <?php echo htmlspecialchars($user['address']); ?>
            </div>
            <div class="info-item">
                <strong>Rol:</strong> <?php echo $user['role'] == 'admin' ? 'Administrador' : 'Cliente'; ?>
            </div>
        </div>
    </div>
    
    <div class="change-password">
        <h2>Cambiar Contraseña</h2>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="current_password">Contraseña Actual:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nueva Contraseña:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary">Cambiar Contraseña</button>
        </form>
    </div>
</div>

<!-- Modal para editar información personal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Información Personal</h2>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="" id="editProfileForm">
                <div class="form-group">
                    <label for="edit_name">Nombre completo:</label>
                    <input type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_phone">Teléfono:</label>
                    <input type="tel" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_address">Dirección:</label>
                    <textarea id="edit_address" name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email:</label>
                    <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" name="update_profile" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos para el modal - MEJORADO PARA RESPONSIVE */
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
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
    from {opacity: 0; transform: translateY(-50px);}
    to {opacity: 1; transform: translateY(0);}
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
    background-color: #6a1b9a; /* Color morado para el header */
    color: white;
    border-radius: 8px 8px 0 0;
}

.modal-header h2 {
    margin: 0;
    color: white;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: white;
}

.close:hover {
    color: #f0f0f0;
}

.modal-body {
    padding: 20px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

/* Estilos para formularios responsive */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px; /* Mejor para móviles */
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #6a1b9a; /* Color morado para el focus */
    outline: none;
    box-shadow: 0 0 0 2px rgba(106, 27, 154, 0.2);
}

/* RESPONSIVE: Estilos específicos para móviles */
@media (max-width: 768px) {
    .modal-content {
        margin: 5% auto;
        width: 95%;
        max-width: none;
    }
    
    .modal-header {
        padding: 15px;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .modal-header h2 {
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .modal-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-actions .btn {
        width: 100%;
        margin-bottom: 5px;
    }
    
    .profile-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
}

/* Para pantallas muy pequeñas (móviles en vertical) */
@media (max-width: 480px) {
    .modal-content {
        margin: 2% auto;
        width: 98%;
    }
    
    .modal-header {
        padding: 10px;
    }
    
    .modal-body {
        padding: 10px;
    }
    
    .form-group input,
    .form-group textarea {
        font-size: 14px; /* Ajuste para pantallas muy pequeñas */
    }
}
</style>

<script>
function openEditModal() {
    document.getElementById('editModal').style.display = 'block';
    // Enfocar el primer campo del formulario al abrir el modal
    document.getElementById('edit_name').focus();
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera del contenido
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
    }
});

// Prevenir envío del formulario al presionar Enter en campos individuales
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editProfileForm');
    const inputs = form.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>