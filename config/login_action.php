<?php
include 'conn.php';

if(isset($_POST['login'])) {

    
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $stmt->close();
            header("Location: ../admin/dashboard.php");
            exit();
        }
    }

    $stmt->close();
    $error_message = "Invalid username or password.";
    header("Location: ../admin/login.php?error=" . urlencode($error_message));
    exit();
    
}


if(isset($_POST['login_guest'])) {

    
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    if (empty($name) || empty($email)) {
        $error_message = "Please enter both your full name and email address.";
        header("Location: ../guest/login.php?error=" . urlencode($error_message));
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
        header("Location: ../guest/login.php?error=" . urlencode($error_message));
        exit();
    } else {
        // Check if guest exists in DB
        $stmt = $conn->prepare("SELECT id, name FROM guests WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Guest exists: start session and redirect to home
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['guest_id'] = $row['id'];
            $_SESSION['guest_name'] = $row['name'];
            $_SESSION['guest_email'] = $email;
            $stmt->close();
            header("Location: ../guest/home.php");
            exit();
        } else {
            // Guest does not exist: redirect to register with name/email prefilled
            $stmt->close();
            header("Location: ../guest/register.php?name=" . urlencode($name) . "&email=" . urlencode($email));
            exit();
        }
    }
    
}


?>