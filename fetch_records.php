<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT p.payroll_id, e.name, p.net_salary 
        FROM payroll p
        INNER JOIN employee e ON p.emp_id = e.emp_id";
$result = $conn->query($sql);

echo "<div class='table-container'>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Net Salary</th>
                <th>Actions</th>
            </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['payroll_id']}</td>
            <td>{$row['name']}</td>
            <td>₱" . number_format($row['net_salary'], 2) . "</td>
            <td>
                <button class='view-btn' onclick='toggleView({$row['payroll_id']})'>View</button>
                <button class='delete-btn' onclick='deleteRecord({$row['payroll_id']})'>Delete</button>
                <a href='edit_record.php?id={$row['payroll_id']}' class='edit-btn'>Edit</a>
            </td>
          </tr>
          <tr id='details-{$row['payroll_id']}' class='details-row' style='display: none;'>
            <td colspan='4'>
                <div id='result-{$row['payroll_id']}'></div>
            </td>
          </tr>";
}
echo "</table>
    </div>";

$conn->close();
?>