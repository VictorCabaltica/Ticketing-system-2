<?php
// Start session to access logged-in agent info
session_start();
include 'agent-dashboard.php';

// Check if agent is logged in, if not, redirect to login page
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent-login.php");
    exit;
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "yna_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the agent's ID from the session
$agent_id = $_SESSION['agent_id']; 

// Query to fetch tickets assigned to the logged-in agent
$query = "
    SELECT t.ticket_id, t.subject, t.description, t.status
    FROM tickets t
    JOIN assignment a ON t.ticket_id = a.ticket_id
    WHERE a.agent_id = $agent_id
";
$tickets = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assigned Tickets</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #3f37c9;
      --secondary: #3a0ca3;
      --success: #4cc9f0;
      --warning: #f8961e;
      --danger: #f72585;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --gray-light: #e9ecef;
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
      color: var(--dark);
      line-height: 1.6;
      padding: 20px;
      
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-top: 60px;
    }
    
    h2 {
      color: var(--primary);
      margin-bottom: 25px;
      font-weight: 600;
    }
    
    .ticket-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    .ticket-table th {
      background-color: var(--primary);
      color: white;
      padding: 12px 15px;
      text-align: left;
    }
    
    .ticket-table td {
      padding: 12px 15px;
      border-bottom: 1px solid var(--gray-light);
    }
    
    .ticket-table tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }
    
    .status-open {
      background-color: rgba(244, 67, 54, 0.1);
      color: #f44336;
    }
    
    .status-in-progress {
      background-color: rgba(255, 152, 0, 0.1);
      color: #ff9800;
    }
    
    .status-resolved {
      background-color: rgba(76, 175, 80, 0.1);
      color: #4caf50;
    }
    
    .action-btn {
      padding: 8px 12px;
      border-radius: 5px;
      background-color: var(--primary);
      color: white;
      text-decoration: none;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    
    .action-btn:hover {
      background-color: var(--primary-light);
    }
    
    .no-tickets {
      text-align: center;
      padding: 40px;
      color: var(--gray);
    }
    
    @media (max-width: 768px) {
      .ticket-table {
        display: block;
        overflow-x: auto;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Your Assigned Tickets</h2>
    
    <?php if ($tickets->num_rows > 0): ?>
      <table class="ticket-table">
        <thead>
          <tr>
            <th>Ticket ID</th>
            <th>Subject</th>
            <th>Description</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $tickets->fetch_assoc()): ?>
            <tr>
              <td>#<?= htmlspecialchars($row['ticket_id']) ?></td>
              <td><?= htmlspecialchars($row['subject']) ?></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td>
                <?php 
                  $statusClass = '';
                  switch(strtolower($row['status'])) {
                    case 'open': $statusClass = 'status-open'; break;
                    case 'in progress': $statusClass = 'status-in-progress'; break;
                    case 'resolved': $statusClass = 'status-resolved'; break;
                    default: $statusClass = 'status-open';
                  }
                ?>
                <span class="status-badge <?= $statusClass ?>">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
              <td>
                <a href="agent-action.php?ticket_id=<?= $row['ticket_id'] ?>" class="action-btn">
                  <i class="fas fa-edit"></i> Action
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="no-tickets">
        <i class="far fa-smile"></i>
        <h3>No tickets assigned to you</h3>
        <p>You're all caught up! Check back later for new tickets.</p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>