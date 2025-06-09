<?php
require_once('../../../wp-config.php'); // Get WordPress database configuration

header('Content-Type: application/json');

// Database connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "wordpress";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

if (isset($_GET['table']) && isset($_GET['id'])) {
    $table = $conn->real_escape_string($_GET['table']);
    $id = intval($_GET['id']);
    
    // Get the correct ID column name for each table
    $id_columns = [
        'users' => 'user_id',
        'books' => 'book_id',
        'authors' => 'author_id',
        'categories' => 'category_id',
        'inventory' => 'inventory_id',
        'borrowers' => 'borrow_id',
        'reviews' => 'review_id',
        'feedback' => 'feedback_id'
    ];
    
    $id_column = isset($id_columns[$table]) ? $id_columns[$table] : $table . '_id';
    
    $sql = "SELECT * FROM $table WHERE $id_column = $id";
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'record' => $row]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Record not found: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
}

$conn->close();