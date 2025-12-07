<?php
/**
 * RESERVATIONS.PHP - Grand Luxe Admin
 * View and manage guest reservations.
 * Follows the "Grand Luxe" Luxury Design System.
 */
include_once '../config/conn.php';
session_start();

// --- 1. SESSION CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Administrator';

// --- 2. FETCH DATA FROM DATABASE ---
$reservations = [];

$sql = "SELECT 
    reservations.id, 
    reservations.check_in, 
    reservations.check_out, 
    reservations.status,
    guests.name AS guest_name, 
    rooms.room_no AS room_number
FROM reservations
JOIN guests ON reservations.guest_id = guests.id
JOIN rooms ON reservations.room_id = rooms.id
ORDER BY reservations.id DESC"; // Added ORDER BY to show newest first

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
    }
    $result->free();
} else {
    // Optional: Log error
    $reservations = [];
}

// Handle Success/Error Messages
$alert_msg = "";
$alert_type = "";

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $alert_msg = "Reservation status updated successfully.";
    $alert_type = "success";
} elseif (isset($_GET['error'])) {
    $alert_msg = htmlspecialchars($_GET['error']);
    $alert_type = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations | Grand Luxe Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        /* --- CSS VARIABLES (MATCHING DESIGN SYSTEM) --- */
        :root {
            --color-navy: #0A1A2F;
            --color-navy-light: #112240;
            --color-gold: #D4AF37;
            --color-gold-hover: #F4C430;
            --color-white: #FFFFFF;
            --color-text-muted: #8892b0;
            --color-danger: #ff7675;
            --color-success: #27ae60;
            --color-pending: #f39c12;
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

        .nav-links {
            display: flex;
            align-items: center;
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

        /* --- HERO SECTION --- */
        .hero {
            text-align: center;
            padding: 50px 20px 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            margin-bottom: 10px;
            color: var(--color-white);
        }
        
        .divider {
            width: 80px;
            height: 3px;
            background: var(--color-gold);
            margin: 0 auto 20px;
            border-radius: 2px;
        }

        /* --- ALERTS --- */
        .alert-container {
            max-width: 800px;
            margin: 0 auto 20px;
            padding: 0 20px;
            text-align: center;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            font-weight: 500;
            border: 1px solid transparent;
        }

        .alert-success {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--color-success);
            border-color: rgba(39, 174, 96, 0.3);
        }

        .alert-error {
            background-color: rgba(214, 48, 49, 0.1);
            color: var(--color-danger);
            border-color: rgba(214, 48, 49, 0.3);
        }

        /* --- RESERVATIONS GRID --- */
        .reservations-grid {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Desktop Default */
            gap: 25px;
        }

        .res-card {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-card);
            border-left: 3px solid var(--color-gold);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .res-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            background-color: #152744;
        }

        .res-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .guest-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--color-white);
            margin-bottom: 5px;
        }

        .room-no {
            color: var(--color-gold);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background-color: rgba(243, 156, 18, 0.15);
            color: var(--color-pending);
            border: 1px solid rgba(243, 156, 18, 0.3);
        }

        .status-approved {
            background-color: rgba(39, 174, 96, 0.15);
            color: var(--color-success);
            border: 1px solid rgba(39, 174, 96, 0.3);
        }

        .status-rejected {
            background-color: rgba(255, 118, 117, 0.15);
            color: var(--color-danger);
            border: 1px solid rgba(255, 118, 117, 0.3);
        }

        .res-dates {
            margin-bottom: 20px;
            color: var(--color-text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .date-label {
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-right: 5px;
        }

        .res-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 15px;
        }

        .btn-action {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-radius: 3px;
            font-size: 0.8rem;
            text-transform: uppercase;
            text-decoration: none;
            letter-spacing: 0.5px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-approve {
            background-color: var(--color-gold);
            border: 1px solid var(--color-gold);
            color: var(--color-navy);
        }

        .btn-approve:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(212, 175, 55, 0.3);
        }

        .btn-reject {
            background-color: transparent;
            border: 1px solid rgba(255, 118, 117, 0.4);
            color: var(--color-danger);
        }

        .btn-reject:hover {
            background-color: var(--color-danger);
            border-color: var(--color-danger);
            color: white;
        }

        .status-text {
            width: 100%;
            text-align: center;
            font-size: 0.9rem;
            color: var(--color-text-muted);
            font-style: italic;
            padding: 8px 0;
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
        @media (max-width: 1024px) {
            .reservations-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            .nav-links a { margin: 0 5px; }
            
            .reservations-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="brand">Grand Luxe Admin</div>
        <nav class="nav-links">
            <span style="color: var(--color-text-muted); margin-right: 15px; display:none;">
                <?php echo $admin_username; ?>
            </span>
            <a href="dashboard.php">Dashboard</a>
            <a href="add_room.php">Add Room</a>
            <a href="manage_rooms.php">Manage Rooms</a>
            <a href="categories.php">Categories</a>
            <a href="logout.php" style="color: var(--color-gold);">Logout</a>
        </nav>
    </header>

    <!-- Page Title -->
    <section class="hero">
        <h1>Reservations</h1>
        <div class="divider"></div>
        <p>Review and process guest booking requests.</p>
    </section>

    <!-- Alerts -->
    <?php if ($alert_msg): ?>
    <div class="alert-container">
        <div class="alert alert-<?php echo $alert_type; ?>">
            <?php echo $alert_msg; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reservations Grid -->
    <main class="reservations-grid">
        <?php foreach ($reservations as $res): ?>
            <?php 
                // Normalize status to lowercase for comparison
                $status_lower = strtolower($res['status']);
                
                // Determine Badge Class
                if ($status_lower === 'approved') {
                    $status_class = 'status-approved';
                } elseif ($status_lower === 'rejected') {
                    $status_class = 'status-rejected';
                } else {
                    $status_class = 'status-pending';
                }
            ?>
            <div class="res-card">
                
                <div class="res-header">
                    <div>
                        <h3 class="guest-name"><?php echo htmlspecialchars($res['guest_name']); ?></h3>
                        <span class="room-no">Room <?php echo htmlspecialchars($res['room_number']); ?></span>
                    </div>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($res['status']); ?>
                    </span>
                </div>

                <div class="res-dates">
                    <div><span class="date-label">Check-in:</span> <?php echo date("M d, Y", strtotime($res['check_in'])); ?></div>
                    <div><span class="date-label">Check-out:</span> <?php echo date("M d, Y", strtotime($res['check_out'])); ?></div>
                </div>

                <div class="res-actions">
                    <?php if ($status_lower === 'pending'): ?>
                        <!-- Show Actions only if Pending -->
                        <a href="../config/update_reservation.php?id=<?php echo $res['id']; ?>&status=approved" 
                           class="btn-action btn-approve"
                           onclick="return confirm('Approve reservation for <?php echo $res['guest_name']; ?>?');">
                           Approve
                        </a>
                        <a href="../config/update_reservation.php?id=<?php echo $res['id']; ?>&status=rejected" 
                           class="btn-action btn-reject"
                           onclick="return confirm('Reject reservation for <?php echo $res['guest_name']; ?>?');">
                           Reject
                        </a>
                    <?php else: ?>
                        <!-- No Delete Button (Records are kept) -->
                        <div class="status-text">
                            Status: <?php echo ucfirst($status_lower); ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
        
        <?php if (empty($reservations)): ?>
            <p style="grid-column: 1 / -1; text-align: center; color: var(--color-text-muted);">
                No reservations found.
            </p>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. Admin Control Panel.
    </footer>

</body>
</html>