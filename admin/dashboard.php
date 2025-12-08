<?php
/**
 * ADMIN/DASHBOARD.PHP - Admin Control Panel
 * Main landing page for administrators.
 * Displays overview statistics fetched from the database.
 */

// --- DEBUGGING: ENABLE ERROR REPORTING (Remove these 3 lines when fixed) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if file exists before including to prevent fatal error
if (file_exists('../config/conn.php')) {
    include_once '../config/conn.php';
} else {
    die("Error: Configuration file '../config/conn.php' not found. Check your file structure.");
}

session_start();

// --- 1. SESSION CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Administrator';

// --- 2. FETCH STATISTICS FROM DATABASE ---

// Initialize defaults
$total_rooms = 0;
$total_categories = 0;
$pending_reservations = 0;
$approved_reservations = 0;

// Ensure $conn exists and is a valid mysqli object
if (isset($conn) && $conn instanceof mysqli) {
    
    // Check connection error
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }

    // 1. Total Rooms
    $sql_rooms = "SELECT COUNT(*) FROM rooms";
    $result = $conn->query($sql_rooms);
    if ($result) {
        $total_rooms = $result->fetch_row()[0];
    }

    // 2. Total Categories 
    // (Tries 'categories' table first, falls back to distinct values in 'rooms' table)
    // We check if table exists first to avoid crashing on missing table
    $table_check = $conn->query("SHOW TABLES LIKE 'categories'");
    
    if ($table_check && $table_check->num_rows > 0) {
        $sql_cats = "SELECT COUNT(*) FROM categories"; 
        $result = $conn->query($sql_cats);
        if ($result) {
            $total_categories = $result->fetch_row()[0];
        }
    } else {
        // Fallback if categories table doesn't exist yet
        $sql_cats_fallback = "SELECT COUNT(DISTINCT category_id) FROM rooms";
        $result = $conn->query($sql_cats_fallback);
        if ($result) {
            $total_categories = $result->fetch_row()[0];
        }
    }

    // 3. Pending Reservations
    $sql_pending = "SELECT COUNT(*) FROM reservations WHERE status = 'Pending'";
    $result = $conn->query($sql_pending);
    if ($result) {
        $pending_reservations = $result->fetch_row()[0];
    }

    // 4. Approved Reservations
    $sql_approved = "SELECT COUNT(*) FROM reservations WHERE status = 'Approved'";
    $result = $conn->query($sql_approved);
    if ($result) {
        $approved_reservations = $result->fetch_row()[0];
    }
} else {
    echo "<div style='color: red; padding: 20px; text-align: center; background: #fff;'><strong>Warning:</strong> Database connection is not established. Check config/conn.php.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Grand Luxe Hotel</title>
    
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
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.4);
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

        .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--color-gold);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .user-panel {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-text {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        .logout-btn {
            border: 1px solid var(--color-gold);
            padding: 8px 18px;
            border-radius: 4px;
            color: var(--color-gold);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
        }

        /* --- MAIN CONTAINER --- */
        main {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 20px;
            flex-grow: 1;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--color-white);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        /* --- STATS GRID --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .stat-card {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-card);
            border-left: 4px solid var(--color-gold);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            background-color: #162a4a; /* Slightly lighter navy */
        }

        .stat-title {
            color: var(--color-text-muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--color-gold);
            font-weight: 600;
        }

        /* --- MANAGEMENT NAVIGATION --- */
        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--color-white);
            margin-bottom: 25px;
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .nav-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: var(--border-radius);
            padding: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            height: 100px;
        }

        .nav-card:hover {
            background-color: var(--color-gold);
            border-color: var(--color-gold);
            transform: translateY(-3px);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .nav-card-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-gold);
            transition: color 0.3s;
        }

        .nav-card:hover .nav-card-title {
            color: var(--color-navy);
        }

        .nav-arrow {
            color: var(--color-gold);
            font-size: 1.2rem;
            transition: transform 0.3s, color 0.3s;
        }

        .nav-card:hover .nav-arrow {
            color: var(--color-navy);
            transform: translateX(5px);
        }

        /* --- FOOTER --- */
        footer {
            text-align: center;
            padding: 30px;
            color: var(--color-text-muted);
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            h1 {
                font-size: 2rem;
                text-align: center;
            }
            h2 {
                text-align: center;
            }
        }
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .nav-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Navigation -->
    <header>
        <div class="brand">Grand Luxe Admin</div>
        <div class="user-panel">
            <span class="welcome-text">Welcome, <strong><?php echo $admin_username; ?></strong></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <main>
        <!-- Page Title -->
        <h1>Admin Dashboard</h1>

        <!-- Statistics Overview -->
        <section class="stats-grid">
            
            <div class="stat-card">
                <span class="stat-title">Total Rooms</span>
                <span class="stat-number"><?php echo $total_rooms; ?></span>
            </div>

            <div class="stat-card">
                <span class="stat-title">Categories</span>
                <span class="stat-number"><?php echo $total_categories; ?></span>
            </div>

            <div class="stat-card">
                <span class="stat-title">Pending Bookings</span>
                <span class="stat-number"><?php echo $pending_reservations; ?></span>
            </div>

            <div class="stat-card">
                <span class="stat-title">Approved Bookings</span>
                <span class="stat-number"><?php echo $approved_reservations; ?></span>
            </div>

        </section>

        <!-- Management Navigation -->
        <h2>Management Console</h2>
        <section class="nav-grid">
            
            <a href="manage_rooms.php" class="nav-card">
                <span class="nav-card-title">Manage Rooms</span>
                <span class="nav-arrow">&rarr;</span>
            </a>

            <a href="add_room.php" class="nav-card">
                <span class="nav-card-title">Add New Room</span>
                <span class="nav-arrow">&rarr;</span>
            </a>

            <a href="categories.php" class="nav-card">
                <span class="nav-card-title">Room Categories</span>
                <span class="nav-arrow">&rarr;</span>
            </a>

            <a href="reservations.php" class="nav-card">
                <span class="nav-card-title">Reservations</span>
                <span class="nav-arrow">&rarr;</span>
            </a>

            <a href="change_password.php" class="nav-card">
                <span class="nav-card-title">Change Password</span>
                <span class="nav-arrow">&rarr;</span>
            </a>

        </section>

    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. Admin Control Panel.
    </footer>

</body>
</html>