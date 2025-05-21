<?php
// Database connection
include 'user-dashboard2.php';
$conn = new mysqli("localhost", "root", "", "yna_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the ticket ID from the URL
$ticket_id = $_GET['ticket_id'];

// Fetch the ticket details
$sql = "SELECT * FROM tickets WHERE ticket_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get updated ticket details from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $priority = $_POST['priority'];
    $subject = $_POST['subject'];
    $description = $_POST['description'];

    // Update the ticket in the database
    $update_sql = "UPDATE tickets SET name = ?, email = ?, department = ?, priority = ?, subject = ?, description = ? WHERE ticket_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssi", $name, $email, $department, $priority, $subject, $description, $ticket_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Ticket updated successfully!'); window.location='user-viewticket.php';</script>";
    } else {
        echo "Error: " . $update_stmt->error;
    }

    $update_stmt->close();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Update Ticket</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f4ff, #ffffff);
            margin-left: 150px;
        }

        .container {
            max-width: 850px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2e3a5f;
            margin-bottom: 30px;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 30px;
        }

        input, textarea, select, button {
            padding: 12px;
            border-radius: 8px;
            font-size: 15px;
            width: 100%;
        }

        textarea {
            height: 150px;
        }

        button {
            background: #4c64d3;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #3c4db0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Update Ticket Details</h2>
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($ticket['name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($ticket['email']); ?>" required>

        <label for="department">Department:</label>
        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($ticket['department']); ?>" required>

        <label for="priority">Priority:</label>
        <select name="priority" id="priority" required>
            <option value="low" <?php if ($ticket['priority'] == 'low') echo 'selected'; ?>>Low</option>
            <option value="medium" <?php if ($ticket['priority'] == 'medium') echo 'selected'; ?>>Medium</option>
            <option value="high" <?php if ($ticket['priority'] == 'high') echo 'selected'; ?>>High</option>
            <option value="critical" <?php if ($ticket['priority'] == 'critical') echo 'selected'; ?>>Critical</option>
        </select>

        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($ticket['subject']); ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>

        <button type="submit">Update Ticket</button>
    </form>
</div>

</body>
</html>
<?php
$conn->close();
?>
