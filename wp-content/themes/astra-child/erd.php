
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
    <title>Library System ERD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mermaid/9.4.3/mermaid.min.js"></script>
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

        .erd-container {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.2);
            margin-top: 20px;
            overflow-x: auto;
        }

        h1 {
            color: var(--primary-red);
            text-align: center;
            margin-bottom: 30px;
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

        #erd-diagram {
            width: 100%;
            min-height: 800px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="profile.php" class="nav-button">Back to Profile</a>
        <h1>Library Management System ERD</h1>
        
        <div class="erd-container">
            <pre class="mermaid" id="erd-diagram">
                erDiagram
                    USERS ||--o{ BORROWERS : borrows
                    USERS ||--o{ REVIEWS : writes
                    USERS ||--o{ FEEDBACK : submits
                    BOOKS ||--o{ BORROWERS : borrowed_by
                    BOOKS ||--o{ REVIEWS : has
                    BOOKS ||--o{ INVENTORY : tracked_in
                    AUTHORS ||--o{ BOOKS : writes
                    CATEGORIES ||--o{ BOOKS : belongs_to

                    USERS {
                        int user_id PK
                        string name
                        string email
                        string password
                        int campus_id
                        int age
                        string role
                    }

                    BOOKS {
                        int book_id PK
                        string title
                        int author_id FK
                        int category_id FK
                        string image_url
                        string pdf_link
                    }

                    AUTHORS {
                        int author_id PK
                        string name
                    }

                    CATEGORIES {
                        int category_id PK
                        string category_name
                    }

                    INVENTORY {
                        int inventory_id PK
                        int book_id FK
                        string location
                        boolean is_available
                    }

                    BORROWERS {
                        int borrow_id PK
                        int user_id FK
                        int book_id FK
                        date borrow_date
                        date return_date
                    }

                    REVIEWS {
                        int review_id PK
                        int user_id FK
                        int book_id FK
                        string review_text
                        date review_date
                    }

                    FEEDBACK {
                        int feedback_id PK
                        int user_id FK
                        string message
                        date feedback_date
                    }
            </pre>
        </div>
    </div>

    <script>
        mermaid.initialize({
            theme: 'default',
            securityLevel: 'loose',
            er: {
                diagramPadding: 20,
                entityPadding: 15,
                useMaxWidth: true,
            }
        });
    </script>
</body>
</html>