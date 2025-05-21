<?php
session_start();
include 'db_connection.php';

// Verify admin access
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Get all agents with their performance metrics
$agents = [];
$query = "SELECT a.agent_id, a.name, a.email, a.department,
                 COUNT(t.ticket_id) AS total_tickets,
                 SUM(CASE WHEN t.status = 'closed' THEN 1 ELSE 0 END) AS resolved_tickets,
                 AVG(TIMESTAMPDIFF(HOUR, t.created_at, t.updated_at)) AS avg_resolution_time,
                 SUM(CASE WHEN TIMESTAMPDIFF(HOUR, t.created_at, t.updated_at) > 
                     CASE t.priority
                         WHEN 'critical' THEN 2
                         WHEN 'high' THEN 4
                         WHEN 'medium' THEN 6
                         WHEN 'low' THEN 8
                     END THEN 1 ELSE 0 END) AS breached_tickets
          FROM agents a
          LEFT JOIN tickets t ON a.agent_id = t.assigned_agent_id
          GROUP BY a.agent_id
          ORDER BY resolved_tickets DESC";

$result = $conn->query($query);
if ($result) {
    $agents = $result->fetch_all(MYSQLI_ASSOC);
}

// Calculate performance scores (0-100)
foreach ($agents as &$agent) {
    $resolution_rate = $agent['total_tickets'] > 0 ? ($agent['resolved_tickets'] / $agent['total_tickets']) * 100 : 0;
    $sla_compliance = $agent['total_tickets'] > 0 ? (($agent['total_tickets'] - $agent['breached_tickets']) / $agent['total_tickets']) * 100 : 0;
    $speed_score = $agent['avg_resolution_time'] > 0 ? min(100, 100 - ($agent['avg_resolution_time'] / 24)) : 100;
    
    // Weighted performance score (adjust weights as needed)
    $agent['performance_score'] = round(
        ($resolution_rate * 0.4) + 
        ($sla_compliance * 0.4) + 
        ($speed_score * 0.2)
    );
    
    // Performance rating (A-F)
    $agent['rating'] = match(true) {
        $agent['performance_score'] >= 90 => 'A',
        $agent['performance_score'] >= 80 => 'B',
        $agent['performance_score'] >= 70 => 'C',
        $agent['performance_score'] >= 60 => 'D',
        default => 'F'
    };
}
unset($agent); // Break the reference

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Performance Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 {
            color: var(--primary);
            font-size: 28px;
        }

        .refresh-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-title {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--gray);
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        .agents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .agent-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .agent-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .agent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .agent-name {
            font-size: 18px;
            font-weight: 600;
        }

        .agent-rating {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            color: white;
        }

        .rating-A { background: var(--success); }
        .rating-B { background: #7bc043; }
        .rating-C { background: var(--warning); }
        .rating-D { background: #ee8d1a; }
        .rating-F { background: var(--danger); }

        .agent-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .agent-stat {
            font-size: 14px;
        }

        .agent-stat strong {
            display: block;
            color: var(--gray);
            margin-bottom: 3px;
        }

        .progress-container {
            margin-top: 15px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .progress-bar {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 5px;
            width: 0;
            transition: width 1s ease-out;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
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

        .chart-filter {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animated {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }

        /* Pulse animation for top performers */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }

        .top-performer {
            position: relative;
            border: 2px solid transparent;
        }

        .top-performer::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 10px;
            border: 2px solid var(--success);
            animation: pulse 2s infinite;
            opacity: 0.7;
        }

        .top-performer .agent-rating {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>Agent Performance Dashboard</h1>
            <button class="refresh-btn" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card animated">
                <div class="stat-title">Total Agents</div>
                <div class="stat-value"><?= count($agents) ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 15% from last month
                </div>
            </div>
            <div class="stat-card animated delay-1">
                <div class="stat-title">Avg. Performance Score</div>
                <div class="stat-value">
                    <?= count($agents) > 0 ? round(array_sum(array_column($agents, 'performance_score')) / count($agents)) : 0 ?>%
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 8% from last month
                </div>
            </div>
            <div class="stat-card animated delay-2">
                <div class="stat-title">Avg. Resolution Time</div>
                <div class="stat-value">
                    <?= count($agents) > 0 ? round(array_sum(array_column($agents, 'avg_resolution_time')) / count($agents), 1) : 0 ?>h
                </div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i> 12% from last month
                </div>
            </div>
            <div class="stat-card animated delay-3">
                <div class="stat-title">SLA Compliance Rate</div>
                <div class="stat-value">
                    <?= count($agents) > 0 ? round(100 - (array_sum(array_column($agents, 'breached_tickets')) / array_sum(array_column($agents, 'total_tickets')) * 100)) : 100 ?>%
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 5% from last month
                </div>
            </div>
        </div>

        <!-- Performance Chart -->
        <div class="chart-container animated">
            <div class="chart-header">
                <div class="chart-title">Agent Performance Overview</div>
                <select class="chart-filter" id="chartFilter">
                    <option value="score">By Performance Score</option>
                    <option value="resolved">By Tickets Resolved</option>
                    <option value="sla">By SLA Compliance</option>
                </select>
            </div>
            <canvas id="performanceChart"></canvas>
        </div>

        <!-- Individual Agent Cards -->
        <div class="agents-grid">
            <?php foreach ($agents as $index => $agent): ?>
                <?php 
                    $isTopPerformer = $agent['performance_score'] >= 90;
                    $animationClass = $isTopPerformer ? 'animated top-performer' : 'animated delay-' . min(3, floor($index / 2));
                ?>
                <div class="agent-card <?= $animationClass ?>">
                    <div class="agent-header">
                        <div class="agent-name"><?= htmlspecialchars($agent['name']) ?></div>
                        <div class="agent-rating rating-<?= $agent['rating'] ?>">
                            <?= $agent['rating'] ?>
                        </div>
                    </div>
                    <div class="agent-stats">
                        <div class="agent-stat">
                            <strong>Department</strong>
                            <?= htmlspecialchars($agent['department']) ?>
                        </div>
                        <div class="agent-stat">
                            <strong>Total Tickets</strong>
                            <?= $agent['total_tickets'] ?>
                        </div>
                        <div class="agent-stat">
                            <strong>Resolved</strong>
                            <?= $agent['resolved_tickets'] ?> (<?= round(($agent['resolved_tickets'] / max(1, $agent['total_tickets'])) * 100) ?>%)
                        </div>
                        <div class="agent-stat">
                            <strong>Avg. Time</strong>
                            <?= round($agent['avg_resolution_time'], 1) ?> hours
                        </div>
                    </div>
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Performance Score</span>
                            <span><?= $agent['performance_score'] ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" 
                                 style="width: <?= $agent['performance_score'] ?>%; 
                                        background: <?= 
                                            $agent['performance_score'] >= 90 ? 'var(--success)' : 
                                            ($agent['performance_score'] >= 80 ? '#7bc043' : 
                                            ($agent['performance_score'] >= 70 ? 'var(--warning)' : 
                                            ($agent['performance_score'] >= 60 ? '#ee8d1a' : 'var(--danger)'))) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const agents = <?= json_encode($agents) ?>;
        
        const chartData = {
            labels: agents.map(agent => agent.name),
            datasets: [{
                label: 'Performance Score',
                data: agents.map(agent => agent.performance_score),
                backgroundColor: agents.map(agent => 
                    agent.performance_score >= 90 ? '#4cc9f0' :
                    agent.performance_score >= 80 ? '#7bc043' :
                    agent.performance_score >= 70 ? '#f8961e' :
                    agent.performance_score >= 60 ? '#ee8d1a' : '#f72585'
                ),
                borderWidth: 0,
                borderRadius: 6
            }]
        };

        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
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

        // Chart filter
        document.getElementById('chartFilter').addEventListener('change', function() {
            const value = this.value;
            
            if (value === 'resolved') {
                performanceChart.data.datasets[0].label = 'Tickets Resolved';
                performanceChart.data.datasets[0].data = agents.map(agent => agent.resolved_tickets);
                performanceChart.options.scales.ticks = {
                    callback: function(value) {
                        return value;
                    }
                };
                performanceChart.options.scales.max = undefined;
            } 
            else if (value === 'sla') {
                performanceChart.data.datasets[0].label = 'SLA Compliance';
                performanceChart.data.datasets[0].data = agents.map(agent => 
                    agent.total_tickets > 0 ? 
                    Math.round(((agent.total_tickets - agent.breached_tickets) / agent.total_tickets) * 100) : 
                    100
                );
                performanceChart.options.scales.max = 100;
                performanceChart.options.scales.ticks = {
                    callback: function(value) {
                        return value + '%';
                    }
                };
            }
            else {
                performanceChart.data.datasets[0].label = 'Performance Score';
                performanceChart.data.datasets[0].data = agents.map(agent => agent.performance_score);
                performanceChart.options.scales.max = 100;
                performanceChart.options.scales.ticks = {
                    callback: function(value) {
                        return value + '%';
                    }
                };
            }
            
            performanceChart.update();
        });

        // Animate progress bars on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const width = entry.target.style.width;
                        entry.target.style.width = '0';
                        setTimeout(() => {
                            entry.target.style.width = width;
                        }, 100);
                    }
                });
            }, { threshold: 0.5 });
            
            progressBars.forEach(bar => {
                observer.observe(bar);
            });
        });

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing';
            setTimeout(() => {
                location.reload();
            }, 1000);
        });

        // Add hover effect to cards
        document.querySelectorAll('.agent-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                anime({
                    targets: card,
                    scale: 1.02,
                    duration: 300,
                    easing: 'easeInOutQuad'
                });
            });
            
            card.addEventListener('mouseleave', () => {
                anime({
                    targets: card,
                    scale: 1,
                    duration: 300,
                    easing: 'easeInOutQuad'
                });
            });
        });
    </script>
</body>
</html>