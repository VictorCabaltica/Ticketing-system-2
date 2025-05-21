<?php
// Database connection
include 'user-dashboard2.php';
$conn = new mysqli("localhost", "root", "", "yna_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// SLA checker function
function isSlaBreached($created_at, $priority) {
    date_default_timezone_set('Asia/Manila');

    $now = new DateTime();
    $created = new DateTime($created_at);

    $slaHours = [
        'critical' => 2,
        'high'     => 4,
        'medium'   => 6,
        'low'      => 8
    ];

    if (!isset($slaHours[$priority])) return false;

    $deadline = clone $created;
    $deadline->modify("+{$slaHours[$priority]} hours");

    return $now > $deadline;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $priority = $_POST['priority'];
    $subject = $_POST['subject'];
    $device = $_POST['device'];
    $frequency = $_POST['frequency'];
    $urgency_reason = $_POST['urgency_reason'];
    $description = $_POST['description']; 

    $attachment = "";
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $attachment = $target_dir . basename($_FILES["attachment"]["name"]);
        move_uploaded_file($_FILES["attachment"]["tmp_name"], $attachment);
    }

    $stmt = $conn->prepare("INSERT INTO tickets (
    user_id, name, email, department, priority, subject, device_used, 
    issue_frequency, urgency_reason, description, attachment, status, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open', NOW())");


    $stmt->bind_param("issssssssss", $user_id, $name, $email, $department, $priority,
    $subject, $device, $frequency, $urgency_reason, $description, $attachment);


    if ($stmt->execute()) {
        $ticket_id = $stmt->insert_id;
        $result = $conn->query("SELECT created_at FROM tickets WHERE ticket_id = $ticket_id");
        $row = $result->fetch_assoc();
        $created_at = $row['created_at'];

        if (isSlaBreached($created_at, $priority)) {
            $conn->query("UPDATE tickets SET status = 'overdue' WHERE ticket_id = $ticket_id");
        }

        echo "<script>
                showSuccessNotification('Ticket submitted successfully!');
                setTimeout(() => { window.location='user-dashboard.php'; }, 2000);
              </script>";
    } else {
        echo "<script>showErrorNotification('Error submitting ticket: " . addslashes($stmt->error) . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Submit Ticket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
  :root {
    --primary: #4c64d3;
    --primary-light: #6b80e0;
    --accent: #ff6b6b;
    --success: #4caf50;
    --error: #f44336;
    --light-gray: #f5f7fa;
    --dark-gray: #333;
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f0f4ff, #ffffff);
    min-height: 100vh;
    animation: gradientBG 15s ease infinite;
    background-size: 400% 400%;
    margin-left: 20px;
    
  }

  @keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  .container {
    max-width: 850px;
    margin: 50px auto;
    padding: 40px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: var(--shadow);
    animation: fadeInUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform-style: preserve-3d;
    transition: transform 0.3s, box-shadow 0.3s;
    margin-left: 300px;
  }

  .container:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px) rotateX(10deg);
    }
    to {
      opacity: 1;
      transform: translateY(0) rotateX(0);
    }
  }

  h2 {
    text-align: center;
    color: var(--primary);
    margin-bottom: 30px;
    font-size: 2rem;
    position: relative;
    padding-bottom: 10px;
  }

  h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--primary);
    border-radius: 3px;
  }

  form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px 30px;
  }

  .form-group {
    position: relative;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--dark-gray);
  }

  input,
  select,
  textarea {
    padding: 14px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 15px;
    width: 100%;
    box-sizing: border-box;
    transition: all 0.3s ease;
    background-color: rgba(245, 247, 250, 0.5);
  }

  input:focus,
  select:focus,
  textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(76, 100, 211, 0.2);
    outline: none;
    transform: translateY(-2px);
  }

  textarea {
    resize: vertical;
    min-height: 120px;
  }

  form textarea,
  form .file-upload,
  form button {
    grid-column: span 2;
  }

  button {
    padding: 16px;
    background: var(--primary);
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  button:hover {
    background: var(--primary-light);
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(76, 100, 211, 0.3);
  }

  button::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
  }

  button:focus:not(:active)::after {
    animation: ripple 1s ease-out;
  }

  @keyframes ripple {
    0% {
      transform: scale(0, 0);
      opacity: 0.5;
    }
    100% {
      transform: scale(20, 20);
      opacity: 0;
    }
  }

  /* File upload styling */
  .file-upload {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
  }

  .file-upload-btn {
    border: 2px dashed #ccc;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .file-upload-btn:hover {
    border-color: var(--primary);
    background: rgba(76, 100, 211, 0.05);
  }

  .file-upload-input {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  .file-upload-icon {
    font-size: 40px;
    color: var(--primary);
    margin-bottom: 10px;
  }

  .file-upload-text {
    color: var(--dark-gray);
    margin-bottom: 5px;
  }

  .file-upload-hint {
    color: #888;
    font-size: 13px;
  }

  /* Priority indicator */
  .priority-indicator {
    display: flex;
    align-items: center;
    margin-top: 5px;
  }

  .priority-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
    transition: all 0.3s ease;
  }

  /* Notification */
  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    box-shadow: var(--shadow);
    transform: translateX(150%);
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    z-index: 1000;
    display: flex;
    align-items: center;
  }

  .notification.success {
    background: var(--success);
  }

  .notification.error {
    background: var(--error);
  }

  .notification.show {
    transform: translateX(0);
  }

  .notification i {
    margin-right: 10px;
    font-size: 20px;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    form {
      grid-template-columns: 1fr;
    }

    form textarea,
    form .file-upload,
    form button {
      grid-column: span 1;
    }

    .container {
      margin: 20px;
      padding: 25px;
    }
  }

  /* Priority colors */
  .priority-low { background-color: #4caf50; }
  .priority-medium { background-color: #ff9800; }
  .priority-high { background-color: #f44336; }
  .priority-critical { background-color: #9c27b0; }
</style>
</head>
<body>

<div class="container">
  <h2><i class="fas fa-ticket-alt"></i> Submit Support Ticket</h2>
  <form method="POST" enctype="multipart/form-data" id="ticketForm">
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" placeholder="John Doe" required>
    </div>

    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" placeholder="john@example.com" required>
    </div>

    <div class="form-group">
      <label for="department">Department</label>
      <input type="text" id="department" name="department" placeholder="e.g., IT, HR, Finance" required>
    </div>

    <div class="form-group">
      <label for="priority">Priority</label>
      <select id="priority" name="priority" required onchange="updatePriorityIndicator(this.value)">
        <option value="" disabled selected>Select Priority</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
        <option value="critical">Critical</option>
      </select>
      <div class="priority-indicator" id="priorityIndicator">
        <div class="priority-dot"></div>
        <span id="priorityText">Select a priority level</span>
      </div>
    </div>

    <div class="form-group">
      <label for="subject">Subject</label>
      <input type="text" id="subject" name="subject" placeholder="Brief description of the issue" required>
    </div>

    <div class="form-group">
      <label for="device">Device/System</label>
      <input type="text" id="device" name="device" placeholder="What device or system is affected?" required>
    </div>

    <div class="form-group">
      <label for="frequency">Frequency</label>
      <input type="text" id="frequency" name="frequency" placeholder="How often does this happen?" required>
    </div>

    <div class="form-group">
      <label for="urgency_reason">Urgency Reason</label>
      <input type="text" id="urgency_reason" name="urgency_reason" placeholder="Why is this urgent?" required>
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4" placeholder="Please describe the issue in detail..." required></textarea>
    </div>

    <div class="file-upload">
      <label class="file-upload-btn">
        <div class="file-upload-icon">
          <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <div class="file-upload-text">Click to upload attachment</div>
        <div class="file-upload-hint">Supports JPG, PNG, PDF, DOCX (Max 5MB)</div>
        <input type="file" class="file-upload-input" name="attachment" accept=".jpg,.png,.pdf,.docx,.txt">
      </label>
    </div>

    <button type="submit" id="submitBtn">
      <i class="fas fa-paper-plane"></i> Submit Ticket
    </button>
  </form>
</div>

<div id="notification" class="notification">
  <i class="fas fa-check-circle"></i>
  <span id="notificationText">Notification message</span>
</div>

<script>
  // Priority indicator update
  function updatePriorityIndicator(priority) {
    const dot = document.querySelector('#priorityIndicator .priority-dot');
    const text = document.querySelector('#priorityIndicator #priorityText');
    
    // Reset classes
    dot.className = 'priority-dot';
    
    switch(priority) {
      case 'low':
        dot.classList.add('priority-low');
        text.textContent = 'Low Priority - Response within 8 hours';
        break;
      case 'medium':
        dot.classList.add('priority-medium');
        text.textContent = 'Medium Priority - Response within 6 hours';
        break;
      case 'high':
        dot.classList.add('priority-high');
        text.textContent = 'High Priority - Response within 4 hours';
        break;
      case 'critical':
        dot.classList.add('priority-critical');
        text.textContent = 'Critical Priority - Response within 2 hours';
        break;
      default:
        text.textContent = 'Select a priority level';
    }
  }

  // File upload display
  document.querySelector('.file-upload-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
    document.querySelector('.file-upload-text').textContent = fileName;
  });

  // Form submission
  document.getElementById('ticketForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
  });

  // Show notification
  function showSuccessNotification(message) {
    const notification = document.getElementById('notification');
    notification.className = 'notification success';
    document.getElementById('notificationText').textContent = message;
    notification.querySelector('i').className = 'fas fa-check-circle';
    notification.classList.add('show');
    
    setTimeout(() => {
      notification.classList.remove('show');
    }, 3000);
  }

  function showErrorNotification(message) {
    const notification = document.getElementById('notification');
    notification.className = 'notification error';
    document.getElementById('notificationText').textContent = message;
    notification.querySelector('i').className = 'fas fa-exclamation-circle';
    notification.classList.add('show');
    
    setTimeout(() => {
      notification.classList.remove('show');
    }, 3000);
  }

  // Add hover effect to form groups
  document.querySelectorAll('.form-group').forEach(group => {
    const input = group.querySelector('input, select, textarea');
    
    input.addEventListener('focus', () => {
      group.style.transform = 'translateY(-3px)';
      group.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.05)';
    });
    
    input.addEventListener('blur', () => {
      group.style.transform = '';
      group.style.boxShadow = '';
    });
  });
</script>
</body>
</html>