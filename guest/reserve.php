<?php
/**
 * RESERVE.PHP - Room Reservation Page
 * Allows authenticated users to select check-in/out dates and confirm a reservation
 * for a specific room.
 */

session_start();

// --- 1. SESSION CHECK ---
// Redirect to login if not authenticated
// if (!isset($_SESSION['guest_id'])) {
//     header("Location: login.php");
//     exit();
// }

//$guest_id = $_SESSION['guest_id'];
$guest_name = isset($_SESSION['guest_name']) ? htmlspecialchars($_SESSION['guest_name']) : 'Guest';

// --- 2. SAMPLE DYNAMIC ROOM DATA GENERATION ---
$rooms_data = [];
$room_details_base = [
    "Deluxe Suite" => ["price" => 280, "description_suffix" => "sublime elegance and spacious comfort."],
    "Executive King" => ["price" => 220, "description_suffix" => "a dedicated workspace and executive privileges."],
    "Ocean View Balcony" => ["price" => 350, "description_suffix" => "breathtaking panoramic sea views and luxury amenities."],
    "Standard Premium" => ["price" => 180, "description_suffix" => "exceptional value without compromising quality or style."]
];
$room_keys = array_keys($room_details_base);

// Populate 20 sample rooms
for ($i = 1; $i <= 20; $i++) {
    $key_index = ($i - 1) % 4;
    $key = $room_keys[$key_index];
    $detail = $room_details_base[$key];

    $rooms_data[$i] = [
        "id" => $i,
        "room_no" => "Room " . str_pad($i, 3, '0', STR_PAD_LEFT),
        "category" => $key,
        "price" => $detail['price'] + rand(-20, 20),
        "description" => "This " . strtolower($key) . " offers " . $detail['description_suffix'],
        "image_url" => "https://placehold.co/400x250/0A1A2F/D4AF37?text=" . urlencode(str_replace(' ', '+', $key))
    ];
}

// --- 3. DYNAMIC ROOM LOOKUP ---
$room_id = $_GET['room_id'] ?? 0;
$room_id = is_numeric($room_id) ? (int)$room_id : 0;
$room = $rooms_data[$room_id] ?? null;

// Handle missing room ID or invalid room
if (!$room) {
    // Note: In a real system, this would redirect to available_rooms.php or show a full error page.
    $error_message = "Error: Invalid room selection. Please return to the listing.";
    $room_id = 0; // Prevent linking to invalid reservation action
}

// Sanitize room data for display
if ($room) {
    $room_price = htmlspecialchars($room['price']);
    $room_no = htmlspecialchars($room['room_no']);
    $room_category = htmlspecialchars($room['category']);
    $room_description = htmlspecialchars($room['description']);
}

// --- 4. FORM SUBMISSION HANDLING ---
$reservation_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $room) {
    $check_in = filter_input(INPUT_POST, 'check_in_date', FILTER_SANITIZE_STRING);
    $check_out = filter_input(INPUT_POST, 'check_out_date', FILTER_SANITIZE_STRING);
    $guest_id_submitted = filter_input(INPUT_POST, 'guest_id', FILTER_SANITIZE_STRING);

    // Basic Date Validation
    $ts_check_in = strtotime($check_in);
    $ts_check_out = strtotime($check_out);
    $today = strtotime(date('Y-m-d'));

    if ($ts_check_in < $today || $ts_check_out <= $ts_check_in) {
        $reservation_message = '<div class="message error-message">Validation Error: Check-in date must be today or later, and check-out must be after check-in.</div>';
    } else {
        // Calculate costs
        $nights = ($ts_check_out - $ts_check_in) / (60 * 60 * 24);
        $total_cost = $nights * $room['price'];
        
        // --- Successful Reservation Simulation ---
        $reservation_message = "
            <div class='message success-message'>
                <h3>Reservation Request Submitted Successfully!</h3>
                <p><strong>Room:</strong> {$room_no} ({$room_category})</p>
                <p><strong>Check-in:</strong> {$check_in}</p>
                <p><strong>Check-out:</strong> {$check_out}</p>
                <p><strong>Nights:</strong> {$nights}</p>
                <p><strong>Total Cost:</strong> $<span class='cost-display'>{$total_cost}</span></p>
                <p>A confirmation email will be sent to your registered address.</p>
            </div>
        ";
    }
}

// Set minimum date for input fields to today
$min_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Room <?php echo $room_id; ?> | Grand Luxe Hotel</title>
    
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
            --input-radius: 6px;
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
            max-width: 900px;
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
        
        /* --- CARD LAYOUT --- */
        .luxury-card {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        /* --- ROOM INFO SECTION --- */
        .room-info-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .room-info-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--color-gold);
            margin-bottom: 5px;
        }

        .room-info-header h2 {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--color-white);
            margin-bottom: 10px;
        }
        
        .price-tag-large {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--color-gold-hover);
        }
        .price-tag-large span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--color-text-muted);
        }
        
        .room-info-header p {
            color: var(--color-text-muted);
            margin-top: 10px;
        }

        /* --- FORM STYLING --- */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--color-gold);
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--color-white);
            border: 1px solid rgba(212, 175, 55, 0.5);
            border-radius: var(--input-radius);
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--color-gold-hover);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
        }
        
        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* --- TOTAL COST DISPLAY --- */
        .total-cost-display {
            background-color: var(--color-navy);
            padding: 15px;
            border-radius: var(--input-radius);
            margin-top: 20px;
            text-align: center;
            border: 2px dashed var(--color-gold);
        }

        .total-cost-display p {
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0;
            color: var(--color-white);
        }

        .total-cost-display strong {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--color-gold-hover);
            margin-left: 10px;
        }

        /* --- BUTTONS --- */
        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-submit {
            flex-grow: 1;
            background-color: var(--color-gold);
            color: var(--color-navy);
            padding: 15px 30px;
            border: none;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
            max-width: 300px;
        }

        .btn-submit:hover {
            background-color: var(--color-gold-hover);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.6);
            transform: translateY(-2px);
        }
        
        .btn-back {
            display: inline-block;
            background-color: transparent;
            color: var(--color-gold);
            border: 2px solid var(--color-gold);
            padding: 15px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            max-width: 300px;
        }

        .btn-back:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: var(--color-white);
        }

        /* --- MESSAGES (Success/Error) --- */
        .message {
            padding: 20px;
            border-radius: var(--input-radius);
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background-color: rgba(67, 160, 71, 0.2);
            border: 2px solid #4CAF50;
            color: #A5D6A7;
        }
        .error-message {
            background-color: rgba(211, 47, 47, 0.2);
            border: 2px solid #D32F2F;
            color: #EF9A9A;
        }
        .message h3 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 10px;
            color: var(--color-gold-hover);
        }
        .message p {
            margin: 5px 0;
            font-size: 0.95rem;
        }
        .cost-display {
            font-weight: 700;
            color: var(--color-gold-hover);
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
        @media (max-width: 600px) {
            .luxury-card {
                padding: 30px 20px;
            }
            .date-inputs {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-submit, .btn-back {
                max-width: 100%;
            }
            .room-info-header h1 {
                font-size: 2rem;
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
            <a href="available_rooms.php">Rooms</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main class="container">

        <?php 
        // Display any reservation message (success or error)
        echo $reservation_message; 

        // Only show the reservation form if a valid room was found AND no success message is set
        if ($room && empty(strip_tags($reservation_message))) : 
        ?>
        
        <div class="luxury-card">
            
            <!-- Room Information Header -->
            <div class="room-info-header">
                <p style="color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem;">Confirming Reservation For</p>
                <h1><?php echo $room_no; ?></h1>
                <h2><?php echo $room_category; ?></h2>
                <p class="price-tag-large">
                    $<?php echo $room_price; ?>
                    <span>per night</span>
                </p>
                <p><?php echo $room_description; ?></p>
            </div>

            <!-- Reservation Form -->
            <form action="reserve.php?room_id=<?php echo $room_id; ?>" method="POST" id="reservation-form">
                
                <input type="hidden" name="guest_id" value="<?php echo $guest_id; ?>">
                
                <div class="date-inputs">
                    <!-- Check-in Date -->
                    <div class="form-group">
                        <label for="check_in_date">Check-in Date</label>
                        <input 
                            type="date" 
                            id="check_in_date" 
                            name="check_in_date" 
                            class="form-input" 
                            required 
                            min="<?php echo $min_date; ?>"
                            value="<?php echo $min_date; ?>"
                            onchange="calculateTotalCost(<?php echo $room['price']; ?>)"
                        >
                    </div>

                    <!-- Check-out Date -->
                    <div class="form-group">
                        <label for="check_out_date">Check-out Date</label>
                        <input 
                            type="date" 
                            id="check_out_date" 
                            name="check_out_date" 
                            class="form-input" 
                            required 
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                            value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                            onchange="calculateTotalCost(<?php echo $room['price']; ?>)"
                        >
                    </div>
                </div>

                <!-- Total Cost Display (Calculated by JS) -->
                <div class="total-cost-display">
                    <p>Estimated Total Cost: 
                        <strong id="total-cost">$<?php echo $room_price; ?></strong> 
                        <span style="font-size: 1rem; font-weight: 400; color: var(--color-text-muted);">
                             (for 1 night)
                        </span>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="room_details.php?id=<?php echo $room_id; ?>" class="btn-back">
                        &larr; Back to Room Details
                    </a>
                    <button type="submit" class="btn-submit">
                        Confirm Reservation
                    </button>
                </div>
            </form>
        </div>

        <?php else: 
            // Display if room is invalid/missing AND no successful reservation message was generated
            if (!$room && empty(strip_tags($reservation_message))):
        ?>
            <div class="luxury-card" style="text-align: center; padding: 60px;">
                <h3 style="color: #D32F2F;">Room Not Found</h3>
                <p style="color: var(--color-text-muted); margin-top: 15px;">The room you are attempting to reserve is invalid or was not specified.</p>
                <div class="action-buttons">
                    <a href="available_rooms.php" class="btn-back" style="margin-top: 20px;">
                        &larr; Browse Available Rooms
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php endif; ?>
        
    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. All Rights Reserved.
    </footer>

    <!-- JavaScript for Dynamic Cost Calculation -->
    <script>
        /**
         * Calculates the total reservation cost based on dates and room price.
         * @param {number} pricePerNight - The nightly rate of the room.
         */
        function calculateTotalCost(pricePerNight) {
            const checkInDate = document.getElementById('check_in_date').value;
            const checkOutDate = document.getElementById('check_out_date').value;
            const totalCostElement = document.getElementById('total-cost');
            const costContainer = totalCostElement.parentElement;
            
            if (!checkInDate || !checkOutDate) {
                totalCostElement.innerHTML = '$0.00';
                costContainer.querySelector('span').textContent = '';
                return;
            }

            const date1 = new Date(checkInDate);
            const date2 = new Date(checkOutDate);

            // Ensure check-out is after check-in
            if (date2 <= date1) {
                // Set check-out to the day after check-in
                const nextDay = new Date(date1);
                nextDay.setDate(date1.getDate() + 1);
                document.getElementById('check_out_date').value = nextDay.toISOString().split('T')[0];
                date2.setTime(nextDay.getTime());
            }

            // Calculate difference in days (nights)
            const timeDiff = date2.getTime() - date1.getTime();
            const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));

            if (nights > 0) {
                const total = (nights * pricePerNight).toFixed(2);
                totalCostElement.innerHTML = `$${total}`;
                costContainer.querySelector('span').textContent = `(for ${nights} night${nights > 1 ? 's' : ''})`;
            } else {
                totalCostElement.innerHTML = '$0.00';
                costContainer.querySelector('span').textContent = '(for 0 nights)';
            }
            
            // Set min date for check-out to be the day after check-in
            const nextDayISO = new Date(date1);
            nextDayISO.setDate(date1.getDate() + 1);
            document.getElementById('check_out_date').min = nextDayISO.toISOString().split('T')[0];
        }

        // Initial calculation on load
        window.onload = function() {
            // Check if PHP successfully found the room price
            const roomPrice = <?php echo $room ? $room['price'] : 0; ?>;
            if (roomPrice > 0) {
                calculateTotalCost(roomPrice);
            }
        };
    </script>

</body>
</html>