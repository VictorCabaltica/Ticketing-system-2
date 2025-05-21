<?php
session_start();
include 'db_connection.php';
include 'user-dashboard2.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
    $stmt->bind_param("si", $name, $user_id);
    $stmt->execute();
    
    // Update session name if changed
    $_SESSION['name'] = $name;
    
    $success = true;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #415380;
            --primary-light: #5d6e9c;
            --accent: #AEE5D1;
            --accent-dark: #8dcbb5;
            --light: #F2F2F2;
            --gray: #E4E4E4;
            --dark-gray: #333;
            --success: #4CAF50;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .profile-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 40px;
            max-width: 600px;
            width: 90%;
            animation: fadeInUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .profile-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .profile-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            animation: borderGrow 0.8s ease-out;
        }

        @keyframes borderGrow {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px) rotateX(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }

        .profile-container h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 30px;
            font-size: 2rem;
            position: relative;
            padding-bottom: 15px;
        }

        .profile-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent);
            border-radius: 3px;
        }

        .profile-info, .profile-edit {
            margin: 20px 0;
            padding: 20px;
            background: rgba(242, 242, 242, 0.5);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .profile-info:hover, .profile-edit:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .profile-info strong, .profile-edit label {
            display: inline-block;
            width: 120px;
            color: var(--primary);
            font-weight: 600;
            margin-right: 15px;
        }

        .profile-info i, .profile-edit i {
            width: 25px;
            color: var(--primary);
            margin-right: 10px;
            text-align: center;
        }

        .profile-edit input {
            padding: 12px 15px;
            border: 2px solid var(--gray);
            border-radius: 8px;
            width: calc(100% - 170px);
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .profile-edit input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(65, 83, 128, 0.2);
            outline: none;
        }

        .button-group {
            text-align: center;
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        button {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
            position: relative;
            overflow: hidden;
        }

        button i {
            margin-right: 8px;
        }

        button:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(65, 83, 128, 0.3);
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

        .secondary-btn {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .secondary-btn:hover {
            background: var(--light);
        }

        .hidden {
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s ease;
        }

        .visible {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .success-msg {
            background: var(--accent);
            color: #10403B;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 10px;
            text-align: center;
            animation: slideInDown 0.5s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-msg i {
            margin-right: 10px;
            font-size: 20px;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary);
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .profile-avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                transparent,
                transparent,
                transparent,
                rgba(255, 255, 255, 0.3)
            );
            transform: rotate(30deg);
            transition: all 0.3s ease;
        }

        .profile-avatar:hover::before {
            transform: rotate(30deg) translate(20%, 20%);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-container {
                padding: 30px 20px;
            }
            
            .profile-info strong, .profile-edit label {
                width: 100px;
            }
            
            .profile-edit input {
                width: calc(100% - 150px);
            }
            
            .button-group {
                flex-direction: column;
                gap: 10px;
            }
            
            button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .profile-info strong, .profile-edit label {
                width: 100%;
                margin-bottom: 8px;
            }
            
            .profile-edit input {
                width: 100%;
            }
            
            .profile-info, .profile-edit {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<div class="profile-container">
    <div class="profile-avatar">
        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
    </div>
    
    <h2><i class="fas fa-user-circle"></i> User Profile</h2>

    <?php if (isset($success)): ?>
        <div class="success-msg">
            <i class="fas fa-check-circle"></i>
            Profile updated successfully!
        </div>
    <?php endif; ?>

    <div id="profile-view" class="visible">
        <div class="profile-info">
            <strong><i class="fas fa-user"></i> Name:</strong> 
            <?php echo htmlspecialchars($user['name']); ?>
        </div>
        <div class="profile-info">
            <strong><i class="fas fa-envelope"></i> Email:</strong> 
            <?php echo htmlspecialchars($user['email']); ?>
        </div>
        <div class="profile-info">
            <strong><i class="fas fa-calendar-alt"></i> Member Since:</strong> 
            <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
        </div>
    </div>

    <form id="profile-form" method="POST" class="hidden">
        <div class="profile-edit">
            <label><i class="fas fa-user-edit"></i> Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required />
        </div>
        <div class="button-group">
            <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
            <button type="button" class="secondary-btn" onclick="toggleForm()">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </form>

    <div class="button-group" id="edit-button">
        <button onclick="toggleForm()"><i class="fas fa-edit"></i> Update Profile</button>
    </div>
</div>

<script>
    function toggleForm() {
        const profileView = document.getElementById('profile-view');
        const profileForm = document.getElementById('profile-form');
        const editButton = document.getElementById('edit-button');
        
        profileView.classList.toggle('visible');
        profileView.classList.toggle('hidden');
        profileForm.classList.toggle('visible');
        profileForm.classList.toggle('hidden');
        
        if (profileForm.classList.contains('visible')) {
            editButton.style.display = 'none';
        } else {
            editButton.style.display = 'flex';
        }
    }

    // Add ripple effect to buttons
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function(e) {
            // Only create ripple if not already animating
            if (!this.querySelector('.ripple-effect')) {
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.pointerEvents = 'none';
                
                // Set size and position
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${e.clientX - rect.left - size/2}px`;
                ripple.style.top = `${e.clientY - rect.top - size/2}px`;
                
                this.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            }
        });
    });

    // Add hover effect to profile info cards
    document.querySelectorAll('.profile-info, .profile-edit').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const angleX = (y - centerY) / 20;
            const angleY = (centerX - x) / 20;
            
            card.style.transform = `perspective(1000px) rotateX(${angleX}deg) rotateY(${angleY}deg) translateY(-3px)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
        });
    });
</script>
</body>
</html>