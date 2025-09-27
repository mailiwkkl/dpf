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

// Obtener todos los usuarios (excepto el admin actual)
$database = new Database();
$db = $database->getConnection();

$users_query = "SELECT id, name, email, phone, address, role, created_at FROM users WHERE id != :current_user ORDER BY created_at DESC";
$users_stmt = $db->prepare($users_query);
$users_stmt->bindParam(':current_user', $_SESSION['user_id']);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar eliminación de usuario
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // No permitir eliminarse a sí mismo
    if ($delete_id == $_SESSION['user_id']) {
        $error = "No puede eliminarse a sí mismo";
    } else {
        $delete_query = "DELETE FROM users WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $delete_id);
        
        if ($delete_stmt->execute()) {
            $success = "Usuario eliminado correctamente";
            header('Location: users.php');
            exit();
        } else {
            $error = "Error al eliminar el usuario";
        }
    }
}

// Procesar reset de contraseña
if (isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];
    $admin_password = $_POST['admin_password'];
    
    // Verificar que no sea el mismo usuario
    if ($user_id == $_SESSION['user_id']) {
        $error = "No puede resetear su propia contraseña desde aquí";
    } else {
        // Verificar la contraseña del administrador
        $admin_query = "SELECT password FROM users WHERE id = :admin_id";
        $admin_stmt = $db->prepare($admin_query);
        $admin_stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $admin_stmt->execute();
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($admin_password, $admin['password'])) {
            // Generar nueva contraseña aleatoria
            $new_password = "1234";
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña del usuario
            $update_query = "UPDATE users SET password = :password WHERE id = :user_id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':password', $hashed_password);
            $update_stmt->bindParam(':user_id', $user_id);
            
            if ($update_stmt->execute()) {
                $success = "Contraseña reseteada correctamente. Nueva contraseña: <strong>$new_password</strong>";
                header('Location: users.php');
                exit();
            } else {
                $error = "Error al resetear la contraseña";
            }
        } else {
            $error = "Contraseña de administrador incorrecta";
        }
    }
}

// Procesar cambio de rol
if (isset($_POST['change_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];
    $admin_password = $_POST['admin_password_role'];
    
    // Verificar que no sea el mismo usuario
    if ($user_id == $_SESSION['user_id']) {
        $error = "No puede cambiar su propio rol";
    } else {
        // Verificar la contraseña del administrador
        $admin_query = "SELECT password FROM users WHERE id = :admin_id";
        $admin_stmt = $db->prepare($admin_query);
        $admin_stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $admin_stmt->execute();
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($admin_password, $admin['password'])) {
            // Actualizar el rol del usuario
            $update_query = "UPDATE users SET role = :role WHERE id = :user_id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':role', $new_role);
            $update_stmt->bindParam(':user_id', $user_id);
            
            if ($update_stmt->execute()) {
                $role_text = $new_role == 'admin' ? 'Administrador' : 'Cliente';
                $success = "Rol cambiado correctamente a: <strong>$role_text</strong>";
                header('Location: users.php');
                exit();
            } else {
                $error = "Error al cambiar el rol";
            }
        } else {
            $error = "Contraseña de administrador incorrecta";
        }
    }
}

// Función para generar contraseña aleatoria
function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

$page_title = "Gestión de Usuarios";
require_once '../includes/headera.php';
?>

<div class="container">
    <h1>Gestión de Usuarios</h1>
    
    <?php if ($error): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="users-list">
        <h2>Lista de Usuarios</h2>
        
        <?php if (empty($users)): ?>
        <p>No hay usuarios registrados.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Rol</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone']; ?></td>
                    <td><?php echo substr($user['address'], 0, 50) . (strlen($user['address']) > 50 ? '...' : ''); ?></td>
                    <td>
                        <span class="role-badge <?php echo $user['role'] == 'admin' ? 'role-admin' : 'role-client'; ?>">
                            <?php echo $user['role'] == 'admin' ? 'Administrador' : 'Cliente'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    <td class="actions-column">
                        <button type="button" class="btn btn-change-role" 
                                onclick="openChangeRoleModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>', '<?php echo $user['role']; ?>')">
                            Cambiar Rol
                        </button>
                        <button type="button" class="btn btn-reset-password" 
                                onclick="openResetPasswordModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>')">
                            Resetear Contraseña
                        </button>
                        <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-delete" 
                           onclick="return confirm('¿Está seguro de eliminar este usuario?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para resetear contraseña -->
<div id="resetPasswordModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeResetPasswordModal()">&times;</span>
        <h2>Resetear Contraseña</h2>
        <p id="modal-user-info-reset">Va a resetear la contraseña del usuario: </p>
        <form method="POST" action="">
            <input type="hidden" name="user_id" id="reset_user_id">
            <div class="form-group">
                <label for="admin_password">Ingrese su contraseña de administrador para confirmar:</label>
                <input type="password" id="admin_password" name="admin_password" required class="form-control">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeResetPasswordModal()">Cancelar</button>
                <button type="submit" name="reset_password" class="btn btn-reset-password">Resetear Contraseña</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para cambiar rol -->
<div id="changeRoleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeChangeRoleModal()">&times;</span>
        <h2>Cambiar Rol de Usuario</h2>
        <p id="modal-user-info-role">Va a cambiar el rol del usuario: </p>
        <form method="POST" action="">
            <input type="hidden" name="user_id" id="change_role_user_id">
            <div class="form-group">
                <label for="new_role">Nuevo rol:</label>
                <select id="new_role" name="new_role" required class="form-control">
                    <option value="client">Cliente</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label for="admin_password_role">Ingrese su contraseña de administrador para confirmar:</label>
                <input type="password" id="admin_password_role" name="admin_password_role" required class="form-control">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeChangeRoleModal()">Cancelar</button>
                <button type="submit" name="change_role" class="btn btn-change-role">Cambiar Rol</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetPasswordModal(userId, userName) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('modal-user-info-reset').textContent = 'Va a resetear la contraseña por 1234 para el usuario: ' + userName;
    document.getElementById('resetPasswordModal').style.display = 'block';
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').style.display = 'none';
    document.getElementById('admin_password').value = '';
}

function openChangeRoleModal(userId, userName, currentRole) {
    document.getElementById('change_role_user_id').value = userId;
    document.getElementById('modal-user-info-role').textContent = 'Va a cambiar el rol del usuario: ' + userName;
    document.getElementById('new_role').value = currentRole;
    document.getElementById('changeRoleModal').style.display = 'block';
}

function closeChangeRoleModal() {
    document.getElementById('changeRoleModal').style.display = 'none';
    document.getElementById('admin_password_role').value = '';
}

// Cerrar modales al hacer clic fuera de ellos
window.onclick = function(event) {
    const resetModal = document.getElementById('resetPasswordModal');
    const roleModal = document.getElementById('changeRoleModal');
    
    if (event.target == resetModal) {
        closeResetPasswordModal();
    }
    if (event.target == roleModal) {
        closeChangeRoleModal();
    }
}
</script>

<style>
/* Estilos generales */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Tabla */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(106, 17, 203, 0.1);
}

.admin-table th,
.admin-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.admin-table th {
    background-color: #6a11cb;
    color: white;
    font-weight: 600;
}

.admin-table tr:hover {
    background-color: #f8f6ff;
}

/* Badges de rol */
.role-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.role-admin {
    background-color: #8a2be2;
    color: white;
}

.role-client {
    background-color: #9370db;
    color: white;
}

/* Botones */
.actions-column {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 200px;
}

.btn {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-change-role {
    background-color: #8a2be2;
    color: white;
}

.btn-change-role:hover {
    background-color: #7b1fa2;
    transform: translateY(-1px);
}

.btn-reset-password {
    background-color: #9370db;
    color: white;
}

.btn-reset-password:hover {
    background-color: #8367c7;
    transform: translateY(-1px);
}

.btn-delete {
    background-color: #dc3545;
    color: white;
}

.btn-delete:hover {
    background-color: #c82333;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* Modales */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(106, 17, 203, 0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 25px;
    border-radius: 10px;
    width: 450px;
    max-width: 90%;
    box-shadow: 0 5px 25px rgba(106, 17, 203, 0.3);
    border: 2px solid #6a11cb;
}

.close {
    color: #6a11cb;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: #8a2be2;
}

/* Formularios */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #6a11cb;
}

.form-actions {
    margin-top: 25px;
    text-align: right;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Mensajes */
.error-message {
    background-color: #ffebee;
    color: #c62828;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #c62828;
}

.success-message {
    background-color: #e8f5e9;
    color: #2e7d32;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #2e7d32;
}

/* Responsive */
@media (max-width: 768px) {
    .actions-column {
        min-width: 150px;
    }
    
    .btn {
        font-size: 11px;
        padding: 6px 10px;
    }
    
    .modal-content {
        width: 95%;
        margin: 5% auto;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>