<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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

// Get user's reading history
$user_id = $_SESSION['user_id'];

// 1. Get favorite categories based on borrowing history
$category_query = "
    SELECT c.category_id, c.category_name, COUNT(*) as category_count
    FROM borrowers b
    JOIN books bk ON b.book_id = bk.book_id
    JOIN categories c ON bk.category_id = c.category_id
    WHERE b.user_id = ?
    GROUP BY c.category_id
    ORDER BY category_count DESC
    LIMIT 3
";

$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favorite_categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 2. Get favorite authors
$author_query = "
    SELECT a.author_id, a.name, COUNT(*) as author_count
    FROM borrowers b
    JOIN books bk ON b.book_id = bk.book_id
    JOIN authors a ON bk.author_id = a.author_id
    WHERE b.user_id = ?
    GROUP BY a.author_id
    ORDER BY author_count DESC
    LIMIT 3
";

$stmt = $conn->prepare($author_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favorite_authors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Get book recommendations based on favorite categories and authors
$recommendations = array();

// Get category-based recommendations
if (!empty($favorite_categories)) {
    $category_ids = array_column($favorite_categories, 'category_id');
    $category_list = implode(',', $category_ids);
    
    $category_recommendations = $conn->query("
        SELECT DISTINCT b.*, a.name as author_name, c.category_name,
               CASE WHEN i.is_available = 1 THEN 'Available' ELSE 'Not Available' END as availability
        FROM books b
        JOIN authors a ON b.author_id = a.author_id
        JOIN categories c ON b.category_id = c.category_id
        LEFT JOIN inventory i ON b.book_id = i.book_id
        WHERE b.category_id IN ($category_list)
        AND b.book_id NOT IN (
            SELECT book_id FROM borrowers WHERE user_id = $user_id
        )
        LIMIT 5
    ");
    
    while ($book = $category_recommendations->fetch_assoc()) {
        $recommendations['category'][] = $book;
    }
}

// Get author-based recommendations
if (!empty($favorite_authors)) {
    $author_ids = array_column($favorite_authors, 'author_id');
    $author_list = implode(',', $author_ids);
    
    $author_recommendations = $conn->query("
        SELECT DISTINCT b.*, a.name as author_name, c.category_name,
               CASE WHEN i.is_available = 1 THEN 'Available' ELSE 'Not Available' END as availability
        FROM books b
        JOIN authors a ON b.author_id = a.author_id
        JOIN categories c ON b.category_id = c.category_id
        LEFT JOIN inventory i ON b.book_id = i.book_id
        WHERE b.author_id IN ($author_list)
        AND b.book_id NOT IN (
            SELECT book_id FROM borrowers WHERE user_id = $user_id
        )
        LIMIT 5
    ");
    
    while ($book = $author_recommendations->fetch_assoc()) {
        $recommendations['author'][] = $book;
    }
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Book Recommendations</title>
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
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--light-red);
            transition: all 0.3s ease;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .recommendation-section {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            margin-bottom: 20px;
            border: 1px solid var(--secondary-red);
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        
        .book-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--light-red);
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
        }
        
        .book-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom: 1px solid var(--light-red);
        }
        
        .book-info {
            padding: 15px;
        }
        
        .book-title {
            color: var(--text-dark);
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .book-author {
            color: var(--text-light);
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .book-category {
            color: var(--primary-red);
            font-size: 0.85em;
        }
        
        .nav-button {
            display: inline-block;
            background-color: var(--primary-red);
            color: var(--white);
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        
        .nav-button:hover {
            background-color: var(--dark-red);
        }
        
        h1 {
            color: var(--primary-red);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .section-title {
            color: var(--primary-red);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-red);
        }

        .no-recommendations {
            text-align: center;
            color: var(--text-dark);
            padding: 40px 20px;
        }

        .no-recommendations h2 {
            color: var(--primary-red);
            margin-bottom: 10px;
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

        @media (max-width: 768px) {
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            
            .book-image {
                height: 200px;
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
    <div class="container">
        <a href="profile.php" class="nav-button">Back to Profile</a>
        
        <h1>Personalized Book Recommendations</h1>
        
        <?php if (!empty($recommendations['category'])): ?>
        <div class="recommendation-section">
            <h2 class="section-title">Based on Your Favorite Categories</h2>
            <div class="books-grid">
                <?php foreach ($recommendations['category'] as $book): ?>
                    <div class="book-card">
                        <a href="<?php echo htmlspecialchars($book['pdf_link']); ?>" target="_blank">
                            <img src="<?php echo htmlspecialchars($book['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                 class="book-image">
                        </a>
                        <div class="book-info">
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($book['author_name']); ?></div>
                            <div class="book-category"><?php echo htmlspecialchars($book['category_name']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($recommendations['author'])): ?>
        <div class="recommendation-section">
            <h2 class="section-title">Based on Your Favorite Authors</h2>
            <div class="books-grid">
                <?php foreach ($recommendations['author'] as $book): ?>
                    <div class="book-card">
                        <a href="<?php echo htmlspecialchars($book['pdf_link']); ?>" target="_blank">
                            <img src="<?php echo htmlspecialchars($book['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                 class="book-image">
                        </a>
                        <div class="book-info">
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($book['author_name']); ?></div>
                            <div class="book-category"><?php echo htmlspecialchars($book['category_name']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (empty($recommendations['category']) && empty($recommendations['author'])): ?>
            <div class="recommendation-section">
                <div class="no-recommendations">
                    <h2>No Recommendations Yet</h2>
                    <p>Borrow some books to get personalized recommendations!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>