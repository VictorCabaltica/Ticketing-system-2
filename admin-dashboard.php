<?php


$mysqli = new mysqli("localhost", "root", "", "yna_db");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$sql = "SELECT * FROM tickets ORDER BY created_at DESC LIMIT 10";
$result = $mysqli->query($sql);

$sql_stats = "SELECT
                SUM(status = 'open') AS open_tickets,
                SUM(status = 'in_progress') AS in_progress_tickets,
                SUM(status = 'closed') AS closed_tickets,
                COUNT(ticket_id) AS total_tickets
              FROM tickets";
$stats_result = $mysqli->query($sql_stats);
$stats = $stats_result->fetch_assoc();


$low = $mysqli->query("SELECT COUNT(*) FROM tickets WHERE priority = 'low'")->fetch_row()[0];
$medium = $mysqli->query("SELECT COUNT(*) FROM tickets WHERE priority = 'medium'")->fetch_row()[0];
$high = $mysqli->query("SELECT COUNT(*) FROM tickets WHERE priority = 'high'")->fetch_row()[0];
$critical = $mysqli->query("SELECT COUNT(*) FROM tickets WHERE priority = 'critical'")->fetch_row()[0];


$dept_query = $mysqli->query("SELECT department, COUNT(*) as count FROM tickets GROUP BY department");
$departments = [];
$dept_counts = [];
while ($row = $dept_query->fetch_assoc()) {
    $departments[] = $row['department'];
    $dept_counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --low-priority: #2ecc71;
            --medium-priority: #f39c12;
            --high-priority: #e74c3c;
            --critical-priority: #9b59b6;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
            animation: shine 8s infinite linear;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) rotate(30deg); }
            100% { transform: translateX(100%) rotate(30deg); }
        }
        
        .stat-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            cursor: pointer;
            transform-style: preserve-3d;
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.total::after { background-color: var(--primary); }
        .stat-card.open::after { background-color: var(--warning); }
        .stat-card.progress::after { background-color: var(--success); }
        .stat-card.closed::after { background-color: #6c757d; }
        
        .stat-icon {
            font-size: 2rem;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 20px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.2) rotate(10deg);
            opacity: 0.3;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 200px;
        }
        
        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .chart-container:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }
        
        .chart-container:hover::before {
            opacity: 1;
        }
        
        .ticket-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .ticket-table:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .priority-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }
        
        .priority-low { background-color: var(--low-priority); }
        .priority-medium { background-color: var(--medium-priority); }
        .priority-high { background-color: var(--high-priority); }
        .priority-critical { background-color: var(--critical-priority); }
        
        .priority-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .status-open { background-color: #fff3cd; color: #856404; }
        .status-in_progress { background-color: #cce5ff; color: #004085; }
        .status-closed { background-color: #d4edda; color: #155724; }
        
        .status-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            transform: scale(1.2);
        }
        
        .action-btn.view {
            color: var(--primary);
        }
        
        .action-btn.edit {
            color: var(--warning);
        }
        
        .action-btn.delete {
            color: var(--danger);
        }
        
        .animate-delay-1 { animation-delay: 0.2s; }
        .animate-delay-2 { animation-delay: 0.4s; }
        .animate-delay-3 { animation-delay: 0.6s; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .custom-animate {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }
        
        .pulse-animate {
            animation: pulse 2s infinite;
        }
        
        .ticket-row {
            transition: all 0.3s ease;
        }
        
        .ticket-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .refresh-btn {
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: rotate(180deg);
        }
        
        .new-ticket-btn {
            position: relative;
            overflow: hidden;
        }
        
        .new-ticket-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
            transition: all 0.3s ease;
        }
        
        .new-ticket-btn:hover::after {
            left: 100%;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate__animated animate__fadeIn">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-5 fw-bold">Ticket Dashboard</h1>
                    <p class="mb-0">Overview of all ticket activities</p>
                </div>
                <button class="btn btn-light refresh-btn" id="refreshDashboard">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                
            </div>
        </div>
        <div class="mb-3">
    <a href="home.php" class="btn btn-outline-secondary" color='blue'>
        &larr; Back to Home
    </a>
</div>
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card total h-100 custom-animate" onclick="filterTickets('all')">
                    <div class="card-body text-white bg-primary">
                        <i class="fas fa-ticket-alt stat-icon"></i>
                        <h5 class="card-title">Total Tickets</h5>
                        <h2 class="card-text mb-0"><?php echo $stats['total_tickets']; ?></h2>
                        <small class="text-white-50">All tickets in system</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card open h-100 custom-animate animate-delay-1" onclick="filterTickets('open')">
                    <div class="card-body text-white bg-warning">
                        <i class="fas fa-folder-open stat-icon"></i>
                        <h5 class="card-title">Open Tickets</h5>
                        <h2 class="card-text mb-0"><?php echo $stats['open_tickets']; ?></h2>
                        <small class="text-white-50">Requiring attention</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card progress h-100 custom-animate animate-delay-2" onclick="filterTickets('in_progress')">
                    <div class="card-body text-white" style="background-color: var(--success);">
                        <i class="fas fa-tasks stat-icon"></i>
                        <h5 class="card-title">In Progress</h5>
                        <h2 class="card-text mb-0"><?php echo $stats['in_progress_tickets']; ?></h2>
                        <small class="text-white-50">Being worked on</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card closed h-100 custom-animate animate-delay-3" onclick="filterTickets('closed')">
                    <div class="card-body text-white bg-secondary">
                        <i class="fas fa-check-circle stat-icon"></i>
                        <h5 class="card-title">Closed Tickets</h5>
                        <h2 class="card-text mb-0"><?php echo $stats['closed_tickets']; ?></h2>
                        <small class="text-white-50">Resolved issues</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="chart-container custom-animate">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Ticket Status</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" onclick="updateChartTimeframe('status', 'week')">Week</button>
                            <button class="btn btn-outline-secondary" onclick="updateChartTimeframe('status', 'month')">Month</button>
                            <button class="btn btn-outline-secondary" onclick="updateChartTimeframe('status', 'year')">Year</button>
                        </div>
                    </div>
                    <canvas id="ticketStatusChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-container custom-animate animate-delay-1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Ticket Priority</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" onclick="updateChartTimeframe('priority', 'week')">Week</button>
                            <button class="btn btn-outline-secondary" onclick="updateChartTimeframe('priority', 'month')">Month</button>
                            <button class="btn btn-outline-secondary" onclick="updateChartTimeframe('priority', 'year')">Year</button>
                        </div>
                    </div>
                    <canvas id="priorityChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-container custom-animate animate-delay-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Department Distribution</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" onclick="updateChartTimeframe('department', 'week')">Week</button>
                            <button class="btn btn-outline-secondary" onclick="updateChartTimeframe('department', 'month')">Month</button>
                            <button class="btn btn-outline-secondary" onclick="updateChartTimeframe('department', 'year')">Year</button>
                        </div>
                    </div>
                    <canvas id="departmentChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Tickets Table -->
        <div class="ticket-table custom-animate animate-delay-3">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Tickets</h5>
                    <div>
                        <button class="btn btn-sm btn-primary new-ticket-btn" 
                         onclick="window.location.href='new_ticket.php'">
                       <i class="fas fa-plus me-1"></i> New Ticket
                      </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody id="ticketTableBody">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="ticket-row" data-status="<?php echo $row['status']; ?>">
                                    <td><?php echo $row['ticket_id']; ?></td>
                                    <td><?php echo substr($row['subject'], 0, 30) . (strlen($row['subject']) > 30 ? '...' : ''); ?></td>
                                    <td><?php echo $row['department']; ?></td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo $row['priority']; ?>">
                                            <?php echo ucfirst($row['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo str_replace('_', '', $row['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>

                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- New Ticket Modal -->
    <div class="modal fade" id="newTicketModal" tabindex="-1" aria-labelledby="newTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTicketModalLabel">Create New Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ticketForm">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" required>
                                <option value="">Select Department</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" required>
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitTicket()">Create Ticket</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Ticket Modal -->
    <div class="modal fade" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTicketModalLabel">Ticket Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ticketDetails">
                    <!-- Ticket details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts
        let statusChart, priorityChart, deptChart;

        // Ticket Status Chart
        function initStatusChart() {
            const statusCtx = document.getElementById('ticketStatusChart').getContext('2d');
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Open', 'In Progress', 'Closed'],
                    datasets: [{
                        data: [<?php echo $stats['open_tickets']; ?>, <?php echo $stats['in_progress_tickets']; ?>, <?php echo $stats['closed_tickets']; ?>],
                        backgroundColor: ['#FFC107', '#17A2B8', '#6C757D'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
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
                    cutout: '70%',
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });
        }

        // Priority Chart
        function initPriorityChart() {
            const priorityCtx = document.getElementById('priorityChart').getContext('2d');
            priorityChart = new Chart(priorityCtx, {
                type: 'bar',
                data: {
                    labels: ['Low', 'Medium', 'High', 'Critical'],
                    datasets: [{
                        label: 'Tickets',
                        data: [<?php echo "$low, $medium, $high, $critical"; ?>],
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.8)',
                            'rgba(248, 150, 30, 0.8)',
                            'rgba(231, 76, 60, 0.8)',
                            'rgba(155, 89, 182, 0.8)'
                        ],
                        borderColor: [
                            'rgba(46, 204, 113, 1)',
                            'rgba(248, 150, 30, 1)',
                            'rgba(231, 76, 60, 1)',
                            'rgba(155, 89, 182, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    animation: {
                        duration: 1000
                    }
                }
            });
        }

        // Department Chart
        function initDeptChart() {
            const deptCtx = document.getElementById('departmentChart').getContext('2d');
            deptChart = new Chart(deptCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($departments); ?>,
                    datasets: [{
                        data: <?php echo json_encode($dept_counts); ?>,
                        backgroundColor: [
                            'rgba(67, 97, 238, 0.8)',
                            'rgba(76, 175, 80, 0.8)',
                            'rgba(255, 152, 0, 0.8)',
                            'rgba(156, 39, 176, 0.8)',
                            'rgba(233, 30, 99, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
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
        }

        // Initialize all charts
        function initCharts() {
            initStatusChart();
            initPriorityChart();
            initDeptChart();
        }

        // Ticket actions
        function viewTicket(ticketId) {
            // In a real app, this would fetch ticket details via AJAX
            fetch(`/api/tickets/${ticketId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ticketDetails').innerHTML = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Subject</h6>
                                <p>${data.subject}</p>
                            </div>
                            <div class="col-md-3">
                                <h6>Priority</h6>
                                <span class="priority-badge priority-${data.priority}">
                                    ${data.priority.charAt(0).toUpperCase() + data.priority.slice(1)}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <h6>Status</h6>
                                <span class="status-badge status-${data.status.replace('_', '')}">
                                    ${data.status.replace('_', ' ').charAt(0).toUpperCase() + data.status.replace('_', ' ').slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Department</h6>
                                <p>${data.department}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Created</h6>
                                <p>${new Date(data.created_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Description</h6>
                            <p>${data.description}</p>
                        </div>
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('viewTicketModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load ticket details');
                });
        }

        function editTicket(ticketId) {
            // In a real app, this would open an edit form
            alert(`Editing ticket #${ticketId} would open an edit form in a real application`);
        }

        function deleteTicket(ticketId) {
            if (confirm(`Are you sure you want to delete ticket #${ticketId}?`)) {
                // Perform AJAX delete
                fetch(`/api/tickets/${ticketId}`, {
                    method: 'DELETE'
                })
                .then(response => {
                    if (response.ok) {
                        // Remove the row from the table
                        document.querySelector(`tr[data-ticket-id="${ticketId}"]`).remove();
                        // Show success message
                        showAlert('Ticket deleted successfully', 'success');
                        // Refresh charts
                        refreshCharts();
                    } else {
                        throw new Error('Failed to delete ticket');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to delete ticket', 'danger');
                });
            }
        }

        function filterTickets(status) {
            const rows = document.querySelectorAll('#ticketTableBody tr');
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Highlight the selected filter
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.remove('pulse-animate');
            });
            if (status === 'all') {
                document.querySelector('.stat-card.total').classList.add('pulse-animate');
            } else {
                document.querySelector(`.stat-card.${status.replace('_', '')}`).classList.add('pulse-animate');
            }
        }

        function updateChartTimeframe(chartType, timeframe) {
            // In a real app, this would fetch new data based on the timeframe
            alert(`Updating ${chartType} chart to show ${timeframe} data would fetch new data in a real application`);
            
            // Update button states
            const buttons = document.querySelectorAll(`#${chartType}Chart .btn-group .btn`);
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase() === timeframe) {
                    btn.classList.add('active');
                }
            });
        }

        function submitTicket() {
            const form = document.getElementById('ticketForm');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            
            // In a real app, this would submit via AJAX
            const formData = {
                subject: document.getElementById('subject').value,
                department: document.getElementById('department').value,
                priority: document.getElementById('priority').value,
                description: document.getElementById('description').value
            };
            
            fetch('/api/tickets', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('newTicketModal'));
                modal.hide();
                
                // Show success message
                showAlert('Ticket created successfully', 'success');
                
                // Reset form
                form.reset();
                form.classList.remove('was-validated');
                
                // Refresh the ticket list and charts
                refreshDashboard();
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to create ticket', 'danger');
            });
        }

        function refreshDashboard() {
            // In a real app, this would refresh all data via AJAX
            alert('Refreshing dashboard data would fetch new data in a real application');
            
            // Add spinning animation to refresh button
            const refreshBtn = document.getElementById('refreshDashboard');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Refreshing';
            
            // Simulate refresh delay
            setTimeout(() => {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                showAlert('Dashboard refreshed', 'success');
            }, 1500);
        }

        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '9999';
            alert.style.minWidth = '300px';
            alert.role = 'alert';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            document.body.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            
            // Animate elements when they come into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.custom-animate').forEach(el => {
                el.style.opacity = 0;
                observer.observe(el);
            });
            
            // Setup event listeners
            document.getElementById('refreshDashboard').addEventListener('click', refreshDashboard);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the connection
$mysqli->close();
?>