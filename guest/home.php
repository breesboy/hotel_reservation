<?php
/**
 * HOME.PHP - Guest Dashboard
 * The landing page for authenticated guests.
 * Checks for valid session and displays main actions.
 */

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve guest name safely
$guest_name = isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard | Grand Luxe Hotel</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        /* --- CSS VARIABLES --- */
        :root {
            --color-navy: #0A1A2F;
            --color-navy-light: #112240;
            --color-gold: #D4AF37;
            --color-gold-hover: #F4C430;
            --color-white: #FFFFFF;
            --color-text-muted: #8892b0;
            --shadow-soft: 0 10px 30px -10px rgba(2, 12, 27, 0.7);
            --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.25);
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
            background: linear-gradient(180deg, var(--color-navy) 0%, #050e1a 100%);
            color: var(--color-white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- NAVIGATION --- */
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

        .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--color-gold);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-links a {
            color: var(--color-white);
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--color-gold);
        }

        .logout-btn {
            border: 1px solid var(--color-gold);
            padding: 8px 16px;
            border-radius: 4px;
            color: var(--color-gold) !important;
        }

        .logout-btn:hover {
            background-color: var(--color-gold);
            color: var(--color-navy) !important;
        }

        /* --- HERO SECTION --- */
        .hero {
            text-align: center;
            padding: 80px 20px 60px;
            max-width: 800px;
            margin: 0 auto;
        }

        .welcome-text {
            font-size: 1rem;
            color: var(--color-gold);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 500;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            color: var(--color-text-muted);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .divider {
            width: 80px;
            height: 3px;
            background: var(--color-gold);
            margin: 0 auto;
            border-radius: 2px;
        }

        /* --- DASHBOARD GRID --- */
        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto 60px;
            padding: 0 20px;
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .action-card {
            background-color: var(--color-navy-light);
            padding: 40px;
            border-radius: var(--border-radius);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid transparent;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 250px;
            box-shadow: var(--shadow-card);
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: rgba(212, 175, 55, 0.4);
            box-shadow: var(--shadow-soft);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--color-gold);
        }

        .action-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--color-white);
        }

        .action-card p {
            color: var(--color-text-muted);
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        /* --- BUTTONS --- */
        .btn-gold {
            display: inline-block;
            background-color: var(--color-gold);
            color: var(--color-navy);
            padding: 14px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-gold:hover {
            background-color: var(--color-gold-hover);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
            transform: translateY(-2px);
        }

        .btn-outline {
            display: inline-block;
            background-color: transparent;
            color: var(--color-gold);
            border: 2px solid var(--color-gold);
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: var(--color-white);
        }

        /* --- FOOTER --- */
        footer {
            margin-top: auto;
            text-align: center;
            padding: 40px;
            color: var(--color-text-muted);
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            header {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
            }
            .nav-links {
                margin-top: 10px;
            }
            .nav-links a {
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="brand">Grand Luxe</div>
        <nav class="nav-links">
            <span style="color: var(--color-text-muted); margin-right: 15px;">Hello, <?php echo $guest_name; ?></span>
            <a href="available_rooms.php">Rooms</a>
            <a href="logout.php" >Logout</a>

        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="welcome-text">Welcome Back</div>
        <h1><?php echo $guest_name; ?></h1>
        <div class="divider"></div>
        <p style="margin-top: 20px;">
            Experience the pinnacle of luxury tailored just for you. 
            Manage your stay and discover our exclusive offerings below.
        </p>
    </section>

    <!-- Dashboard Actions -->
    <main class="dashboard-container">
        
        <!-- Card 1: Check Availability -->
        <div class="action-card">
            <div class="card-icon">🛏️</div>
            <h3>Book Your Stay</h3>
            <p>Explore our luxurious suites and find the perfect room for your next getaway.</p>
            <a href="available_rooms.php" class="btn-gold">Check Availability</a>
        </div>

        <!-- Card 2: My Reservations -->
        <div class="action-card">
            <div class="card-icon">📅</div>
            <h3>My Reservations</h3>
            <p>View details of your upcoming visits and manage your booking history.</p>
            <a href="my_reservations.php" class="btn-outline">View Bookings</a>
        </div>

    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. All Rights Reserved.
    </footer>

</body>
</html>