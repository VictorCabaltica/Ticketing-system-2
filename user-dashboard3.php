<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['email'];

// Get ticket statistics
$ticket_stats = [
    'total' => 0,
    'open' => 0,
    'pending' => 0,
    'closed' => 0,
    'overdue' => 0
];

$status_query = "SELECT status, COUNT(*) as count FROM tickets WHERE email = ? GROUP BY status";
$stmt = $conn->prepare($status_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $ticket_stats[strtolower($row['status'])] = $row['count'];
    $ticket_stats['total'] += $row['count'];
}

// Get recent tickets
$recent_tickets = [];
$recent_query = "SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($recent_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_result = $stmt->get_result();
$recent_tickets = $recent_result->fetch_all(MYSQLI_ASSOC);

// Get priority distribution
$priority_stats = [
    'low' => 0,
    'medium' => 0,
    'high' => 0,
    'critical' => 0
];

$priority_query = "SELECT priority, COUNT(*) as count FROM tickets WHERE user_id = ? GROUP BY priority";
$stmt = $conn->prepare($priority_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$priority_result = $stmt->get_result();

while ($row = $priority_result->fetch_assoc()) {
    $priority_stats[$row['priority']] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #6b80e0;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #ffbe0b;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
        }

        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 10;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo i {
            font-size: 24px;
            margin-right: 10px;
        }

        .logo h2 {
            font-size: 20px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            padding: 30px;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            font-weight: bold;
        }

        .greeting h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .greeting p {
            color: var(--gray);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .stat-card.total::before { background-color: var(--primary); }
        .stat-card.open::before { background-color: var(--success); }
        .stat-card.pending::before { background-color: var(--warning); }
        .stat-card.closed::before { background-color: var(--gray); }
        .stat-card.overdue::before { background-color: var(--danger); }

        .stat-title {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
        }

        /* Recent Tickets */
        .recent-tickets {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
        }

        .btn {
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .tickets-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tickets-table th {
            text-align: left;
            padding: 12px 15px;
            background: var(--light);
            color: var(--gray);
            font-weight: 500;
        }

        .tickets-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--light);
        }

        .tickets-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-open { background: #e3f2fd; color: #1976d2; }
        .status-pending { background: #fff8e1; color: #ff8f00; }
        .status-closed { background: #e8f5e9; color: #388e3c; }
        .status-overdue { background: #ffebee; color: #d32f2f; }

        .priority-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .priority-low { background-color: #4caf50; }
        .priority-medium { background-color: #ff9800; }
        .priority-high { background-color: #f44336; }
        .priority-critical { background-color: #9c27b0; }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animated {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-headset"></i>
                <h2>Ticketease</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="user-viewticket.php" class="nav-link">
                        <i class="fas fa-ticket-alt"></i>
                        <span>My Tickets</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="user-slafor.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Ticket</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="user-profile.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                    </div>
                    <div class="greeting">
                        <h1>Welcome back, <?= $_SESSION['name'] ?></h1>
                        <p>Here's what's happening with your tickets</p>
                    </div>
                </div>
                <div class="date-time">
                    <span id="current-date"></span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card total animated">
                    <div class="stat-title">Total Tickets</div>
                    <div class="stat-value"><?= $ticket_stats['total'] ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 
                    </div>
                </div>
                <div class="stat-card open animated delay-1">
                    <div class="stat-title">Open Tickets</div>
                    <div class="stat-value"><?= $ticket_stats['open'] ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> 
                    </div>
                </div>
                <div class="stat-card pending animated delay-1">
                    <div class="stat-title">Pending Tickets</div>
                    <div class="stat-value"><?= $ticket_stats['pending'] ?></div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 
                    </div>
                </div>
                <div class="stat-card overdue animated delay-2">
                    <div class="stat-title">Overdue Tickets</div>
                    <div class="stat-value"><?= $ticket_stats['overdue'] ?></div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i> 
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-container animated">
                    <div class="chart-header">
                        <div class="chart-title">Ticket Status Distribution</div>
                        
                    </div>
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-container animated delay-1">
                    <div class="chart-header">
                        <div class="chart-title">Ticket Priority</div>
                       
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="recent-tickets animated delay-2">
                <div class="section-header">
                    <div class="section-title">Recent Tickets</div>
                    <a href="user-viewticket.php" class="btn">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_tickets as $ticket): ?>
                        <tr>
                            <td>#<?= $ticket['ticket_id'] ?></td>
                            <td><?= htmlspecialchars($ticket['subject']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $ticket['status'] ?>">
                                    <?= ucfirst($ticket['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="priority-dot priority-<?= $ticket['priority'] ?>"></span>
                                <?= ucfirst($ticket['priority']) ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($ticket['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Current date display
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', options);

        // Status Chart (Pie)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'Pending', 'Closed', 'Overdue'],
                datasets: [{
                    data: [
                        <?= $ticket_stats['open'] ?>,
                        <?= $ticket_stats['pending'] ?>,
                        <?= $ticket_stats['closed'] ?>,
                        <?= $ticket_stats['overdue'] ?>
                    ],
                    backgroundColor: [
                        '#4cc9f0',
                        '#ffbe0b',
                        '#6c757d',
                        '#f72585'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Priority Chart (Bar)
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        const priorityChart = new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: ['Low', 'Medium', 'High', 'Critical'],
                datasets: [{
                    label: 'Tickets by Priority',
                    data: [
                        <?= $priority_stats['low'] ?>,
                        <?= $priority_stats['medium'] ?>,
                        <?= $priority_stats['high'] ?>,
                        <?= $priority_stats['critical'] ?>
                    ],
                    backgroundColor: [
                        '#4caf50',
                        '#ff9800',
                        '#f44336',
                        '#9c27b0'
                    ],
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    delay: function(context) {
                        return context.dataIndex * 100;
                    }
                }
            }
        });

        // Interactive elements
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                const status = this.classList[1]; // Gets the status class (open, closed, etc.)
                window.location.href = `tickets.php?status=${status}`;
            });
        });
    </script>
</body>
</html>