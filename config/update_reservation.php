<?php
include_once 'conn.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];

    // Validate status
    if ($status !== 'approved' && $status !== 'rejected') {
        die('Invalid status');
    }

    // Update reservation status
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        header("Location: ../admin/reservations.php?message=Reservation updated successfully");
    } else {
        header("Location: ../admin/reservations.php?error=Failed to update reservation");
    }

    $stmt->close();
} else {
    header("Location: ../admin/reservations.php?error=Invalid request");
}



?>