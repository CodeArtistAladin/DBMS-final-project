<?php
session_start();

// Check if user is admin/teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
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

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete']) && !empty($_POST['selected_records'])) {
        $table = $_POST['table_name'];
        $id_column = $_POST['id_column'];
        $selected = implode(',', array_map('intval', $_POST['selected_records']));
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // If deleting from books table, delete related records first
            if ($table == 'books') {
                // Delete from inventory
                $sql = "DELETE FROM inventory WHERE book_id IN ($selected)";
                $conn->query($sql);
                
                // Delete from borrowers
                $sql = "DELETE FROM borrowers WHERE book_id IN ($selected)";
                $conn->query($sql);
                
                // Delete from reviews
                $sql = "DELETE FROM reviews WHERE book_id IN ($selected)";
                $conn->query($sql);
            }
            
            // Now delete from the main table
            $sql = "DELETE FROM $table WHERE $id_column IN ($selected)";
            if ($conn->query($sql)) {
                $conn->commit();
                $success = "Selected records deleted successfully!";
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error deleting records: " . $e->getMessage();
        }
    }

    // Add edit handling
    if (isset($_POST['edit'])) {
        $table = $_POST['table_name'];
        $id_column = $_POST['id_column'];
        $record_id = $_POST['record_id'];
        
        $updates = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'edit_') === 0) {
                $column = substr($key, 5); // Remove 'edit_' prefix
                $value = $conn->real_escape_string($value);
                $updates[] = "$column = '$value'";
            }
        }
        
        if (!empty($updates)) {
            $sql = "UPDATE $table SET " . implode(', ', $updates) . 
                   " WHERE $id_column = " . intval($record_id);
            
            if ($conn->query($sql)) {
                $success = "Record updated successfully!";
            } else {
                $error = "Error updating record: " . $conn->error;
            }
        }
    }
}

// Get all tables
$tables = [
    ['name' => 'users', 'id' => 'user_id', 'display' => 'name, email, role'],
    ['name' => 'books', 'id' => 'book_id', 'display' => 'title'],
    ['name' => 'authors', 'id' => 'author_id', 'display' => 'name'],
    ['name' => 'categories', 'id' => 'category_id', 'display' => 'category_name'],
    ['name' => 'inventory', 'id' => 'inventory_id', 'display' => 'location, is_available'],
    ['name' => 'borrowers', 'id' => 'borrow_id', 'display' => 'borrow_date, return_date'],
    ['name' => 'reviews', 'id' => 'review_id', 'display' => 'review_text'],
    ['name' => 'feedback', 'id' => 'feedback_id', 'display' => 'message']
];
?>

<!DOCTYPE html>
<html data-theme="light">
<head>
    <title>Database Management</title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: var(--primary-red);
            margin-bottom: 20px;
        }

        h2 {
            color: var(--dark-red);
            margin-bottom: 15px;
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
        
        .table-container {
            background: var(--card-bg);
            color: var(--text-dark);
            border-color: var(--light-red);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(255, 68, 68, 0.2);
            margin-bottom: 20px;
            border: 1px solid var(--light-red);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            color: var(--text-dark);
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

        tr:hover {
            background-color: var(--light-red);
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
        
        .delete-btn {
            background-color: var(--dark-red);
            color: var(--white);
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .delete-btn:hover {
            background-color: var(--hover-red);
        }
        
        .select-all {
            margin-right: 10px;
            accent-color: var(--primary-red);
        }

        input[type="checkbox"] {
            accent-color: var(--primary-red);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .table-container {
                padding: 15px;
                overflow-x: auto;
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

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: var(--card-bg);
            margin: 5% auto; /* Changed from 15% to 5% to move it higher */
            padding: 20px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            color: var(--text-dark);
            animation: modalSlide 0.3s ease; /* Added smooth animation */
        }

        /* Add animation keyframes */
        @keyframes modalSlide {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: var(--text-dark);
        }

        .edit-input {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid var(--input-border);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text-dark);
        }

        .edit-form input,
        .edit-form select {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid var(--input-border);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text-dark);
        }

        .edit-form label {
            display: block;
            margin-top: 10px;
            color: var(--text-dark);
            font-weight: bold;
        }

        .edit-btn {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .edit-btn:hover {
            background-color: var(--dark-red);
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

        function toggleAll(source, tableName) {
            const checkboxes = document.getElementsByClassName(tableName + '-checkbox');
            for (let checkbox of checkboxes) {
                checkbox.checked = source.checked;
            }
        }

        // Add this to your existing script section
        function editRecord(tableName, recordId) {
            fetch(`get_record.php?table=${tableName}&id=${recordId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = document.getElementById('editModal');
                        const fieldsContainer = document.getElementById('edit_fields');
                        
                        // Set form values
                        document.getElementById('edit_table_name').value = tableName;
                        document.getElementById('edit_id_column').value = `${tableName}_id`;
                        document.getElementById('edit_record_id').value = recordId;
                        
                        // Clear previous fields
                        fieldsContainer.innerHTML = '';
                        
                        // Create input fields for each column
                        Object.entries(data.record).forEach(([column, value]) => {
                            if (column !== `${tableName}_id`) {
                                const div = document.createElement('div');
                                div.innerHTML = `
                                    <label>${column.replace(/_/g, ' ').charAt(0).toUpperCase() + column.slice(1)}</label>
                                    <input type="text" name="edit_${column}" value="${value || ''}" class="edit-input">
                                `;
                                fieldsContainer.appendChild(div);
                            }
                        });
                        
                        modal.style.display = 'block';
                    } else {
                        alert('Error loading record: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading record. Please try again.');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editModal');
            const closeBtn = document.querySelector('.close');
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });

        function openEditModal(recordId, tableName) {
            console.log('Fetching record:', tableName, recordId);
            
            // Get the correct ID column name
            const idColumns = {
                'users': 'user_id',
                'books': 'book_id',
                'authors': 'author_id',
                'categories': 'category_id',
                'inventory': 'inventory_id',
                'borrowers': 'borrow_id',
                'reviews': 'review_id',
                'feedback': 'feedback_id'
            };
            
            const idColumn = idColumns[tableName] || tableName + '_id';
            
            fetch(`get_record.php?table=${tableName}&id=${recordId}`)
                .then(response => {
                    console.log('Response:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Data:', data);
                    if (data.success) {
                        const modal = document.getElementById('editModal');
                        const fieldsContainer = document.getElementById('edit_fields');
                        
                        // Set form values
                        document.getElementById('edit_table_name').value = tableName;
                        document.getElementById('edit_id_column').value = idColumn;
                        document.getElementById('edit_record_id').value = recordId;
                        
                        // Clear previous fields
                        fieldsContainer.innerHTML = '';
                        
                        // Create input fields for each column
                        Object.entries(data.record).forEach(([column, value]) => {
                            if (column !== idColumn) {
                                const div = document.createElement('div');
                                div.innerHTML = `
                                    <label>${column.replace(/_/g, ' ').charAt(0).toUpperCase() + column.slice(1)}</label>
                                    <input type="text" name="edit_${column}" value="${value || ''}" class="edit-input">
                                `;
                                fieldsContainer.appendChild(div);
                            }
                        });
                        
                        modal.style.display = 'block';
                    } else {
                        alert('Error loading record: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading record. Please try again.');
                });
        }

        // Modal close handlers
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editModal');
            const closeBtn = document.querySelector('.close');
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });
    </script>
</head>
<body>
    <button class="theme-switch" onclick="toggleTheme()" aria-label="Toggle dark mode"></button>
    <div class="container">
        <h1>Database Management</h1>
        <a href="profile.php" class="nav-button">Back to Profile</a>

        <?php if (!empty($error)): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="msg success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php foreach ($tables as $table): ?>
            <div class="table-container">
                <h2><?php echo ucfirst($table['name']); ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="table_name" value="<?php echo $table['name']; ?>">
                    <input type="hidden" name="id_column" value="<?php echo $table['id']; ?>">
                    
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="select-all" 
                                           onclick="toggleAll(this, '<?php echo $table['name']; ?>')">
                                </th>
                                <th>ID</th>
                                <?php 
                                    $display_columns = explode(', ', $table['display']);
                                    foreach ($display_columns as $col):
                                ?>
                                    <th><?php echo ucfirst($col); ?></th>
                                <?php endforeach; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT {$table['id']}, {$table['display']} FROM {$table['name']}";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_records[]" 
                                               value="<?php echo $row[$table['id']]; ?>"
                                               class="<?php echo $table['name']; ?>-checkbox">
                                    </td>
                                    <td><?php echo $row[$table['id']]; ?></td>
                                    <?php foreach ($display_columns as $col): ?>
                                        <td><?php echo htmlspecialchars($row[trim($col)]); ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button type="button" 
                                                class="edit-btn" 
                                                onclick="openEditModal('<?php echo $row[$table['id']]; ?>', '<?php echo $table['name']; ?>')">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="delete" class="delete-btn" 
                            onclick="return confirm('Are you sure you want to delete selected records?')">
                        Delete Selected
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Record</h2>
            <form method="POST" class="edit-form">
                <input type="hidden" name="table_name" id="edit_table_name">
                <input type="hidden" name="id_column" id="edit_id_column">
                <input type="hidden" name="record_id" id="edit_record_id">
                <div id="edit_fields"></div>
                <button type="submit" name="edit" class="edit-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(recordId, tableName) {
            console.log('Fetching record:', tableName, recordId);
            
            // Get the correct ID column name
            const idColumns = {
                'users': 'user_id',
                'books': 'book_id',
                'authors': 'author_id',
                'categories': 'category_id',
                'inventory': 'inventory_id',
                'borrowers': 'borrow_id',
                'reviews': 'review_id',
                'feedback': 'feedback_id'
            };
            
            const idColumn = idColumns[tableName] || tableName + '_id';
            
            fetch(`get_record.php?table=${tableName}&id=${recordId}`)
                .then(response => {
                    console.log('Response:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Data:', data);
                    if (data.success) {
                        const modal = document.getElementById('editModal');
                        const fieldsContainer = document.getElementById('edit_fields');
                        
                        // Set form values
                        document.getElementById('edit_table_name').value = tableName;
                        document.getElementById('edit_id_column').value = idColumn;
                        document.getElementById('edit_record_id').value = recordId;
                        
                        // Clear previous fields
                        fieldsContainer.innerHTML = '';
                        
                        // Create input fields for each column
                        Object.entries(data.record).forEach(([column, value]) => {
                            if (column !== idColumn) {
                                const div = document.createElement('div');
                                div.innerHTML = `
                                    <label>${column.replace(/_/g, ' ').charAt(0).toUpperCase() + column.slice(1)}</label>
                                    <input type="text" name="edit_${column}" value="${value || ''}" class="edit-input">
                                `;
                                fieldsContainer.appendChild(div);
                            }
                        });
                        
                        modal.style.display = 'block';
                    } else {
                        alert('Error loading record: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading record. Please try again.');
                });
        }

        // Modal close handlers
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editModal');
            const closeBtn = document.querySelector('.close');
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>