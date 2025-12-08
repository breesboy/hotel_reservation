<?php
/**
 * AVAILABLE_ROOMS.PHP - Room Availability Listing Page
 * Displays a list of available rooms with pagination from the database.
 */

// Adjust the path if your config folder is located elsewhere (e.g., '../config/conn.php')
include_once '../config/conn.php'; 

session_start();
if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php" . $redirect_room);
    exit();
}

// --- 1. SESSION CHECK ---
$guest_name = isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : 'Guest';

// --- 2. PAGINATION CONFIGURATION ---
$rooms_per_page = 6;

// Get current page from GET request, ensure it's valid
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$offset = ($current_page - 1) * $rooms_per_page;

// --- 3. DATABASE FETCHING ---
$current_rooms = [];
$total_pages = 0;

if (isset($conn)) {
    // A. Get Total Count for Pagination
    $sql_count = "SELECT COUNT(*) as total FROM rooms";
    $result_count = $conn->query($sql_count);
    if ($result_count) {
        $row_count = $result_count->fetch_assoc();
        $total_rooms = $row_count['total'];
        $total_pages = ceil($total_rooms / $rooms_per_page);
    }

    // B. Get Rooms for Current Page
    // Join with rooms_categories to get the category name
    $sql_rooms = "SELECT r.*, c.category_name 
                  FROM rooms r 
                  LEFT JOIN room_categories c ON r.category_id = c.id 
                  LIMIT $rooms_per_page OFFSET $offset";
                  
    $result_rooms = $conn->query($sql_rooms);

    if ($result_rooms && $result_rooms->num_rows > 0) {
        while ($row = $result_rooms->fetch_assoc()) {
            // Map DB columns to the keys expected by the HTML template
            
            // Ensure 'image_url' exists even if DB column is named 'image'
            $row['image_url'] = isset($row['image']) ? $row['image'] : (isset($row['image_url']) ? $row['image_url'] : '');
            
            // Ensure room_no exists
            $row['room_no'] = isset($row['room_no']) ? $row['room_no'] : (isset($row['room_number']) ? $row['room_number'] : 'N/A');

            // Fallback for category name if join fails or is null
            if (empty($row['category_name'])) {
                $row['category_name'] = 'Standard Room'; 
            }

            $current_rooms[] = $row;
        }
    }
} else {
    // Fallback if connection fails
    $total_pages = 1;
}

// Handle edge case where user requests a page > total_pages
if ($current_page > $total_pages && $total_pages > 0) {
    header("Location: ?page=" . $total_pages);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms | Grand Luxe Hotel</title>
    
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
            --color-black: #000000;
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

        /* --- NAVIGATION (Reused from home.php) --- */
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

        .home-btn {
            border: 1px solid var(--color-gold);
            padding: 8px 16px;
            border-radius: 4px;
            color: var(--color-gold) !important;
        }
        .home-btn:hover {
            background-color: var(--color-gold);
            color: var(--color-navy) !important;
        }
        
        /* --- HERO SECTION --- */
        .hero {
            text-align: center;
            padding: 60px 20px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .hero p {
            color: var(--color-text-muted);
            font-size: 1.1rem;
            margin: 0 auto 30px;
        }

        .divider {
            width: 60px;
            height: 3px;
            background: var(--color-gold);
            margin: 0 auto;
            border-radius: 2px;
        }

        /* --- ROOMS GRID --- */
        .rooms-grid {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            flex-grow: 1;
        }

        .room-card {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid transparent;
        }

        .room-card:hover {
            transform: translateY(-5px);
            border-color: rgba(212, 175, 55, 0.4);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .room-image-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        .room-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .room-card:hover img {
            transform: scale(1.05);
        }

        .room-info {
            padding: 20px;
        }

        .room-category {
            font-size: 0.85rem;
            color: var(--color-gold);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .room-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--color-white);
        }
        
        .room-description {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-bottom: 15px;
            min-height: 40px;
        }

        .room-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
            margin-top: 15px;
        }

        .room-price {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--color-gold-hover);
        }
        .room-price span {
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--color-text-muted);
        }

        /* --- BUTTONS --- */
        .btn-detail {
            display: inline-block;
            background-color: var(--color-gold);
            color: var(--color-navy);
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-detail:hover {
            background-color: var(--color-gold-hover);
            box-shadow: 0 4px 10px rgba(212, 175, 55, 0.5);
            transform: translateY(-1px);
        }

        /* --- PAGINATION --- */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0 50px;
            gap: 10px;
        }

        .page-link {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 40px;
            height: 40px;
            background-color: var(--color-navy-light);
            color: var(--color-white);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            border: 1px solid #233554;
        }

        .page-link:hover {
            background-color: rgba(212, 175, 55, 0.15);
            color: var(--color-gold);
            border-color: var(--color-gold);
        }

        .page-link.active {
            background-color: var(--color-gold);
            color: var(--color-navy);
            font-weight: 600;
            border-color: var(--color-gold);
        }

        .page-link.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* --- FOOTER (Reused from home.php) --- */
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
            .rooms-grid {
                grid-template-columns: 1fr;
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
            <a href="home.php" class="home-btn">Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>

        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Available Rooms</h1>
        <div class="divider"></div>
        <p style="margin-top: 20px;">
            Select from our exclusive collection of luxury suites and rooms.
        </p>
    </section>

    <!-- Room Listings Grid -->
    <main class="rooms-grid">
        
        <?php foreach ($current_rooms as $room): ?>
            <div class="room-card">
                <div class="room-image-container">
                    <img 
                        src="<?php echo htmlspecialchars($room['image_url']); ?>" 
                        alt="<?php echo htmlspecialchars($room['category_name']); ?>"
                        onerror="this.onerror=null; this.src='https://placehold.co/400x250/0A1A2F/D4AF37?text=Luxury+Room'"
                    >
                </div>
                <div class="room-info">
                    <p class="room-category"><?php echo htmlspecialchars($room['category_name']); ?></p>
                    <h3 class="room-name"><?php echo htmlspecialchars($room['room_no']); ?></h3>
                    
                    <p class="room-description"><?php echo htmlspecialchars($room['description']); ?></p>

                    <div class="room-footer">
                        <span class="room-price">$<?php echo htmlspecialchars($room['price']); ?><span>/night</span></span>
                        <a href="room_details.php?id=<?php echo $room['id']; ?>" class="btn-detail">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($current_rooms)): ?>
            <p style="grid-column: 1 / -1; text-align: center; padding: 50px;">
                No rooms available matching your current search criteria.
            </p>
        <?php endif; ?>

    </main>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        
        <!-- Previous Button -->
        <?php $prev_page = $current_page - 1; ?>
        <a href="?page=<?php echo $prev_page; ?>" class="page-link <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
            &laquo; Prev
        </a>

        <!-- Page Numbers -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($i === $current_page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <!-- Next Button -->
        <?php $next_page = $current_page + 1; ?>
        <a href="?page=<?php echo $next_page; ?>" class="page-link <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
            Next &raquo;
        </a>

    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. All Rights Reserved.
    </footer>

</body>
</html>