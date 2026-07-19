<?php
session_start();
include('../config/db.php'); // Make sure this file sets $conn correctly

// TOTAL ORDERS (All orders)
$totalOrdersQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_orders FROM orders");
$totalOrders = mysqli_fetch_assoc($totalOrdersQuery)['total_orders'];

// COMPLETED ORDERS (For revenue calculation)
$completedOrdersQuery = mysqli_query($conn, "SELECT COUNT(*) AS completed_orders FROM orders WHERE status = 'Completed'");
$completedOrders = mysqli_fetch_assoc($completedOrdersQuery)['completed_orders'];

// TOTAL REVENUE (Only from completed orders)
$totalRevenueQuery = mysqli_query($conn, "SELECT SUM(price) AS total_revenue FROM orders WHERE status = 'Completed'");
$totalRevenue = mysqli_fetch_assoc($totalRevenueQuery)['total_revenue'];
$totalRevenue = $totalRevenue ?? 0;

// PENDING REVENUE (From Pending + In Progress orders)
$pendingRevenueQuery = mysqli_query($conn, "SELECT SUM(price) AS pending_revenue FROM orders WHERE status IN ('Pending', 'In Progress')");
$pendingRevenue = mysqli_fetch_assoc($pendingRevenueQuery)['pending_revenue'];
$pendingRevenue = $pendingRevenue ?? 0;

// TOTAL EXPENSES (All expenses from all orders)
$totalExpensesQuery = mysqli_query($conn, "SELECT SUM(cost) AS total_expenses FROM expenses");
$totalExpenses = mysqli_fetch_assoc($totalExpensesQuery)['total_expenses'];
$totalExpenses = $totalExpenses ?? 0;

// EXPENSES FOR COMPLETED ORDERS (For profit calculation)
$completedExpensesQuery = mysqli_query($conn, "SELECT SUM(e.cost) AS completed_expenses 
                                                FROM expenses e 
                                                JOIN orders o ON e.order_id = o.id 
                                                WHERE o.status = 'Completed'");
$completedExpenses = mysqli_fetch_assoc($completedExpensesQuery)['completed_expenses'];
$completedExpenses = $completedExpenses ?? 0;

// WORK IN PROGRESS EXPENSES (Expenses on incomplete orders)
$wipExpenses = $totalExpenses - $completedExpenses;

// NET PROFIT (Revenue from completed orders - Expenses from completed orders)
$netProfit = $totalRevenue - $completedExpenses;

// CASH FLOW (Revenue from completed orders - All expenses)
$cashFlow = $totalRevenue - $totalExpenses;

// ORDER STATUS COUNTS FOR CHART
$statusQuery = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$statusData = [];
while($row = mysqli_fetch_assoc($statusQuery)) {
    $statusData[$row['status']] = $row['count'];
}

// PENDING ORDERS COUNT
$pendingOrders = $statusData['Pending'] ?? 0;

// IN PROGRESS ORDERS COUNT
$inProgressOrders = $statusData['In Progress'] ?? 0;

// MONTHLY REVENUE DATA (Last 6 months)
$monthlyRevenueQuery = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(order_date, '%M') as month,
        MONTH(order_date) as month_num,
        SUM(price) as revenue
    FROM orders 
    WHERE status = 'Completed' 
        AND order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY MONTH(order_date)
    ORDER BY order_date ASC
");
$months = [];
$revenues = [];
while($row = mysqli_fetch_assoc($monthlyRevenueQuery)) {
    $months[] = $row['month'];
    $revenues[] = $row['revenue'];
}

// TOP CLIENTS BY REVENUE
$topClientsQuery = mysqli_query($conn, "
    SELECT u.name, SUM(o.price) as total_revenue 
    FROM orders o 
    JOIN users u ON o.client_id = u.id 
    WHERE o.status = 'Completed'
    GROUP BY o.client_id 
    ORDER BY total_revenue DESC 
    LIMIT 5
");
$clientNames = [];
$clientRevenues = [];
while($row = mysqli_fetch_assoc($topClientsQuery)) {
    $clientNames[] = $row['name'];
    $clientRevenues[] = $row['total_revenue'];
}

include('../includes/header.php'); 
include('../includes/sidebar.php'); 
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="content">
    <h2>Dashboard</h2>

    <!-- Summary Cards - Row 1 (Order Metrics) -->
    <div class="dashboard-cards">
        <div class="card card-total-orders">
            <h4>Total Orders</h4>
            <p><?php echo $totalOrders; ?></p>
            <small>All orders in system</small>
            <div class="card-icon">📊</div>
        </div>

        <div class="card card-completed-orders">
            <h4>Completed Orders</h4>
            <p><?php echo $completedOrders; ?></p>
            <small>Delivered orders</small>
            <div class="card-icon">✅</div>
        </div>

        <div class="card card-pending-orders">
            <h4>Pending Orders</h4>
            <p><?php echo $pendingOrders; ?></p>
            <small>Awaiting start</small>
            <div class="card-icon">⏳</div>
        </div>

        <div class="card card-progress-orders">
            <h4>In Progress</h4>
            <p><?php echo $inProgressOrders; ?></p>
            <small>Work in process</small>
            <div class="card-icon">🔄</div>
        </div>
    </div>

    <!-- Summary Cards - Row 2 (Financial Metrics) -->
    <div class="dashboard-cards">
        <div class="card card-total-revenue">
            <h4>Total Revenue</h4>
            <p>$<?php echo number_format($totalRevenue, 2); ?></p>
            <small>From completed orders only</small>
            <div class="card-icon">💰</div>
        </div>

        <div class="card card-pending-revenue">
            <h4>Pending Revenue</h4>
            <p>$<?php echo number_format($pendingRevenue, 2); ?></p>
            <small>From Pending + In Progress orders</small>
            <div class="card-icon">📈</div>
        </div>

        <div class="card card-total-expenses">
            <h4>Total Expenses</h4>
            <p>$<?php echo number_format($totalExpenses, 2); ?></p>
            <small>All expenses incurred</small>
            <div class="card-icon">💸</div>
        </div>

        <div class="card card-cost-sales">
            <h4>Cost of Sales</h4>
            <p>$<?php echo number_format($completedExpenses, 2); ?></p>
            <small>Expenses for completed orders</small>
            <div class="card-icon">🏭</div>
        </div>
    </div>

    <!-- Summary Cards - Row 3 (Profit Metrics) -->
    <div class="dashboard-cards">
        <div class="card card-gross-profit">
            <h4>Gross Profit</h4>
            <p style="color: <?php echo $netProfit >= 0 ? '#44bd32' : '#e84118'; ?>;">
                $<?php echo number_format($netProfit, 2); ?>
            </p>
            <small>Revenue - Cost of Sales</small>
            <div class="card-icon">🎯</div>
        </div>

        <div class="card card-wip">
            <h4>Work in Progress</h4>
            <p>$<?php echo number_format($wipExpenses, 2); ?></p>
            <small>Expenses on incomplete orders</small>
            <div class="card-icon">⚙️</div>
        </div>

        <div class="card card-cash-flow">
            <h4>Cash Flow</h4>
            <p style="color: <?php echo $cashFlow >= 0 ? '#44bd32' : '#e84118'; ?>;">
                $<?php echo number_format($cashFlow, 2); ?>
            </p>
            <small>Revenue - All expenses</small>
            <div class="card-icon">💵</div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="charts-row">
        <div class="chart-container">
            <h3>Order Status Distribution</h3>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Revenue vs Expenses</h3>
            <canvas id="revenueExpensesChart"></canvas>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="charts-row">
        <div class="chart-container">
            <h3>Monthly Revenue Trend (Last 6 Months)</h3>
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Top 5 Clients by Revenue</h3>
            <canvas id="topClientsChart"></canvas>
        </div>
    </div>

    <!-- Financial Health Gauge -->
    <div class="charts-row">
        <div class="chart-container full-width">
            <h3>Financial Health Overview</h3>
            <div class="financial-health">
                <div class="health-card">
                    <div class="health-label">Profit Margin</div>
                    <div class="health-value" id="profitMargin">0%</div>
                    <div class="health-bar">
                        <div class="health-bar-fill" id="profitMarginBar" style="width: 0%;"></div>
                    </div>
                </div>
                <div class="health-card">
                    <div class="health-label">Expense Ratio</div>
                    <div class="health-value" id="expenseRatio">0%</div>
                    <div class="health-bar">
                        <div class="health-bar-fill" id="expenseRatioBar" style="width: 0%; background: #e84118;"></div>
                    </div>
                </div>
                <div class="health-card">
                    <div class="health-label">Completion Rate</div>
                    <div class="health-value" id="completionRate">0%</div>
                    <div class="health-bar">
                        <div class="health-bar-fill" id="completionRateBar" style="width: 0%; background: #3498db;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Order Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Completed'],
            datasets: [{
                data: [
                    <?php echo $statusData['Pending'] ?? 0; ?>,
                    <?php echo $statusData['In Progress'] ?? 0; ?>,
                    <?php echo $statusData['Completed'] ?? 0; ?>
                ],
                backgroundColor: ['#f39c12', '#3498db', '#44bd32'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { size: 11 }
                    }
                }
            }
        }
    });

    // Revenue vs Expenses Chart
    const revExpCtx = document.getElementById('revenueExpensesChart').getContext('2d');
    new Chart(revExpCtx, {
        type: 'bar',
        data: {
            labels: ['Revenue', 'Expenses', 'Gross Profit'],
            datasets: [{
                label: 'Amount ($)',
                data: [
                    <?php echo $totalRevenue; ?>,
                    <?php echo $totalExpenses; ?>,
                    <?php echo $netProfit; ?>
                ],
                backgroundColor: ['#44bd32', '#e84118', '#3498db'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        },
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 11 }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.raw.toLocaleString();
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });

    // Monthly Revenue Trend Chart
    const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?php echo json_encode($revenues); ?>,
                borderColor: '#44bd32',
                backgroundColor: 'rgba(68, 189, 50, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#44bd32',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        },
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: $' + context.raw.toLocaleString();
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: { font: { size: 11 } }
                }
            }
        }
    });

    // Top Clients Chart
    const clientsCtx = document.getElementById('topClientsChart').getContext('2d');
    new Chart(clientsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($clientNames); ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?php echo json_encode($clientRevenues); ?>,
                backgroundColor: '#9b59b6',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        },
                        font: { size: 10 }
                    }
                },
                y: {
                    ticks: {
                        font: { size: 10 }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: $' + context.raw.toLocaleString();
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: { font: { size: 11 } }
                }
            }
        }
    });

    // Financial Health Metrics
    const profitMargin = (<?php echo $netProfit; ?> / <?php echo $totalRevenue ?: 1; ?>) * 100;
    const expenseRatio = (<?php echo $totalExpenses; ?> / <?php echo $totalRevenue ?: 1; ?>) * 100;
    const completionRate = (<?php echo $completedOrders; ?> / <?php echo $totalOrders ?: 1; ?>) * 100;

    document.getElementById('profitMargin').innerHTML = profitMargin.toFixed(1) + '%';
    document.getElementById('expenseRatio').innerHTML = expenseRatio.toFixed(1) + '%';
    document.getElementById('completionRate').innerHTML = completionRate.toFixed(1) + '%';

    document.getElementById('profitMarginBar').style.width = Math.min(profitMargin, 100) + '%';
    document.getElementById('expenseRatioBar').style.width = Math.min(expenseRatio, 100) + '%';
    document.getElementById('completionRateBar').style.width = completionRate + '%';
</script>

<style>
    .content {
        padding: 30px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f6fa 0%, #eef0f5 100%);
        color: #333;
    }

    h2 {
        font-size: 28px;
        margin-bottom: 25px;
        color: #2f3640;
        position: relative;
        display: inline-block;
    }
    
    h2:after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #44bd32, #273c75);
        border-radius: 3px;
    }

    h3 {
        font-size: 18px;
        margin-bottom: 20px;
        color: #2f3640;
        text-align: center;
    }

    .dashboard-cards {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    /* Base Card Styles */
    .card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        flex: 1;
        min-width: 200px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .card:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.1);
        transition: left 0.4s ease;
    }
    
    .card:hover:before {
        left: 100%;
    }

    .card h4 {
        margin-bottom: 10px;
        font-size: 14px;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    .card p {
        font-size: 28px;
        font-weight: bold;
        margin: 10px 0;
        transition: all 0.3s ease;
    }

    .card small {
        font-size: 11px;
        color: #95a5a6;
        display: block;
        margin-top: 5px;
        transition: all 0.3s ease;
    }
    
    .card-icon {
        position: absolute;
        bottom: 15px;
        right: 15px;
        font-size: 35px;
        opacity: 0.15;
        transition: all 0.4s ease;
    }
    
    .card:hover .card-icon {
        opacity: 0.3;
        transform: scale(1.1) rotate(5deg);
    }

    /* Card Hover Colors */
    .card-total-orders:hover { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4); }
    .card-completed-orders:hover { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(17, 153, 142, 0.4); }
    .card-pending-orders:hover { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(242, 153, 74, 0.4); }
    .card-progress-orders:hover { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(79, 172, 254, 0.4); }
    .card-total-revenue:hover { background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(68, 189, 50, 0.4); }
    .card-pending-revenue:hover { background: linear-gradient(135deg, #ffa502 0%, #ff6348 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(255, 165, 2, 0.4); }
    .card-total-expenses:hover { background: linear-gradient(135deg, #ee5a24 0%, #c0392b 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(238, 90, 36, 0.4); }
    .card-cost-sales:hover { background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(230, 126, 34, 0.4); }
    .card-gross-profit:hover { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(46, 204, 113, 0.4); }
    .card-wip:hover { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(52, 152, 219, 0.4); }
    .card-cash-flow:hover { background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%); transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(26, 188, 156, 0.4); }
    
    .card-total-orders:hover h4, .card-total-orders:hover p, .card-total-orders:hover small,
    .card-completed-orders:hover h4, .card-completed-orders:hover p, .card-completed-orders:hover small,
    .card-pending-orders:hover h4, .card-pending-orders:hover p, .card-pending-orders:hover small,
    .card-progress-orders:hover h4, .card-progress-orders:hover p, .card-progress-orders:hover small,
    .card-total-revenue:hover h4, .card-total-revenue:hover p, .card-total-revenue:hover small,
    .card-pending-revenue:hover h4, .card-pending-revenue:hover p, .card-pending-revenue:hover small,
    .card-total-expenses:hover h4, .card-total-expenses:hover p, .card-total-expenses:hover small,
    .card-cost-sales:hover h4, .card-cost-sales:hover p, .card-cost-sales:hover small,
    .card-gross-profit:hover h4, .card-gross-profit:hover p, .card-gross-profit:hover small,
    .card-wip:hover h4, .card-wip:hover p, .card-wip:hover small,
    .card-cash-flow:hover h4, .card-cash-flow:hover p, .card-cash-flow:hover small {
        color: white;
    }

    .charts-row {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .chart-container {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        flex: 1;
        min-width: 280px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .chart-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .chart-container canvas {
        max-height: 300px;
        width: 100% !important;
    }

    .full-width {
        width: 100%;
    }

    .financial-health {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        justify-content: space-around;
    }

    .health-card {
        flex: 1;
        min-width: 180px;
        text-align: center;
        padding: 15px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .health-card:hover {
        background: #f8f9fa;
        transform: translateY(-3px);
    }

    .health-label {
        font-size: 14px;
        color: #7f8c8d;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .health-value {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #2f3640;
    }

    .health-bar {
        background: #ecf0f1;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }

    .health-bar-fill {
        height: 100%;
        background: #44bd32;
        border-radius: 4px;
        transition: width 1s ease;
    }

    /* Responsive Breakpoints */
    @media (max-width: 1200px) {
        .card {
            min-width: 180px;
        }
        .card p {
            font-size: 24px;
        }
    }

    @media (max-width: 992px) {
        .content {
            padding: 20px;
        }
        h2 {
            font-size: 24px;
        }
        .dashboard-cards {
            gap: 15px;
        }
        .card {
            min-width: calc(33.33% - 15px);
        }
        .card p {
            font-size: 22px;
        }
        .card-icon {
            font-size: 28px;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }
        h2 {
            font-size: 22px;
        }
        h3 {
            font-size: 16px;
        }
        .dashboard-cards {
            gap: 12px;
        }
        .card {
            min-width: calc(50% - 12px);
            padding: 15px;
        }
        .card h4 {
            font-size: 12px;
        }
        .card p {
            font-size: 20px;
        }
        .card small {
            font-size: 10px;
        }
        .card-icon {
            font-size: 24px;
            bottom: 10px;
            right: 10px;
        }
        .chart-container {
            padding: 15px;
        }
        .health-value {
            font-size: 24px;
        }
        .health-label {
            font-size: 12px;
        }
    }

    @media (max-width: 576px) {
        .content {
            padding: 12px;
        }
        .dashboard-cards {
            gap: 10px;
        }
        .card {
            min-width: 100%;
            padding: 15px;
        }
        .card p {
            font-size: 24px;
        }
        .charts-row {
            gap: 15px;
        }
        .chart-container {
            min-width: 100%;
        }
        .financial-health {
            flex-direction: column;
            gap: 15px;
        }
        .health-card {
            min-width: 100%;
        }
        h2:after {
            width: 40px;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>