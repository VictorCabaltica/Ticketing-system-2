<?php
include 'db_connection.php';

session_start();

$agent_id = $_SESSION['agent_id'] ?? null;  

if (!$agent_id) {
    echo "<script>alert('Unauthorized access. Please log in.'); window.location.href='login.php';</script>";
    exit;
}

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE agents SET name = ?, email = ? WHERE agent_id = ?");
    $stmt->bind_param("ssi", $new_name, $new_email, $agent_id);
    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='agent-profile.php';</script>";
    } else {
        echo "<script>alert('Failed to update profile');</script>";
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM agents WHERE agent_id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agent Profile</title>
  <style>
    :root {
      --primary: #415380;
      --accent: #AEE5D1;
      --gray: #F0F0F0;
    }

    body {
      background-color: var(--gray);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .profile-card {
      background-color: #fff;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
      width: 350px;
      text-align: center;
      animation: slideUp 0.8s ease;
      transition: transform 0.3s;
      position: relative;
    }

    .profile-card:hover {
      transform: scale(1.02);
    }

    .avatar {
      width: 100px;
      height: 100px;
      background: var(--primary);
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      color: white;
      font-weight: bold;
    }

    h2 {
      margin: 10px 0;
      color: var(--primary);
    }

    p {
      margin: 5px 0;
      color: #555;
    }

    .email {
      color: #888;
    }

    button {
      margin-top: 15px;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      background-color: var(--primary);
      color: white;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #2f3f65;
    }

    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.4);
      display: none;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: white;
      padding: 20px;
      border-radius: 12px;
      width: 300px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .modal-content input {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
    }

    .modal-content form {
      display: flex;
      flex-direction: column;
    }

    .modal-content button[type="submit"] {
      margin-top: 15px;
      background-color: var(--accent);
      color: #333;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(40px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <div class="profile-card">
    <div class="avatar"><?= strtoupper(substr($agent['name'], 0, 1)) ?></div>
    <h2><?= htmlspecialchars($agent['name']) ?></h2>
    <p class="email"><?= htmlspecialchars($agent['email']) ?></p>
    <p>Agent ID: <?= htmlspecialchars($agent['agent_id']) ?></p>

    <button onclick="document.getElementById('modal').style.display='flex'">Update Profile</button>
    <button onclick="window.location.href='agent-dashboard.php'">Back to Dashboard</button>
  </div>

  <div id="modal" class="modal" onclick="event.target === this && (this.style.display='none')">
    <div class="modal-content">
      <form method="POST">
        <h3>Update Profile</h3>
        <input type="text" name="name" value="<?= htmlspecialchars($agent['name']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($agent['email']) ?>" required>
        <button type="submit" name="update">Save Changes</button>
      </form>
    </div>
  </div>

</body>
</html>
