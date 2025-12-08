<?php
/**
 * MY_RESERVATIONS.PHP - Guest Reservation History Page
 * Displays a list of the current user's past and upcoming reservations with pagination from the database.
 */

include_once '../config/conn.php';
session_start();

// --- 1. SESSION CHECK ---
// Redirect to login if not authenticated
if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit();
}

$guest_id = $_SESSION['guest_id'];
$guest_name = isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : 'Valued Guest';

// --- 2. PAGINATION CONFIGURATION ---
$reservations_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$offset = ($current_page - 1) * $reservations_per_page;

// --- 3. FETCH DATA FROM DB ---
$current_reservations = [];
$total_reservations = 0;
$total_pages = 0;

if (isset($conn)) {
    // A. Count Total Reservations for this Guest
    $count_sql = "SELECT COUNT(*) FROM reservations WHERE guest_id = ?";
    $stmt = $conn->prepare($count_sql);
    if ($stmt) {
        $stmt->bind_param("i", $guest_id);
        $stmt->execute();
        $stmt->bind_result($total_reservations);
        $stmt->fetch();
        $stmt->close();
    }

    $total_pages = ceil($total_reservations / $reservations_per_page);
    
    // Adjust page if out of bounds
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
        $offset = ($current_page - 1) * $reservations_per_page;
    }

    // B. Fetch Reservation Details
    // Joins with rooms to get price/number, and categories for the name
    // Note: We handle both 'room_no' and 'room_number' column possibilities via aliases if needed
    $sql = "SELECT 
                r.id, 
                r.room_id, 
                r.check_in, 
                r.check_out, 
                r.status, 
                rm.room_no,      -- Assuming column name based on previous files. Change to rm.room_number if needed
                rm.price, 
                c.category_name 
            FROM reservations r
            JOIN rooms rm ON r.room_id = rm.id
            LEFT JOIN room_categories c ON rm.category_id = c.id
            WHERE r.guest_id = ?
            ORDER BY r.id DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iii", $guest_id, $reservations_per_page, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Calculate Total Cost dynamically (Days * Price)
            $in_date = strtotime($row['check_in']);
            $out_date = strtotime($row['check_out']);
            $datediff = $out_date - $in_date;
            $days = max(1, round($datediff / (60 * 60 * 24))); // Minimum 1 day
            $total_cost = $days * $row['price'];

            // Prepare display array
            $current_reservations[] = [
                "id" => $row['id'],
                "room_id" => $row['room_id'],
                "room_no" => $row['room_no'] ?? 'Room #' . $row['room_id'], // Fallback
                "category" => $row['category_name'] ?? 'Standard Room',
                "check_in" => $row['check_in'],
                "check_out" => $row['check_out'],
                "total_cost" => $total_cost,
                "status" => ucfirst($row['status']) // Capitalize for display/CSS
            ];
        }
        $stmt->close();
    }
}
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
            --badge-approved: #27ae60; /* Green for Approved */
            --badge-pending: #D4AF37;  /* Gold for Pending */
            --badge-rejected: #D32F2F; /* Red for Rejected */
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
            background-color: rgba(39, 174, 96, 0.15);
            color: var(--badge-approved);
            border: 1px solid var(--badge-approved);
        }
        .status-Pending {
            background-color: var(--badge-pending-bg);
            color: var(--badge-pending);
            border: 1px solid var(--badge-pending);
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
            <a href="logout.php" class="logout-btn" style="border: none; color: white;">Logout</a>
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
        <?php if ($total_pages > 1): ?>
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
        <?php endif; ?>

        <?php else: ?>
            <!-- No Reservations Message -->
            <div style="text-align: center; padding: 60px; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px dashed var(--color-gold);">
                <h3 style="color: var(--color-gold); margin-bottom: 15px;">No Reservations Found</h3>
                <p style="color: var(--color-text-muted); margin-bottom: 30px;">It looks like you haven't booked anything yet. Start your luxury stay!</p>
                <a href="available_rooms.php" class="btn-details" style="display: inline-block;">
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