<?php
session_start();

// Ensure the agent is logged in
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent-login.php");
    exit;
}

if (!isset($_GET['ticket_id'])) {
    die("Ticket ID is required.");
}

// Get the ticket ID from the URL
$ticket_id = $_GET['ticket_id'];
$agent_id = $_SESSION['agent_id'];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "yna_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = '';
$photo_path = '';

// Handle the form submission for recording the message and photo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response_message = trim($_POST['message'] ?? '');
    
    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $file_ext;
        $destination = $upload_dir . $filename;
        
        // Check if file is an image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['photo']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                $photo_path = $destination;
            } else {
                $message = "Error uploading photo.";
            }
        } else {
            $message = "Only JPG, PNG, and GIF files are allowed.";
        }
    }
    
    // Insert message into the database (with photo path if available)
    if (!empty($response_message) || !empty($photo_path)) {
        $stmt = $conn->prepare("INSERT INTO messages (ticket_id, agent_id, message, photo_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $ticket_id, $agent_id, $response_message, $photo_path);
        
        if ($stmt->execute()) {
            $message = "Message recorded successfully.";
            // Redirect to avoid form resubmission
            header("Location: agent-action.php?ticket_id=$ticket_id");
            exit;
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please enter a message or attach a photo.";
    }
}

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE ticket_id = ?");
    $update_stmt->bind_param("si", $new_status, $ticket_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Fetch ticket details
$query = "SELECT ticket_id, name, email, department, priority, sla, subject, device_used, 
                 issue_frequency, urgency_reason, description, attachment, status, created_at 
          FROM tickets WHERE ticket_id = $ticket_id";
$result = $conn->query($query);

if ($result->num_rows === 1) {
    $ticket = $result->fetch_assoc();
} else {
    die("Ticket not found.");
}

// Fetch all messages for the ticket with photo paths
$messages_query = "SELECT m.message, m.photo_path, m.created_at, a.name AS agent_name, a.agent_id 
                   FROM messages m 
                   LEFT JOIN agents a ON m.agent_id = a.agent_id 
                   WHERE m.ticket_id = $ticket_id ORDER BY m.created_at ASC";
$messages_result = $conn->query($messages_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticket #<?= htmlspecialchars($ticket_id) ?> | Support Chat</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --light-bg: #f5f7fa;
      --card-bg: #ffffff;
      --text-dark: #2c3e50;
      --text-light: #f8f9fa;
      --accent: #ffd369;
      --success: #4cc9f0;
      --warning: #f8961e;
      --danger: #f72585;
      --border-radius: 10px;
      --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      --chat-bg: #f0f4f8;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light-bg);
      color: var(--text-dark);
      line-height: 1.6;
    }

    .container {
      display: grid;
      grid-template-columns: 300px 1fr;
      min-height: 100vh;
    }

    /* Ticket Info Sidebar */
    .ticket-sidebar {
      background: var(--card-bg);
      border-right: 1px solid #e1e5eb;
      padding: 20px;
      overflow-y: auto;
    }

    .ticket-header {
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .ticket-header h2 {
      color: var(--primary);
      font-size: 20px;
      margin-bottom: 10px;
    }

    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
      text-transform: capitalize;
      margin-bottom: 15px;
    }

    .status-open {
      background: rgba(247, 37, 133, 0.1);
      color: var(--danger);
    }

    .status-in-progress {
      background: rgba(248, 150, 30, 0.1);
      color: var(--warning);
    }

    .status-closed {
      background: rgba(76, 201, 240, 0.1);
      color: var(--success);
    }

    .ticket-details {
      margin-bottom: 25px;
    }

    .detail-item {
      margin-bottom: 15px;
    }

    .detail-item strong {
      display: block;
      font-size: 13px;
      color: #6c757d;
      margin-bottom: 3px;
    }

    .detail-item p {
      font-size: 14px;
      margin: 0;
    }

    .priority-high {
      color: var(--danger);
      font-weight: 600;
    }

    .priority-medium {
      color: var(--warning);
      font-weight: 600;
    }

    .priority-low {
      color: var(--success);
      font-weight: 600;
    }

    .attachment-link {
      display: inline-flex;
      align-items: center;
      color: var(--primary);
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    .attachment-link:hover {
      color: var(--secondary);
      text-decoration: underline;
    }

    .attachment-link i {
      margin-right: 5px;
    }

    /* Status Update Form */
    .status-form {
      background: var(--light-bg);
      padding: 15px;
      border-radius: var(--border-radius);
      margin-top: 20px;
    }

    .status-form label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 500;
    }

    .status-form select {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: var(--border-radius);
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      margin-bottom: 10px;
    }

    .status-form button {
      width: 100%;
      padding: 8px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: var(--border-radius);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .status-form button:hover {
      background: var(--secondary);
    }

    /* Chat Area */
    .chat-container {
      display: flex;
      flex-direction: column;
      height: 100vh;
    }

    .chat-header {
      background: var(--primary);
      color: white;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .chat-header h2 {
      font-size: 18px;
      font-weight: 600;
    }

    .chat-messages {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      background: var(--chat-bg);
    }

    .message {
      display: flex;
      margin-bottom: 15px;
      animation: fadeIn 0.3s ease-out;
    }

    .message.agent {
      justify-content: flex-end;
    }

    .message-content {
      max-width: 70%;
      padding: 12px 15px;
      border-radius: var(--border-radius);
      position: relative;
      box-shadow: var(--box-shadow);
    }

    .message.customer .message-content {
      background: white;
      border-top-left-radius: 0;
    }

    .message.agent .message-content {
      background: var(--primary);
      color: white;
      border-top-right-radius: 0;
    }

    .message-meta {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      margin-top: 5px;
    }

    .message.customer .message-meta {
      color: #6c757d;
    }

    .message.agent .message-meta {
      color: rgba(255, 255, 255, 0.7);
    }

    .message-time {
      font-size: 11px;
    }

    .chat-input {
      padding: 15px;
      background: var(--card-bg);
      border-top: 1px solid #e1e5eb;
    }

    .chat-form {
      display: flex;
      gap: 10px;
    }

    .chat-form textarea {
      flex: 1;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: var(--border-radius);
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      resize: none;
      min-height: 50px;
      max-height: 150px;
      transition: all 0.3s ease;
    }

    .chat-form textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    .chat-form button {
      padding: 0 20px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: var(--border-radius);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .chat-form button:hover {
      background: var(--secondary);
    }

    /* Success message */
    .success-message {
      padding: 10px 15px;
      background: rgba(76, 201, 240, 0.1);
      color: #0d6e85;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
    }
    .photo-preview {
      max-width: 200px;
      max-height: 200px;
      margin-top: 10px;
      border-radius: 5px;
      display: none;
    }
    .photo-message {
      margin-top: 10px;
    }
    .photo-message img {
      max-width: 100%;
      max-height: 300px;
      border-radius: 5px;
      cursor: pointer;
    }
    .file-input-wrapper {
      position: relative;
      display: inline-block;
    }
    .file-input-wrapper input[type="file"] {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }
    .file-input-button {
      padding: 8px 15px;
      background: var(--primary-light);
      color: white;
      border-radius: var(--border-radius);
      display: inline-flex;
      align-items: center;
      gap: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .file-input-button:hover {
      background: var(--primary);
    }
    .file-name {
      margin-left: 10px;
      font-size: 12px;
      color: #666;
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .container {
        grid-template-columns: 1fr;
      }

      .ticket-sidebar {
        border-right: none;
        border-bottom: 1px solid #e1e5eb;
      }

      .chat-header {
        padding: 12px 15px;
      }

      .message-content {
        max-width: 85%;
      }
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Ticket Information Sidebar -->
    <div class="ticket-sidebar">
      <div class="ticket-header">
        <h2><?= htmlspecialchars($ticket['subject']) ?></h2>
        <div class="status-badge status-<?= str_replace(' ', '-', strtolower($ticket['status'])) ?>">
          <?= htmlspecialchars($ticket['status']) ?>
        </div>
      </div>

      <div class="ticket-details">
        <div class="detail-item">
          <strong>Ticket ID</strong>
          <p>#<?= htmlspecialchars($ticket_id) ?></p>
        </div>
        <div class="detail-item">
          <strong>Priority</strong>
          <p class="priority-<?= strtolower($ticket['priority']) ?>">
            <?= htmlspecialchars($ticket['priority']) ?>
          </p>
        </div>
        <div class="detail-item">
          <strong>Customer</strong>
          <p><?= htmlspecialchars($ticket['name']) ?></p>
        </div>
        <div class="detail-item">
          <strong>Email</strong>
          <p><?= htmlspecialchars($ticket['email']) ?></p>
        </div>
        <div class="detail-item">
          <strong>Department</strong>
          <p><?= htmlspecialchars($ticket['department']) ?></p>
        </div>
        <div class="detail-item">
          <strong>Created</strong>
          <p><?= date("M j, Y g:i a", strtotime($ticket['created_at'])) ?></p>
        </div>
        <?php if ($ticket['attachment']): ?>
        <div class="detail-item">
          <strong>Attachment</strong>
          <p>
            <a href="<?= htmlspecialchars($ticket['attachment']) ?>" target="_blank" class="attachment-link">
              <i class="fas fa-paperclip"></i> View File
            </a>
          </p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Status Update Form -->
      <form method="POST" class="status-form">
        <label for="status">Update Ticket Status</label>
        <select id="status" name="status">
          <option value="open" <?= $ticket['status'] == 'open' ? 'selected' : '' ?>>Open</option>
          <option value="in progress" <?= $ticket['status'] == 'in progress' ? 'selected' : '' ?>>In Progress</option>
          <option value="closed" <?= $ticket['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <button type="submit" name="update_status">Update Status</button><br>
        <a href='agent-tickets.php'>Back to menu</a>
      </form>
    </div>

    <!-- Chat Area -->
    <div class="chat-container">
      <div class="chat-header">
        <h2>Conversation with <?= htmlspecialchars($ticket['name']) ?></h2>
        <div class="priority-<?= strtolower($ticket['priority']) ?>">
          <?= htmlspecialchars($ticket['priority']) ?> Priority
        </div>
      </div>

      <div class="chat-messages">
        <?php if ($message): ?>
          <div class="success-message animate__animated animate__fadeIn">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <!-- Initial customer message -->
        <div class="message customer animate__animated animate__fadeIn">
          <div class="message-content">
            <div class="message-text">
              <p><strong>Subject:</strong> <?= htmlspecialchars($ticket['subject']) ?></p>
              <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
              <?php if ($ticket['urgency_reason']): ?>
                <p><strong>Urgency:</strong> <?= nl2br(htmlspecialchars($ticket['urgency_reason'])) ?></p>
              <?php endif; ?>
            </div>
            <div class="message-meta">
              <span class="message-sender"><?= htmlspecialchars($ticket['name']) ?></span>
              <span class="message-time"><?= date("M j, g:i a", strtotime($ticket['created_at'])) ?></span>
            </div>
          </div>
        </div>

        <!-- Agent responses -->
        <?php if ($messages_result && $messages_result->num_rows > 0): ?>
          <?php while ($msg = $messages_result->fetch_assoc()): ?>
            <div class="message <?= $msg['agent_id'] == $agent_id ? 'agent' : 'customer' ?> animate__animated animate__fadeIn">
              <div class="message-content">
                <div class="message-text">
                  <?= nl2br(htmlspecialchars($msg['message'])) ?>
                  
                  <?php if (!empty($msg['photo_path'])): ?>
                  <div class="photo-message">
                    <img src="<?= htmlspecialchars($msg['photo_path']) ?>" alt="Attached photo" 
                         onclick="window.open('<?= htmlspecialchars($msg['photo_path']) ?>', '_blank')">
                  </div>
                  <?php endif; ?>
                </div>
                <div class="message-meta">
                  <span class="message-sender"><?= htmlspecialchars($msg['agent_name']) ?></span>
                  <span class="message-time"><?= date("M j, g:i a", strtotime($msg['created_at'])) ?></span>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div style="text-align: center; margin: 30px 0; color: #6c757d;">
            <i class="fas fa-comments" style="font-size: 24px; margin-bottom: 10px;"></i>
            <p>No responses yet. Be the first to reply!</p>
          </div>
        <?php endif; ?>
      </div>

      <div class="chat-input">
        <form method="POST" class="chat-form" enctype="multipart/form-data">
          <textarea id="message" name="message" placeholder="Type your response here..."></textarea>
          <div style="display: flex; gap: 10px; align-items: center;">
            <div class="file-input-wrapper">
              <div class="file-input-button">
                <i class="fas fa-camera"></i> Add Photo
                <input type="file" name="photo" id="photo" accept="image/*">
              </div>
              <span id="file-name" class="file-name"></span>
            </div>
            <button type="submit">
              <i class="fas fa-paper-plane"></i> Send
            </button>
          </div>
          <img id="photo-preview" class="photo-preview" src="#" alt="Preview">
        </form>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('photo').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('photo-preview');
      const fileName = document.getElementById('file-name');
      
      if (file) {
        fileName.textContent = file.name;
        
        const reader = new FileReader();
        reader.onload = function(event) {
          preview.src = event.target.result;
          preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
        fileName.textContent = '';
      }
    });
    
    // Auto-scroll to bottom of chat
    const chatMessages = document.querySelector('.chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // Auto-resize textarea
    const textarea = document.querySelector('textarea');
    textarea.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
    });

    // Add animation delay to messages
    document.querySelectorAll('.message').forEach((msg, index) => {
      msg.style.animationDelay = `${index * 0.1}s`;
    });

    // Character counter for textarea
    textarea.addEventListener('input', function() {
      const charCount = this.value.length;
      const counter = document.getElementById('char-counter') || document.createElement('div');
      counter.id = 'char-counter';
      counter.style.fontSize = '12px';
      counter.style.color = charCount > 1000 ? 'var(--danger)' : '#6c757d';
      counter.textContent = `${charCount}/1000 characters`;
      
      if (!this.nextElementSibling || this.nextElementSibling.id !== 'char-counter') {
        this.insertAdjacentElement('afterend', counter);
      }
    });
  </script>
</body>
</html>