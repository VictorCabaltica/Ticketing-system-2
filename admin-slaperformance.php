<?php
include 'db_connection.php'; // Connect to DB

// Count tickets by status
$statusQuery = "SELECT status, COUNT(*) as count FROM tickets GROUP BY status";
$statusResult = $conn->query($statusQuery);
$statusData = ['open' => 0, 'in_progress' => 0, 'closed' => 0];
while ($row = $statusResult->fetch_assoc()) {
    $statusData[$row['status']] = $row['count'];
}

// SLA performance - response time
$slaQuery = "SELECT TIMESTAMPDIFF(HOUR, created_at, updated_at) as response_time FROM tickets WHERE updated_at IS NOT NULL";
$slaResult = $conn->query($slaQuery);
$totalTime = 0;
$ticketCount = 0;
$responseTimes = [];

while ($row = $slaResult->fetch_assoc()) {
    $responseTimes[] = $row['response_time'];
    $totalTime += $row['response_time'];
    $ticketCount++;
}
$avgResponseTime = $ticketCount > 0 ? round($totalTime / $ticketCount, 2) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        :root {
            --primary: #4361ee;
            --warning: #f8961e;
            --success: #198754;
            --info: #17a2b8;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 200px;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #f5f7ff, #e6e9ff);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.1);
        }
        
        .stat-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            padding: 30px;
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
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.open::after { background-color: var(--primary); }
        .stat-card.progress::after { background-color: var(--warning); }
        .stat-card.closed::after { background-color: var(--success); }
        .stat-card.sla::after { background-color: var(--info); }
        
        .stat-icon {
            font-size: 2rem;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            height: 400px;
        }
        
        .chart-container:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn {
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateX(-3px);
        }
        
        .animate-delay-1 { animation-delay: 0.2s; }
        .animate-delay-2 { animation-delay: 0.4s; }
        .animate-delay-3 { animation-delay: 0.6s; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .custom-animate {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate__animated animate__fadeIn">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold text-primary mb-1">SLA Performance</h1>
                    <p class="text-muted mb-0">Ticket system overview and analytics</p>
                </div>
                <a href="home.php" class="btn btn-outline-primary back-btn animate__animated animate__fadeIn">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card open h-100 custom-animate">
                    <div class="card-body text-primary">
                        <i class="fas fa-folder-open stat-icon"></i>
                        <h5 class="card-title">Open Tickets</h5>
                        <h2 class="card-text mb-0 fw-bold"><?php echo $statusData['open']; ?></h2>
                        <small class="text-muted">Requiring attention</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card progress h-100 custom-animate animate-delay-1">
                    <div class="card-body text-warning">
                        <i class="fas fa-tasks stat-icon"></i>
                        <h5 class="card-title">In Progress</h5>
                        <h2 class="card-text mb-0 fw-bold"><?php echo $statusData['in_progress']; ?></h2>
                        <small class="text-muted">Being worked on</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card closed h-100 custom-animate animate-delay-2">
                    <div class="card-body text-success">
                        <i class="fas fa-check-circle stat-icon"></i>
                        <h5 class="card-title">Closed Tickets</h5>
                        <h2 class="card-text mb-0 fw-bold"><?php echo $statusData['closed']; ?></h2>
                        <small class="text-muted">Resolved issues</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card sla h-100 custom-animate animate-delay-3">
                    <div class="card-body text-info">
                        <i class="fas fa-clock stat-icon"></i>
                        <h5 class="card-title">Avg Response</h5>
                        <h2 class="card-text mb-0 fw-bold"><?php echo $avgResponseTime; ?> <small class="fs-6">hrs</small></h2>
                        <small class="text-muted">SLA performance</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="chart-container custom-animate">
                    <h5 class="fw-bold mb-3">Ticket Status Overview</h5>
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container custom-animate animate-delay-1">
                    <h5 class="fw-bold mb-3">SLA Response Time (hours)</h5>
                    <canvas id="slaChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Ticket Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'In Progress', 'Closed'],
                datasets: [{
                    data: [<?php echo $statusData['open']; ?>, <?php echo $statusData['in_progress']; ?>, <?php echo $statusData['closed']; ?>],
                    backgroundColor: ['#4361ee', '#f8961e', '#198754'],
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
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${percentage}%`;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                cutout: '70%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            },
            plugins: [ChartDataLabels]
        });

        // SLA Chart
        const slaCtx = document.getElementById('slaChart').getContext('2d');
        const slaChart = new Chart(slaCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($i) { return 'Ticket '.($i+1); }, range(0, count($responseTimes)-1))); ?>,
                datasets: [{
                    label: 'Response Time (hrs)',
                    data: <?php echo json_encode($responseTimes); ?>,
                    backgroundColor: '#17a2b8',
                    borderColor: '#138496',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Response: ${context.raw} hours`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        },
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    duration: 1500
                }
            }
        });

        // Add click event to cards to highlight corresponding chart segments
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                this.classList.toggle('pulse');
                
                // In a real app, you would highlight the corresponding chart segment
                setTimeout(() => {
                    this.classList.remove('pulse');
                }, 2000);
            });
        });

        // Animate elements when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.custom-animate').forEach(el => {
            observer.observe(el);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>