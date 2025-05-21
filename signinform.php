<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ticketing System</title>
  <style>
    :root {
      --primary: #415380;
      --accent: #AEE5D1;
      --light: #F2F2F2;
      --gray: #E4E4E4;
    }

    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      margin: 0;
      background-color: var(--light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      overflow: hidden;
    }

    .container {
      background-color: white;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 420px;
      animation: slideIn 1s ease;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h2 {
      color: var(--primary);
      text-align: center;
      margin-bottom: 1rem;
    }

    label {
      font-weight: 500;
      margin-top: 10px;
      display: block;
      color: #333;
    }

    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border: 1px solid var(--gray);
      border-radius: 8px;
      background: var(--light);
    }

    button {
      margin-top: 15px;
      width: 100%;
      padding: 12px;
      background-color: var(--primary);
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }

    button:hover {
      background-color: #2f3e66;
    }

    .link {
      background: none;
      border: none;
      color: var(--primary);
      text-decoration: underline;
      cursor: pointer;
      margin-top: 1rem;
      display: block;
      text-align: center;
    }

    .hidden {
      display: none;
    }

    .homepage {
      text-align: center;
    }

    .homepage h1 {
      color: var(--primary);
    }

    .role-select {
      margin-top: 10px;
    }

  </style>
</head>
<body>

  <div class="container" id="homepage">
    <div class="homepage">
      <h1>Welcome to TicketEase</h1>
      <p>Your simple solution for issue tracking and support.</p>
      <button onclick="showForm('login')">Get Started</button>
    </div>
  </div>

  <div class="container hidden" id="loginForm">
    <h2>Login</h2>
    <label for="role">Select Role</label>
    <select id="loginRole">
      <option value="user">User</option>
      <option value="admin">Admin</option>
      <option value="agent">Agent</option>
    </select>

    <label>Email</label>
    <input type="email" placeholder="Enter your email">

    <label>Password</label>
    <input type="password" placeholder="Enter your password">

    <button>Login</button>
    <button class="link" onclick="showForm('signup')">Don't have an account? Sign Up</button>
    <button class="link" onclick="showForm('homepage')">← Back to Home</button>
  </div>

  <div class="container hidden" id="signupForm">
    <h2>Sign Up</h2>

    <label for="signupRole">Register as</label>
    <select id="signupRole" onchange="updateSignupRole()">
      <option value="user">User</option>
      <option value="admin">Admin</option>
      <option value="agent">Agent</option>
    </select>

    <div id="signupFields">
      <label>Full Name</label>
      <input type="text" placeholder="Your full name">

      <label>Email</label>
      <input type="email" placeholder="Enter email">

      <label>Password</label>
      <input type="password" placeholder="Enter password">

      <label>Confirm Password</label>
      <input type="password" placeholder="Confirm password">
    </div>

    <button>Sign Up</button>
    <button class="link" onclick="showForm('login')">Already have an account? Login</button>
    <button class="link" onclick="showForm('homepage')">← Back to Home</button>
  </div>

  <script>
    function showForm(form) {
      document.getElementById("homepage").classList.add("hidden");
      document.getElementById("loginForm").classList.add("hidden");
      document.getElementById("signupForm").classList.add("hidden");

      if (form === "homepage") document.getElementById("homepage").classList.remove("hidden");
      if (form === "login") document.getElementById("loginForm").classList.remove("hidden");
      if (form === "signup") document.getElementById("signupForm").classList.remove("hidden");
    }

    function updateSignupRole() {
      const role = document.getElementById("signupRole").value;
      const signupFields = document.getElementById("signupFields");

      // Optionally add different fields for different roles
      signupFields.innerHTML = `
        <label>Full Name</label>
        <input type="text" placeholder="Your full name">
        <label>Email</label>
        <input type="email" placeholder="Enter email">
        <label>Password</label>
        <input type="password" placeholder="Enter password">
        <label>Confirm Password</label>
        <input type="password" placeholder="Confirm password">
      `;
    }
  </script>
</body>
</html>
