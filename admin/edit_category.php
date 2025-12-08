<?php
/**
 * EDIT_CATEGORY.PHP - Grand Luxe Admin
 * Edit existing room category details.
 */
include '../config/conn.php';
session_start();

// 1. Session Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Administrator';

// 2. Fetch Category ID
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$id = intval($_GET['id']);
$category = null;
$msg = "";
$msg_type = "";

// 3. Handle Form Submission (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $new_name = htmlspecialchars(trim($_POST['category_name']));
    
    if (!empty($new_name)) {
        $update_sql = "UPDATE room_categories SET category_name = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_name, $id);
        
        if ($stmt->execute()) {
            $msg = "Category updated successfully.";
            $msg_type = "success";
            // Optional: Redirect back after delay
            header("refresh:1;url=categories.php");
        } else {
            $msg = "Error updating category: " . $conn->error;
            $msg_type = "error";
        }
        $stmt->close();
    } else {
        $msg = "Category name cannot be empty.";
        $msg_type = "error";
    }
}

// 4. Fetch Current Data
$sql = "SELECT * FROM room_categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $category = $result->fetch_assoc();
} else {
    echo "Category not found.";
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | Grand Luxe Admin</title>
    
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
                .logout-btn {
            border: 1px solid var(--color-gold);
            padding: 8px 18px;
            border-radius: 4px;
            color: var(--color-gold);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: var(--color-gold);
            color: var(--color-navy);
        }


        /* --- CONTENT --- */
        main {
            flex: 1;
            padding: 40px 20px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--color-white);
            margin-bottom: 10px;
        }

        .divider {
            width: 60px;
            height: 3px;
            background: var(--color-gold);
            margin: 0 auto;
        }

        .edit-card {
            background-color: var(--color-navy-light);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow-card);
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-gold);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid #233554;
            color: var(--color-white);
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }

        input[type="text"]:focus {
            border-color: var(--color-gold);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-save {
            flex: 2;
            padding: 12px;
            background-color: var(--color-gold);
            color: var(--color-navy);
            border: none;
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-save:hover {
            background-color: var(--color-gold-hover);
            transform: translateY(-2px);
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            background-color: transparent;
            color: var(--color-text-muted);
            border: 1px solid var(--color-text-muted);
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            color: var(--color-white);
            border-color: var(--color-white);
        }

        .alert-success {
            background-color: rgba(39, 174, 96, 0.15);
            color: var(--color-success);
            padding: 15px;
            border-radius: 4px;
            border: 1px solid rgba(39, 174, 96, 0.3);
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-error {
            background-color: rgba(214, 48, 49, 0.15);
            color: var(--color-danger);
            padding: 15px;
            border-radius: 4px;
            border: 1px solid rgba(214, 48, 49, 0.3);
            margin-bottom: 20px;
            text-align: center;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            main {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="brand">Grand Luxe Admin</div>
        <nav class="nav-links">
            <a href="categories.php">Back to Categories</a>
            <a href="logout.php" style="color: var(--color-gold); margin-left: 20px;" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main>
        <div class="page-header">
            <h1>Edit Category</h1>
            <div class="divider"></div>
        </div>

        <div class="edit-card">
            <?php if(!empty($msg)): ?>
                <div class="<?php echo ($msg_type == 'error') ? 'alert-error' : 'alert-success'; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <a href="categories.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>