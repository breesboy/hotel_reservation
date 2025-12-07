<?php
include_once 'conn.php';
if(isset($_POST['register_guest'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        $error_message = "All fields are required.";
        header("Location: ../guest/register.php?error=" . urlencode($error_message));
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
        header("Location: ../guest/register.php?error=" . urlencode($error_message));
        exit();
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM guests WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email already registered
            $stmt->close();
            $error_message = "This email is already registered. Please log in.";
            header("Location: ../guest/register.php?error=" . urlencode($error_message));
            exit();
        } else {
            // Insert new guest
            $stmt = $conn->prepare("INSERT INTO guests (name, email, phone, address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $address);
            if ($stmt->execute()) {
                // Registration successful
                $stmt->close();
                $success_message = "Registration successful! You can now log in.";
                header("Location: ../guest/login.php?success=" . urlencode($success_message));
                exit();
            } else {
                // Insertion failed
                $stmt->close();
                $error_message = "An error occurred during registration. Please try again.";
                header("Location: ../guest/register.php?error=" . urlencode($error_message));
                exit();
            }
        }
    }
}



?>