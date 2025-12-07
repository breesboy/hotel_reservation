<?php
include_once 'conn.php';

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Validate ID
    if ($id <= 0) {
        header("Location: ../admin/categories.php?error=Invalid category ID.");
        exit();
    }

    // Prepare delete statement
    $stmt = $conn->query("DELETE FROM room_categories WHERE id = $id");
    if (!$stmt) {
        header("Location: ../admin/categories.php?error=Failed to delete category.");
        exit();
    }
    header("Location: ../admin/categories.php?success=Category deleted successfully.");

    $stmt->close();
} else {
    header("Location: ../admin/categories.php?error=Invalid request");
}



?>