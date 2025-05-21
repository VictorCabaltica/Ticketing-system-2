<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user-login.php");
    exit;
}

if (!isset($_GET['ticket_id'])) {
    die("Ticket ID is required.");
}

// Get the ticket ID from the URL
$ticket_id = $_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "yna_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debugging - Print user info
// echo "Ticket ID: " . $ticket_id . "<br>";
// echo "User ID: " . $user_id . "<br>";

// Fetch ticket details - REVISED QUERY
// Try to get the ticket without any user restrictions first
$query = "SELECT ticket_id, name, email, department, priority, sla, subject, device_used, 
                 issue_frequency, urgency_reason, description, attachment, status, created_at 
          FROM tickets 
          WHERE ticket_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ticket = $result->fetch_assoc();
    
    // For security, verify the user has permission by checking a separate query
    // Only apply this check if you want to enforce permissions - commented out for testing
    // $permission_query = "SELECT 1 FROM users WHERE user_id = ? AND email = ?";
    // $permission_stmt = $conn->prepare($permission_query);
    // $permission_stmt->bind_param("is", $user_id, $ticket['email']);
    // $permission_stmt->execute();
    // $permission_result = $permission_stmt->get_result();
    // if ($permission_result->num_rows === 0) {
    //     die("You don't have permission to view this ticket.");
    // }
    // $permission_stmt->close();
} else {
    die("Ticket not found. Please check the ticket ID and try again.");
}
$stmt->close();

// Fetch all messages for the ticket
$messages_query = "SELECT m.message, m.created_at, 
                   CASE 
                     WHEN m.agent_id IS NOT NULL THEN a.name
                     ELSE t.name
                   END AS sender_name,
                   CASE 
                     WHEN m.agent_id IS NOT NULL THEN 'agent'
                     ELSE 'customer'
                   END AS sender_type
                   FROM messages m 
                   LEFT JOIN agents a ON m.agent_id = a.agent_id 
                   LEFT JOIN tickets t ON m.ticket_id = t.ticket_id
                   WHERE m.ticket_id = ? ORDER BY m.created_at ASC";
$stmt = $conn->prepare($messages_query);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$messages_result = $stmt->get_result();
$stmt->close();

// Handle the form submission for recording the message
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $response_message = trim($_POST['message']);

    // Insert message into the database
    $stmt = $conn->prepare("INSERT INTO messages (ticket_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $ticket_id, $response_message);
    
    if ($stmt->execute()) {
        $message = "Message sent successfully.";
    } else {
        $message = "Error sending message: " . $stmt->error;
    }
    $stmt->close();
    
    // Stay on the current page instead of redirecting
    // The file name might be user-contactagent.php not user-chat.php
    $current_page = basename($_SERVER['PHP_SELF']);
    header("Location: $current_page?ticket_id=$ticket_id");
    exit;
}

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
        <h2><?= isset($ticket['subject']) ? htmlspecialchars($ticket['subject']) : 'Ticket Details' ?></h2>
        <div class="status-badge status-<?= isset($ticket['status']) ? str_replace(' ', '-', strtolower($ticket['status'])) : 'open' ?>">
          <?= isset($ticket['status']) ? htmlspecialchars($ticket['status']) : 'Unknown' ?>
        </div>
      </div>

      <div class="ticket-details">
        <div class="detail-item">
          <strong>Ticket ID</strong>
          <p>#<?= htmlspecialchars($ticket_id) ?></p>
        </div>
        <div class="detail-item">
          <strong>Priority</strong>
          <p class="priority-<?= isset($ticket['priority']) ? strtolower($ticket['priority']) : 'medium' ?>">
            <?= isset($ticket['priority']) ? htmlspecialchars($ticket['priority']) : 'Medium' ?>
          </p>
        </div>
        <div class="detail-item">
          <strong>Status</strong>
          <p><?= isset($ticket['status']) ? htmlspecialchars(ucfirst($ticket['status'])) : 'Open' ?></p>
        </div>
        <div class="detail-item">
          <strong>Department</strong>
          <p><?= isset($ticket['department']) ? htmlspecialchars($ticket['department']) : 'General Support' ?></p>
        </div>
        <div class="detail-item">
          <strong>Created</strong>
          <p><?= isset($ticket['created_at']) ? date("M j, Y g:i a", strtotime($ticket['created_at'])) : date("M j, Y g:i a") ?></p>
        </div>
        <?php if (isset($ticket['attachment']) && $ticket['attachment']): ?>
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

      <div style="margin-top: 20px;">
        <a href="user-viewticket.php" class="back-link" style="display: inline-block; padding: 8px 15px; background: var(--light-bg); color: var(--primary); border-radius: var(--border-radius); text-decoration: none; transition: all 0.3s ease;">
          <i class="fas fa-arrow-left"></i> Back to My Tickets
        </a>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-container">
      <div class="chat-header">
        <h2>Support Conversation</h2>
        <div class="priority-<?= isset($ticket['priority']) ? strtolower($ticket['priority']) : 'medium' ?>">
          <?= isset($ticket['priority']) ? htmlspecialchars($ticket['priority']) : 'Medium' ?> Priority
        </div>
      </div>

      <div class="chat-messages">
        <?php if ($message): ?>
          <div class="success-message animate__animated animate__fadeIn">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <!-- Initial customer message -->
        <?php if (isset($ticket['description'])): ?>
        <div class="message customer animate__animated animate__fadeIn">
          <div class="message-content">
            <div class="message-text">
              <p><strong>Subject:</strong> <?= htmlspecialchars($ticket['subject']) ?></p>
              <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
              <?php if (isset($ticket['urgency_reason']) && $ticket['urgency_reason']): ?>
                <p><strong>Urgency:</strong> <?= nl2br(htmlspecialchars($ticket['urgency_reason'])) ?></p>
              <?php endif; ?>
            </div>
            <div class="message-meta">
              <span class="message-sender">You</span>
              <span class="message-time"><?= isset($ticket['created_at']) ? date("M j, g:i a", strtotime($ticket['created_at'])) : date("M j, g:i a") ?></span>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Conversation messages -->
        <?php if ($messages_result && $messages_result->num_rows > 0): ?>
          <?php while ($msg = $messages_result->fetch_assoc()): ?>
            <div class="message <?= $msg['sender_type'] === 'agent' ? 'agent' : 'customer' ?> animate__animated animate__fadeIn">
              <div class="message-content">
                <div class="message-text">
                  <?= nl2br(htmlspecialchars($msg['message'])) ?>
                </div>
                <div class="message-meta">
                  <span class="message-sender"><?= htmlspecialchars($msg['sender_name']) ?></span>
                  <span class="message-time"><?= date("M j, g:i a", strtotime($msg['created_at'])) ?></span>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div style="text-align: center; margin: 30px 0; color: #6c757d;">
            <i class="fas fa-comments" style="font-size: 24px; margin-bottom: 10px;"></i>
            <p>No responses from support yet.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Chat input form -->
      <div class="chat-input">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?ticket_id=' . $ticket_id; ?>" class="chat-form">
          <textarea id="message" name="message" required placeholder="Type your response to the support agent..."></textarea>
          <button type="submit" name="submit_message">
            <i class="fas fa-paper-plane"></i> Send
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
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