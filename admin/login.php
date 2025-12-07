<?php
/**
 * ADMIN/LOGIN.PHP - Admin Authentication Page
 * Displays the login form for administrators.
 */

session_start();

// Redirect admin to dashboard if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Check for error message passed via GET parameter
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Grand Luxe Hotel</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        /* --- CSS VARIABLES --- */
        :root {
            --color-navy: #0A1A2F;
            --color-navy-light: #112240;
            --color-gold: #D4AF37;
            --color-gold-hover: #F4C430;
            --color-white: #FFFFFF;
            --color-text-muted: #8892b0;
            --shadow-card: 0 10px 40px rgba(0, 0, 0, 0.7);
            --border-radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--color-navy);
            background: radial-gradient(circle at center, #1a2a40 0%, #000000 100%);
            color: var(--color-white);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* --- LOGIN CARD CONTAINER --- */
        .login-card {
            background-color: rgba(10, 26, 47, 0.95);
            width: 100%;
            max-width: 420px;
            padding: 50px 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(212, 175, 55, 0.3);
            text-align: center;
            position: relative;
            backdrop-filter: blur(10px);
        }

        /* Top Gold Gradient Line */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--color-gold), transparent);
        }

        /* --- TYPOGRAPHY & BRANDING --- */
        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 0.9rem;
            color: var(--color-gold);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 15px;
            display: block;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--color-white);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .divider {
            height: 1px;
            width: 60px;
            background-color: var(--color-gold);
            margin: 0 auto 35px;
            opacity: 0.6;
        }

        /* --- FORM ELEMENTS --- */
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.85rem;
            color: var(--color-text-muted);
            font-weight: 500;
            transition: color 0.3s;
        }

        .form-group:focus-within label {
            color: var(--color-gold);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid #2a3b55;
            border-radius: 8px;
            color: var(--color-white);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--color-gold);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.2);
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* --- LOGIN BUTTON --- */
        .btn-login {
            width: 100%;
            padding: 16px;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        }

        .btn-login:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }

        /* --- ERROR MESSAGE --- */
        .error-container {
            background-color: rgba(220, 53, 69, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
            padding: 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 25px;
            text-align: center;
        }

        /* --- FOOTER LINK --- */
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: var(--color-text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
            border-bottom: 1px solid transparent;
        }

        .back-link:hover {
            color: var(--color-gold);
            border-bottom-color: var(--color-gold);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        
        <!-- Brand Header -->
        <span class="brand-title">Hotel Admin Panel</span>
        <h1>Admin Login</h1>
        <div class="divider"></div>

        <!-- Error Message Display -->
        <?php if (!empty($error_message)): ?>
            <div class="error-container">
                ⚠️ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="../config/login_action.php" method="POST">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter admin username" required autofocus autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter admin password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login" name="login">Login</button>
        </form>

        <!-- Footer Link -->
        <a href="../guest/home.php" class="back-link">&larr; Back to Guest Site</a>

    </div>

</body>
</html>