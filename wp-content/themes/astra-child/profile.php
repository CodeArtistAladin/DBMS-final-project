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

$error = "";
$success = "";

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        // Verify old password
        $sql = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($old_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error updating password!";
            }
        } else {
            $error = "Current password is incorrect!";
        }
    } else {
        $error = "New passwords do not match!";
    }
}

// Fetch user's borrowing history
$borrowings = $conn->query("
    SELECT b.borrow_date, b.return_date, bk.title as book_title
    FROM borrowers b
    JOIN books bk ON b.book_id = bk.book_id
    WHERE b.user_id = {$_SESSION['user_id']}
    ORDER BY b.borrow_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-red: #ff4444;
            --secondary-red: #ff8585;
            --dark-red: #cc0000;
            --light-red: #ffe6e6;
            --hover-red: #fa5252;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --white: #ffffff;
            --background-light: #f4f4f9;
            --background-dark: #121212;
            --text-dark-mode: #e0e0e0;
            --text-light-mode: #121212;
            --primary-dark-mode: #bb86fc;
            --secondary-dark-mode: #3700b3;
            --card-bg: #ffffff;
            --input-bg-light: #ffffff;
            --input-bg-dark: #333333;
            --input-text-light: #2d3436;
            --input-text-dark: #ffffff;
            --input-border-light: #ffe6e6;
            --input-border-dark: #666666;
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
            background-color: var(--light-red);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.dark-mode {
            background-color: var(--background-dark);
            color: var(--text-dark-mode);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .profile-section {
            background: var(--card-bg);
            color: var(--text-dark);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            margin-bottom: 20px;
            border: 1px solid var(--secondary-red);
        }
        
        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .nav-buttons .nav-button {
            flex: 1;
            min-width: 200px;
            max-width: 300px;
        }
        
        @media (max-width: 768px) {
            .nav-buttons .nav-button {
                min-width: 100%;
            }
        }
        
        .nav-button {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .nav-button:hover {
            background-color: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 68, 68, 0.2);
        }
        
        body.dark-mode .nav-button {
            background-color: var(--primary-dark-mode);
        }

        body.dark-mode .nav-button:hover {
            background-color: var(--secondary-dark-mode);
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
        }
        
        h2, h3 {
            color: var(--primary-red);
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: var(--card-bg);
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-red);
        }
        
        th {
            background-color: var(--light-red);
            color: var(--text-dark);
        }
        
        .user-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: var(--light-red);
            border-radius: 4px;
        }
        
        .info-label {
            font-weight: bold;
            color: var(--dark-red);
            margin-bottom: 5px;
        }
        
        .password-change form {
            max-width: 400px;
        }
        
        input[type="password"],
        input[type="text"],
        input[type="email"] {
            background-color: var(--input-bg-light);
            color: var(--input-text-light);
            border: 2px solid var(--input-border-light);
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }
        
        input[type="password"]:focus,
        input[type="text"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 5px rgba(255, 68, 68, 0.3);
        }
        
        body.dark-mode input[type="password"],
        body.dark-mode input[type="text"],
        body.dark-mode input[type="email"] {
            background-color: var(--input-bg-dark);
            color: var(--input-text-dark);
            border: 2px solid var(--input-border-dark);
        }
        
        .form-group label {
            color: var(--text-dark);
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .status-returned {
            color: #4CAF50;
        }

        .status-borrowed {
            color: var(--primary-red);
            font-weight: bold;
        }

        .ai-recommend-button {
            background: linear-gradient(45deg, #6f42c1, #8a2be2);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: glow 1.5s ease-in-out infinite;
            transition: all 0.3s ease;
        }

        .ai-recommend-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(138, 43, 226, 0.6);
        }

        .ai-recommend-button::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: sparkle 2s linear infinite;
        }

        @keyframes glow {
            0% {
                box-shadow: 0 0 5px rgba(138, 43, 226, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(138, 43, 226, 0.8);
            }
            100% {
                box-shadow: 0 0 5px rgba(138, 43, 226, 0.5);
            }
        }

        @keyframes sparkle {
            0% {
                transform: rotate(45deg) translateX(-100%);
            }
            100% {
                transform: rotate(45deg) translateX(100%);
            }
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

        body.dark-mode .theme-switch::before {
            content: '🌙';
        }

        @media (max-width: 600px) {
            .nav-buttons {
                flex-direction: column;
            }
            
            .user-info {
                grid-template-columns: 1fr;
            }
        }

        body.dark-mode .profile-section {
            background: var(--background-dark);
            color: var(--text-dark-mode);
            border-color: #444;
        }

        body.dark-mode .info-item {
            background-color: #333;
        }

        body.dark-mode table {
            background: var(--background-dark);
        }

        body.dark-mode th {
            background-color: #333;
            color: var(--text-dark-mode);
        }

        body.dark-mode td {
            color: var(--text-dark-mode);
            border-bottom: 1px solid #444;
        }
    </style>
    <script>
        function createStars() {
            const container = document.body;
            const starCount = 150; // Increased count for better effect
            
            const existingStars = document.querySelectorAll('.star');
            existingStars.forEach(star => star.remove());
            
            if (!document.body.classList.contains('dark-mode')) return;
            
            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                
                // Random position
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                
                // Random size between 1-3px
                const size = Math.random() * 2 + 1;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                
                // Random twinkle duration
                star.style.setProperty('--duration', `${Math.random() * 4 + 2}s`);
                
                container.appendChild(star);
            }
        }

        // Update toggleTheme function
        function toggleTheme() {
            const body = document.body;
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                createStars();
            } else {
                localStorage.setItem('theme', 'light');
                const stars = document.querySelectorAll('.star');
                stars.forEach(star => star.remove());
            }
        }

        // Keep the DOMContentLoaded listener
        document.addEventListener('DOMContentLoaded', () => {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                createStars();
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <!-- Replace the existing theme switch button -->
        <button class="theme-switch" onclick="toggleTheme()" aria-label="Toggle dark mode"></button>

        <div class="nav-buttons">
            <a href="borrower.php" class="nav-button">Borrow Books</a>
            <a href="review.php" class="nav-button">Write Reviews</a>
            <a href="feedback.php" class="nav-button">Submit Feedback</a>
            <a href="recommendations.php" class="ai-recommend-button">AI Book Recommendation</a>
            <a href="logout.php" class="nav-button" style="background-color: #ff4444;">Logout</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="msg success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="profile-section">
            <h2>User Profile</h2>
            <div class="user-info">
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Campus ID</div>
                    <div><?php echo htmlspecialchars($_SESSION['campus_id']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Role</div>
                    <div><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                </div>
            </div>
        </div>

        <div class="profile-section password-change">
            <h3>Change Password</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="nav-button">Change Password</button>
            </form>
        </div>

        <div class="profile-section">
            <h3>Borrowing History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($borrow = $borrowings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($borrow['book_title']); ?></td>
                            <td><?php echo $borrow['borrow_date']; ?></td>
                            <td><?php echo $borrow['return_date']; ?></td>
                            <td>
                                <?php 
                                $today = new DateTime();
                                $return_date = new DateTime($borrow['return_date']);
                                if ($return_date < $today) {
                                    echo '<span class="status-returned">Returned</span>';
                                } else {
                                    echo '<span class="status-borrowed">Borrowed</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($_SESSION['role'] == 'teacher'): ?>
            <div class="nav-buttons">
                <a href="inventory.php" class="nav-button">Manage Inventory</a>
                <a href="manage-database.php" class="nav-button">Database Management</a>
                <a href="category.php" class="nav-button">Manage Categories</a>
                <a href="add-book.php" class="nav-button">Add New Book</a>
                <a href="author.php" class="nav-button">Manage Authors</a>
                <a href="erd.php" class="nav-button">View System ERD</a>
                <a href="wireframe.php" class="nav-button">View Wireframes</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>