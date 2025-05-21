
<?php
INCLUDE 'db_connection.php';
include 'admin-sidebar.php';

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign'])) {
    $ticket_id = $_POST['ticket_id'];
    $agent_id = $_POST['agent_id'];
    $assigned_at = date("Y-m-d H:i:s");


    $sql = "INSERT INTO assignment (ticket_id, agent_id, assigned_at) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $ticket_id, $agent_id, $assigned_at);

    if ($stmt->execute()) {
   
        $update_status_sql = "UPDATE tickets SET status = 'in_progress' WHERE ticket_id = ?";
        $update_stmt = $conn->prepare($update_status_sql);
        $update_stmt->bind_param("i", $ticket_id);
        $update_stmt->execute();
        $update_stmt->close();

        $message = "Ticket #$ticket_id successfully assigned!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$sql_tickets = "SELECT * FROM tickets WHERE status = 'open'";
$result_tickets = $conn->query($sql_tickets);

$sql_agents = "SELECT * FROM agents";
$result_agents = $conn->query($sql_agents);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Ticket to Agent</title>
  <style>
    :root {
      --primary: #4361ee;
      --danger: #f72585;
      --success: #4cc9f0;
      --warning: #f8961e;
      --text-dark: #212529;
      --card-bg: #fff;
      --bg-light: #f9f9f9;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg-light);
      margin: 0;
      padding: 20px;
      color: var(--text-dark);
      font-size: 14px;
    }

    .container {
      max-width: 900px;
      margin: 20px auto;
      background: var(--card-bg);
      padding: 25px 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    }

    h2 {
      font-size: 20px;
      margin-bottom: 20px;
      color: var(--primary);
    }

    .notification {
      padding: 10px 15px;
      border-radius: 6px;
      margin-bottom: 15px;
      font-size: 13px;
    }

    .success {
      background: #e1f7fd;
      color: #137991;
    }

    .error {
      background: #fde1ed;
      color: #a01a4d;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th, td {
      padding: 10px 12px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      background: var(--primary);
      color: white;
      font-size: 12px;
      text-transform: uppercase;
    }

    .priority-high { color: var(--danger); }
    .priority-medium { color: var(--warning); }
    .priority-low { color: var(--success); }

    .status-badge {
      font-size: 12px;
      padding: 3px 8px;
      border-radius: 12px;
      display: inline-block;
      color: white;
    }

    .status-open { background: var(--primary); }
    .status-pending { background: var(--warning); }

    .assign-form {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    select, button {
      font-size: 13px;
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    select {
      background: white;
    }

    button {
      background: var(--primary);
      color: white;
      border: none;
      cursor: pointer;
      transition: 0.2s;
    }

    button:hover {
      background: #3a56d4;
    }

    .no-tickets {
      text-align: center;
      padding: 30px;
      color: #888;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Assign Ticket to Agent</h2>

  <?php if (!empty($message)): ?>
    <div class="notification <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($result_tickets->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Subject</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Assign To</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($ticket = $result_tickets->fetch_assoc()): ?>
          <tr>
            <td><?= $ticket['ticket_id'] ?></td>
            <td><?= htmlspecialchars($ticket['subject']) ?></td>
            <td class="priority-<?= $ticket['priority'] ?>"><?= ucfirst($ticket['priority']) ?></td>
            <td><span class="status-badge status-open"><?= ucfirst($ticket['status']) ?></span></td>
            <td>
              <form method="post" class="assign-form">
                <input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">
                <select name="agent_id" required>
                  <option value="">Select Agent</option>
                  <?php
                  $result_agents->data_seek(0);
                  while ($agent = $result_agents->fetch_assoc()): ?>
                    <option value="<?= $agent['agent_id'] ?>"><?= htmlspecialchars($agent['name']) ?></option>
                  <?php endwhile; ?>
                </select>
                <button type="submit" name="assign">Assign</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="no-tickets">No open tickets found.</div>
  <?php endif; ?>
</div>

</body>
</html>
