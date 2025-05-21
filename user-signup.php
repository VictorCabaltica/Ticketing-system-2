<?php
include 'db_connection.php';

// Initialize variables for sticky form and error messages
$name = $email = '';
$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $errors['name'] = 'Please enter your full name';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Please enter your email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Please enter a password';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match';
    }

    // Only proceed if no validation errors
    if (empty($errors)) {
        // Check if email already exists
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $errors['email'] = 'Email is already in use';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                // Success - redirect or show success message
                header("Location: user-login.php");
                exit;
            } else {
                $errors['general'] = "Error: " . $conn->error;
            }
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Sign Up</title>
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
      background-color: var(--light);
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

    .container {
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

    .container:hover {
      transform: translateY(-5px);
    }

    .container::before {
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

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--primary);
      font-weight: 500;
      font-size: 0.9rem;
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

    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      color: var(--dark-gray);
      font-size: 0.9rem;
    }

    .login-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
    }

    .login-link a::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 0;
      height: 1px;
      background: var(--primary);
      transition: width 0.3s ease;
    }

    .login-link a:hover::after {
      width: 100%;
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

    /* Success message styles */
    .success-message {
      text-align: center;
      color: var(--success);
      margin-bottom: 1rem;
      font-weight: 500;
      opacity: 0;
      transform: translateY(-10px);
      transition: all 0.3s ease;
    }

    .success-message.show {
      opacity: 1;
      transform: translateY(0);
    }

    /* Password strength indicator */
    .password-strength {
      height: 4px;
      background: var(--gray);
      border-radius: 2px;
      margin-top: 5px;
      overflow: hidden;
      position: relative;
    }

    .strength-meter {
      height: 100%;
      width: 0;
      background: var(--error);
      transition: width 0.3s ease, background 0.3s ease;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .container {
        padding: 1.5rem;
      }
      
      h2 {
        font-size: 1.4rem;
      }
    }
  </style>
</head>
<body>
  <div class="container <?php echo !empty($errors) ? 'shake' : '' ?>">
    <h2>Create Your Account</h2>
    
    <?php if (!empty($errors['general'])): ?>
      <div class="error-message" style="display: block; height: auto; margin-bottom: 1rem; text-align: center;">
        <?php echo $errors['general']; ?>
      </div>
    <?php endif; ?>

    <form action="user-signup.php" method="POST" id="signupForm">
      <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : '' ?>">
        <label for="name">Full Name</label>
        <div class="input-wrapper">
          <input 
            type="text" 
            name="name" 
            id="name" 
            placeholder="John Doe" 
            value="<?php echo htmlspecialchars($name); ?>" 
            required 
          />
          <i class="fas fa-user input-icon"></i>
        </div>
        <?php if (isset($errors['name'])): ?>
          <div class="error-message"><?php echo $errors['name']; ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : '' ?>">
        <label for="email">Email Address</label>
        <div class="input-wrapper">
          <input 
            type="email" 
            name="email" 
            id="email" 
            placeholder="john@example.com" 
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
        <label for="password">Password</label>
        <div class="input-wrapper">
          <input 
            type="password" 
            name="password" 
            id="password" 
            placeholder="At least 6 characters" 
            minlength="6" 
            required 
          />
          <i class="fas fa-lock input-icon"></i>
        </div>
        <div class="password-strength">
          <div class="strength-meter" id="strengthMeter"></div>
        </div>
        <?php if (isset($errors['password'])): ?>
          <div class="error-message"><?php echo $errors['password']; ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group <?php echo isset($errors['confirm']) ? 'has-error' : '' ?>">
        <label for="confirm">Confirm Password</label>
        <div class="input-wrapper">
          <input 
            type="password" 
            name="confirm" 
            id="confirm" 
            placeholder="Re-enter your password" 
            minlength="6" 
            required 
          />
          <i class="fas fa-lock input-icon"></i>
        </div>
        <?php if (isset($errors['confirm'])): ?>
          <div class="error-message"><?php echo $errors['confirm']; ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" id="submitBtn">
        <span id="buttonText">Sign Up</span>
      </button>
    </form>

    <div class="login-link">
      Already have an account? <a href="user-login.php">Log in</a>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('signupForm');
      const passwordInput = document.getElementById('password');
      const confirmInput = document.getElementById('confirm');
      const strengthMeter = document.getElementById('strengthMeter');
      const submitBtn = document.getElementById('submitBtn');
      const buttonText = document.getElementById('buttonText');

      // Password strength indicator
      passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Length check
        if (password.length >= 6) strength += 1;
        if (password.length >= 8) strength += 1;
        
        // Character variety checks
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Update strength meter
        const width = (strength / 5) * 100;
        strengthMeter.style.width = width + '%';
        
        // Update color based on strength
        if (strength <= 2) {
          strengthMeter.style.backgroundColor = '#e74c3c'; // Weak (red)
        } else if (strength <= 4) {
          strengthMeter.style.backgroundColor = '#f39c12'; // Medium (orange)
        } else {
          strengthMeter.style.backgroundColor = '#2ecc71'; // Strong (green)
        }
      });

      // Confirm password validation
      confirmInput.addEventListener('input', function() {
        if (passwordInput.value !== this.value) {
          this.setCustomValidity("Passwords don't match");
        } else {
          this.setCustomValidity('');
        }
      });

      // Form submission animation
      form.addEventListener('submit', function(e) {
        // Only animate if form is valid
        if (form.checkValidity()) {
          e.preventDefault();
          
          // Button loading state
          submitBtn.disabled = true;
          buttonText.textContent = 'Creating Account...';
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