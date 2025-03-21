<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$host = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Handle API request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    
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

    // Store data in database
    $stmt = $conn->prepare("INSERT INTO employee (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $emp_id = $stmt->insert_id;
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO contact (emp_id, phone_number, email_address) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $emp_id, $phone_number, $email_address);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO address (emp_id, city_municipality, province) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $emp_id, $city_municipality, $province);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO job (emp_id, position, department) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $emp_id, $position, $department);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO work_hours (emp_id, hours_worked, hourly_rate, overtime_hours) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $emp_id, $hours_worked, $hourly_rate, $overtime_hours);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO payroll (emp_id, salary, overtime_pay, sss, philhealth, pagibig, taxable_income, withholding_tax, net_salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idddddddd", $emp_id, $salary, $overtime_pay, $sss, $philHealth, $pagIbig, $taxableIncome, $withholdingTax, $net_salary);
    $stmt->execute();
    $stmt->close();

    // Return response in JSON format
    echo json_encode([
        "name" => $name,
        "salary" => $salary,
        "overtime_pay"=> $overtime_pay,
        "sss" => $sss,
        "philhealth" => $philHealth,
        "pagibig" => $pagIbig,
        "taxable_income" => $taxableIncome,
        "withholding_tax" => $withholdingTax,
        "net_salary" => $net_salary
    ]);
}

// Close database connection
$conn->close();
?>