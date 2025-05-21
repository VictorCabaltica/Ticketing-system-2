<?php
include 'db_connection.php';
include 'agent-dashboard.php';

session_start();

// Verify agent login
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    header("Location: agent-login.php");
    exit;
}

// Fetch agent details
$agent = [];
$stmt = $conn->prepare("SELECT name, email FROM agents WHERE agent_id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();
$stmt->close();

// Fetch ticket status counts
$status_counts = ['open' => 0, 'in_progress' => 0, 'closed' => 0, 'overdue' => 0];
$stmt = $conn->prepare("
    SELECT t.status, COUNT(*) as total,
           SUM(CASE WHEN TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > 
               CASE t.priority
                   WHEN 'critical' THEN 2
                   WHEN 'high' THEN 4
                   WHEN 'medium' THEN 6
                   WHEN 'low' THEN 8
               END THEN 1 ELSE 0 END) as overdue
    FROM tickets t
    JOIN assignment a ON a.ticket_id = t.ticket_id
    WHERE a.agent_id = ?
    GROUP BY t.status
");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['total'];
    $status_counts['overdue'] += $row['overdue'];
}
$stmt->close();

// Calculate performance metrics
$total_tickets = array_sum($status_counts);
$closed_rate = $total_tickets > 0 ? ($status_counts['closed'] / $total_tickets) * 100 : 0;
$sla_compliance = $total_tickets > 0 ? (($total_tickets - $status_counts['overdue']) / $total_tickets) * 100 : 100;

// Fetch recent tickets
$tickets = [];
$stmt = $conn->prepare("
    SELECT t.*, 
           TIMESTAMPDIFF(HOUR, t.created_at, NOW()) as hours_open,
           CASE t.priority
               WHEN 'critical' THEN 2
               WHEN 'high' THEN 4
               WHEN 'medium' THEN 6
               WHEN 'low' THEN 8
           END as sla_hours
    FROM tickets t
    JOIN assignment a ON a.ticket_id = t.ticket_id
    WHERE a.agent_id = ?
    ORDER BY t.created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$tickets_result = $stmt->get_result();
while ($row = $tickets_result->fetch_assoc()) {
    $row['is_overdue'] = $row['hours_open'] > $row['sla_hours'];
    $tickets[] = $row;
}
$stmt->close();

// Fetch performance trends
$performance_data = [];
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(t.updated_at, '%Y-%m-%d') as day,
           COUNT(*) as closed_count,
           AVG(TIMESTAMPDIFF(HOUR, t.created_at, t.updated_at)) as avg_time
    FROM tickets t
    JOIN assignment a ON a.ticket_id = t.ticket_id
    WHERE a.agent_id = ? AND t.status = 'closed' AND t.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY day
    ORDER BY day
");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $performance_data[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Dashboard | Performance Hub</title>
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
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f5f7fa;
      color: var(--dark);
    }

    .dashboard-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 30px;
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 30px;
    }

    /* Sidebar */
    .sidebar {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 15px;
      padding: 30px 20px;
      color: white;
      box-shadow: var(--shadow);
      position: relative;
      overflow: hidden;
    }

    .sidebar::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .sidebar::after {
      content: '';
      position: absolute;
      bottom: -80px;
      left: -80px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
    }

    .profile {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
      z-index: 1;
    }

    .profile-image {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid white;
      margin: 0 auto 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    .profile-image:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .profile h2 {
      font-size: 20px;
      margin-bottom: 5px;
    }

    .profile p {
      font-size: 14px;
      opacity: 0.8;
    }

    .nav-menu {
      list-style: none;
      margin-top: 40px;
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
      transition: all 0.3s ease;
    }

    .nav-link i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }

    .nav-link:hover, .nav-link.active {
      background: rgba(255, 255, 255, 0.15);
    }

    /* Main Content */
    .main-content {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .welcome-banner {
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
      color: white;
      padding: 25px 30px;
      border-radius: 15px;
      box-shadow: var(--shadow);
      position: relative;
      overflow: hidden;
    }

    .welcome-banner::after {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .welcome-banner h1 {
      font-size: 24px;
      margin-bottom: 10px;
      position: relative;
      z-index: 1;
    }

    .welcome-banner p {
      opacity: 0.9;
      position: relative;
      z-index: 1;
    }

    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
    }

    .stat-card {
      background: white;
      border-radius: 15px;
      padding: 20px;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
    }

    .stat-card.open::before { background: var(--warning); }
    .stat-card.in_progress::before { background: var(--primary); }
    .stat-card.closed::before { background: var(--success); }
    .stat-card.overdue::before { background: var(--danger); }

    .stat-title {
      font-size: 14px;
      color: var(--gray);
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .stat-value {
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .stat-change {
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .stat-change.positive {
      color: var(--success);
    }

    .stat-change.negative {
      color: var(--danger);
    }

    /* Performance Charts */
    .charts-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    @media (max-width: 1200px) {
      .charts-container {
        grid-template-columns: 1fr;
      }
    }

    .chart-card {
      background: white;
      border-radius: 15px;
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

    /* Tickets Table */
    .tickets-card {
      background: white;
      border-radius: 15px;
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
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
    }

    .btn:hover {
      background: var(--secondary);
      transform: translateY(-2px);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      padding: 12px 16px;
      background: var(--light);
      color: var(--gray);
      font-weight: 500;
      position: sticky;
      top: 0;
    }

    td {
      padding: 12px 16px;
      border-bottom: 1px solid var(--light);
    }

    tr:last-child td {
      border-bottom: none;
    }

    tr:hover td {
      background: rgba(67, 97, 238, 0.05);
    }

    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
    }

    .badge.open { background: #fff3e0; color: #e65100; }
    .badge.in_progress { background: #e3f2fd; color: #1565c0; }
    .badge.closed { background: #e8f5e9; color: #2e7d32; }
    .badge.overdue { background: #ffebee; color: #c62828; }

    .priority-dot {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 6px;
    }

    .priority-critical { background: #d50000; }
    .priority-high { background: #ff6d00; }
    .priority-medium { background: #ffab00; }
    .priority-low { background: #00c853; }

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

    /* Pulse animation for important elements */
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.03); }
      100% { transform: scale(1); }
    }

    .pulse {
      animation: pulse 2s infinite;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .dashboard-container {
        grid-template-columns: 1fr;
      }
      
      .sidebar {
        order: -1;
      }
    }
  </style>
</head>
<body>
  

    <!-- Main Content -->
    <div class="main-content">
      <!-- Welcome Banner -->
      <div class="welcome-banner animated">
        <h1>Welcome back, <?= explode(' ', $agent['name'])[0] ?>!</h1>
        <p>Here's what's happening with your tickets today</p>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card open animated">
          <div class="stat-title">
            <i class="fas fa-folder-open"></i>
            <span>Open Tickets</span>
          </div>
          <div class="stat-value"><?= $status_counts['open'] ?></div>
          <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 12% from yesterday
          </div>
        </div>
        
        <div class="stat-card in_progress animated delay-1">
          <div class="stat-title">
            <i class="fas fa-spinner"></i>
            <span>In Progress</span>
          </div>
          <div class="stat-value"><?= $status_counts['in_progress'] ?></div>
          <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 5% from yesterday
          </div>
        </div>
        
        <div class="stat-card closed animated delay-2">
          <div class="stat-title">
            <i class="fas fa-check-circle"></i>
            <span>Closed Today</span>
          </div>
          <div class="stat-value"><?= $status_counts['closed'] ?></div>
          <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i> 8% from yesterday
          </div>
        </div>
        
        <div class="stat-card overdue animated delay-3">
          <div class="stat-title">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Overdue</span>
          </div>
          <div class="stat-value"><?= $status_counts['overdue'] ?></div>
          <div class="stat-change negative">
            <i class="fas fa-arrow-down"></i> 3% from yesterday
          </div>
        </div>
      </div>

      <!-- Performance Charts -->
      <div class="charts-container">
        <div class="chart-card animated">
          <div class="chart-header">
            <div class="chart-title">Ticket Resolution Trend</div>
          </div>
          <canvas id="resolutionChart"></canvas>
        </div>
        
        <div class="chart-card animated delay-1">
          <div class="chart-header">
            <div class="chart-title">Performance Metrics</div>
          </div>
          <canvas id="performanceChart"></canvas>
        </div>
      </div>

      <!-- Recent Tickets -->
      <div class="tickets-card animated delay-2">
        <div class="section-header">
          <div class="section-title">Recent Tickets</div>
          <a href="agent-tickets.php" class="btn">
            <i class="fas fa-eye"></i>
            <span>View All</span>
          </a>
        </div>
        
        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th>Ticket ID</th>
                <th>Subject</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
                <th>SLA</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($tickets) > 0): ?>
                <?php foreach ($tickets as $ticket): ?>
                  <tr>
                    <td>#<?= htmlspecialchars($ticket['ticket_id']) ?></td>
                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                    <td>
                      <span class="priority-dot priority-<?= $ticket['priority'] ?>"></span>
                      <?= ucfirst(htmlspecialchars($ticket['priority'])) ?>
                    </td>
                    <td>
                      <span class="badge <?= $ticket['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                      </span>
                    </td>
                    <td><?= date('M j, g:i a', strtotime($ticket['created_at'])) ?></td>
                    <td>
                      <?php if ($ticket['status'] != 'closed' && $ticket['is_overdue']): ?>
                        <span style="color: var(--danger);">
                          <i class="fas fa-exclamation-circle"></i> Overdue
                        </span>
                      <?php elseif ($ticket['status'] != 'closed'): ?>
                        <?= max(0, $ticket['sla_hours'] - $ticket['hours_open']) ?>h left
                      <?php else: ?>
                        <span style="color: var(--success);">
                          <i class="fas fa-check-circle"></i> Resolved
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 30px; color: var(--gray);">
                    <i class="fas fa-inbox" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>No tickets assigned to you</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Resolution Trend Chart
    const resolutionCtx = document.getElementById('resolutionChart').getContext('2d');
    const resolutionChart = new Chart(resolutionCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode(array_column($performance_data, 'day')) ?>,
        datasets: [{
          label: 'Tickets Closed',
          data: <?= json_encode(array_column($performance_data, 'closed_count')) ?>,
          borderColor: 'var(--primary)',
          backgroundColor: 'rgba(67, 97, 238, 0.1)',
          tension: 0.3,
          fill: true,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.dataset.label}: ${context.raw}`;
              }
            }
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

    // Performance Metrics Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(performanceCtx, {
      type: 'doughnut',
      data: {
        labels: ['SLA Compliance', 'Resolution Rate', 'Overdue'],
        datasets: [{
          data: [<?= $sla_compliance ?>, <?= $closed_rate ?>, <?= $status_counts['overdue'] ?>],
          backgroundColor: [
            'var(--success)',
            'var(--primary)',
            'var(--danger)'
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
                return `${label}: ${value}${context.label !== 'Overdue' ? '%' : ''}`;
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

    // Add hover animations to cards
    document.querySelectorAll('.stat-card').forEach(card => {
      card.addEventListener('mouseenter', () => {
        anime({
          targets: card,
          scale: 1.03,
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

    // Add pulse animation to overdue card if overdue tickets exist
    <?php if ($status_counts['overdue'] > 0): ?>
      document.querySelector('.stat-card.overdue').classList.add('pulse');
    <?php endif; ?>

    // Auto-scroll to top on page load
    window.addEventListener('load', () => {
      window.scrollTo(0, 0);
    });

    // Refresh button simulation
    document.querySelector('.btn').addEventListener('click', function(e) {
      if (this.getAttribute('href') === '#') {
        e.preventDefault();
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing';
        setTimeout(() => {
          location.reload();
        }, 1500);
      }
    });
  </script>
</body>
</html>