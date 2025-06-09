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
            $borrow_date = $_POST['borrow_date'];
            $return_date = $_POST['return_date'];
            
            // Check if book is already borrowed
            $check_sql = "SELECT COUNT(*) as count FROM borrowers WHERE book_id = ? AND return_date >= CURRENT_DATE()";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $book_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $error = "This book is currently borrowed!";
            } else {
                $sql = "INSERT INTO borrowers (user_id, book_id, borrow_date, return_date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiss", $user_id, $book_id, $borrow_date, $return_date);
                
                if ($stmt->execute()) {
                    $success = "Book borrowed successfully!";
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
        } elseif ($_POST['action'] == 'return' && isset($_POST['borrow_id'])) {
            $borrow_id = intval($_POST['borrow_id']);
            $today = date('Y-m-d');
            
            $sql = "UPDATE borrowers SET return_date = ? WHERE borrow_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $today, $borrow_id);
            
            if ($stmt->execute()) {
                $success = "Book returned successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}

// Fetch all borrowing records with user and book details
$borrowers = $conn->query("
    SELECT b.borrow_id, b.borrow_date, b.return_date, 
           u.name as user_name, u.email,
           bk.title as book_title
    FROM borrowers b
    JOIN users u ON b.user_id = u.user_id
    JOIN books bk ON b.book_id = bk.book_id
    ORDER BY b.borrow_date DESC
");

// Replace the users query with:
$users = $conn->query("
    SELECT user_id, name, email 
    FROM users 
    WHERE user_id = {$_SESSION['user_id']}
    LIMIT 1
");

// Fetch available books for dropdown
$books = $conn->query("
    SELECT b.book_id, b.title 
    FROM books b
    WHERE NOT EXISTS (
        SELECT 1 FROM borrowers br 
        WHERE br.book_id = b.book_id 
        AND br.return_date >= CURRENT_DATE()
    )
    ORDER BY b.title
");
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Book Borrowing Management</title>
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

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--light-red);
            color: var(--text-dark);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            border: 1px solid var(--input-border);
        }

        h2 {
            color: var(--primary-red);
            text-align: center;
            margin-bottom: 30px;
        }

        .borrow-form {
            width: 100%;
            border: 1px solid var(--light-red);
            border-radius: 8px;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 15px;
            background: var(--white);
            border-bottom: 1px solid var(--light-red);
        }

        .form-group {
            margin: 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-red);
            font-weight: bold;
        }

        select, input[type="date"], input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 2px solid var(--light-red);
            border-radius: 4px;
            font-size: 14px;
            background-color: var(--input-bg);
            color: var(--text-dark);
            border-color: var(--input-border);
        }

        select:focus, input[type="date"]:focus {
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
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: var(--dark-red);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--card-bg);
        }

        th {
            background-color: var(--light-red);
            color: var(--dark-red);
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid var(--light-red);
            color: var(--text-dark);
        }

        .return-btn {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .return-btn:hover {
            background-color: var(--dark-red);
        }

        .borrowed {
            color: var(--primary-red);
            font-weight: bold;
        }

        .returned {
            color: var(--dark-red);
        }

        .msg {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .error {
            background-color: var(--light-red);
            color: var(--primary-red);
            border: 1px solid var(--primary-red);
        }

        .success {
            background-color: #e6ffe6;
            color: #008000;
            border: 1px solid #4CAF50;
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
            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
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
        <h2>Book Borrowing Management</h2>
        
        <?php if (!empty($error)): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="msg success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Replace the existing form with this new structure -->
        <form method="POST" action="" class="borrow-form">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="form-group">
                    <label>User</label>
                    <?php $user = $users->fetch_assoc(); ?>
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <input type="text" value="<?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>" readonly 
                           style="background-color: var(--light-red); cursor: not-allowed;">
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
                    <label>Borrow Date</label>
                    <input type="date" name="borrow_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" name="return_date" required value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>">
                </div>
            </div>
            <div style="padding: 15px; background: var(--white);">
                <input type="submit" value="Borrow Book">
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Book</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($borrow = $borrowers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $borrow['borrow_id']; ?></td>
                        <td><?php echo htmlspecialchars($borrow['book_title']); ?></td>
                        <td><?php echo $borrow['borrow_date']; ?></td>
                        <td><?php echo $borrow['return_date']; ?></td>
                        <td>
                            <?php 
                            $today = new DateTime();
                            $return_date = new DateTime($borrow['return_date']);
                            if ($return_date < $today) {
                                echo '<span class="returned">Returned</span>';
                            } else {
                                echo '<span class="borrowed">Borrowed</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>