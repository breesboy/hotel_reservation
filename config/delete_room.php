<?php
include_once 'conn.php';

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Validate ID
    if ($id <= 0) {
        header("Location: ../admin/manage_rooms.php?error=Invalid room ID.");
        exit();
    }

    // Prepare delete statement
    $stmt = $conn->query("DELETE FROM rooms WHERE id = $id");
    if (!$stmt) {
        header("Location: ../admin/manage_rooms.php?error=Failed to delete room.");
        exit();
    }
    header("Location: ../admin/manage_rooms.php?success=Room deleted successfully.");

    $stmt->close();
} else {
    header("Location: ../admin/manage_rooms.php?error=Invalid request");
}



?>