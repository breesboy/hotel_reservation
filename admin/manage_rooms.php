<?php
/**
 * MANAGE_ROOMS.PHP - Grand Luxe Admin
 * Displays all hotel rooms with pagination and management actions.
 * Matches the exact design of the Guest/Available Rooms page.
 */
include '../config/conn.php';
session_start();

// --- 1. SESSION CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Administrator';

// --- 2. MOCK DATA GENERATION ---
$rooms = [];
for ($i = 1; $i <= 20; $i++) {
    // Generate data similar to guest page for visual consistency
    $category_index = ($i - 1) % 4;    

    // Fetch real data from database
    /* Fetch categories and rooms from database */
    $categories = [];
    $fetched_rooms = [];

    // Support both PDO and mysqli connections from ../config/conn.php

            $res = mysqli_query($conn, "SELECT id, category_name FROM room_categories");
            if ($res) {
                while ($r = mysqli_fetch_assoc($res)) {
                    
                    $categories[$r['id']] = $r['category_name'];
                    
                }
                mysqli_free_result($res);
            }
            $stmt2 = $conn->query("SELECT * FROM room_categories");
                if ($stmt2->num_rows > 0) {
                    while ($row = $stmt2->fetch_assoc()) {
                        $categories[$row['id']] = $row['category_name'];
                    }
                }


            $res = mysqli_query($conn, "SELECT id, room_no, price, image, category_id FROM rooms ORDER BY id ASC");
            if ($res) {
                while ($r = mysqli_fetch_assoc($res)) {
                    $fetched_rooms[] = $r;
                }
                mysqli_free_result($res);
            }

        
    }

    foreach ($fetched_rooms as $r) {
        $rooms[] = [
            'id' => $r['id'],
            // keep both keys used in template to avoid undefined index notices
            'room_no' => isset($r['room_no']) ? $r['room_no'] : "Room {$r['id']}",
            'room_number' => isset($r['room_no']) ? $r['room_no'] : "Room {$r['id']}",
            'price' => isset($r['price']) ? number_format((float)$r['price'], 2) : '0.00',
            'image' => !empty($r['image']) ? $r['image'] : 'https://placehold.co/400x250/0A1A2F/D4AF37?text=Luxury+Room',
            'category' => (isset($r['category_id']) && isset($categories['category_id'])) ? $categories[$r['category_id']] : 'Standard',
        ];
    }



// --- 3. PAGINATION LOGIC ---
$rooms_per_page = 6;
$total_rooms = count($rooms);
$total_pages = ceil($total_rooms / $rooms_per_page);

// Get current page from GET request, ensure it's valid
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages) $current_page = $total_pages;

$start_index = ($current_page - 1) * $rooms_per_page;
$current_rooms = array_slice($rooms, $start_index, $rooms_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms | Grand Luxe Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        /* --- CSS VARIABLES (MATCHING GUEST PAGE) --- */
        :root {
            --color-navy: #0A1A2F;
            --color-navy-light: #112240;
            --color-gold: #D4AF37;
            --color-gold-hover: #F4C430;
            --color-white: #FFFFFF;
            --color-black: #000000;
            --color-text-muted: #8892b0;
            --color-danger: #d63031;
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

        .btn-nav {
            border: 1px solid var(--color-gold);
            padding: 8px 16px;
            border-radius: 4px;
            color: var(--color-gold) !important;
        }
        .btn-nav:hover {
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
            color: var(--color-white);
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

        /* Guest Card Styling */
        .room-card {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid transparent;
            display: flex;
            flex-direction: column;
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
            flex: 1;
            display: flex;
            flex-direction: column;
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
        
        /* Admin specific: room footer */
        .room-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
            margin-top: auto; /* Push to bottom */
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
        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-edit {
            background-color: transparent;
            border: 1px solid var(--color-gold);
            color: var(--color-gold);
        }
        .btn-edit:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
        }

        .btn-delete {
            background-color: transparent;
            border: 1px solid rgba(214, 48, 49, 0.5);
            color: #fab1a0;
        }
        .btn-delete:hover {
            background-color: var(--color-danger);
            color: white;
            border-color: var(--color-danger);
        }

        .btn-add {
            background-color: var(--color-gold);
            color: var(--color-navy);
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-add:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
        }

        /* --- PAGINATION (EXACT GUEST STYLE) --- */
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
            padding: 0 10px;
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
            h1 { font-size: 2.2rem; }
            header {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
            }
            .nav-links { margin-top: 10px; }
            .rooms-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="brand">Grand Luxe Admin</div>
        <nav class="nav-links">
            <span style="color: var(--color-text-muted); margin-right: 15px;">Welcome, <?php echo $admin_username; ?></span>
            <a href="dashboard.php" class="btn-nav">Dashboard</a>
            <a href="logout.php" style="margin-left: 20px;">Logout</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Manage Rooms</h1>
        <div class="divider"></div>
        <p style="margin-top: 20px;">
            Oversee room inventory, pricing, and availability.
        </p>
        <a href="add_room.php" class="btn-add">+ Add New Room</a>
    </section>

    <!-- Room Listings Grid -->
    <main class="rooms-grid">
        
        <?php foreach ($current_rooms as $room): ?>
            <div class="room-card">
                <div class="room-image-container">
                    <img 
                        src="<?php echo htmlspecialchars($room['image']); ?>" 
                        alt="<?php echo htmlspecialchars($room['category']); ?>"
                        onerror="this.onerror=null; this.src='https://placehold.co/400x250/0A1A2F/D4AF37?text=Luxury+Room'"
                    >
                </div>
                <div class="room-info">
                    <p class="room-category"><?php echo htmlspecialchars($room['category']); ?></p>
                    <h3 class="room-name"><?php echo htmlspecialchars($room['room_no']); ?></h3>
                    
                    <div class="room-footer">
                        <span class="room-price">$<?php echo htmlspecialchars($room['price']); ?><span>/night</span></span>
                        
                        <div class="btn-group">
                            <a href="edit_room.php?id=<?php echo $room['id']; ?>" class="btn-action btn-edit">Edit</a>
                            <a href="../config/delete_room.php?id=<?php echo $room['id']; ?>" 
                               class="btn-action btn-delete"
                               onclick="return confirm('Are you sure you want to delete <?php echo $room['room_number']; ?>?');">
                               Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($current_rooms)): ?>
            <p style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--color-text-muted);">
                No rooms found in the system.
            </p>
        <?php endif; ?>

    </main>

    <!-- Pagination Controls (Matched to Guest Design) -->
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

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. Admin Control Panel.
    </footer>

</body>
</html>