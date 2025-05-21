<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'open';

// Validate status filter
$valid_statuses = ['open', 'in_progress', 'closed'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'open';
}

// Fetch tickets for the user with status filter
$tickets_sql = "SELECT * FROM tickets WHERE user_id = ? AND status = ? ORDER BY created_at DESC";
$tickets_stmt = $conn->prepare($tickets_sql);
$tickets_stmt->bind_param("is", $user_id, $status_filter);
$tickets_stmt->execute();
$tickets_result = $tickets_stmt->get_result();

// Fetch ticket counts for the status cards
$counts_sql = "SELECT status, COUNT(*) as total FROM tickets WHERE user_id = ? GROUP BY status";
$counts_stmt = $conn->prepare($counts_sql);
$counts_stmt->bind_param("i", $user_id);
$counts_stmt->execute();
$counts_result = $counts_stmt->get_result();

$counts = ['open' => 0, 'in_progress' => 0, 'closed' => 0];
while ($row = $counts_result->fetch_assoc()) {
    $counts[$row['status']] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tickets - Ticket System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Use the same styles as user-dashboard.php */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 20px;
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 8px 15px;
      background-color: #4361ee;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 14px;
    }
    
    .card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    
    .card-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 15px;
      color: #4361ee;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .status-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
      margin-bottom: 20px;
    }
    
    .status-card {
      padding: 15px;
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
    }
    
    .status-card h4 {
      margin: 0 0 5px 0;
      font-size: 14px;
      color: #666;
    }
    
    .status-card p {
      font-size: 24px;
      font-weight: bold;
      margin: 0;
    }
    
    .status-open {
      background-color: rgba(67, 97, 238, 0.1);
      border-left: 4px solid #4361ee;
    }
    
    .status-in_progress {
      background-color: rgba(248, 150, 30, 0.1);
      border-left: 4px solid #f8961e;
    }
    
    .status-closed {
      background-color: rgba(76, 201, 240, 0.1);
      border-left: 4px solid #4cc9f0;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    th {
      background-color: #f0f4ff;
      font-weight: 600;
    }
    
    tr {
      cursor: pointer;
    }
    
    tr:hover {
      background-color: #f5f9ff;
    }
    
    .priority-critical { color: #d00000; font-weight: bold; }
    .priority-high { color: #ff5400; font-weight: bold; }
    .priority-medium { color: #ff9e00; font-weight: bold; }
    .priority-low { color: #38b000; font-weight: bold; }
    
    .empty-state {
      text-align: center;
      padding: 40px;
      color: #666;
    }
    
    .empty-state i {
      font-size: 50px;
      margin-bottom: 20px;
      color: #ddd;
    }
    
    @media (max-width: 768px) {
      .status-cards {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <h1><i class="fas fa-ticket-alt"></i> My Tickets - <?= ucfirst(str_replace('_', ' ', $status_filter)) ?></h1>
    <a href="user-dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
  </div>

  <!-- Status Summary Cards -->
  <div class="status-cards">
    <div class="status-card status-open" onclick="filterTickets('open')">
      <h4>Open Tickets</h4>
      <p><?= $counts['open'] ?></p>
    </div>
    <div class="status-card status-in_progress" onclick="filterTickets('in_progress')">
      <h4>In Progress</h4>
      <p><?= $counts['in_progress'] ?></p>
    </div>
    <div class="status-card status-closed" onclick="filterTickets('closed')">
      <h4>Closed Tickets</h4>
      <p><?= $counts['closed'] ?></p>
    </div>
  </div>

  <!-- Tickets Table -->
  <div class="card">
    <div class="card-title">
      <i class="fas fa-filter"></i> 
      <?= ucfirst(str_replace('_', ' ', $status_filter)) ?> Tickets (<?= $tickets_result->num_rows ?>)
    </div>
    
    <?php if ($tickets_result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Ticket ID</th>
            <th>Subject</th>
            <th>Department</th>
            <th>Priority</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($ticket = $tickets_result->fetch_assoc()): ?>
            <tr onclick="window.location='view-ticket.php?id=<?= $ticket['ticket_id'] ?>'">
              <td>#<?= $ticket['ticket_id'] ?></td>
              <td>