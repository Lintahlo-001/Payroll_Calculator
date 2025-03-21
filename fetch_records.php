<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT e.emp_id, e.name, c.phone_number, c.email_address, a.city_municipality, a.province, j.position, j.department, 
        w.hours_worked, w.hourly_rate, w.overtime_hours, p.net_salary 
        FROM payroll p
        JOIN employee e ON p.emp_id = e.emp_id
        LEFT JOIN contact c ON c.emp_id = e.emp_id
        LEFT JOIN address a ON a.emp_id = e.emp_id
        LEFT JOIN job j ON j.emp_id = e.emp_id
        LEFT JOIN work_hours w ON w.emp_id = e.emp_id";

$result = $conn->query($sql);

echo "<div class='table-container'>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>City/Municipality</th>
                <th>Province</th>
                <th>Position</th>
                <th>Department</th>
                <th>Hours Worked</th>
                <th>Rate</th>
                <th>Overtime</th>
                <th>Net Salary</th>
                <th>Actions</th>
            </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['emp_id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['phone_number']}</td>
            <td>{$row['email_address']}</td>
            <td>{$row['city_municipality']}</td>
            <td>{$row['province']}</td>
            <td>{$row['position']}</td>
            <td>{$row['department']}</td>
            <td>" . number_format($row['hours_worked'], 0) . "</td>
            <td>" . number_format($row['hourly_rate'], 0) . "</td>
            <td>" . number_format($row['overtime_hours'], 0) . "</td>
            <td>₱" . number_format($row['net_salary'], 2) . "</td>
            <td>
                <button class='view-btn' onclick='toggleView({$row['emp_id']})'>View</button>
                <button class='delete-btn' onclick='deleteRecord({$row['emp_id']})'>Delete</button>
                <a href='edit_record.php?id={$row['emp_id']}' class='edit-btn'>Edit</a>
            </td>
          </tr>
          <tr id='details-{$row['emp_id']}' class='details-row' style='display: none;'>
            <td colspan='4'>
                <div id='result-{$row['emp_id']}'></div>
            </td>
          </tr>";
}
echo "</table>
    </div>";

$conn->close();
?>