<?php
include 'db_connection.php';

if (isset($_GET['agent_id'])) {
    $agent_id = intval($_GET['agent_id']);

    // First delete related messages
    $deleteMessages = "DELETE FROM messages WHERE agent_id = $agent_id";
    mysqli_query($conn, $deleteMessages);

    // Then delete the agent
    $deleteAgent = "DELETE FROM agents WHERE agent_id = $agent_id";
    if (mysqli_query($conn, $deleteAgent)) {
        header("Location: admin-manageagent.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting agent: " . mysqli_error($conn);
    }
}
?>
