<?php
include 'db_connection.php';

if (isset($_GET['admin_id'])) {
    $user_id = intval($_GET['admin_id']);
    $sql = "DELETE FROM admins WHERE admin_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin-manageadmin.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>
<?php
include 'db_connection.php';

if (isset($_GET['admin_id'])) {
    $user_id = intval($_GET['admin_id']);
    $sql = "DELETE FROM admin WHERE admin_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin-manageadmin.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>
