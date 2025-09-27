<?php
require_once '../config.php';
require_once '../functions.php';

// Verificar si el usuario está logueado y es administrador
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Obtener estadísticas de ingresos por mes
$database = new Database();
$db = $database->getConnection();

$current_year = date('Y');
$income_query = "SELECT MONTH(order_date) as month, SUM(total_amount) as total_income 
                 FROM invoices 
                 WHERE YEAR(order_date) = :year 
                 GROUP BY MONTH(order_date) 
                 ORDER BY month";
$income_stmt = $db->prepare($income_query);
$income_stmt->bindParam(':year', $current_year);
$income_stmt->execute();
$monthly_income = $income_stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear array con todos los meses
$all_months = [];
for ($i = 1; $i <= 12; $i++) {
    $all_months[$i] = [
        'month' => $i,
        'total_income' => 0,
        'month_name' => DateTime::createFromFormat('!m', $i)->format('F')
    ];
}

// Combinar con datos reales
foreach ($monthly_income as $income) {
    $all_months[$income['month']]['total_income'] = $income['total_income'];
}

// Obtener estadísticas de pedidos por estado
$status_query = "SELECT status, COUNT(*) as count FROM invoices GROUP BY status";
$status_stmt = $db->prepare($status_query);
$status_stmt->execute();
$status_stats = $status_stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener ingresos por temática
$themes_income_query = "SELECT t.name, SUM(i.total_amount) as total_income 
                        FROM invoices i 
                        JOIN users u ON i.client_id = u.id 
                        JOIN favorites f ON u.id = f.user_id 
                        JOIN images img ON f.image_id = img.id 
                        JOIN themes t ON img.theme_id = t.id 
                        GROUP BY t.id 
                        ORDER BY total_income DESC";
$themes_income_stmt = $db->prepare($themes_income_query);
$themes_income_stmt->execute();
$themes_income = $themes_income_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Estadísticas e Ingresos";
require_once '../includes/headera.php';
?>

<div class="container">
    <h1>Estadísticas e Ingresos</h1>
    
    <div class="stats-section">
        <h2>Ingresos Mensuales - <?php echo $current_year; ?></h2>
        
        <div class="income-chart">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Ingresos</th>
                        <th>Barra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_months as $month): ?>
                    <tr>
                        <td><?php echo $month['month_name']; ?></td>
                        <td>$<?php echo number_format($month['total_income'], 2); ?></td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $month['total_income'] > 0 ? min(($month['total_income'] / 10000) * 100, 100) : 0; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="stats-section">
        <h2>Estadísticas de Pedidos por Estado</h2>
        
        <div class="status-stats">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Cantidad</th>
                        <th>Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_orders = array_sum(array_column($status_stats, 'count'));
                    foreach ($status_stats as $stat): 
                        $percentage = $total_orders > 0 ? ($stat['count'] / $total_orders) * 100 : 0;
                    ?>
                    <tr>
                        <td><?php echo $stat['status']; ?></td>
                        <td><?php echo $stat['count']; ?></td>
                        <td><?php echo number_format($percentage, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="stats-section">
        <h2>Ingresos por Temática</h2>
        
        <div class="themes-income">
            <?php if (empty($themes_income)): ?>
            <p>No hay datos de ingresos por temática.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Temática</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($themes_income as $theme): ?>
                    <tr>
                        <td><?php echo $theme['name']; ?></td>
                        <td>$<?php echo number_format($theme['total_income'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="stats-section">
        <h2>Resumen General</h2>
        
        <?php
        // Calcular total de ingresos del año
        $total_income = array_sum(array_column($all_months, 'total_income'));
        
        // Calcular total de pedidos
        $total_orders = array_sum(array_column($status_stats, 'count'));
        ?>
        
        <div class="summary-cards">
            <div class="summary-card">
                <h3>Ingresos Totales del Año</h3>
                <p class="summary-number">$<?php echo number_format($total_income, 2); ?></p>
            </div>
            
            <div class="summary-card">
                <h3>Total de Pedidos</h3>
                <p class="summary-number"><?php echo $total_orders; ?></p>
            </div>
            
            <div class="summary-card">
                <h3>Promedio por Pedido</h3>
                <p class="summary-number">$<?php echo $total_orders > 0 ? number_format($total_income / $total_orders, 2) : '0.00'; ?></p>
            </div>
            
            <div class="summary-card">
                <h3>Temática más Popular</h3>
                <p class="summary-number">
                    <?php echo !empty($themes_income) ? $themes_income[0]['name'] : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>