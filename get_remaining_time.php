<?php
// db_connection.php - Ensure this file includes your database connection code
include 'db_connection.php';

if (isset($_GET['ticket_id'])) {
    $ticket_id = $_GET['ticket_id'];

    // Fetch the ticket details based on ticket_id
    $sql = "SELECT * FROM tickets WHERE ticket_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($ticket = $result->fetch_assoc()) {
        echo getRemainingTime($ticket['created_at'], $ticket['priority']);
    } else {
        echo 'Ticket not found.';
    }
}

function getRemainingTime($created_at, $priority) {
    date_default_timezone_set('Asia/Manila');
    
    $now = new DateTime();
    $created = new DateTime($created_at);

    $slaHours = [
        'critical' => 2,
        'high'     => 4,
        'medium'   => 6,
        'low'      => 8
    ];

    if (!isset($slaHours[$priority])) return 'Invalid Priority';

    $deadline = clone $created;
    $deadline->modify("+{$slaHours[$priority]} hours");

    $remaining = $now->diff($deadline);

    if ($now > $deadline) {
        return 'Overdue';
    } else {
        return $remaining->format('%h hours, %i minutes remaining');
    }
}
?>
