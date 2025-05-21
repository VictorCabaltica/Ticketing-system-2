<?php
include 'db_connection.php';
include 'admin-sidebar.php';


// Fetch all users
$sql = "SELECT user_id, name, email, created_at FROM users ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Manage Users</title>
  <!-- Ionicons for icons -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <!-- Animate.css for animations -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #34495e;
      --accent: #3498db;
      --danger: #e74c3c;
      --success: #2ecc71;
      --text-light: #ecf0f1;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f5f7fa;
      display: flex;
      min-height: 100vh;
    }
    /* Sidebar */
    .sidebar {
      width: 260px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: var(--text-light);
      padding: 20px 0;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    .menu {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .menu-item {
      padding: 12px 20px;
      display: flex;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .menu-item:hover {
      background: rgba(255,255,255,0.1);
      transform: translateX(5px);
    }
    .menu-item ion-icon {
      margin-right: 12px;
      font-size: 20px;
    }
    /* Main Content */
    .main-content {
      flex: 1;
      padding: 30px;
      animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    h1 {
      color: var(--primary);
      margin-top: 0;
    }
    /* User Table */
    .table-container {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      overflow-x: auto;
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
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: white;
      position: sticky;
      top: 0;
    }
    tr {
      transition: all 0.3s ease;
    }
    tr:hover {
      background: #f8f9fa;
      transform: translateX(5px);
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    /* Action Buttons */
    .action-btns {
      display: flex;
      gap: 8px;
    }
    .btn {
      border: none;
      border-radius: 6px;
      padding: 8px 12px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      transition: all 0.3s ease;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .btn-update {
      background: var(--success);
      color: white;
    }
    .btn-delete {
      background: var(--danger);
      color: white;
    }
    .btn ion-icon {
      margin-right: 5px;
      font-size: 16px;
    }
    /* Animations */
    .animate-delay-1 { animation-delay: 0.1s; }
    .animate-delay-2 { animation-delay: 0.2s; }
    .animate-delay-3 { animation-delay: 0.3s; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  
  <!-- Main Content -->
  <div class="main-content">
    <h1>User Management</h1>
    
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($user = $users_result->fetch_assoc()): ?>
          <tr class="animate__animated animate__fadeIn animate__faster">
            <td><?= $user['user_id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
            <td>
              <div class="action-btns">
                <button class="btn btn-update" 
                        onclick="location.href='admin-update-user.php?user_id=<?= $user['user_id'] ?>'">
                  <ion-icon name="create-outline"></ion-icon> Edit
                </button>
                <button class="btn btn-delete" 
                        onclick="confirmDelete(<?= $user['user_id'] ?>)">
                  <ion-icon name="trash-outline"></ion-icon> Delete
                </button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Confirm delete action
    function confirmDelete(userId) {
      if (confirm('Are you sure you want to delete this user?')) {
        window.location.href = 'admin-delete-user.php?user_id=' + userId;
      }
    }

    // Add animation to table rows
    document.querySelectorAll('tbody tr').forEach((row, index) => {
      row.style.animationDelay = `${index * 0.05}s`;
    });
  </script>
</body>
</html>