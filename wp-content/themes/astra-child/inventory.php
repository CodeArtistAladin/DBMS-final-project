<?php
session_start();

// Check if user is admin/teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "wordpress";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $book_id = intval($_POST['book_id']);
            $location = $conn->real_escape_string($_POST['location']);
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            $sql = "INSERT INTO inventory (book_id, location, is_available) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $book_id, $location, $is_available);
            
            if ($stmt->execute()) {
                $success = "Inventory item added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        } elseif ($_POST['action'] == 'update') {
            $inventory_id = intval($_POST['inventory_id']);
            $location = $conn->real_escape_string($_POST['location']);
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            $sql = "UPDATE inventory SET location = ?, is_available = ? WHERE inventory_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $location, $is_available, $inventory_id);
            
            if ($stmt->execute()) {
                $success = "Inventory updated successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        } elseif ($_POST['action'] == 'delete') {
            $inventory_id = intval($_POST['inventory_id']);
            
            $sql = "DELETE FROM inventory WHERE inventory_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $inventory_id);
            
            if ($stmt->execute()) {
                $success = "Inventory item deleted successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}

// Fetch available books
$books = $conn->query("SELECT book_id, title FROM books ORDER BY title");

// Fetch inventory with book details
$inventory = $conn->query("
    SELECT i.inventory_id, i.location, i.is_available,
           b.title as book_title
    FROM inventory i
    JOIN books b ON i.book_id = b.book_id
    ORDER BY b.title
");
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Inventory Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root[data-theme="light"] {
            --primary-red: #ff4444;
            --secondary-red: #ff8585;
            --dark-red: #cc0000;
            --light-red: #ffe6e6;
            --hover-red: #fa5252;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --white: #ffffff;
            --bg-color: var(--light-red);
            --card-bg: var(--white);
        }

        :root[data-theme="dark"] {
            --primary-red: #ff6b6b;
            --secondary-red: #ff8585;
            --dark-red: #ff4444;
            --light-red: #4a1010;
            --hover-red: #ff5252;
            --text-dark: #ffffff;
            --text-light: #cccccc;
            --white: #1a1a1a;
            --bg-color: #121212;
            --card-bg: #2d2d2d;
        }

        * {
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--bg-color);
            color: var(--text-dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h2 {
            color: var(--primary-red);
            text-align: center;
            margin-bottom: 30px;
        }

        .inventory-form {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            margin-bottom: 20px;
            border: 1px solid var(--light-red);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
            font-weight: bold;
        }
        
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            border: 2px solid var(--light-red);
            border-radius: 4px;
            background-color: var(--card-bg);
            color: var(--text-dark);
        }
        
        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: var(--primary-red);
        }
        
        button {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: var(--dark-red);
        }
        
        .msg {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .error { 
            background-color: var(--light-red); 
            color: var(--dark-red);
            border: 1px solid var(--primary-red);
        }
        
        .success { 
            background-color: #e6ffe6; 
            color: #008000;
            border: 1px solid #4CAF50;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-red);
            color: var(--text-dark);
        }
        
        th {
            background-color: var(--light-red);
            color: var(--dark-red);
            font-weight: bold;
        }
        
        .status-available { color: #4CAF50; }
        .status-unavailable { color: var(--primary-red); }
        
        .delete-btn {
            background-color: var(--dark-red);
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .delete-btn:hover {
            background-color: var(--hover-red);
        }

        .star {
            position: fixed;
            width: 2px;
            height: 2px;
            background: white;
            border-radius: 50%;
            animation: twinkle var(--duration) infinite;
            opacity: 0;
            pointer-events: none;
            box-shadow: 
                0 0 2px rgba(255,255,255,0.5),
                0 0 4px rgba(255,255,255,0.3);
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: transparent;
            overflow: hidden;
            z-index: 1000;
        }

        .theme-switch::before {
            content: '💡';
            font-size: 30px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .theme-switch::before {
            content: '🌙';
        }
    </style>

    <script>
        function createStars() {
            const container = document.body;
            const starCount = 150;
            
            const existingStars = document.querySelectorAll('.star');
            existingStars.forEach(star => star.remove());
            
            if (document.documentElement.getAttribute('data-theme') !== 'dark') return;
            
            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                
                const size = Math.random() * 2 + 1;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                
                star.style.setProperty('--duration', `${Math.random() * 4 + 2}s`);
                
                container.appendChild(star);
            }
        }

        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            if (newTheme === 'dark') {
                createStars();
            } else {
                const stars = document.querySelectorAll('.star');
                stars.forEach(star => star.remove());
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            if (savedTheme === 'dark') {
                createStars();
            }
        });
    </script>
</head>
<body>
    <button class="theme-switch" onclick="toggleTheme()" aria-label="Toggle dark mode"></button>
    <div class="container">
        <h2>Inventory Management</h2>
        
        <?php if (!empty($error)): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="msg success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="inventory-form">
            <h3>Add New Inventory Item</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Select Book</label>
                    <select name="book_id" required>
                        <option value="">Choose Book</option>
                        <?php while($book = $books->fetch_assoc()): ?>
                            <option value="<?php echo $book['book_id']; ?>">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" required placeholder="e.g., Shelf A-1">
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_available" id="is_available" checked>
                    <label for="is_available">Available for Borrowing</label>
                </div>

                <button type="submit">Add Inventory Item</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $inventory->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['book_title']); ?></td>
                        <td>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                <input type="text" name="location" value="<?php echo htmlspecialchars($item['location']); ?>" style="width: 150px;">
                                <input type="checkbox" name="is_available" <?php echo $item['is_available'] ? 'checked' : ''; ?>>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td>
                            <span class="<?php echo $item['is_available'] ? 'status-available' : 'status-unavailable'; ?>">
                                <?php echo $item['is_available'] ? 'Available' : 'Not Available'; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this inventory item?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>