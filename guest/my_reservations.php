<?php
/**
 * MY_RESERVATIONS.PHP - Guest Reservation History Page
 * Displays a list of the current user's past and upcoming reservations with pagination.
 */

session_start();

// --- 1. SESSION CHECK ---
// Redirect to login if not authenticated
// if (!isset($_SESSION['guest_id'])) {
//     header("Location: login.php");
//     exit();
// }

// $guest_id = $_SESSION['guest_id'];
$guest_name = isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : 'Valued Guest';

// --- 2. SAMPLE RESERVATION DATA ---
// NOTE: In a real system, this data would be fetched from a database based on $guest_id
$all_reservations = [];
$room_categories = ["Deluxe Suite", "Executive King", "Ocean View Balcony", "Standard Premium"];

for ($i = 1; $i <= 10; $i++) {
    $category_index = ($i - 1) % count($room_categories);
    $status_code = $i % 3;
    $status = $status_code == 0 ? "Approved" : ($status_code == 1 ? "Pending" : "Rejected");
    
    $all_reservations[] = [
        "id" => $i,
        "room_id" => $i, // Simulated link to room_details
        "room_no" => "Room " . str_pad($i, 3, '0', STR_PAD_LEFT),
        "category" => $room_categories[$category_index],
        "check_in" => date("Y-m-d", strtotime("+$i days")),
        "check_out" => date("Y-m-d", strtotime("+".($i + 3)." days")), // 3 nights stay
        "total_cost" => rand(600, 1200) * (($i % 4) + 1), // Varies based on room/nights
        "status" => $status
    ];
}

// --- 3. PAGINATION LOGIC ---
$reservations_per_page = 5;
$total_reservations = count($all_reservations);
$total_pages = ceil($total_reservations / $reservations_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Ensure current page is within valid range
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages) $current_page = $total_pages;

$start_index = ($current_page - 1) * $reservations_per_page;
$current_reservations = array_slice($all_reservations, $start_index, $reservations_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations | Grand Luxe Hotel</title>
    
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
            --badge-approved: #D4AF37; /* Gold */
            --badge-pending: #0A1A2F; /* Deep Navy */
            --badge-rejected: #D32F2F; /* Dark Red */
            --badge-pending-bg: rgba(212, 175, 55, 0.1);
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

        /* --- UTILITIES & LAYOUT --- */
        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 20px;
        }
        main {
            flex-grow: 1;
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
            color: var(--color-gold-hover);
        }

        /* --- DASHBOARD HEADER --- */
        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }
        .dashboard-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--color-gold);
            margin-bottom: 10px;
        }
        .dashboard-header p {
            font-size: 1.1rem;
            color: var(--color-text-muted);
        }
        .dashboard-btn {
            border: 1px solid var(--color-gold);
            padding: 8px 16px;
            border-radius: 4px;
            color: var(--color-gold) !important;
        }
        .dashboard-btn:hover {
            background-color: var(--color-gold);
            color: var(--color-navy) !important;
        }

        /* --- RESERVATION LISTING --- */
        .reservations-grid {
            display: grid;
            gap: 25px;
            /* Single column on mobile, expands on desktop */
            grid-template-columns: 1fr;
        }

        .reservation-card {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            padding: 25px;
            border-left: 5px solid var(--color-gold);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .reservation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--color-gold-hover);
        }
        .card-header p {
            font-size: 0.9rem;
            color: var(--color-text-muted);
        }

        .card-body {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }
        .detail-item:last-child {
            border-right: none;
        }
        .detail-item label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--color-text-muted);
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .detail-item strong {
            display: block;
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--color-white);
        }

        /* --- STATUS BADGE --- */
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
            align-self: flex-start;
        }
        .status-Approved {
            background-color: var(--badge-approved);
            color: var(--color-navy);
            border: 1px solid var(--badge-approved);
        }
        .status-Pending {
            background-color: var(--badge-pending-bg);
            color: var(--badge-approved);
            border: 1px solid var(--badge-approved);
        }
        .status-Rejected {
            background-color: rgba(211, 47, 47, 0.2);
            color: var(--badge-rejected);
            border: 1px solid var(--badge-rejected);
        }

        /* --- VIEW DETAILS BUTTON --- */
        .card-footer {
            text-align: right;
        }
        .btn-details {
            background-color: transparent;
            color: var(--color-gold);
            border: 1px solid var(--color-gold);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .btn-details:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
        }
        
        /* --- PAGINATION --- */
        .pagination-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
        }

        .page-link {
            text-decoration: none;
            color: var(--color-gold);
            border: 1px solid var(--color-gold);
            padding: 8px 15px;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s;
            font-weight: 600;
        }
        .page-link:hover:not(.active) {
            background-color: rgba(212, 175, 55, 0.1);
        }
        .page-link.active {
            background-color: var(--color-gold);
            color: var(--color-navy);
            border-color: var(--color-gold);
            box-shadow: 0 4px 10px rgba(212, 175, 55, 0.4);
        }
        .page-link.disabled {
            color: var(--color-text-muted);
            border-color: var(--color-text-muted);
            cursor: not-allowed;
            opacity: 0.5;
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
        @media (min-width: 768px) {
            .reservations-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .card-header h2 {
                margin-bottom: 5px;
            }
            .card-body {
                grid-template-columns: 1fr;
            }
            .detail-item {
                border-right: none;
                border-bottom: 1px dotted rgba(255, 255, 255, 0.05);
                padding-bottom: 10px;
            }
            .detail-item:last-child {
                border-bottom: none;
            }
            .card-footer {
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="brand">Grand Luxe</div>
        <nav class="nav-links">
            <span style="color: var(--color-text-muted); margin-right: 15px;">Welcome, <?php echo $guest_name; ?></span>
            <a href="home.php" class="dashboard-btn">Dashboard</a>
            <a href="available_rooms.php">Rooms</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main class="container">

        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>My Reservations</h1>
            <p>Review the status and details of your upcoming and past bookings (<?php echo $total_reservations; ?> total).</p>
        </div>

        <?php if ($total_reservations > 0): ?>

        <!-- Reservation Listing Grid -->
        <div class="reservations-grid">
            <?php foreach ($current_reservations as $reservation): ?>
            
            <div class="reservation-card">
                
                <div class="card-header">
                    <div>
                        <h2><?php echo htmlspecialchars($reservation['room_no']); ?></h2>
                        <p><?php echo htmlspecialchars($reservation['category']); ?></p>
                    </div>
                    <span class="status-badge status-<?php echo htmlspecialchars($reservation['status']); ?>">
                        <?php echo htmlspecialchars($reservation['status']); ?>
                    </span>
                </div>
                
                <div class="card-body">
                    
                    <div class="detail-item">
                        <label>Check-in</label>
                        <strong><?php echo date('M d, Y', strtotime($reservation['check_in'])); ?></strong>
                    </div>

                    <div class="detail-item">
                        <label>Check-out</label>
                        <strong><?php echo date('M d, Y', strtotime($reservation['check_out'])); ?></strong>
                    </div>
                    
                    <div class="detail-item">
                        <label>Total Cost</label>
                        <strong style="color: var(--color-gold);">$<?php echo number_format($reservation['total_cost'], 2); ?></strong>
                    </div>

                </div>
                
                <div class="card-footer">
                    <a 
                        href="room_details.php?id=<?php echo $reservation['room_id']; ?>" 
                        class="btn-details"
                    >
                        View Room Details
                    </a>
                </div>

            </div>
            
            <?php endforeach; ?>
        </div>

        <!-- Pagination Controls -->
        <div class="pagination-controls">
            <?php
            // Previous Page Link
            $prev_page = $current_page - 1;
            $prev_class = $current_page <= 1 ? 'disabled' : '';
            $prev_link = $prev_class ? '#' : '?page=' . $prev_page;
            ?>
            <a href="<?php echo $prev_link; ?>" class="page-link <?php echo $prev_class; ?>">
                &larr; Previous
            </a>

            <!-- Page Number Links -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a 
                    href="?page=<?php echo $i; ?>" 
                    class="page-link <?php echo ($i === $current_page) ? 'active' : ''; ?>"
                >
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php
            // Next Page Link
            $next_page = $current_page + 1;
            $next_class = $current_page >= $total_pages ? 'disabled' : '';
            $next_link = $next_class ? '#' : '?page=' . $next_page;
            ?>
            <a href="<?php echo $next_link; ?>" class="page-link <?php echo $next_class; ?>">
                Next &rarr;
            </a>
        </div>

        <?php else: ?>
            <!-- No Reservations Message -->
            <div class="luxury-card" style="text-align: center; padding: 60px;">
                <h3 style="color: var(--color-gold);">No Reservations Found</h3>
                <p style="color: var(--color-text-muted); margin-top: 15px;">It looks like you haven't booked anything yet. Start your luxury stay!</p>
                <a href="available_rooms.php" class="btn-details" style="display: inline-block; margin-top: 30px;">
                    Browse Available Rooms
                </a>
            </div>
        <?php endif; ?>
        
    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. Excellence in Hospitality.
    </footer>

</body>
</html>