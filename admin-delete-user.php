<?php
include 'db_connection.php';

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $sql = "DELETE FROM users WHERE user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin-manageusers.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>
