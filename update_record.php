<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read JSON data from request
$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id']);
$name = $conn->real_escape_string($data['name']);
$phone_number = $conn->real_escape_string($data['phone_number']);
$email_address = $conn->real_escape_string($data['email_address']);
$city_municipality = $conn->real_escape_string($data['city_municipality']);
$province = $conn->real_escape_string($data['province']);
$position = $conn->real_escape_string($data['position']);
$department = $conn->real_escape_string($data['department']);
$hours_worked = floatval($data['hours_worked']);
$hourly_rate = floatval($data['hourly_rate']);
$overtime_hours = floatval($data['overtime_hours']);

// Update employee table
$stmt = $conn->prepare("UPDATE employee SET name = ? WHERE emp_id = ?");
$stmt->bind_param("si", $name, $id);
$stmt->execute();
$stmt->close();

// Update contact table
$stmt = $conn->prepare("UPDATE contact SET phone_number = ?, email_address = ? WHERE emp_id = ?");
$stmt->bind_param("ssi", $phone_number, $email_address, $id);
$stmt->execute();
$stmt->close();

// Update address table
$stmt = $conn->prepare("UPDATE address SET city_municipality = ?, province = ? WHERE emp_id = ?");
$stmt->bind_param("ssi", $city_municipality, $province, $id);
$stmt->execute();
$stmt->close();

// Update job table
$stmt = $conn->prepare("UPDATE job SET position = ?, department = ? WHERE emp_id = ?");
$stmt->bind_param("ssi", $position, $department, $id);
$stmt->execute();
$stmt->close();

// Update work hours table
$stmt = $conn->prepare("UPDATE work_hours SET hours_worked = ?, hourly_rate = ?, overtime_hours = ? WHERE emp_id = ?");
$stmt->bind_param("dddi", $hours_worked, $hourly_rate, $overtime_hours, $id);
$stmt->execute();
$stmt->close();

// Calculate salary
$overtime_pay = $hourly_rate * 1.25 * $overtime_hours;
$salary = $hours_worked * $hourly_rate + ($hourly_rate * 1.25 * $overtime_hours);

// SSS computation
function getSSS($conn, $salary) {
    $rate = 0;
    $monthly_salary_credit = 0;

    // Query to get the correct bracket
    $stmt = $conn->prepare("SELECT monthly_salary_credit, rate FROM sss_bracket 
                            WHERE ? BETWEEN min_salary AND max_salary");
    $stmt->bind_param("d", $salary);
    $stmt->execute();
    $stmt->bind_result($monthly_salary_credit, $rate);
    $stmt->fetch();
    $stmt->close();

    return $monthly_salary_credit * $rate;
}

// PhilHealth computation
function getPhilHealth($conn, $salary) {
    $rate = 0;
    $fixed_contribution = 0;
    $min_salary = 0;
    $max_salary = NULL;
    
    // Query to get the appropriate bracket based on salary
    $stmt = $conn->prepare("SELECT rate, fixed_contribution, min_salary, max_salary 
                                FROM philhealth_bracket 
                                WHERE ? BETWEEN min_salary AND COALESCE(max_salary, ?)");
    $stmt->bind_param("dd", $salary, $salary);
    $stmt->execute();
    $stmt->bind_result($rate, $fixed_contribution, $min_salary, $max_salary);
    $stmt->fetch();
    $stmt->close();
    
    // Determine PhilHealth contribution
    if ($fixed_contribution > 0) {
        return $fixed_contribution;
    } else {
        return min(($salary * $rate)/2, 5000);
    }
}

// Pag-IBIG computation
function getPagIbig($conn, $salary) {
    $rate = 0;
    $fixed_contribution = 0;

    // Query to get the appropriate bracket
    $stmt = $conn->prepare("SELECT rate, fixed_contribution FROM pagibig_bracket 
                            WHERE ? BETWEEN min_salary AND COALESCE(max_salary, ?)");
    $stmt->bind_param("dd", $salary, $salary);
    $stmt->execute();
    $stmt->bind_result($rate, $fixed_contribution);
    $stmt->fetch();
    $stmt->close();

    // Determine Pag-IBIG contribution
    return ($fixed_contribution > 0) ? $fixed_contribution : ($salary * $rate);
}

// Retrieve tax rate from database
function getTaxRate($conn, $taxableIncome) {
    $rate = 0;
    $base_tax = 0;
    $min_income = 0;
    $stmt = $conn->prepare("SELECT rate, base_tax, min_income FROM withholding_tax_bracket WHERE ? BETWEEN min_income AND max_income");
    $stmt->bind_param("d", $taxableIncome);
    $stmt->execute();
    $stmt->bind_result($rate, $base_tax, $min_income);
    $stmt->fetch();
    $stmt->close();
    return [$rate, $base_tax, $min_income];
}

// Calculate deductions
$sss = getSSS($conn, $salary);
$philHealth = getPhilHealth($conn, $salary);
$pagIbig = getPagIbig($conn, $salary);
$taxableIncome = $salary - ($sss + $philHealth + $pagIbig);

// Get tax rate dynamically
list($rate, $base_tax, $min_income) = getTaxRate($conn, $taxableIncome);
$withholdingTax = $rate * ($taxableIncome - $min_income) + $base_tax;

$net_salary = $salary - ($sss + $philHealth + $pagIbig + $withholdingTax);

// Update payroll table with salary calculations
$stmt = $conn->prepare("UPDATE payroll SET salary = ?, overtime_pay = ?, sss = ?, philhealth = ?, pagibig = ?, taxable_income = ?,  withholding_tax = ?, net_salary = ? WHERE payroll_id = ?");
$stmt->bind_param("ddddddddi", $salary, $overtime_pay, $sss, $philHealth, $pagIbig, $taxableIncome, $withholdingTax, $net_salary, $id);
$stmt->execute();
$stmt->close();

echo "Record updated successfully.";

$conn->close();
?>