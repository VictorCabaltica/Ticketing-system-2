<?php include 'admin-sidebar.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Ticketing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4cc9f0;
            --secondary: #3f37c9;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7ff, #e6e9ff);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }
        
        .welcome-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(67, 97, 238, 0.15);
            overflow: hidden;
            position: relative;
        }
        
        .welcome-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(67,97,238,0.1) 0%, transparent 70%);
            animation: rotate 15s linear infinite;
            z-index: -1;
            color: #4361ee;
        }
        
        .welcome-icon {
            transition: all 0.5s ease;
            cursor: pointer;
        }
        
        .welcome-icon:hover {
            transform: scale(1.1) rotate(10deg);
            filter: drop-shadow(0 5px 15px rgba(67, 97, 238, 0.3));
        }
        
        .action-btn {
            transition: all 0.3s ease;
            border-radius: 50px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }
        
        .action-btn:hover::after {
            left: 100%;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.2);
        }
        
        .typing-text {
            border-right: 2px solid;
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
            color: #4cc9f0;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }
        
        @keyframes blink {
            from, to { border-color: transparent }
            50% { border-color: var(--primary) }
        }
        
        .floating {
            animation: float 4s ease-in-out infinite;
        }
        
        .typing-animation {
            animation: 
                typing 3.5s steps(40, end),
                blink 0.75s step-end infinite;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .ripple {
            position: relative;
            overflow: hidden;
        }
        
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="welcome-container p-5 text-center animate__animated animate__fadeIn">
                    <!-- Animated Icon -->
                    <div class="mb-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/3142/3142027.png" 
                             alt="Welcome" 
                             class="welcome-icon floating" 
                             style="width: 120px; height: 120px;"
                             onclick="this.classList.toggle('pulse')">
                    </div>
                    
                    <!-- Typing Text Animation -->
                    <h1 class="display-4 fw-bold mb-3 text-primary">
                        <span class="typing-text typing-animation">Welcome to Ticketing System</span>
                    </h1>
                    
                    <!-- Interactive Welcome Message -->
                    <p class="lead mb-4" id="welcome-message">
                        Hello Admin, ready to manage today's tickets?
                    </p>
                    
                    <!-- Interactive Buttons with Ripple Effect -->
                    <div class="d-flex justify-content-center gap-3">
                        <button class="action-btn btn btn-primary btn-lg px-4 ripple" 
                                id="main-action"
                                onclick="createRipple(event, this)">
                            <i class="fas fa-rocket me-2"></i> Get Started
                        </button>
                        <button class="action-btn btn btn-outline-primary btn-lg px-4 ripple"
                                onclick="createRipple(event, this); changeWelcomeMessage()">
                            <i class="fas fa-sync-alt me-2"></i> Refresh
                        </button>
                    </div>
                    
                    <!-- Date Display -->
                    <div class="mt-4 text-muted">
                        <i class="fas fa-calendar-day me-2"></i>
                        <span id="current-date"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set current date
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Ripple effect for buttons
        function createRipple(event, button) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }
        
        // Change welcome message interactively
        const messages = [
            "Hello Admin, ready to manage today's tickets?",
            "Welcome back! The system missed you.",
            "Ready to tackle today's challenges?",
            "Your expertise makes the difference!",
            "Let's make today productive!",
            "The ticketing system is at your command."
        ];
        
        function changeWelcomeMessage() {
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            const welcomeElement = document.getElementById('welcome-message');
            
            // Fade out animation
            welcomeElement.style.transition = 'opacity 0.3s ease';
            welcomeElement.style.opacity = '0';
            welcomeElement.style.color = 'blue';
            
            setTimeout(() => {
                welcomeElement.textContent = randomMessage;
                welcomeElement.style.opacity = '1';
                
                // Add temporary animation class
                welcomeElement.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => {
                    welcomeElement.classList.remove('animate__animated', 'animate__pulse');
                }, 1000);
            }, 300);
        }
        
        // Make the main action button interactive
        document.getElementById('main-action').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Loading Dashboard...';
            setTimeout(() => {
                // In a real app, this would redirect to the dashboard
                this.innerHTML = '<i class="fas fa-check me-2"></i> Dashboard Ready!';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
            }, 1500);
        });
        
        // Add hover effect to the welcome container
        const welcomeContainer = document.querySelector('.welcome-container');
        welcomeContainer.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 20px 40px rgba(67, 97, 238, 0.2)';
        });
        
        welcomeContainer.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '0 15px 35px rgba(67, 97, 238, 0.15)';
        });
    </script>
</body>
</html>