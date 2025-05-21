<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Agent Navbar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f6fa;
    }

    .navbar {
      background-color: #415380;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      position: relative;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar .logo {
      font-size: 20px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    .navbar ul {
      list-style: none;
      display: flex;
      gap: 30px;
      transition: all 0.3s ease;
    }

    .navbar ul li {
      position: relative;
    }

    .navbar ul li a {
      color: white;
      text-decoration: none;
      font-size: 16px;
      padding: 8px 0;
      transition: color 0.3s;
    }

    .navbar ul li a:hover,
    .navbar ul li a.active {
      color: #ffd369;
    }

    /* Animated underline effect */
    .navbar ul li a::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -2px;
      height: 2px;
      width: 0%;
      background-color: #ffd369;
      transition: width 0.3s ease;
    }

    .navbar ul li a:hover::after,
    .navbar ul li a.active::after {
      width: 100%;
    }

    /* Hamburger for mobile */
    .menu-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
    }

    .menu-toggle span {
      height: 3px;
      width: 25px;
      background: white;
      margin: 4px 0;
      border-radius: 5px;
      transition: 0.3s;
    }

    @media (max-width: 768px) {
      .navbar ul {
        flex-direction: column;
        background-color: #415380;
        position: absolute;
        top: 60px;
        right: 0;
        width: 100%;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-in-out;
      }

      .navbar ul.open {
        max-height: 300px;
      }

      .menu-toggle {
        display: flex;
      }
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <div class="logo">Agent Panel</div>
    <div class="menu-toggle" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <ul id="nav-links">
      <li><a href="agent-profile.php">Agent Profile</a></li>
      <li><a href="agent-dashboard1.php" class="active">Dashboard</a></li>
      <li><a href="agent-tickets.php">Assigned Tickets</a></li>
      <li><a href="index.php">Log out</a></li>
    </ul>
  </nav>

  <script>
    function toggleMenu() {
      const nav = document.getElementById('nav-links');
      nav.classList.toggle('open');
    }
  </script>

</body>
</html>
