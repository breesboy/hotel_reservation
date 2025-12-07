<?php
/**
 * REGISTER.PHP - Guest Registration for Hotel Reservation System
 * Handles new guest sign-up form submission.
 * Validates presence of Name, Email, Phone, ID, and Address.
 */

// Initialize variables to keep form populated on error
$name = "";
$email = "";
$phone = "";
$id_number = "";
$address = "";
$error = "";
$success = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and retrieve inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $id_number = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    // Basic Validation
    if (empty($name) || empty($email) || empty($phone) || empty($id_number) || empty($address)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // --- LOGIC PLACEHOLDER ---
        // Here you would typically Insert the new user into the database
        // e.g. INSERT INTO guests (name, email, phone...) VALUES (...)
        
        $success = "Registration successful! You may now login.";
        
        // Reset form after success
        $name = $email = $phone = $id_number = $address = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Registration | Grand Luxe Hotel</title>
    
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
            padding: 40px 20px; /* Added padding for scrolling on mobile */
        }

        /* --- REGISTER CARD CONTAINER --- */
        .register-wrapper {
            width: 100%;
            max-width: 550px; /* Slightly wider than login for more fields */
            position: relative;
        }

        .register-card {
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

        .register-card:hover {
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.8);
        }

        /* Decorative Gold Top Line */
        .register-card::before {
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
        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 8px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
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

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--color-gold);
            background-color: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
        }

        /* --- BUTTONS --- */
        .btn-register {
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
            margin-top: 20px;
        }

        .btn-register:hover {
            background-color: var(--color-gold-hover);
            box-shadow: var(--shadow-gold);
            transform: translateY(-1px);
        }

        .btn-register:active {
            transform: translateY(1px);
        }

        /* --- FOOTER LINKS --- */
        .card-footer {
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--color-text-muted);
            text-align: center;
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
            text-align: center;
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
            .register-card {
                padding: 30px 20px;
            }
            .logo-placeholder {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>

    <div class="register-wrapper">
        <div class="register-card">
            
            <!-- Branding -->
            <div class="logo-placeholder">Grand Luxe</div>
            <div class="logo-subtitle">Hotels & Resorts</div>

            <!-- Page Title -->
            <h1>Guest Registration</h1>

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

            <!-- Registration Form -->
            <form action="../config/register_action.php" method="POST">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="+250 700 000 000" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>


                <div class="form-group">
                    <label for="address">Residential Address</label>
                    <textarea id="address" name="address" placeholder="Your permanent address" rows="3" required><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <button type="submit" class="btn-register" name="register_guest">Register</button>
            </form>

            <!-- Footer -->
            <div class="card-footer">
                Already have an account? <a href="login.php">Login</a>
            </div>

        </div>
    </div>

</body>
</html>