<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id']);

    // First, delete related messages
    $stmt1 = $conn->prepare("DELETE FROM messages WHERE ticket_id = ?");
    $stmt1->bind_param("i", $ticket_id);
    $stmt1->execute();
    $stmt1->close();

    // Then, delete the ticket
    $stmt2 = $conn->prepare("DELETE FROM tickets WHERE ticket_id = ?");
    $stmt2->bind_param("i", $ticket_id);

    if ($stmt2->execute()) {
        header("Location: admin-manageticket.php?deleted=success");
        exit;
    } else {
        echo "Error deleting ticket: " . $stmt2->error;
    }

    $stmt2->close();
    $conn->close();
} else {
    header("Location: manage_tickets.php");
    exit;
}
