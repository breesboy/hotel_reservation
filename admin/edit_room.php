<?php
/**
 * ADMIN/EDIT_ROOM.PHP - Edit Existing Room Details
 * Allows an admin to modify room number, category, price, description, and image.
 * Follows the "Grand Luxe" Luxury Design System.
 */
include '../config/conn.php';
session_start();

// --- 1. PHP SESSION CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Administrator';

// --- 2. FETCH ROOM DATA & INITIALIZATION ---

// Get room ID from URL (or default to 1 for demonstration)
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// // Sample data for demonstration (matches previous mock data patterns)
// $room = [
//     "id" => $room_id,
//     "room_number" => "Room " . str_pad($room_id, 3, '0', STR_PAD_LEFT),
//     "category" => ($room_id % 3 == 0) ? "Executive Suite" : (($room_id % 2 == 0) ? "Deluxe Suite" : "Standard Room"),
//     "price" => 150.00 + $room_id * 5,
//     "description" => "A modern, spacious room (ID: $room_id) with luxury furnishings, high-speed Wi-Fi, and a stunning city view. Perfect for extended stays.",
//     // Placeholder image URL for preview
//     "image_url" => "https://placehold.co/600x400/112240/D4AF37?text=Room+" . $room_id
// ];
// Fetch room from database using prepared statement
$stmt = $conn->prepare("SELECT id, room_no, category_id, price, description, image FROM rooms WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $room = $result->fetch_assoc();
    } else {
        header("Location: manage_rooms.php?error=" . urlencode("Room not found."));
        exit();
    }
    $stmt->close();
} else {
    header("Location: manage_rooms.php?error=" . urlencode("Database error."));
    exit();
}

$categories = [];
if ($stmt = $conn->prepare("SELECT id, category_name FROM room_categories ORDER BY category_name ASC")) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();

    // If the room record stores a category ID, convert it to the category name so the existing form logic works
    if (isset($room['category_id']) && is_numeric($room['category_id'])) {
        if ($stmt2 = $conn->prepare("SELECT name FROM categories WHERE id = ? LIMIT 1")) {
            $stmt2->bind_param("i", $room['category_id']);
            $stmt2->execute();
            $r2 = $stmt2->get_result();
            if ($r2 && $r2->num_rows > 0) {
                $row2 = $r2->fetch_assoc();
                $room['category_id'] = $row2['name'];
            }
            $stmt2->close();
        }
    }
}


// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would validate inputs and update the DB here.
    $updated_room_number = htmlspecialchars($_POST['room_number']);


    
    // Simulate successful update
    header("Location: manage_rooms.php?success=1");
    exit();
}

// Get message parameters
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room | Grand Luxe Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

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
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.5);
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

        /* --- MAIN CONTENT --- */
        main {
            flex-grow: 1;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--color-white);
            margin-bottom: 10px;
        }

        .divider {
            width: 80px;
            height: 3px;
            background: var(--color-gold);
            margin: 0 auto;
            border-radius: 2px;
        }

        /* --- FORM CARD --- */
        .form-card {
            background-color: var(--color-navy-light);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            width: 100%;
            max-width: 700px;
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-top: 4px solid var(--color-gold); /* Luxury Accent */
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--color-gold);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Luxury Input Styling */
        .form-group input:not([type="file"]), 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 14px;
            border-radius: 4px;
            border: 1px solid #233554;
            background-color: rgba(0, 0, 0, 0.3);
            color: var(--color-white);
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: var(--color-gold);
            outline: none;
            background-color: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }
        
        /* Select Dropdown Fix */
        .form-group select option {
            background-color: var(--color-navy-light);
            color: var(--color-white);
            padding: 10px;
        }

        /* --- IMAGE PREVIEW --- */
        .image-preview-container {
            margin-bottom: 25px;
            border: 1px dashed rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            padding: 10px;
            background-color: rgba(0,0,0,0.2);
            text-align: center;
        }
        
        .image-preview img {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            border-radius: 4px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        /* --- FILE INPUT --- */
        .file-input-wrapper {
            position: relative;
            margin-top: 10px;
        }
        
        input[type="file"] {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        /* --- BUTTONS --- */
        .form-actions {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }

        .btn {
            padding: 14px 20px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-submit {
            flex: 2;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: 1px solid var(--color-gold);
        }

        .btn-submit:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-cancel {
            flex: 1;
            background-color: transparent;
            color: var(--color-text-muted);
            border: 1px solid #233554;
        }

        .btn-cancel:hover {
            border-color: var(--color-white);
            color: var(--color-white);
        }

        /* --- ALERTS --- */
        .alert-box {
            background-color: rgba(220, 53, 69, 0.1);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid rgba(220, 53, 69, 0.3);
            margin-bottom: 20px;
            width: 100%;
            max-width: 700px;
            text-align: center;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            .form-card {
                padding: 25px;
            }
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Navigation -->
    <header>
        <div class="brand">Grand Luxe Admin</div>
        <nav class="nav-links">
            <span style="color: var(--color-text-muted); margin-right: 15px; font-size: 0.9rem;">
                Welcome, <?php echo $admin_username; ?>
            </span>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_rooms.php">Manage Rooms</a>
            <a href="logout.php" style="color: var(--color-gold);">Logout</a>
        </nav>
    </header>

    <main>
        
        <div class="page-header">
            <h1>Edit Room</h1>
            <div class="divider"></div>
        </div>
        
        <!-- Error Message -->
        <?php if (!empty($error_message)): ?>
            <div class="alert-box">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="form-card">
            <form action="../config/edit_room.php?id=<?php echo $room_id; ?>" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>">

                <div class="form-group">
                    <label for="room_number">Room Number</label>
                    <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($room['room_no']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat["id"]); ?>" <?php echo ($cat["id"] == $room['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat["category_name"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price per Night ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($room['price']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($room['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Current Room Image</label>
                    <div class="image-preview-container">
                        <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="Current Room Image" height="400" width="600"> >
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_image">Upload New Image (Optional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="new_image" name="new_image" accept="image/*">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-submit">Save Changes</button>
                    <a href="manage_rooms.php" class="btn btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </main>

</body>
</html>