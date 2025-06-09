
<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Library System Wireframe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-red: #ff4444;
            --secondary-red: #ff8585;
            --dark-red: #cc0000;
            --light-red: #ffe6e6;
            --text-dark: #2d3436;
            --white: #ffffff;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--light-red);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .wireframe-container {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            margin-top: 20px;
        }

        .nav-button {
            display: inline-block;
            background-color: var(--primary-red);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .nav-button:hover {
            background-color: var(--dark-red);
        }

        h1, h2 {
            color: var(--primary-red);
        }

        .wireframe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .wireframe-card {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }

        .wireframe-title {
            font-weight: bold;
            color: var(--primary-red);
            margin-bottom: 10px;
        }

        .wireframe-image {
            width: 100%;
            height: 200px;
            background: #dee2e6;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: monospace;
            color: #6c757d;
            border: 1px dashed #adb5bd;
        }

        .page-flow {
            margin: 40px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .flow-diagram {
            font-family: monospace;
            white-space: pre;
            overflow-x: auto;
            padding: 20px;
            background: #fff;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="profile.php" class="nav-button">Back to Profile</a>
        <h1>Website Wireframe & Flow</h1>

        <div class="wireframe-container">
            <h2>Page Structure</h2>
            <div class="wireframe-grid">
                <div class="wireframe-card">
                    <div class="wireframe-title">Login Page</div>
                    <div class="wireframe-image">
                        [Header]<br>
                        [Login Form]<br>
                        - Username<br>
                        - Password<br>
                        [Submit Button]
                    </div>
                </div>

                <div class="wireframe-card">
                    <div class="wireframe-title">Profile Dashboard</div>
                    <div class="wireframe-image">
                        [User Info]<br>
                        [Navigation Buttons]<br>
                        [Borrowing History]<br>
                        [Password Change]
                    </div>
                </div>

                <div class="wireframe-card">
                    <div class="wireframe-title">Bookshelf</div>
                    <div class="wireframe-image">
                        [Search Bar]<br>
                        [Book Grid]<br>
                        - Book Cards<br>
                        - Filter Options
                    </div>
                </div>

                <div class="wireframe-card">
                    <div class="wireframe-title">Book Management</div>
                    <div class="wireframe-image">
                        [Add Book Form]<br>
                        [Book List]<br>
                        [Edit/Delete Options]
                    </div>
                </div>

                <div class="wireframe-card">
                    <div class="wireframe-title">Borrowing System</div>
                    <div class="wireframe-image">
                        [Available Books]<br>
                        [Borrow Form]<br>
                        [Return Options]
                    </div>
                </div>

                <div class="wireframe-card">
                    <div class="wireframe-title">Review System</div>
                    <div class="wireframe-image">
                        [Book Selection]<br>
                        [Review Form]<br>
                        [Past Reviews]
                    </div>
                </div>
            </div>

            <div class="page-flow">
                <h2>User Flow Diagram</h2>
                <div class="flow-diagram">
                    Login → Profile Dashboard
                        ├─→ Bookshelf
                        │   └─→ Book Details
                        │       ├─→ Borrow Book
                        │       └─→ Write Review
                        ├─→ Borrowing History
                        ├─→ Review Management
                        └─→ Teacher Functions
                            ├─→ Inventory Management
                            ├─→ Database Management
                            ├─→ Category Management
                            └─→ Author Management
                </div>
            </div>
        </div>
    </div>
</body>
</html>