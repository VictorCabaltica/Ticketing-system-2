<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <!-- Ionicons for menu icons -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <style>
    :root {
      /* Color Palette Options - Uncomment one set to use */

      --primary: #2c3e50;
      --secondary: #34495e;
      --accent: #3498db;
      --text: #ecf0f1;
      --highlight: #1abc9c;
      
      
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background-color: #f5f7fa;
      overflow-x: hidden;
    }
    
    /* Sidebar with glass morphism effect */
    .sidebar {
      position: fixed;
      top: 0; left: 0;
      width: 260px; height: 100%;
      background: rgba(65, 83, 128, 0.85);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      overflow-y: auto;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
      z-index: 100;
      box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .sidebar:hover {
      width: 280px;
    }
    
    .sidebar-header {
      padding: 20px;
      display: flex;
      align-items: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      animation: fadeInDown 0.8s;
    }
    
    .sidebar-header ion-icon {
      font-size: 28px;
      color: var(--highlight);
      margin-right: 10px;
    }
    
    .sidebar-header h3 {
      color: var(--text);
      font-weight: 600;
      font-size: 18px;
    }
    
    .menu {
      list-style: none;
      margin: 0; padding: 15px 0;
    }
    
    .menu-item {
      position: relative;
      display: flex;
      align-items: center;
      padding: 14px 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      overflow: hidden;
    }
    
    .menu-item::before {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 100%; height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      transition: 0.5s;
    }
    
    .menu-item:hover::before {
      left: 100%;
    }
    
    .menu-item:hover {
      background: rgba(255, 255, 255, 0.05);
      transform: translateX(8px);
    }
    
    .menu-item.active {
      background: rgba(255, 255, 255, 0.1);
      border-left: 3px solid var(--highlight);
    }
    
    .menu-item ion-icon {
      font-size: 20px;
      color: var(--text);
      margin-right: 15px;
      transition: all 0.3s;
    }
    
    .menu-item:hover ion-icon {
      color: var(--highlight);
      transform: scale(1.1);
    }
    
    .menu-item span {
      color: var(--text);
      font-size: 15px;
      flex: 1;
      transition: all 0.3s;
    }
    
    .menu-item:hover span {
      color: var(--highlight);
    }
    
    /* Submenu with accordion effect */
    .has-submenu .submenu {
      list-style: none;
      margin: 0; padding: 0 0 0 55px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .has-submenu.active .submenu {
      max-height: 300px;
    }
    
    .submenu-item {
      padding: 10px 0;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.7);
      cursor: pointer;
      transition: all 0.3s;
      position: relative;
    }
    
    .submenu-item::before {
      content: '';
      position: absolute;
      left: -15px; top: 50%;
      transform: translateY(-50%);
      width: 6px; height: 6px;
      background: rgba(255, 255, 255, 0.5);
      border-radius: 50%;
      transition: all 0.3s;
    }
    
    .submenu-item:hover {
      color: var(--highlight);
      transform: translateX(5px);
    }
    
    .submenu-item:hover::before {
      background: var(--highlight);
      transform: translateY(-50%) scale(1.5);
    }
    
    .has-submenu .chevron {
      margin-left: auto;
      transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      color: rgba(255, 255, 255, 0.7);
    }
    
    .has-submenu.active .chevron {
      transform: rotate(180deg);
      color: var(--highlight);
    }
    
    /* Main content area */
    .main-content {
      margin-left: 260px;
      padding: 30px;
      min-height: 100vh;
      transition: margin-left 0.4s;
    }
    
    .sidebar:hover ~ .main-content {
      margin-left: 280px;
    }
    
    .welcome-container {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.8s;
    }
    
    .welcome-container::before {
      content: '';
      position: absolute;
      top: -50%; right: -50%;
      width: 100%; height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }
    
    .welcome-container h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      position: relative;
    }
    
    .welcome-container p {
      font-size: 1.1rem;
      opacity: 0.9;
      position: relative;
    }
    
    /* Quick stats cards */
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card::after {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 4px; height: 100%;
      background: var(--highlight);
    }
    
    .stat-card h3 {
      color: #555;
      font-size: 1rem;
      margin-bottom: 10px;
    }
    
    .stat-card .value {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 5px;
    }
    
    .stat-card .change {
      font-size: 0.9rem;
      color: #4CAF50;
      display: flex;
      align-items: center;
    }
    
    .stat-card .change.negative {
      color: #F44336;
    }
    
    /* Recent activity */
    .activity-container {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      animation: fadeIn 1s;
    }
    
    .activity-container h2 {
      color: var(--primary);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    
    .activity-item {
      display: flex;
      padding: 15px 0;
      border-bottom: 1px solid #f5f5f5;
      transition: all 0.3s;
    }
    
    .activity-item:last-child {
      border-bottom: none;
    }
    
    .activity-item:hover {
      background: #f9f9f9;
      transform: translateX(5px);
    }
    
    .activity-icon {
      width: 40px;
      height: 40px;
      background: var(--accent);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      flex-shrink: 0;
    }
    
    .activity-icon ion-icon {
      color: white;
      font-size: 20px;
    }
    
    .activity-content {
      flex: 1;
    }
    
    .activity-content h4 {
      color: #333;
      margin-bottom: 5px;
    }
    
    .activity-content p {
      color: #777;
      font-size: 0.9rem;
    }
    
    .activity-time {
      color: #999;
      font-size: 0.8rem;
      margin-top: 5px;
    }
    
    /* Floating action button */
    .fab {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 60px;
      height: 60px;
      background: var(--highlight);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      transition: all 0.3s;
      z-index: 50;
      animation: pulse 2s infinite;
    }
    
    .fab:hover {
      transform: scale(1.1) rotate(90deg);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }
    
    .fab ion-icon {
      color: white;
      font-size: 28px;
    }
    i {
      color: white;
      font-size: 28px;
    }

    
    /* Animations */
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(var(--highlight-rgb), 0.7); }
      70% { box-shadow: 0 0 0 15px rgba(var(--highlight-rgb), 0); }
      100% { box-shadow: 0 0 0 0 rgba(var(--highlight-rgb), 0); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
      .sidebar {
        width: 80px;
        overflow: hidden;
      }
      
      .sidebar:hover {
        width: 280px;
      }
      
      .sidebar-header h3,
      .menu-item span,
      .submenu {
        display: none;
      }
      
      .sidebar:hover .sidebar-header h3,
      .sidebar:hover .menu-item span,
      .sidebar:hover .submenu {
        display: block;
      }
      
      .menu-item {
        justify-content: center;
      }
      
      .sidebar:hover .menu-item {
        justify-content: flex-start;
      }
      
      .main-content {
        margin-left: 80px;
      }
      
      .sidebar:hover ~ .main-content {
        margin-left: 280px;
      }
    }
    
    @media (max-width: 768px) {
      .sidebar {
        width: 0;
      }
      
      .sidebar.active {
        width: 280px;
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .menu-toggle {
        display: block;
      }
    }
  </style>
</head>
<body>

  <div class="sidebar animate__animated animate__fadeInLeft">
    <div class="sidebar-header">
      <ion-icon name="shield-half-outline"></ion-icon>
      <h3>Admin Portal</h3>
    </div>
    <ul class="menu">
      <li class="menu-item active" onclick="location.href='home.php'">
        <ion-icon name="home-outline"></ion-icon>
        <span>Home</span>
      </li>
      <li class="menu-item active" onclick="location.href='admin-dashboard.php'">
      <ion-icon name="clipboard-outline"></ion-icon>
        <span>Dashboard</span>
      </li>
      <li class="menu-item" onclick="location.href='admin-manageticket.php'">
        <ion-icon name="ticket-outline"></ion-icon>
        <span>Manage Tickets</span>
      </li>
      <li class="menu-item" onclick="location.href='admin-manageusers.php'">
        <ion-icon name="people-outline"></ion-icon>
        <span>Manage Users</span>
      </li>
      <li class="menu-item" onclick="location.href='admin-manageagent.php'">
      <ion-icon name="person-outline"></ion-icon>
        <span>Manage Agent</span>
      </li>
      <li class="menu-item" onclick="location.href='admin-manageadmin.php'">
        <ion-icon name="person-outline"></ion-icon>
        <span>Manage Admins</span>
      </li>
      <li class="menu-item" onclick="location.href='admin-assignticket.php'">
      <ion-icon name="hourglass-outline"></ion-icon>
        <span>Assign Tickets</span>
      </li>
      <li class="menu-item has-submenu" onclick="location.href='admin-slaperformance.php'">
        <div class="menu-item-header">
          <ion-icon name="bar-chart-outline"></ion-icon>
          <span>SLA Performace</span>
          
      <li class="menu-item" onclick="location.href='index.php'">
        <ion-icon name="log-out-outline"></ion-icon>
        <span>Logout</span>
      </li>
    </ul>
  </div>

  <script>
    // Toggle submenus
    document.querySelectorAll('.has-submenu .menu-item-header').forEach(header => {
      header.addEventListener('click', (e) => {
        e.stopPropagation();
        const parent = header.parentElement;
        parent.classList.toggle('active');
        
        // Close other open submenus
        document.querySelectorAll('.has-submenu').forEach(item => {
          if (item !== parent && item.classList.contains('active')) {
            item.classList.remove('active');
          }
        });
      });
    });
    
    // Set active menu item
    document.querySelectorAll('.menu-item').forEach(item => {
      item.addEventListener('click', function() {
        if (!this.classList.contains('has-submenu')) {
          document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
          this.classList.add('active');
        }
      });
    });
    
    // Add ripple effect to menu items
    document.querySelectorAll('.menu-item').forEach(item => {
      item.addEventListener('click', function(e) {
        if (!this.classList.contains('has-submenu')) {
          const rect = this.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          
          const ripple = document.createElement('span');
          ripple.style.left = `${x}px`;
          ripple.style.top = `${y}px`;
          ripple.classList.add('ripple');
          
          this.appendChild(ripple);
          
          setTimeout(() => {
            ripple.remove();
          }, 1000);
        }
      });
    });
    
    // Animate stats cards on scroll
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate__fadeInUp');
        }
      });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.stat-card').forEach(card => {
      observer.observe(card);
    });
  </script>
</body>
</html>