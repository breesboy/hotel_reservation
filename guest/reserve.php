<?php
/**
 * RESERVE.PHP - Room Reservation Page
 * Handles the booking form display and database insertion for a reservation.
 */

include_once '../config/conn.php';
session_start();

// --- 1. SESSION CHECK ---
// Ensure user is logged in as a guest
if (!isset($_SESSION['guest_id'])) {
    // Redirect to login, passing the current room_id to return after login
    $redirect_room = isset($_GET['room_id']) ? "?redirect_room=" . intval($_GET['room_id']) : "";
    header("Location: login.php" . $redirect_room);
    exit();
}

$guest_id = $_SESSION['guest_id'];
$guest_name = isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : 'Guest';

// --- 2. FETCH ROOM DATA ---
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$room = null;
$error = "";
$success = "";

if (isset($conn) && $room_id > 0) {
    // Fetch room details to display summary
    $sql = "SELECT r.*, c.category_name 
            FROM rooms r 
            LEFT JOIN room_categories c ON r.category_id = c.id 
            WHERE r.id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $room = $result->fetch_assoc();
            
            // Normalize data (handle different column names)
            if (!empty($room['category_name'])) $room['category'] = $room['category_name'];
            if (!isset($room['room_no']) && isset($room['room_number'])) $room['room_no'] = $room['room_number'];
            if (empty($room['image_url']) && !empty($room['image'])) $room['image_url'] = $room['image'];
        } else {
            $error = "Room not found.";
        }
        $stmt->close();
    }
} else {
    $error = "Invalid Room ID.";
}

// --- 3. HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $room) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    
    // Basic Validation
    if (strtotime($check_in) < strtotime(date('Y-m-d'))) {
        $error = "Check-in date cannot be in the past.";
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $error = "Check-out date must be after check-in date.";
    } else {
        // Insert Reservation
        $insert_sql = "INSERT INTO reservations (guest_id, room_id, check_in, check_out, status) VALUES (?, ?, ?, ?, 'Pending')";
        $insert_stmt = $conn->prepare($insert_sql);
        
        if ($insert_stmt) {
            $insert_stmt->bind_param("iiss", $guest_id, $room_id, $check_in, $check_out);
            
            if ($insert_stmt->execute()) {
                $success = "Reservation request submitted successfully!";
                // Optional: Redirect to a 'My Reservations' page after a delay
                // header("Refresh: 2; url=my_reservations.php"); 
            } else {
                $error = "Failed to book room. Please try again. " . $conn->error;
            }
            $insert_stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Room | Grand Luxe Hotel</title>
    
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
            --color-danger: #ff7675;
            --color-success: #27ae60;
            --shadow-card: 0 10px 40px rgba(0, 0, 0, 0.6);
            --border-radius: 12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

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
        .nav-links a:hover { color: var(--color-gold); }

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

        /* --- LAYOUT --- */
        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 20px;
            flex-grow: 1;
        }

        .page-title {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--color-white);
            margin-bottom: 40px;
        }

        .booking-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
            align-items: start;
        }

        /* --- ROOM SUMMARY CARD (LEFT) --- */
        .room-summary {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .summary-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .summary-content {
            padding: 25px;
        }

        .summary-category {
            color: var(--color-gold);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .summary-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .summary-price {
            font-size: 1.2rem;
            color: var(--color-text-muted);
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 15px;
        }
        .summary-price strong {
            color: var(--color-white);
            font-size: 1.4rem;
        }

        /* --- BOOKING FORM (RIGHT) --- */
        .booking-form-card {
            background-color: var(--color-navy-light);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            border-top: 4px solid var(--color-gold);
        }

        .form-header {
            margin-bottom: 30px;
        }
        
        .form-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: var(--color-text-muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--color-gold);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        input[type="date"] {
            width: 100%;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid #233554;
            border-radius: 6px;
            color: var(--color-white);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s;
            color-scheme: dark; /* Ensures calendar icon is light */
        }

        input[type="date"]:focus {
            border-color: var(--color-gold);
            outline: none;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);
        }

        .btn-confirm {
            width: 100%;
            padding: 16px;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-confirm:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--color-text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .btn-cancel:hover { color: var(--color-white); }

        /* --- MESSAGES --- */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            border: 1px solid transparent;
            text-align: center;
        }
        .alert-error {
            background-color: rgba(255, 118, 117, 0.1);
            border-color: rgba(255, 118, 117, 0.3);
            color: var(--color-danger);
        }
        .alert-success {
            background-color: rgba(39, 174, 96, 0.1);
            border-color: rgba(39, 174, 96, 0.3);
            color: var(--color-success);
        }

        .success-actions {
            text-align: center;
            margin-top: 20px;
        }
        .btn-home {
            display: inline-block;
            border: 1px solid var(--color-gold);
            padding: 10px 25px;
            color: var(--color-gold);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .btn-home:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
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
            .booking-wrapper {
                grid-template-columns: 1fr;
            }
            .room-summary {
                display: flex;
                align-items: center;
            }
            .summary-img {
                width: 120px;
                height: 100%;
            }
            header {
                flex-direction: column;
                gap: 15px;
            }
        }
        @media (max-width: 500px) {
            .room-summary {
                flex-direction: column;
            }
            .summary-img {
                width: 100%;
                height: 180px;
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
            <a href="home.php" class="dashboard-btn">Dashboard</a>
            <a href="logout.php" class="dashboard-btn" style="border: none; color: white !important;">Logout</a>
        </nav>
    </header>

    <main class="container">
        
        <h1 class="page-title">Secure Your Reservation</h1>

        <?php if ($success): ?>
            <!-- SUCCESS STATE -->
            <div class="booking-form-card" style="max-width: 600px; margin: 0 auto; text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--color-gold); margin-bottom: 15px;">Booking Confirmed</h3>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
                <p style="color: var(--color-text-muted); margin-bottom: 30px;">
                    Your reservation for <strong><?php echo htmlspecialchars($room['room_no']); ?></strong> has been placed successfully. 
                    <br>Status: <span style="color: #f39c12;">Pending Approval</span>
                </p>
                <div class="success-actions">
                    <a href="home.php" class="btn-home">Return to Dashboard</a>
                    <a href="available_rooms.php" class="btn-cancel">Browse More Rooms</a>
                </div>
            </div>

        <?php elseif ($room): ?>
            <!-- BOOKING FORM STATE -->
            <div class="booking-wrapper">
                
                <!-- Room Summary (Left) -->
                <div class="room-summary">
                    <img src="<?php echo htmlspecialchars($room['image_url']); ?>" 
                         alt="Room Preview" class="summary-img"
                         onerror="this.onerror=null; this.src='https://placehold.co/600x400/0A1A2F/D4AF37?text=Grand+Luxe';">
                    <div class="summary-content">
                        <p class="summary-category"><?php echo htmlspecialchars($room['category']); ?></p>
                        <h2 class="summary-title"><?php echo htmlspecialchars($room['room_no']); ?></h2>
                        <div class="summary-price">
                            <strong>$<?php echo number_format($room['price'], 2); ?></strong> / night
                        </div>
                    </div>
                </div>

                <!-- Reservation Form (Right) -->
                <div class="booking-form-card">
                    <div class="form-header">
                        <h3>Select Dates</h3>
                        <p>Please select your check-in and check-out dates to continue.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="check_in">Check-in Date</label>
                            <input type="date" id="check_in" name="check_in" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="check_out">Check-out Date</label>
                            <input type="date" id="check_out" name="check_out" required 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>

                        <!-- TOTAL COST DISPLAY -->
                        <div id="cost-breakdown" style="display: none; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid rgba(212, 175, 55, 0.2);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; color: var(--color-text-muted); font-size: 0.9rem;">
                                <span>Price per night:</span>
                                <span>$<?php echo number_format($room['price'], 2); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--color-text-muted); font-size: 0.9rem;">
                                <span>Total Nights:</span>
                                <span id="display-nights">0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; font-size: 1.2rem; color: var(--color-gold); font-weight: 700;">
                                <span>Total Cost:</span>
                                <span id="display-total">$0.00</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-confirm">Confirm Booking</button>
                        <a href="room_details.php?id=<?php echo $room_id; ?>" class="btn-cancel">Cancel</a>
                    </form>
                </div>

            </div>

            <!-- JavaScript for Dynamic Cost Calculation -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const checkInInput = document.getElementById('check_in');
                    const checkOutInput = document.getElementById('check_out');
                    const costBreakdown = document.getElementById('cost-breakdown');
                    const displayNights = document.getElementById('display-nights');
                    const displayTotal = document.getElementById('display-total');
                    
                    // Get room price from PHP (ensure it's a valid number)
                    const roomPrice = <?php echo floatval($room['price']); ?>;

                    function calculateCost() {
                        const inDateVal = checkInInput.value;
                        const outDateVal = checkOutInput.value;

                        if (inDateVal && outDateVal) {
                            const inDate = new Date(inDateVal);
                            const outDate = new Date(outDateVal);

                            // Calculate difference in milliseconds
                            const diffTime = outDate - inDate;
                            
                            // Convert to days (1000ms * 60s * 60m * 24h)
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                            if (diffDays > 0) {
                                const totalCost = diffDays * roomPrice;
                                
                                displayNights.textContent = diffDays;
                                displayTotal.textContent = '$' + totalCost.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'); // Format currency
                                costBreakdown.style.display = 'block';
                            } else {
                                costBreakdown.style.display = 'none'; // Invalid dates
                            }
                        } else {
                            costBreakdown.style.display = 'none';
                        }
                    }

                    // Attach event listeners
                    checkInInput.addEventListener('change', calculateCost);
                    checkOutInput.addEventListener('change', calculateCost);
                });
            </script>

        <?php else: ?>
            
            <!-- ERROR STATE (Room not found) -->
            <div class="alert alert-error" style="max-width: 600px; margin: 50px auto;">
                <?php echo $error ? $error : "Room not found or unavailable."; ?>
                <br><br>
                <a href="available_rooms.php" style="color: inherit; text-decoration: underline;">Go back to rooms list</a>
            </div>

        <?php endif; ?>

    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. All Rights Reserved.
    </footer>

</body>
</html>