<?php
include 'db_connection.php';

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
    $user = mysqli_fetch_assoc($query);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $sql = "UPDATE users SET name='$name', email='$email' WHERE user_id=$user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin-manageusers.php?msg=updated");
    } else {
        echo "Error updating: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            padding: 50px;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin-top: 200px;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            padding: 30px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        .card h2 {
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin: 8px 0;
            font-size: 14px;
            color: #666;
            text-align: left;
        }

        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .back-btn {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
            margin-top: 15px;
            display: block;
        }
        .back-btn:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

<div class="card">
    <h2>Update User</h2>
    <form method="post">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <input type="submit" value="Update">
    </form>
    <a href="admin-manageusers.php" class="back-btn">Back to User Management</a>
</div>

</body>
</html>
