<?php
/**
 * LOGIN.PHP - Guest Login for Hotel Reservation System
 * * Handles form submission for guest authentication using Name and Email.
 * No database connection is included here, purely form structure and validation logic.
 */

// Initialize variables
$name = "";
$email = "";
$error = "";
$success = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and retrieve inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Basic Validation
    if (empty($name) || empty($email)) {
        $error = "Please enter both your full name and email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // --- LOGIC PLACEHOLDER ---
        // Here you would typically check the database if the guest exists
        // or start a session for the booking process.
        
        $success = "Welcome, " . htmlspecialchars($name) . ". Proceeding to reservation...";
        
        // Example redirect:
        // header("Location: booking.php");
        // exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Login | Grand Luxe Hotel</title>
    
    <!-- Google Fonts for Luxury Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        /* --- CSS VARIABLES & RESET --- */
        :root {
            --color-black: #000000;
            --color-navy: #0A1A2F;
            --color-navy-light: #112240;
            --color-gold: #D4AF37;
            --color-gold-hover: #F4C430;
            --color-white: #FFFFFF;
            --color-text-muted: #8892b0;
            --shadow-soft: 0 10px 30px -10px rgba(2, 12, 27, 0.7);
            --shadow-gold: 0 0 15px rgba(212, 175, 55, 0.3);
            --border-radius: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--color-navy);
            /* Luxury Gradient Background */
            background: linear-gradient(135deg, var(--color-navy) 0%, #000000 100%);
            color: var(--color-white);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* --- LOGIN CARD CONTAINER --- */
        .login-wrapper {
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .login-card {
            background-color: rgba(17, 34, 64, 0.95); /* Deep Navy with slight transparency */
            border: 1px solid rgba(212, 175, 55, 0.3); /* Subtle Gold Border */
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow-soft);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.8);
        }

        /* Decorative Gold Top Line */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--color-gold), transparent);
        }

        /* --- LOGO & HEADER --- */
        .logo-placeholder {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--color-gold);
            letter-spacing: 2px;
            margin-bottom: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .logo-subtitle {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 400;
            margin-bottom: 30px;
            color: var(--color-white);
        }

        /* --- FORM ELEMENTS --- */
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 8px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid #233554;
            border-radius: var(--border-radius);
            color: var(--color-white);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--color-gold);
            background-color: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
        }

        /* --- BUTTONS --- */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: none;
            border-radius: var(--border-radius);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            background-color: var(--color-gold-hover);
            box-shadow: var(--shadow-gold);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* --- FOOTER LINKS --- */
        .card-footer {
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--color-text-muted);
        }

        .card-footer a {
            color: var(--color-gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .card-footer a:hover {
            color: var(--color-white);
            text-decoration: underline;
        }

        /* --- ALERTS --- */
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .alert-error {
            background-color: rgba(220, 53, 69, 0.15);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            .logo-placeholder {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <!-- Branding -->
            <div class="logo-placeholder">Grand Luxe</div>
            <div class="logo-subtitle">Hotels & Resorts</div>

            <!-- Page Title -->
            <h1>Guest Login</h1>

            <!-- PHP Feedback Messages -->
            <?php if(!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="../config/login_action.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g. Jonathan Doe" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <button type="submit" class="btn-submit" name="login_guest">Continue</button>
            </form>

            <!-- Footer -->
            <div class="card-footer">
                Don't have an account? <a href="register.php">Register</a>
            </div>

        </div>
    </div>

</body>
</html>