<?php
// Database connection
include 'admin-sidebar.php';
$conn = new mysqli("localhost", "root", "", "yna_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SLA checker function
function isSlaBreached($created_at, $priority) {
    date_default_timezone_set('Asia/Manila'); // adjust as needed

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
    $sla = $_POST['sla'];
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
        name, email, department, priority, sla, subject, device_used, issue_frequency,
        urgency_reason, description, attachment, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open', NOW())");

    $stmt->bind_param("sssssssssss", $name, $email, $department, $priority, $sla,
        $subject, $device, $frequency, $urgency_reason, $description, $attachment);

    if ($stmt->execute()) {
        // Get the last inserted ticket_id and its created_at time
        $ticket_id = $stmt->insert_id;
        $result = $conn->query("SELECT created_at FROM tickets WHERE ticket_id = $ticket_id");
        $row = $result->fetch_assoc();
        $created_at = $row['created_at'];

        // Check if SLA is already breached upon submission (unlikely but for completeness)
        if (isSlaBreached($created_at, $priority)) {
            $conn->query("UPDATE tickets SET status = 'overdue' WHERE ticket_id = $ticket_id");
        }

        echo "<script>alert('Ticket submitted successfully!'); window.location='admin-dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
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
  <style>
  body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #f0f4ff, #ffffff);
    margin-left: 150px;
  }

  .container {
    max-width: 850px;
    margin: 50px auto;
    padding: 40px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.6s ease-in-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  h2 {
    text-align: center;
    color: #2e3a5f;
    margin-bottom: 30px;
  }

  form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px 30px;
  }

  input,
  select,
  textarea {
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    width: 100%;
    box-sizing: border-box;
    transition: border 0.3s, box-shadow 0.3s;
  }

  input:focus,
  select:focus,
  textarea:focus {
    border-color: #4c64d3;
    box-shadow: 0 0 0 3px rgba(76, 100, 211, 0.15);
    outline: none;
  }

  textarea {
    resize: vertical;
  }

  form textarea,
  form button {
    grid-column: span 2;
  }

  button {
    padding: 14px;
    background: #4c64d3;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.3s;
  }

  button:hover {
    background: #3c4db0;
  }

  @media (max-width: 768px) {
    form {
      grid-template-columns: 1fr;
    }

    form textarea,
    form button {
      grid-column: span 1;
    }
  }
</style>

</head>
<body>

<div class="container">
  <h2>Submit Support Ticket</h2>
  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="text" name="department" placeholder="Department" required>

    <select name="priority" required>
      <option disabled selected>Priority</option>
      <option value="low">Low</option>
      <option value="medium">Medium</option>
      <option value="high">High</option>
      <option value="critical">Critical</option>
    </select>


    <input type="text" name="subject" placeholder="Subject of the Issue" required>
    <input type="text" name="device" placeholder="Device or System Affected" required>
    <input type="text" name="frequency" placeholder="How often does this happen?" required>
    <input type="text" name="urgency_reason" placeholder="Why is this urgent?" required>

    <textarea name="description" rows="4" placeholder="Describe the issue in detail..." required></textarea>

    <input type="file" name="attachment" accept=".jpg,.png,.pdf,.docx,.txt">

    <button type="submit">Submit Ticket</button>
  </form>
</div>

</body>
</html>
