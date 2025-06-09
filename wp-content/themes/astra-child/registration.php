<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "wordpress"; // Replace with your DB name
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campus_id = $_POST['campus_id'];
    $age = $_POST['age'];
    
    // Add age validation
    if ($age < 1) {
        $error = "Invalid age: Must be at least 1 year old";
    } else {
        $role = $_POST['role'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert data
            $sql = "INSERT INTO users (campus_id, age, role, name, email, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", $campus_id, $age, $role, $name, $email, $password);

            if ($stmt->execute()) {
                $success = "Registration successful!";
                $_SESSION['registration_success'] = true;
                header("refresh:2;url=" . dirname($_SERVER['REQUEST_URI']) . "/login.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Registration</title>
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
            transition: all 0.3s ease;
        }

        body {
            background-color: var(--bg-color);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: var(--text-dark);
        }

        .page-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-container {
            flex: 0 1 400px;
            margin-right: 380px; /* Add margin to prevent overlap with gif */
        }

        .gif-container {
            position: fixed;
            right: 80px; /* Changed from 40px to 80px to move closer to form */
            top: 34%;
            transform: translateY(-34%);
            width: 350px; /* Slightly reduced width to get closer */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.3);
            background: var(--card-bg);
            padding: 15px;
            z-index: 10;
            animation: float 3s ease-in-out infinite;
        }

        .gif-container img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            display: block;
        }

        @keyframes float {
            0%, 100% { transform: translateY(-30%) translateX(0); } /* Updated transform */
            50% { transform: translateY(-30%) translateY(-15px); }  /* Updated transform */
        }

        form {
            width: 100%;
            margin: 0;
            padding: 25px;
            background-color: var(--card-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid var(--input-border);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text-dark);
        }

        /* Add specific styles for number and password inputs */
        input[type="number"],
        input[type="password"] {
            background-color: var(--input-bg);
            color: var(--text-dark);
            border-color: var(--input-border);
        }

        input[type="number"]:focus,
        input[type="password"]:focus {
            border-color: var(--primary-red);
            outline: none;
        }

        input[type="submit"] {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 12px;
            margin-top: 15px;
        }

        input[type="submit"]:hover {
            background-color: var(--dark-red);
        }

        input[type="checkbox"] {
            width: auto;
            margin-right: 5px;
        }

        /* Style the checkbox text */
        .checkbox-wrapper {
            color: var(--text-dark);
        }

        .msg {
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .msg.error {
            background-color: var(--light-red);
            color: var(--dark-red);
        }

        .msg.success {
            background-color: #e6ffe6;
            color: #008000;
        }

        label {
            color: var(--text-dark);
            display: block;
            margin-top: 10px;
        }

        h2 {
            color: var(--primary-red);
            text-align: center;
            margin-bottom: 20px;
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

        @media (max-width: 1200px) {
            .page-wrapper {
                flex-direction: column;
                align-items: center;
            }

            .gif-container {
                position: static;
                width: 100%;
                max-width: 400px;
                margin: 20px 0;
                transform: none;
                animation: none;
            }

            .form-container {
                width: 100%;
                max-width: 400px;
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

        function togglePassword() {
            var pass = document.getElementById("password");
            if (pass.type === "password") {
                pass.type = "text";
            } else {
                pass.type = "password";
            }
        }

        function validateAge(input) {
            if (input.value < 1) {
                input.setCustomValidity('Age must be at least 1 year old');
            } else {
                input.setCustomValidity('');
            }
        }
    </script>
</head>
<body>
    <button class="theme-switch" onclick="toggleTheme()" aria-label="Toggle dark mode"></button>
    <div class="page-wrapper">
        <div class="form-container">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                <h2>Register</h2>
                <?php if (!empty($error)) echo "<div class='msg error'>$error</div>"; ?>
                <?php if (!empty($success)) echo "<div class='msg success'>$success</div>"; ?>

                <label>Campus ID</label>
                <input type="number" name="campus_id" required>

                <label>Age</label>
                <input type="number" name="age" required min="18" onchange="validateAge(this)">

                <label>Role</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="Student">Student</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Others">Others</option>
                </select>

                <label>Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" id="password" required>

                <input type="checkbox" onclick="togglePassword()"> Show Password

                <input type="submit" value="Register">
            </form>
        </div>
        <div class="gif-container">
            <img src="/wordpress/wp-content/uploads/library/gallery/gif/sign up.gif" alt="Sign up animation">
        </div>
    </div>
</body>
</html>
