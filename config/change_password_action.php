<?php
session_start();
include_once 'conn.php';

if(isset($_POST['change_password'])) {
    $admin_id = $_SESSION['admin_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Validate inputs
    if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
        header("Location: ../admin/change_password.php?error=All fields are required.");
        exit();
    }

    if ($new_password !== $confirm_new_password) {
        header("Location: ../admin/change_password.php?error=New passwords do not match.");
        exit();
    }

    // Fetch current password from database
    $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../admin/change_password.php?error=Admin not found.");
        exit();
    }

    $row = $result->fetch_assoc();
    
    // Verify old password
    if (!password_verify($old_password, $row['password'])) {
        header("Location: ../admin/change_password.php?error=Incorrect old password.");
        exit();
    }

    // Hash new password and update in database
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_new_password, $admin_id);
    
    if ($update_stmt->execute()) {
        header("Location: ../admin/change_password.php?success=1");
        exit();
    } else {
        header("Location: ../admin/change_password.php?error=Failed to update password.");
        exit();
    }
}




?>