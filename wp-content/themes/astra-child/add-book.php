<?php
session_start();

// Database connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "wordpress";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $title = $conn->real_escape_string($_POST['title']);
        $author_id = intval($_POST['author_id']);
        $category_id = intval($_POST['category_id']);
        $image_url = $conn->real_escape_string($_POST['image_url']);
        $pdf_link = $conn->real_escape_string($_POST['pdf_link']);
        $is_available = intval($_POST['is_available']);
        
        // Insert book data
        $sql = "INSERT INTO books (title, image_url, author_id, category_id, pdf_link) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiis", $title, $image_url, $author_id, $category_id, $pdf_link);
        $stmt->execute();
        
        // Get the inserted book's ID
        $book_id = $conn->insert_id;
        
        // Insert inventory record
        $sql = "INSERT INTO inventory (book_id, is_available) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $book_id, $is_available);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Book added successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get messages from session and clear them
$error = "";
$success = "";
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Fetch authors and categories for dropdowns
$authors = $conn->query("SELECT author_id, name FROM authors");
if (!$authors) {
    die("Error fetching authors: " . $conn->error);
}

$categories = $conn->query("SELECT category_id, category_name as name FROM categories");
if (!$categories) {
    die("Error fetching categories: " . $conn->error);
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Add Book</title>
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
            --input-bg: var(--white);
            --input-border: #ffcccc;
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
            --input-bg: #333333;
            --input-border: #444444;
        }

        * {
            box-sizing: border-box;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--bg-color);
            color: var(--text-dark);
        }
        
        .page-wrapper {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 20px;
            min-height: 100vh;
            max-width: 1400px;
            margin: 0 auto;
        }

        .container {
            flex: 1;
            max-width: 800px;
            margin: 0;
            background: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            border: 1px solid var(--input-border);
        }
        
        h2 {
            color: var(--primary-red);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
            padding: 10px;
            background-color: var(--card-bg);
            border-radius: 4px;
            border: 1px solid var(--input-border);
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: var(--primary-red);
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="url"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 2px solid var(--input-border);
            border-radius: 4px;
            font-size: 16px;
            background-color: var(--input-bg);
            color: var(--text-dark);
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 5px rgba(255, 68, 68, 0.3);
        }

        input[type="submit"] {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
        }

        input[type="submit"]:hover {
            background-color: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 68, 68, 0.3);
        }

        input[type="submit"]:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(255, 68, 68, 0.2);
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

        .gif-container {
            position: sticky;
            top: 20px;
            width: 400px;
            margin-right: 40px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(255, 68, 68, 0.2);
            background: var(--card-bg);
            padding: 15px;
        }

        .gif-container img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            display: block;
            object-fit: contain;
        }

        @media (max-width: 1200px) {
            .page-wrapper {
                flex-direction: column;
                align-items: center;
            }
            
            .gif-container {
                position: static;
                width: 100%;
                max-width: 400px;
                margin: 0 0 20px 0;
            }

            .container {
                width: 100%;
                margin: 0;
            }
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
    <div class="page-wrapper">
        <div class="gif-container">
            <img src="/wordpress/wp-content/uploads/library/gallery/gif/book.gif" alt="Animated book">
        </div>
        <div class="container">
            <h2>Add New Book</h2>
            
            <?php if (!empty($error)): ?>
                <div class="msg error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="msg success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Author</label>
                    <select name="author_id" required>
                        <option value="">Select Author</option>
                        <?php while($author = $authors->fetch_assoc()): ?>
                            <option value="<?php echo $author['author_id']; ?>">
                                <?php echo htmlspecialchars($author['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php while($category = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Book Cover Image URL</label>
                    <input type="url" name="image_url" required placeholder="https://example.com/image.jpg">
                </div>

                <div class="form-group">
                    <label>PDF Link URL</label>
                    <input type="url" name="pdf_link" required placeholder="https://example.com/book.pdf">
                </div>

                <div class="form-group">
                    <label>Availability Status</label>
                    <select name="is_available" required>
                        <option value="1">Available</option>
                        <option value="0">Not Available</option>
                    </select>
                </div>

                <input type="submit" value="Add Book">
            </form>
        </div>
    </div>
</body>
</html>