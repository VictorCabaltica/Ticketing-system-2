<?php
include 'db_connection.php';

// Function to update admin
function updateAdmin($conn, $admin_id, $name, $email) {
    $admin_id = intval($admin_id);
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);

    $query = "UPDATE admins SET name = '$name', email = '$email' WHERE admin_id = $admin_id";
    return mysqli_query($conn, $query);
}

// Fetch existing admin data
if (isset($_GET['agent_id'])) {
    $admin_id = intval($_GET['agent_id']);
    $result = mysqli_query($conn, "SELECT * FROM admins WHERE admin_id = $admin_id");
    $admin = mysqli_fetch_assoc($result);
    if (!$admin) {
        die("Admin not found.");
    }
} else {
    die("No admin ID provided.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (updateAdmin($conn, $admin_id, $name, $email)) {
        header("Location: admin-manageadmin.php?success=1");
        exit;
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f7fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .update-form {
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 500px;
      animation: fadeInDown 0.5s;
    }
    .update-form h2 {
      margin-top: 0;
      color: #2c3e50;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 5px;
      color: #34495e;
    }
    .form-group input {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    .btn {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 12px 20px;
      font-size: 14px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      transition: 0.3s ease;
    }
    .btn:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
    }
    .error {
      color: #e74c3c;
      margin-bottom: 15px;
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <form class="update-form animate__animated animate__fadeInDown" method="POST">
    <h2>Update Admin Info</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" name="name" id="name" required value="<?= htmlspecialchars($admin['name']); ?>">
    </div>
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" name="email" id="email" required value="<?= htmlspecialchars($admin['email']); ?>">
    </div>
    <button type="submit" class="btn">Update Admin</button>
  </form>
</body>
</html>
