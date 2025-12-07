<?php
/**
 * ADMIN/ADD_ROOM.PHP - Add New Room Form
 * Allows authenticated administrators to add new room inventory.
 */
include '../config/conn.php';
session_start();

// --- 1. PHP SESSION CHECK ---
// Redirect to login if not authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$categories = [];
$stmt2 = $conn->query("SELECT * FROM room_categories");
    if ($stmt2->num_rows > 0) {
        while ($row = $stmt2->fetch_assoc()) {
            $categories[$row['id']] = $row['category_name'];
        }
    }





// Get message parameters
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success_message = isset($_GET['success']) && $_GET['success'] == 1 ? "New room successfully added to inventory." : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room | Grand Luxe Admin</title>
    
    <!-- Google Fonts (Consistent with Dashboard) -->
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
            --shadow-card: 0 10px 40px rgba(0, 0, 0, 0.7);
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

        /* --- NAVIGATION / HEADER (Consistent Styling) --- */
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

        .header-left {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--color-gold);
            font-weight: 600;
            letter-spacing: 1px;
        }

        .header-right {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            border: 1px solid var(--color-gold);
            padding: 8px 18px;
            border-radius: 4px;
            color: var(--color-gold);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .nav-btn:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
        }

        /* --- MAIN CONTENT & CARD --- */
        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .form-card {
            background-color: rgba(10, 26, 47, 0.95);
            width: 100%;
            max-width: 600px; /* Slightly wider card for better form layout */
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(212, 175, 55, 0.3);
            text-align: center;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--color-gold);
            margin-bottom: 15px;
            font-weight: 500;
        }

        .divider {
            height: 1px;
            width: 100px;
            background-color: var(--color-gold);
            margin: 0 auto 30px;
            opacity: 0.8;
        }
        
        /* --- MESSAGES (Consistent Styling) --- */
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: left;
            border: 1px solid;
        }

        .error-box {
            background-color: rgba(220, 53, 69, 0.15);
            color: #ff6b6b;
            border-color: rgba(220, 53, 69, 0.4);
        }

        .success-box {
            background-color: rgba(50, 205, 50, 0.15);
            color: #90ee90;
            border-color: rgba(212, 175, 55, 0.6); /* Gold border for luxury theme */
        }

        /* --- FORM FIELDS STYLING --- */
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--color-text-muted);
            font-weight: 500;
            transition: color 0.3s;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 14px 16px;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid #2a3b55;
            border-radius: 8px;
            color: var(--color-white);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Specific styling for file input to look cleaner */
        input[type="file"] {
            padding: 12px 0;
            border: none;
        }
        
        /* Style for select dropdown */
        select {
            appearance: none; /* Remove default browser styling */
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23D4AF37%22%20d%3D%22M287%20197.35l-137.2-137.2c-4.9-4.9-12.8-4.9-17.7%200l-137.2%20137.2c-4.9%204.9-4.9%2012.8%200%2017.7l17.7%2017.7c4.9%204.9%2012.8%204.9%2017.7%200l106.1-106.1L249.3%20232.85c4.9%204.9%2012.8%204.9%2017.7%200l17.7-17.7c4.8-4.9%204.8-12.8-.1-17.7z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat, repeat;
            background-position: right 1em top 50%, 0 0;
            background-size: 0.65em auto, 100%;
            padding-right: 30px; /* Make space for the arrow */
        }


        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--color-gold);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .file-upload-note {
            font-size: 0.8rem;
            color: var(--color-gold);
            margin-top: -15px;
            margin-bottom: 25px;
            display: block;
            text-align: left;
        }

        /* --- SUBMIT BUTTON (Consistent Styling) --- */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        }

        .btn-submit:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }

        /* --- FOOTER --- */
        footer {
            text-align: center;
            padding: 20px;
            color: var(--color-text-muted);
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 650px) {
            .form-card {
                max-width: 100%;
                padding: 30px 20px;
            }
            .header-left {
                font-size: 1.2rem;
            }
            header {
                flex-direction: column;
                gap: 10px;
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Navigation -->
    <header>
        <div class="header-left">Add New Room</div>
        <div class="header-right">
            <a href="dashboard.php" class="nav-btn">Dashboard</a>
            <a href="logout.php" class="nav-btn">Logout</a>
        </div>
    </header>

    <main>
        
        <div class="form-card">
            
            <h2>Add Room</h2>
            <div class="divider"></div>

            <!-- Error Message Display -->
            <?php if (!empty($error_message)): ?>
                <div class="message-box error-box">
                    <strong>Error:</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Success Message Display -->
            <?php if (!empty($success_message)): ?>
                <div class="message-box success-box">
                    <strong>Success!</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Room Addition Form -->
            <form action="../config/add_room_action.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="room_number">Room Number</label>
                    <input type="text" id="room_number" name="room_number" placeholder="e.g., 101, 205A" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="" disabled selected>Select Room Category</option>
                        <?php foreach ($categories as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price per Night (USD)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" placeholder="e.g., 250.00" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="A brief, luxurious description of the room features and amenities." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="room_image">Room Image</label>
                    <input type="file" id="room_image" name="room_image" accept=".jpg, .jpeg, .png" required>
                    <small class="file-upload-note">Upload a high-quality image of the room. (.jpg, .jpeg, .png only)</small>
                </div>

                <button type="submit" class="btn-submit" name="add_room">Add Room</button>
            </form>

        </div>

    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. Room Management.
    </footer>

</body>
</html>