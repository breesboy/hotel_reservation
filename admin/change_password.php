<?php
/**
 * ADMIN/CHANGE_PASSWORD.PHP - Admin Password Update Page
 * Allows authenticated administrators to change their password.
 */

session_start();

// --- 1. SESSION CHECK ---
// Redirect to login if not authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get message parameters
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success_message = isset($_GET['success']) && $_GET['success'] == 1 ? "Password updated successfully." : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Grand Luxe Admin</title>
    
    <!-- Google Fonts (Consistent with Dashboard) -->
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
            background: linear-gradient(180deg, var(--color-navy) 0%, #050e1a 100%);
            color: var(--color-white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- NAVIGATION / HEADER --- */
        header {
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(10, 26, 47, 0.95);
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .header-left {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--color-gold);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .header-right {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            border: 1px solid var(--color-gold);
            padding: 8px 18px;
            border-radius: 4px;
            color: var(--color-gold);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .nav-btn:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
        }

        /* --- MAIN CONTENT & CARD --- */
        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .form-card {
            background-color: rgba(10, 26, 47, 0.95);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(212, 175, 55, 0.3);
            text-align: center;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--color-gold);
            margin-bottom: 15px;
            font-weight: 500;
        }

        .divider {
            height: 1px;
            width: 80px;
            background-color: var(--color-gold);
            margin: 0 auto 30px;
            opacity: 0.8;
        }
        
        /* --- MESSAGES --- */
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: left;
            border: 1px solid;
        }

        .error-box {
            background-color: rgba(220, 53, 69, 0.15);
            color: #ff6b6b;
            border-color: rgba(220, 53, 69, 0.4);
        }

        .success-box {
            background-color: rgba(50, 205, 50, 0.15);
            color: #90ee90;
            border-color: rgba(212, 175, 55, 0.6); /* Gold border for luxury theme */
        }

        /* --- FORM FIELDS --- */
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--color-text-muted);
            font-weight: 500;
            transition: color 0.3s;
        }

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
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* --- SUBMIT BUTTON --- */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        }

        .btn-submit:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }

        /* --- FOOTER --- */
        footer {
            text-align: center;
            padding: 20px;
            color: var(--color-text-muted);
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- Header / Navigation -->
    <header>
        <div class="header-left">Change Password</div>
        <div class="header-right">
            <a href="dashboard.php" class="nav-btn">Back to Dashboard</a>
            <a href="logout.php" class="nav-btn">Logout</a>
        </div>
    </header>

    <main>
        
        <div class="form-card">
            
            <h2>Update Admin Password</h2>
            <div class="divider"></div>

            <!-- Error Message Display -->
            <?php if (!empty($error_message)): ?>
                <div class="message-box error-box">
                    <strong>Error:</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Success Message Display -->
            <?php if (!empty($success_message)): ?>
                <div class="message-box success-box">
                    <strong>Success!</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Password Change Form -->
            <form action="../config/change_password_action.php" method="POST">
                
                <div class="form-group">
                    <label for="old_password">Old Password</label>
                    <input type="password" id="old_password" name="old_password" required autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="confirm_new_password">Confirm New Password</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn-submit" name="change_password">Update Password</button>
            </form>

        </div>

    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. Secure Management.
    </footer>

</body>
</html>