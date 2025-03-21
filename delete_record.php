<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    
    $stmt = $conn->prepare("DELETE FROM employee WHERE emp_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Record deleted successfully!";
    } else {
        echo "Error deleting record!";
    }
    
    $stmt->close();
}

$conn->close();
?>