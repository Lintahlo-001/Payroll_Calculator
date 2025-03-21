<?php
// Set response headers to allow API calls from frontend
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow all origins (for testing)
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
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
    
    // Validate input
    if (!isset($data["salary"], $data["name"])) {
        echo json_encode(["error" => "Invalid request"]);
        exit;
    }

    $salary = floatval($data["salary"]);
    $name = $conn->real_escape_string($data["name"]);

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
        $max_salary = NULL; // Can be NULL for open-ended ranges
        
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
            return $fixed_contribution; // Fixed amount (e.g., ₱500 or ₱5,000)
        } else {
            return min(($salary * $rate)/2, 5000); // Ensure it does not exceed max contribution
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

    $stmt = $conn->prepare("INSERT INTO payroll (emp_id, salary, sss, philhealth, pagibig, taxable_income, withholding_tax, net_salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iddddddd", $emp_id, $salary, $sss, $philHealth, $pagIbig, $taxableIncome, $withholdingTax, $net_salary);
    $stmt->execute();
    $stmt->close();

    // Return response in JSON format
    echo json_encode([
        "name" => $name,
        "salary" => $salary,
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