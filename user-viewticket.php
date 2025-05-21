<?php
// Database connection
include 'user-dashboard2.php';
$conn = new mysqli("localhost", "root", "", "yna_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all submitted tickets
$sql = "SELECT * FROM tickets";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Submitted Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f4ff, #ffffff);
            margin-left: 190px;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            text-align: center;
            color: #2e3a5f;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            animation: slideIn 1s ease-in-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-50%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        th {
            background-color: #4c64d3;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e0e0e0;
        }

        .ticket-status {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
        }

        .open {
            background-color: #ff9800;
        }

        .in-progress {
            background-color: #2196f3;
        }

        .closed {
            background-color: #4caf50;
        }

        .action-btn {
            padding: 5px 10px;
            background-color: #4c64d3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .action-btn:hover {
            background-color: #3c4db0;
        }

        .update-btn {
            background-color: #ff5722;
        }

        .update-btn:hover {
            background-color: #e64a19;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Submitted Tickets</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Subject</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($ticket = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['email']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['department']); ?></td>
                    <td><?php echo ucfirst($ticket['priority']); ?></td>
                    <td>
                        <span class="ticket-status <?php echo strtolower($ticket['status']); ?>">
                            <?php echo ucfirst($ticket['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                    <td><?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?></td>
                    <td>
                        <button class="action-btn" onclick="window.location.href='user-updateticket.php?ticket_id=<?php echo $ticket['ticket_id']; ?>'">
                            Update
                        </button>
                        <button class="action-btn" onclick="window.location.href='user-contactagent.php?ticket_id=<?php echo $ticket['ticket_id']; ?>'">
                            view
                        </button>
                        
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php
$conn->close();
?>