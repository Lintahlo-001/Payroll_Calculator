<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);

$sql = "SELECT p.salary, p.overtime_pay, p.sss, p.philhealth, p.pagibig, p.taxable_income, p.withholding_tax, p.net_salary 
        FROM payroll p
        INNER JOIN employee e ON p.emp_id = e.emp_id
        WHERE p.payroll_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if ($result) {
    echo json_encode($result);
} else {
    echo json_encode(["error" => "No record found"]);
}
?>