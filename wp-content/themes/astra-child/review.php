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

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $user_id = intval($_POST['user_id']);
            $book_id = intval($_POST['book_id']);
            $review_text = $conn->real_escape_string($_POST['review_text']);
            
            // Check if user has already reviewed this book
            $check_sql = "SELECT COUNT(*) as count FROM reviews WHERE user_id = ? AND book_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $book_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $error = "You have already reviewed this book!";
            } else {
                $sql = "INSERT INTO reviews (user_id, book_id, review_text) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $user_id, $book_id, $review_text);
                
                if ($stmt->execute()) {
                    $success = "Review added successfully!";
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['review_id'])) {
            // Check if user is a teacher before allowing delete
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
                $error = "Unauthorized action!";
                exit();
            }
            
            $review_id = intval($_POST['review_id']);
            
            $sql = "DELETE FROM reviews WHERE review_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $review_id);
            
            if ($stmt->execute()) {
                $success = "Review deleted successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}

// Fetch all reviews with user and book details
$reviews = $conn->query("
    SELECT r.review_id, r.review_text, r.review_date,
           u.name as user_name,
           b.title as book_title
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN books b ON r.book_id = b.book_id
    ORDER BY r.review_date DESC
");

// Fetch users for dropdown
$users = $conn->query("SELECT user_id, name, email FROM users ORDER BY name");

// Fetch books for dropdown
$books = $conn->query("SELECT book_id, title FROM books ORDER BY title");
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Book Reviews</title>
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
            max-width: 800px;
            margin: 0 auto;
        }
        
        form {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            margin-bottom: 20px;
            border: 1px solid var(--input-border);
        }
        
        input[type="text"], 
        select, 
        textarea {
            width: 100%;
            padding: 8px;
            border: 2px solid var(--input-border);
            border-radius: 4px;
            margin-bottom: 10px;
            background-color: var(--input-bg);
            color: var(--text-dark);
        }

        label {
            color: var(--text-dark);
        }
        
        button, 
        input[type="submit"] {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        button:hover, 
        input[type="submit"]:hover {
            background-color: var(--dark-red);
        }
        
        .review-card {
            background: var(--card-bg);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            border: 1px solid var(--input-border);
            color: var(--text-dark);
        }
        
        .review-header {
            color: var(--text-light);
        }

        h2, h3 {
            color: var(--primary-red);
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
        <h2>Book Reviews</h2>
        
        <?php if (!empty($error)): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="msg success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Select User</label>
                <select name="user_id" required>
                    <option value="">Choose User</option>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

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
                <label>Review Text</label>
                <textarea name="review_text" required placeholder="Write your review here..."></textarea>
            </div>

            <input type="submit" value="Add Review">
        </form>

        <h3>Recent Reviews</h3>
        <?php while($review = $reviews->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-header">
                    <span>
                        <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                        reviewed
                        <strong><?php echo htmlspecialchars($review['book_title']); ?></strong>
                    </span>
                    <span><?php echo date('M d, Y', strtotime($review['review_date'])); ?></span>
                </div>
                <div class="review-text">
                    <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                </div>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher'): ?>
                    <form method="POST" action="" style="display: inline; background: none; padding: 0; box-shadow: none;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this review?')">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>