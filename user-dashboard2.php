<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard</title>
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
      --link-hover-bg: rgba(65, 83, 128, 0.2);
      --sidebar-bg: rgba(65, 83, 128, 0.95);
      --text-light: #ffffff;
      --text-dark: #333333;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      min-height: 100vh;
      overflow-x: hidden;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Hamburger Icon */
    .hamburger {
      position: fixed;
      top: 20px;
      left: 20px;
      cursor: pointer;
      z-index: 1001;
      width: 30px;
      height: 24px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: transform 0.3s ease;
    }

    .hamburger:hover {
      transform: scale(1.1);
    }

    .hamburger span {
      background: var(--primary);
      height: 3px;
      width: 100%;
      border-radius: 3px;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      transform-origin: left center;
    }

    .hamburger.active span:nth-child(1) {
      transform: rotate(45deg) translate(2px, -1px);
      background: var(--accent);
    }

    .hamburger.active span:nth-child(2) {
      opacity: 0;
      width: 0;
    }

    .hamburger.active span:nth-child(3) {
      transform: rotate(-45deg) translate(2px, 1px);
      background: var(--accent);
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      left: -300px;
      width: 270px;
      height: 100vh;
      background: var(--sidebar-bg);
      color: var(--text-light);
      padding: 80px 20px 20px;
      box-shadow: 5px 0 15px rgba(0,0,0,0.2);
      transition: all 0.5s cubic-bezier(0.77, 0.2, 0.05, 1.0);
      z-index: 1000;
      backdrop-filter: blur(5px);
    }

    .sidebar.active {
      left: 0;
    }

    .sidebar h2 {
      margin-top: 0;
      margin-bottom: 30px;
      font-size: 24px;
      font-weight: bold;
      color: var(--accent);
      text-align: center;
      position: relative;
      padding-bottom: 10px;
    }

    .sidebar h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 2px;
      background: var(--accent);
    }

    /* Sidebar Links */
    .sidebar a {
      display: flex;
      align-items: center;
      color: var(--text-light);
      text-decoration: none;
      padding: 15px;
      margin-bottom: 10px;
      border-radius: 6px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .sidebar a i {
      margin-right: 12px;
      font-size: 18px;
      transition: all 0.3s ease;
    }

    .sidebar a:hover {
      color: var(--accent);
      background-color: var(--link-hover-bg);
      transform: translateX(5px);
    }

    .sidebar a:hover i {
      color: var(--accent);
      transform: scale(1.2);
    }

    .sidebar a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(174, 229, 209, 0.2), transparent);
      transition: all 0.5s ease;
    }

    .sidebar a:hover::before {
      left: 100%;
    }

    /* Main Content */
    .main {
      margin-left: 0;
      padding: 80px 20px;
      transition: all 0.5s cubic-bezier(0.77, 0.2, 0.05, 1.0);
    }

    .sidebar.active ~ .main {
      margin-left: 250px;
      filter: blur(2px);
    }

    .welcome-box {
      background: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
      animation: fadeInUp 0.8s ease-out;
      transform-style: preserve-3d;
      transition: transform 0.3s ease;
    }

    .welcome-box:hover {
      transform: translateY(-5px) rotateX(1deg) rotateY(1deg);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .welcome-box h1 {
      color: var(--primary);
      margin-bottom: 20px;
      font-size: 2.5rem;
      position: relative;
      display: inline-block;
    }

    .welcome-box h1::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background: var(--accent);
      border-radius: 3px;
    }

    .welcome-box p {
      color: var(--text-dark);
      font-size: 1.1rem;
      line-height: 1.6;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Dashboard Cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 40px;
    }

    .card {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      animation: fadeIn 0.6s ease-out;
      animation-fill-mode: both;
    }

    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .card:nth-child(1) { animation-delay: 0.2s; }
    .card:nth-child(2) { animation-delay: 0.4s; }
    .card:nth-child(3) { animation-delay: 0.6s; }

    .card h3 {
      color: var(--primary);
      margin-top: 0;
      display: flex;
      align-items: center;
    }

    .card h3 i {
      margin-right: 10px;
      color: var(--accent-dark);
    }

    .card p {
      color: var(--dark-gray);
      line-height: 1.6;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .sidebar {
        width: 250px;
      }
      
      .sidebar.active ~ .main {
        margin-left: 0;
        transform: translateX(250px);
      }
      
      .dashboard-cards {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      .sidebar {
        width: 220px;
      }
      
      .welcome-box {
        padding: 20px;
      }
      
      .welcome-box h1 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>

  <!-- Hamburger Icon -->
  <div class="hamburger" onclick="toggleMenu()">
    <span></span>
    <span></span>
    <span></span>
  </div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <h2>TicketEase</h2>
    <a href="user-dashboard3.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="user-viewticket.php"><i class="fas fa-ticket-alt"></i> View My Tickets</a>
    <a href="user-slafor.php"><i class="fas fa-plus-circle"></i> Submit Ticket</a>
    <a href="user-profile.php"><i class="fas fa-user-circle"></i> Profile</a>
    <a href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>

  <!-- Main Content -->

  <script>
    function toggleMenu() {
      const hamburger = document.querySelector('.hamburger');
      const sidebar = document.getElementById('sidebar');
      
      hamburger.classList.toggle('active');
      sidebar.classList.toggle('active');
      
      // Add ripple effect to hamburger
      if (hamburger.classList.contains('active')) {
        createRipple(hamburger);
      }
    }

    function createRipple(element) {
      const ripple = document.createElement('span');
      ripple.style.position = 'absolute';
      ripple.style.borderRadius = '50%';
      ripple.style.backgroundColor = 'rgba(174, 229, 209, 0.3)';
      ripple.style.transform = 'scale(0)';
      ripple.style.animation = 'ripple 0.6s linear';
      ripple.style.pointerEvents = 'none';
      
      // Set size and position
      const size = Math.max(element.offsetWidth, element.offsetHeight);
      ripple.style.width = ripple.style.height = `${size}px`;
      ripple.style.left = '0';
      ripple.style.top = '0';
      
      element.appendChild(ripple);
      
      // Remove ripple after animation
      setTimeout(() => {
        ripple.remove();
      }, 600);
    }

    // Add hover effects to cards
    document.querySelectorAll('.card').forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const x = e.pageX - card.getBoundingClientRect().left;
        const y = e.pageY - card.getBoundingClientRect().top;
        
        const centerX = card.offsetWidth / 2;
        const centerY = card.offsetHeight / 2;
        
        const angleX = (y - centerY) / 10;
        const angleY = (centerX - x) / 10;
        
        card.style.transform = `perspective(1000px) rotateX(${angleX}deg) rotateY(${angleY}deg)`;
      });
      
      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
      });
    });
  </script>
</body>
</html>