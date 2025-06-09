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

// Check if this is a page refresh (no form submission)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Fetch all available books with author names
    $books = $conn->query("
        SELECT DISTINCT b.*, a.name as author_name, c.category_name,
               i.is_available, i.location,
               CASE WHEN i.is_available = 1 THEN 'Available' 
                    WHEN i.is_available = 0 THEN 'Not Available'
                    ELSE 'Not Available' END as availability
        FROM books b
        LEFT JOIN authors a ON b.author_id = a.author_id
        LEFT JOIN categories c ON b.category_id = c.category_id
        LEFT JOIN inventory i ON b.book_id = i.book_id
        ORDER BY b.title
    ");
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Digital Bookshelf</title>
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
            margin: 0;
            padding: 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--bg-color);
            padding: 20px;
            color: var(--text-dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-dark);
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .header h1 {
            color: var(--primary-red);
            margin-bottom: 10px;
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        
        .book-card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            transition: all 0.3s ease;
            overflow: hidden;
            border: 1px solid var(--light-red);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
        }
        
        .book-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-bottom: 1px solid var(--light-red);
            cursor: pointer;
        }
        
        .book-info {
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .book-title {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        
        .book-author {
            color: var(--text-light);
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .book-category {
            color: var(--primary-red);
            font-size: 0.85em;
            margin-bottom: 5px;
        }
        
        .availability {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 5px;
        }
        
        .available {
            background-color: #e6ffe6;
            color: #008000;
        }
        
        .not-available {
            background-color: var(--light-red);
            color: var(--dark-red);
            font-weight: bold;
        }
        
        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .search-input {
            width: 100%;
            max-width: 500px;
            padding: 12px;
            border: 2px solid var(--primary-red);
            border-radius: 4px;
            font-size: 16px;
            background-color: var(--card-bg);
            color: var(--text-dark);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--dark-red);
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.3);
        }
        
        .nav-button {
            display: inline-block;
            background-color: var(--primary-red);
            color: var(--white);
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }
        
        .nav-button:hover {
            background-color: var(--dark-red);
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

        .sparkle {
            pointer-events: none;
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            animation: sparkle-fade 0.8s linear forwards;
            will-change: transform, opacity;
        }

        @keyframes sparkle-fade {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 0;
            }
            50% {
                transform: scale(1) rotate(180deg);
                opacity: 0.8;
            }
            100% {
                transform: scale(0) rotate(360deg);
                opacity: 0;
            }
        }

        .inventory-details {
            margin: 10px 0;
            padding: 8px;
            background-color: var(--bg-color);
            border-radius: 4px;
            font-size: 0.9em;
            color: var(--text-dark);
        }

        .inventory-details .location {
            padding: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .location, .shelf, .copies {
            padding: 2px 0;
        }

        @media (max-width: 768px) {
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .book-image {
                height: 250px;
            }
            
            .header {
                padding: 15px;
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

            // Existing search functionality
            const searchInput = document.getElementById('searchInput');
            const booksGrid = document.getElementById('booksGrid');
            const bookCards = document.querySelectorAll('.book-card');

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                
                bookCards.forEach(card => {
                    const title = card.dataset.title;
                    const author = card.dataset.author;
                    const category = card.dataset.category;
                    
                    if (title.includes(searchTerm) || 
                        author.includes(searchTerm) || 
                        category.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Replace the existing mousemove event listener with this optimized version
            document.querySelector('.header').addEventListener('mousemove', function(e) {
                // Throttle the sparkle creation
                if (this.sparkleThrottle) return;
                this.sparkleThrottle = true;
                
                // Reset throttle after 50ms
                setTimeout(() => this.sparkleThrottle = false, 50);
                
                const colors = [
                    '#ff4444', '#ff69b4', '#8a2be2', '#4b0082', 
                    '#9400d3', '#00bfff', '#f0f8ff', '#e6e6fa'
                ];
                
                // Create sparkle container if it doesn't exist
                if (!this.sparkleContainer) {
                    this.sparkleContainer = document.createElement('div');
                    this.sparkleContainer.style.position = 'absolute';
                    this.sparkleContainer.style.top = '0';
                    this.sparkleContainer.style.left = '0';
                    this.sparkleContainer.style.width = '100%';
                    this.sparkleContainer.style.height = '100%';
                    this.sparkleContainer.style.pointerEvents = 'none';
                    this.appendChild(this.sparkleContainer);
                }
                
                // Create fewer sparkles
                for (let i = 0; i < 2; i++) {
                    const sparkle = document.createElement('div');
                    sparkle.className = 'sparkle';
                    
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    sparkle.style.left = x + 'px';
                    sparkle.style.top = y + 'px';
                    
                    const color = colors[Math.floor(Math.random() * colors.length)];
                    sparkle.style.backgroundColor = color;
                    sparkle.style.boxShadow = `0 0 3px ${color}`;
                    
                    const offsetX = (Math.random() - 0.5) * 15;
                    const offsetY = (Math.random() - 0.5) * 15;
                    sparkle.style.transform = `translate(${offsetX}px, ${offsetY}px)`;
                    
                    this.sparkleContainer.appendChild(sparkle);
                    
                    // Use requestAnimationFrame for removal
                    const removeSparkle = () => {
                        if (sparkle && sparkle.parentNode) {
                            sparkle.parentNode.removeChild(sparkle);
                        }
                    };
                    requestAnimationFrame(() => {
                        setTimeout(removeSparkle, 800);
                    });
                }
            });
        });
    </script>
</head>
<body>
    <button class="theme-switch" onclick="toggleTheme()" aria-label="Toggle dark mode"></button>

    <div class="container">
        <div class="header">
            <h1>Digital Bookshelf</h1>
            <p>Click on any book cover to view the PDF</p>
        </div>

        <div class="search-bar">
            <input type="text" id="searchInput" class="search-input" placeholder="Search books by title, author, or category...">
        </div>

        <a href="profile.php" class="nav-button">Back to Profile</a>

        <div class="books-grid" id="booksGrid">
            <?php while($book = $books->fetch_assoc()): ?>
                <div class="book-card" data-title="<?php echo strtolower($book['title']); ?>" 
                     data-author="<?php echo strtolower($book['author_name']); ?>"
                     data-category="<?php echo strtolower($book['category_name']); ?>">
                    <a href="<?php echo htmlspecialchars($book['pdf_link']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                    </a>
                    <div class="book-info">
                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                        <div class="book-author">by <?php echo htmlspecialchars($book['author_name']); ?></div>
                        <div class="book-category"><?php echo htmlspecialchars($book['category_name']); ?></div>
                        <div class="inventory-details">
                            <div class="location">
                                📍 <?php echo htmlspecialchars($book['location'] ?? 'Location not specified'); ?>
                            </div>
                            <div class="availability <?php echo strtolower($book['availability']); ?>">
                                <?php echo $book['availability']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>