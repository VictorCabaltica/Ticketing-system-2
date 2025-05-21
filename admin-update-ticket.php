<?php
// Start session for flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection (db_connection.php)
$conn = new mysqli('localhost', 'root', '', 'yna_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get ticket_id from URL
$ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

// Validate ticket ID
if ($ticket_id <= 0) {
    $_SESSION['error'] = "Invalid ticket ID";
    header('Location: admin-manageticket.php');
    exit;
}

// Fetch ticket details
$sql = "SELECT * FROM tickets WHERE ticket_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if (!$ticket) {
    $_SESSION['error'] = "Ticket not found";
    header('Location: admin-manageticket.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $required = ['subject', 'description', 'status', 'priority'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Please fill all required fields";
            header("Location: admin-update-ticket.php?ticket_id=$ticket_id");
            exit;
        }
    }

    // Sanitize inputs
    $subject = htmlspecialchars(trim($_POST['subject']));
    $description = htmlspecialchars(trim($_POST['description']));
    $status = $_POST['status'];
    $priority = $_POST['priority'];

    // Update ticket
    try {
        $update_sql = "UPDATE tickets SET 
                       subject = ?, 
                       description = ?, 
                       status = ?, 
                       priority = ?, 
                       updated_at = NOW() 
                       WHERE ticket_id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $subject, $description, $status, $priority, $ticket_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = "Ticket updated successfully!";
            } else {
                $_SESSION['warning'] = "No changes were made";
            }
            header("Location: admin-manageticket.php");
            exit;
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating ticket: " . $e->getMessage();
        header("Location: admin-update-ticket.php?ticket_id=$ticket_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Ticket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --danger: #f72585;
      --success: #4cc9f0;
      --warning: #f8961e;
      --critical: #d00000;
      --high: #ff5400;
      --medium: #ff9e00;
      --low: #38b000;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
    }
    .ticket-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 30px;
      margin: 30px auto;
      max-width: 800px;
      animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .priority-badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.8rem;
    }
    .priority-critical { background-color: rgba(208,0,0,0.1); color: var(--critical); }
    .priority-high { background-color: rgba(255,84,0,0.1); color: var(--high); }
    .priority-medium { background-color: rgba(255,158,0,0.1); color: var(--medium); }
    .priority-low { background-color: rgba(56,176,0,0.1); color: var(--low); }
    .status-badge {
      padding: 5px 10px;
      border-radius: 20px;
      color: white;
      font-size: 0.8rem;
    }
    .status-open { background-color: var(--primary); }
    .status-in_progress { background-color: var(--warning); }
    .status-closed { background-color: var(--success); }
    textarea { min-height: 150px; }
    .meta-item { display: flex; align-items: center; gap: 8px; color: #666; }
    .meta-item i { color: var(--primary); }
  </style>
</head>
<body>
  <div class="container">
    <div class="ticket-card">
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
          <i class="fas fa-ticket-alt me-2"></i>Update Ticket #<?= $ticket['ticket_id'] ?>
        </h2>
        <span class="priority-badge priority-<?= $ticket['priority'] ?>">
          <i class="fas fa-<?= 
            $ticket['priority'] === 'critical' ? 'fire' : 
            ($ticket['priority'] === 'high' ? 'exclamation-triangle' : 
            ($ticket['priority'] === 'medium' ? 'exclamation-circle' : 'info-circle'))
          ?>"></i>
          <?= ucfirst($ticket['priority']) ?>
        </span>
      </div>
      
      <div class="d-flex gap-3 mb-4 flex-wrap">
        <div class="meta-item">
          <i class="fas fa-user"></i>
          <span>Created by: <?= htmlspecialchars($ticket['name']) ?></span>
        </div>
        <div class="meta-item">
          <i class="fas fa-envelope"></i>
          <span><?= htmlspecialchars($ticket['email']) ?></span>
        </div>
        <div class="meta-item">
          <i class="fas fa-calendar-alt"></i>
          <span>Created: <?= date('M d, Y H:i', strtotime($ticket['created_at'])) ?></span>
        </div>
      </div>
      
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Subject</label>
          <input type="text" class="form-control" name="subject" 
                 value="<?= htmlspecialchars($ticket['subject']) ?>" required>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" required><?= 
            htmlspecialchars($ticket['description']) 
          ?></textarea>
          <small class="text-muted d-block mt-1" id="char-counter"></small>
        </div>
        
        <div class="row mb-4">
          <div class="col-md-6 mb-3 mb-md-0">
            <label class="form-label">Priority</label>
            <select class="form-select" name="priority" required>
              <option value="low" <?= $ticket['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
              <option value="medium" <?= $ticket['priority'] == 'medium' ? 'selected' : '' ?>>Medium</option>
              <option value="high" <?= $ticket['priority'] == 'high' ? 'selected' : '' ?>>High</option>
              <option value="critical" <?= $ticket['priority'] == 'critical' ? 'selected' : '' ?>>Critical</option>
            </select>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" required id="status-select">
              <option value="open" <?= $ticket['status'] == 'open' ? 'selected' : '' ?>>Open</option>
              <option value="in_progress" <?= $ticket['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
              <option value="closed" <?= $ticket['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
          </div>
        </div>
        
        <div class="d-flex justify-content-between">
          <a href="admin-manageticket.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Tickets
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update Ticket
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Character counter
    document.querySelector('textarea[name="description"]').addEventListener('input', function() {
      const count = this.value.length;
      document.getElementById('char-counter').textContent = `${count} characters`;
    }).dispatchEvent(new Event('input'));
    
    // Priority badge update
    document.querySelector('select[name="priority"]').addEventListener('change', function() {
      const badge = document.querySelector('.priority-badge');
      const priority = this.value;
      const icons = {
        critical: 'fire',
        high: 'exclamation-triangle',
        medium: 'exclamation-circle',
        low: 'info-circle'
      };
      
      // Update classes
      badge.className = 'priority-badge priority-' + priority;
      
      // Update icon
      const icon = badge.querySelector('i');
      icon.className = 'fas fa-' + icons[priority];
      
      // Update text
      badge.querySelector('span').textContent = priority.charAt(0).toUpperCase() + priority.slice(1);
    });
    
    // Status change animation
    document.getElementById('status-select').addEventListener('change', function() {
      const status = this.value;
      const badge = document.querySelector('.status-badge');
      if (badge) {
        badge.className = 'status-badge status-' + status;
        badge.textContent = this.options[this.selectedIndex].text;
      }
    });
  </script>
</body>
</html>