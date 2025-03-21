<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get payroll ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "SELECT e.emp_id, e.name, c.phone_number, c.email_address, a.city_municipality, a.province, j.position, j.department, 
            w.hours_worked, w.hourly_rate, w.overtime_hours
            FROM payroll p
            JOIN employee e ON p.emp_id = e.emp_id
            LEFT JOIN contact c ON c.emp_id = e.emp_id
            LEFT JOIN address a ON a.emp_id = e.emp_id
            LEFT JOIN job j ON j.emp_id = e.emp_id
            LEFT JOIN work_hours w ON w.emp_id = e.emp_id
            WHERE e.emp_id = $id";
    
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        die("Record not found.");
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
            <input type="hidden" id="id" value="<?php echo $row['emp_id']; ?>">

            <label for="name">Employee Name:</label>
            <input type="text" id="name" value="<?php echo $row['name']; ?>" maxlength="60" required>

            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" value="<?php echo $row['phone_number']; ?>" maxlength="20" required>

            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" value="<?php echo $row['email_address']; ?>" maxlength="50" required>

            <label for="city_municipality">City/Municipality:</label>
            <input type="text" id="city_municipality" value="<?php echo $row['city_municipality']; ?>" placeholder="Enter City/Municipality" maxlength="20">

            <label for="province">Province:</label>
            <input type="text" id="province" value="<?php echo $row['province']; ?>" placeholder="Enter Province" maxlength="20">

            <label for="position">Position:</label>
            <input type="text" id="position" value="<?php echo $row['position']; ?>" placeholder="Enter Position" maxlength="20">

            <label for="department">Department:</label>
            <input type="text" id="department" value="<?php echo $row['department']; ?>" placeholder="Enter Department" maxlength="20">

            <label for="hours_worked">Hours Worked:</label>
            <input type="number" id="hours_worked" placeholder="Enter Hours Worked" value="<?php echo intval($row['hours_worked'], 0); ?>" onKeyPress="if(this.value.length==3) return false;">

            <label for="hourly_rate">Hourly Rate:</label>
            <input type="number" id="hourly_rate" placeholder="Enter Hourly Rate" value="<?php echo intval($row['hourly_rate'], 0); ?>"  onKeyPress="if(this.value.length==6) return false;">

            <label for="overtime_hours">Overtime:</label>
            <input type="number" id="overtime_hours" placeholder="Enter Overtime(optional)" value="<?php echo intval($row['overtime_hours'], 0); ?>"  onKeyPress="if(this.value.length==3) return false;">

            <button type="button" class="submit" onclick="updateRecord()">Update</button>
        </form>
    </div>

    <script>
        async function updateRecord() {
            let id = document.getElementById("id").value;
            let name = document.getElementById("name").value;
            let phone_number = document.getElementById("phone_number").value;
            let email_address = document.getElementById("email_address").value;
            let city_municipality = document.getElementById("city_municipality").value;
            let province = document.getElementById("province").value;
            let position = document.getElementById("position").value;
            let department = document.getElementById("department").value;
            let hours_worked = document.getElementById("hours_worked").value;
            let hourly_rate = document.getElementById("hourly_rate").value;
            let overtime_hours = document.getElementById("overtime_hours").value;

            if (name && phone_number && email_address && city_municipality && province && department && position && department && hours_worked && hourly_rate && overtime_hours) {
                try {
                    let response = await fetch("update_record.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            id: id,
                            name: name,
                            phone_number: phone_number,
                            email_address: email_address,
                            city_municipality: city_municipality,
                            province: province,
                            position: position,
                            department: department,
                            hours_worked: parseFloat(hours_worked),
                            hourly_rate: parseFloat(hourly_rate),
                            overtime_hours: parseFloat(overtime_hours)
                        })
                    });

                    let result = await response.text();
                    alert(result);
                    window.location.href = "index.php";
                } catch (error) {
                    console.error("Error updating record:", error);
                }
            }
        }
    </script>
</body>
</html>