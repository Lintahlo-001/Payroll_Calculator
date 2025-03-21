<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee ID from the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "SELECT payroll.payroll_id, employee.name FROM payroll 
        INNER JOIN employee ON payroll.emp_id = employee.emp_id 
        WHERE payroll.payroll_id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        die("Employee not found.");
    }
} else {
    die("Invalid ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Edit Employee</h2>
        <form id="editForm">
            <input type="hidden" id="id" value="<?php echo $row['payroll_id']; ?>">

            <label for="name">Employee Name:</label>
            <input type="text" id="name" value="<?php echo $row['name']; ?>" required>

            <label for="hours_worked">Hours Worked:</label>
            <input type="number" id="hours_worked" placeholder="Enter hours worked" required>

            <label for="hourly_rate">Hourly Rate:</label>
            <input type="number" id="hourly_rate" placeholder="Enter hourly rate" required>

            <button type="button" onclick="updateRecord()">Update</button>
        </form>
    </div>

    <script>
        async function updateRecord() {
            let id = document.getElementById("id").value;
            let name = document.getElementById("name").value;
            let hoursWorked = document.getElementById("hours_worked").value;
            let hourlyRate = document.getElementById("hourly_rate").value;

            if (name && hoursWorked && hourlyRate) {
                try {
                    let response = await fetch("update_record.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            id: id,
                            name: name,
                            hours_worked: parseFloat(hoursWorked),
                            hourly_rate: parseFloat(hourlyRate)
                        })
                    });

                    let result = await response.text();
                    alert(result);
                    window.location.href = "index.php"; // Redirect back to the main page
                } catch (error) {
                    console.error("Error updating record:", error);
                }
            }
        }
    </script>
</body>
</html>
