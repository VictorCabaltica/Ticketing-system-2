<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Welcome to TicketEase</title>
  <style>
    :root {
      --primary: #415380;
      --secondary: #AEE5D1;
      --bg-light: #F2F2F2;
      --text-gray: #333;
      --accent: #E4E4E4;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      background-color: var(--bg-light);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      text-align: center;
      animation: fadeIn 1.2s ease-in-out;
    }

    @keyframes fadeIn {
      0% {
        opacity: 0;
        transform: translateY(30px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h1 {
      color: var(--primary);
      font-size: 2.8rem;
      margin-bottom: 0.3em;
    }

    p {
      color: var(--text-gray);
      font-size: 1.2rem;
      margin-bottom: 40px;
    }

    .button-group {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: center;
    }

    a {
      background: var(--primary);
      color: white;
      padding: 15px 32px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
    }

    a::after {
      content: "";
      position: absolute;
      width: 100%;
      height: 100%;
      background: var(--secondary);
      top: 0;
      left: -100%;
      transition: left 0.4s ease;
      z-index: 0;
    }

    a span {
      position: relative;
      z-index: 1;
    }

    a:hover::after {
      left: 0;
    }

    a:hover {
      color: #000;
      transform: scale(1.05);
    }

    @media (max-width: 500px) {
      .button-group {
        flex-direction: column;
        gap: 15px;
      }

      a {
        width: 220px;
      }
    }
  </style>
</head>
<body>
  <h1>Welcome to TicketEase</h1>
  <p>Your smart ticketing companion</p>
  <div class="button-group">
    <a href="user-login.php"><span>Login as User</span></a>
    <a href="admin-login.php"><span>Login as Admin</span></a>
    <a href="agent-login.php"><span>Login as Agent</span></a>
  </div>
</body>
</html>
