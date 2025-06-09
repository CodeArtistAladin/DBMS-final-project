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

// Handle form submission for adding/deleting author
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['name']);
            
            $sql = "INSERT INTO authors (name) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $name);
            
            if ($stmt->execute()) {
                $success = "Author added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['author_id'])) {
            $author_id = intval($_POST['author_id']);
            
            // Check if author has any books
            $check_sql = "SELECT COUNT(*) as count FROM books WHERE author_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $author_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $error = "Cannot delete author with existing books!";
            } else {
                $sql = "DELETE FROM authors WHERE author_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $author_id);
                
                if ($stmt->execute()) {
                    $success = "Author deleted successfully!";
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
        }
    }
}

// Fetch all authors
$authors = $conn->query("SELECT * FROM authors ORDER BY name");
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Author Management</title>
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
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--bg-color);
            color: var(--text-dark);
            transition: all 0.3s ease;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h2 {
            color: var(--primary-red);
            text-align: center;
            margin-bottom: 30px;
        }
        
        form {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            margin-bottom: 20px;
            border: 1px solid var(--secondary-red);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-dark);
        }
        
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 2px solid var(--light-red);
            border-radius: 4px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            background-color: var(--input-bg);
            color: var(--text-dark);
            border-color: var(--input-border);
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 5px rgba(255, 68, 68, 0.3);
        }
        
        button, input[type="submit"] {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        button:hover, input[type="submit"]:hover {
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
            margin-top: 20px;
            background: var(--card-bg);
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-red);
        }
        
        th {
            background-color: var(--light-red);
            color: var(--text-dark);
            font-weight: bold;
        }
        
        td {
            color: var(--text-dark);
            border-color: var(--input-border);
        }
        
        tr:hover {
            background-color: var(--light-red);
        }
        
        .delete-btn {
            background-color: var(--dark-red);
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .delete-btn:hover {
            background-color: var(--hover-red);
        }

        form[style*="display: inline"] {
            background: none;
            padding: 0;
            margin: 0;
            box-shadow: none;
            border: none;
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

        @media (max-width: 600px) {
            .container {
                width: 100%;
                padding: 10px;
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
    <div class="container">
        <h2>Author Management</h2>
        
        <?php if (!empty($error)): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="msg success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Author Name</label>
                <input type="text" name="name" required>
            </div>
            <input type="submit" value="Add Author">
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($author = $authors->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($author['author_id']); ?></td>
                        <td><?php echo htmlspecialchars($author['name']); ?></td>
                        <td>
                            <form method="POST" action="" style="display: inline; background: none; padding: 0; box-shadow: none;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="author_id" value="<?php echo $author['author_id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this author?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>