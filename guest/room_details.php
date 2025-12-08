<?php
/**
 * ROOM_DETAILS.PHP - Specific Room Details Page
 * Displays detailed information about a single room retrieved dynamically from the database.
 */

// Adjust the path if your config folder is located elsewhere
include_once '../config/conn.php'; 

session_start();
if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit();
}
// --- 1. SESSION CHECK ---
$guest_name = isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : 'Guest';

// --- 2. FETCH ROOM DATA ---
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$room = null;

// Define base amenities for fallback if specific amenities aren't in the DB
// This ensures rich content is displayed based on the category
$room_details_fallback = [
    "Deluxe" => ["amenities" => ["King Size Bed", "Private Balcony", "Marble Bathroom", "Nespresso Machine", "High-Speed Wi-Fi"]],
    "Executive" => ["amenities" => ["King Size Bed", "Dedicated Workspace", "Complimentary Breakfast", "Smart TV", "Mini Bar"]],
    "Ocean" => ["amenities" => ["Two Queen Beds", "Panoramic Sea View", "Ensuite Jacuzzi", "Luxury Toiletries", "Robes & Slippers"]],
    "Standard" => ["amenities" => ["Queen Size Bed", "Air Conditioning", "In-Room Safe", "Complimentary Water", "Fast Check-in"]]
];
// Generic default
$default_amenities = ["Queen Size Bed", "Ensuite Bathroom", "Free Wi-Fi", "TV", "Coffee Maker"];

if (isset($conn) && $room_id > 0) {
    // JOIN rooms with rooms_categories to get the actual category name
    // UPDATED: Using c.category_name assuming the table has 'category_id' (PK) and 'category_name' (Label)
    $sql = "SELECT r.*, c.category_name 
            FROM rooms r 
            LEFT JOIN room_categories c ON r.category_id = c.id 
            WHERE r.id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        // Debugging: Output error if query preparation fails
        die("Database Error: " . $conn->error);
    }
    
    if ($stmt) {
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $room = $result->fetch_assoc();
            
            // --- DATA NORMALIZATION ---
            
            // 0. Category Name from Join
            // Use category_name from the join if available
            if (!empty($room['category_name'])) {
                $room['category'] = $room['category_name'];
            }

            // 1. Room Number
            if (!isset($room['room_no']) && isset($room['room_number'])) {
                $room['room_no'] = $room['room_number'];
            }
            
            // 2. Image URL
            if (empty($room['image_url']) && !empty($room['image'])) {
                $room['image_url'] = $room['image'];
            }
            
            // 3. Description Fallback
            if (empty($room['description'])) {
                $category_text = strtolower($room['category'] ?? 'room');
                $room['description'] = "Experience unparalleled comfort in this {$category_text}. Designed with discerning travelers in mind, it offers sublime elegance and modern convenience. This room boasts exquisite furnishings, bespoke artwork, and the highest quality linens to ensure a restful and memorable stay.";
            }

            // 4. Amenities Logic
            if (!empty($room['amenities'])) {
                $room['amenities'] = array_map('trim', explode(',', $room['amenities']));
            } else {
                // Match category keyword to fallback list
                $cat = $room['category'] ?? '';
                $found_amenities = $default_amenities;
                
                foreach ($room_details_fallback as $keyword => $details) {
                    if (stripos($cat, $keyword) !== false) {
                        $found_amenities = $details['amenities'];
                        break;
                    }
                }
                $room['amenities'] = $found_amenities;
            }
        }
        $stmt->close();
    }
}

// --- 3. SANITIZATION FOR DISPLAY ---
if ($room) {
    $room['room_no'] = htmlspecialchars($room['room_no']);
    $room['category'] = htmlspecialchars($room['category']);
    // Price formatted to 2 decimals
    $room['price'] = number_format((float)$room['price'], 2); 
    // Description is htmlspecialchars sanitized
    $room['description'] = htmlspecialchars($room['description']);
    // Amenities are sanitized during the loop in HTML
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $room ? "{$room['room_no']} Details" : "Room Not Found"; ?> | Grand Luxe Hotel</title>
    
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
            --shadow-card: 0 8px 30px rgba(0, 0, 0, 0.4);
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

        /* --- UTILITIES & LAYOUT --- */
        .container {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
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
            color: var(--color-gold);
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
        
        /* --- ROOM DETAILS HEADER --- */
        .details-header {
            text-align: center;
            padding: 40px 20px 20px;
        }

        .details-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: var(--color-gold);
            margin-bottom: 5px;
        }

        .details-header h2 {
            font-size: 1.5rem;
            font-weight: 400;
            color: var(--color-white);
            margin-bottom: 20px;
        }

        .divider-wide {
            width: 150px;
            height: 3px;
            background: var(--color-gold);
            margin: 20px auto 0;
            border-radius: 2px;
        }

        /* --- ROOM CARD LAYOUT --- */
        .room-content {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            padding: 30px;
            margin-bottom: 50px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .room-image-section img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            max-height: 500px;
            object-fit: cover;
        }
        
        .room-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        /* --- INFO AND AMENITIES --- */
        .info-block {
            padding-right: 20px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--color-gold);
            margin-bottom: 15px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.1);
            padding-bottom: 5px;
        }

        .price-tag {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-gold-hover);
            margin-bottom: 20px;
            display: block;
        }
        .price-tag span {
            font-size: 1.2rem;
            font-weight: 400;
            color: var(--color-text-muted);
        }

        .description-text {
            color: var(--color-text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .amenities-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .amenities-list li {
            background-color: rgba(212, 175, 55, 0.1);
            color: var(--color-white);
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .amenities-list li::before {
            content: "✨";
            margin-right: 10px;
            color: var(--color-gold);
        }

        /* --- BUTTONS --- */
        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            justify-content: center;
        }
        
        .btn-reserve {
            display: inline-block;
            background-color: var(--color-gold);
            color: var(--color-navy);
            padding: 18px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
        }

        .btn-reserve:hover {
            background-color: var(--color-gold-hover);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.6);
            transform: translateY(-2px);
        }

        .btn-back {
            display: inline-block;
            background-color: transparent;
            color: var(--color-gold);
            border: 2px solid var(--color-gold);
            padding: 18px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: var(--color-white);
        }

        /* --- ERROR CARD --- */
        .error-card {
            background-color: #331A1A;
            border: 2px solid #D9534F;
            color: #FFCCCC;
            padding: 50px;
            text-align: center;
            border-radius: var(--border-radius);
            margin: 50px auto;
            max-width: 600px;
        }
        .error-card h3 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .error-card p {
            font-size: 1.1rem;
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
        @media (max-width: 900px) {
            .room-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .details-header h1 { font-size: 2.5rem; }
            .details-header h2 { font-size: 1.2rem; }
            .room-content { padding: 20px; }
            .action-buttons { flex-direction: column; }
            .btn-reserve, .btn-back { width: 100%; padding: 15px; }
            .amenities-list { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="brand">Grand Luxe</div>
        <nav class="nav-links">
            <span style="color: var(--color-text-muted); margin-right: 15px;">Hello, <?php echo $guest_name; ?></span>
            <a href="home.php" class="dashboard-btn">Dashboard</a>
            <a href="logout.php" class="dashboard-btn" style="border: 1px solid transparent; color: white !important;">Logout</a>
        </nav>
    </header>

    <main class="container">

        <?php if ($room): ?>
            
            <!-- Room Details Hero Header -->
            <section class="details-header">
                <h1><?php echo $room['room_no']; ?></h1>
                <h2><?php echo $room['category']; ?></h2>
                <div class="divider-wide"></div>
            </section>
            
            <!-- Room Content Card -->
            <div class="room-content">
                
                <!-- Image Section -->
                <div class="room-image-section">
                    <img 
                        src="<?php echo htmlspecialchars($room['image_url']); ?>" 
                        alt="<?php echo $room['category']; ?>"
                        onerror="this.onerror=null; this.src='https://placehold.co/800x500/0A1A2F/D4AF37?text=Grand+Luxe+Interior';"
                    >
                </div>
                
                <div class="room-grid">
                    
                    <!-- Information Block -->
                    <div class="info-block">
                        <span class="price-tag">
                            $<?php echo $room['price']; ?>
                            <span>per night</span>
                        </span>
                        
                        <h3 class="section-title">Description</h3>
                        <p class="description-text"><?php echo $room['description']; ?></p>
                    </div>

                    <!-- Amenities Block -->
                    <div class="amenities-block">
                        <h3 class="section-title">Amenities Included</h3>
                        <ul class="amenities-list">
                            <?php foreach ($room['amenities'] as $amenity): ?>
                                <li><?php echo htmlspecialchars($amenity); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="available_rooms.php" class="btn-back">
                        &larr; Back to Available Rooms
                    </a>
                    <a href="reserve.php?room_id=<?php echo $room_id; ?>" class="btn-reserve">
                        Reserve This Room
                    </a>
                </div>

            </div>

        <?php else: ?>

            <!-- Error Card -->
            <div class="error-card">
                <h3>Room Not Found</h3>
                <p>The requested room details could not be loaded. Please check the room ID or return to the room listing.</p>
                <div class="action-buttons" style="margin-top: 30px;">
                    <a href="available_rooms.php" class="btn-back" style="padding: 10px 20px;">
                        &larr; Back to Listing
                    </a>
                </div>
            </div>

        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. All Rights Reserved.
    </footer>

</body>
</html>