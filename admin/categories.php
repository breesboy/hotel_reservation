<?php
/**
 * CATEGORIES.PHP - Grand Luxe Admin
 * Manage room categories (View, Add, Edit, Delete).
 * Follows the "Grand Luxe" Luxury Design System.
 */
include '../config/conn.php';

session_start();

// --- 1. SESSION CHECK ---
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit();
// }

$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Administrator';


$stmt2 = $conn->query("SELECT * FROM room_categories");
    if ($stmt2->num_rows > 0) {
        while ($row = $stmt2->fetch_assoc()) {
            // Mock count of rooms per category
            $row['count'] = rand(1, 15);
            $categories[] = $row;
            
        }
    }

// Handle Form Submission (Mock)
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $new_category = htmlspecialchars($_POST['category_name']);
    
    $stmt = $conn->query("INSERT INTO room_categories (category_name) VALUES ('$new_category')");
    
 
    $msg = "Category '$new_category' added successfully.";

    // Refresh categories list
    $categories = [];
    $stmt2 = $conn->query("SELECT * FROM room_categories");
    if ($stmt2->num_rows > 0) {
        while ($row = $stmt2->fetch_assoc()) {
            // Mock count of rooms per category
            $row['count'] = rand(1, 15);
            $categories[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Categories | Grand Luxe Admin</title>
    
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
            --color-danger: #d63031;
            --color-success: #27ae60;
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

        /* --- ADD CATEGORY SECTION --- */
        .add-section {
            max-width: 600px;
            margin: 0 auto 50px;
            padding: 0 20px;
        }

        .add-card {
            background-color: rgba(17, 34, 64, 0.6);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: var(--border-radius);
            padding: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .form-group {
            width: 100%;
            display: flex;
            gap: 10px;
        }

        input[type="text"] {
            flex: 1;
            padding: 12px 15px;
            background-color: rgba(10, 26, 47, 0.8);
            border: 1px solid #233554;
            color: var(--color-white);
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            border-color: var(--color-gold);
        }

        .btn-submit {
            padding: 12px 25px;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }

        .alert-success {
            color: var(--color-success);
            background-color: rgba(39, 174, 96, 0.1);
            padding: 10px;
            border-radius: 4px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(39, 174, 96, 0.3);
        }

        /* --- CATEGORIES GRID --- */
        .categories-grid {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Desktop Default */
            gap: 25px;
        }

        .cat-card {
            background-color: var(--color-navy-light);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-card);
            border-left: 3px solid var(--color-gold);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 160px;
        }

        .cat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            background-color: #152744;
        }

        .cat-info {
            margin-bottom: 20px;
        }

        .cat-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--color-white);
            margin-bottom: 8px;
        }

        .cat-count {
            color: var(--color-text-muted);
            font-size: 0.9rem;
            font-weight: 300;
        }

        .cat-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 15px;
        }

        .btn-action {
            flex: 1;
            padding: 8px;
            text-align: center;
            border-radius: 3px;
            font-size: 0.8rem;
            text-transform: uppercase;
            text-decoration: none;
            letter-spacing: 0.5px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-edit {
            border: 1px solid var(--color-gold);
            color: var(--color-gold);
        }

        .btn-edit:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
        }

        .btn-delete {
            border: 1px solid rgba(214, 48, 49, 0.4);
            color: #ff7675;
        }

        .btn-delete:hover {
            background-color: var(--color-danger);
            border-color: var(--color-danger);
            color: white;
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
            .categories-grid {
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
            
            .categories-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group {
                flex-direction: column;
            }
            .btn-submit {
                width: 100%;
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
            <a href="manage_rooms.php">Manage Rooms</a>
            <a href="reservations.php">Reservations</a>
            <a href="logout.php" style="color: var(--color-gold);">Logout</a>
        </nav>
    </header>

    <!-- Page Title -->
    <section class="hero">
        <h1>Room Categories</h1>
        <div class="divider"></div>
        <p>Manage the classification of your luxury suites and rooms.</p>
    </section>

    <!-- Add Category Section -->
    <section class="add-section">
        <div class="add-card">
            <?php if(!empty($msg)): ?>
                <div class="alert-success"><?php echo $msg; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="form-group">
                <input type="text" name="category_name" placeholder="Enter new category name..." required>
                <button type="submit" class="btn-submit">Add Category</button>
            </form>
        </div>
    </section>

    <!-- Categories Grid -->
    <main class="categories-grid">
        <?php foreach ($categories as $cat): ?>
            <div class="cat-card">
                <div class="cat-info">
                    <h3 class="cat-name"><?php echo htmlspecialchars($cat['category_name']); ?></h3>
                    <p class="cat-count"><?php echo $cat['count']; ?> Active Rooms</p>
                </div>
                <div class="cat-actions">
                    <a href="edit_category.php?id=<?php echo $cat['id']; ?>" class="btn-action btn-edit">Edit</a>
                    <a href="../config/delete_category.php?id=<?php echo $cat['id']; ?>" 
                       class="btn-action btn-delete"
                       onclick="return confirm('Are you sure you want to delete this category?');">
                       Delete
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Grand Luxe Hotels & Resorts. Admin Control Panel.
    </footer>

</body>
</html>