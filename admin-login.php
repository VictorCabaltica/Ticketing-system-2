<?php
include 'db_connection.php';
session_start();

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email)) $errors['email'] = 'Please enter your email';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email';
    if (empty($password)) $errors['password'] = 'Please enter your password';

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_name'] = $admin['name'];
                header("Location: admin-dashboard.php");
                exit;
            } else {
                $errors['password'] = "Incorrect password";
            }
        } else {
            $errors['email'] = "Admin account not found";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Ticketing System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #415380;
      --primary-light: #5d6e9c;
      --accent: #AEE5D1;
      --accent-dark: #8dcbb5;
      --light: #F2F2F2;
      --gray: #E4E4E4;
      --dark-gray: #888;
      --error: #e74c3c;
      --success: #2ecc71;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .login-container {
      background: white;
      padding: 2.5rem;
      border-radius: 16px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      animation: fadeInUp 0.6s ease-out;
      max-width: 450px;
      width: 90%;
      position: relative;
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .login-container:hover {
      transform: translateY(-5px);
    }

    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      animation: borderGrow 0.8s ease-out;
    }

    @keyframes borderGrow {
      from { width: 0; }
      to { width: 100%; }
    }

    h2 {
      text-align: center;
      color: var(--primary);
      margin-bottom: 1.5rem;
      font-weight: 600;
      position: relative;
      padding-bottom: 10px;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 3px;
      background: var(--accent);
      border-radius: 3px;
    }

    .form-group {
      margin-bottom: 1.2rem;
      position: relative;
    }

    .input-wrapper {
      position: relative;
    }

    input {
      width: 100%;
      padding: 12px 15px 12px 40px;
      margin: 5px 0;
      border: 2px solid var(--gray);
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background-color: rgba(242, 242, 242, 0.3);
    }

    input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(65, 83, 128, 0.2);
      outline: none;
    }

    input::placeholder {
      color: var(--dark-gray);
      opacity: 0.7;
    }

    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--primary);
      transition: all 0.3s ease;
    }

    input:focus + .input-icon {
      color: var(--accent-dark);
      transform: translateY(-50%) scale(1.1);
    }

    .error-message {
      color: var(--error);
      font-size: 0.8rem;
      margin-top: 5px;
      height: 0;
      overflow: hidden;
      transition: height 0.3s ease;
    }

    .has-error input {
      border-color: var(--error);
    }

    .has-error .input-icon {
      color: var(--error);
    }

    .has-error .error-message {
      height: auto;
      margin-top: 8px;
    }

    button {
      width: 100%;
      padding: 14px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 1rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      margin-top: 10px;
      position: relative;
      overflow: hidden;
    }

    button:hover {
      background: var(--primary-light);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(65, 83, 128, 0.3);
    }

    button:active {
      transform: translateY(0);
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

    .note {
      font-size: 0.9rem;
      color: var(--dark-gray);
      text-align: center;
      margin-top: 1.5rem;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }

    .shake {
      animation: shake 0.5s ease-in-out;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .login-container {
        padding: 1.5rem;
      }
      
      h2 {
        font-size: 1.4rem;
      }
    }
  </style>
</head>
<body>
  <form class="login-container <?php echo !empty($errors) ? 'shake' : '' ?>" method="POST">
    <h2>Admin Login</h2>
    
    <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : '' ?>">
      <div class="input-wrapper">
        <input 
          type="email" 
          name="email" 
          placeholder="Admin Email" 
          value="<?php echo htmlspecialchars($email); ?>" 
          required
        />
        <i class="fas fa-envelope input-icon"></i>
      </div>
      <?php if (isset($errors['email'])): ?>
        <div class="error-message"><?php echo $errors['email']; ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : '' ?>">
      <div class="input-wrapper">
        <input 
          type="password" 
          name="password" 
          placeholder="Password (min 6 chars)" 
          minlength="6" 
          required
        />
        <i class="fas fa-lock input-icon"></i>
      </div>
      <?php if (isset($errors['password'])): ?>
        <div class="error-message"><?php echo $errors['password']; ?></div>
      <?php endif; ?>
    </div>

    <button type="submit" id="submitBtn">
      <span id="buttonText">Login</span>
    </button>
    
    <div class="note">Only authorized admins can log in</div>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('.login-container');
      const submitBtn = document.getElementById('submitBtn');
      const buttonText = document.getElementById('buttonText');

      // Form submission animation
      form.addEventListener('submit', function(e) {
        // Only animate if form is valid
        if (form.checkValidity()) {
          e.preventDefault();
          
          // Button loading state
          submitBtn.disabled = true;
          buttonText.textContent = 'Logging in...';
          submitBtn.style.cursor = 'not-allowed';
          
          // Simulate processing delay (replace with actual form submission)
          setTimeout(() => {
            form.submit();
          }, 1500);
        }
      });

      // Add focus effects to form groups
      const formGroups = document.querySelectorAll('.form-group');
      formGroups.forEach(group => {
        const input = group.querySelector('input');
        
        input.addEventListener('focus', function() {
          group.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
          group.classList.remove('focused');
        });
      });
    });
  </script>
</body>
</html>