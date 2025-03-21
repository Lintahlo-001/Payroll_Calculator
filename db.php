<?php
$servername = "localhost";
$username = "root"; // Change if necessary
$password = ""; // Change if necessary
$dbname = "payroll_db";

try {
    // Connect to MySQL
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");
    
    // Create employee table
    $conn->exec("CREATE TABLE IF NOT EXISTS employee (
        emp_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL
    )");

    // Create contact table
    $conn->exec("CREATE TABLE IF NOT EXISTS contact (
        contact_id INT AUTO_INCREMENT PRIMARY KEY,
        emp_id INT,
        phone_number VARCHAR(20),
        email_address VARCHAR(255),
        FOREIGN KEY (emp_id) REFERENCES employee(emp_id) ON DELETE CASCADE
    )");

    // Create address table
    $conn->exec("CREATE TABLE IF NOT EXISTS address (
        address_id INT AUTO_INCREMENT PRIMARY KEY,
        emp_id INT,
        city_municipality VARCHAR(50),
        province VARCHAR(50),
        FOREIGN KEY (emp_id) REFERENCES employee(emp_id) ON DELETE CASCADE
    )");

    // Create job table
    $conn->exec("CREATE TABLE IF NOT EXISTS job (
        job_id INT AUTO_INCREMENT PRIMARY KEY,
        emp_id INT,
        position VARCHAR(50),
        department VARCHAR(50),
        FOREIGN KEY (emp_id) REFERENCES employee(emp_id) ON DELETE CASCADE
    )");

    // Create work hours table
    $conn->exec("CREATE TABLE IF NOT EXISTS work_hours (
        work_id INT AUTO_INCREMENT PRIMARY KEY,
        emp_id INT,
        hours_worked DECIMAL(5,2),
        hourly_rate DECIMAL(8,2),
        overtime_hours DECIMAL(5,2),
        FOREIGN KEY (emp_id) REFERENCES employee(emp_id) ON DELETE CASCADE
    )");

    // Create payroll table
    $conn->exec("CREATE TABLE IF NOT EXISTS payroll (
        payroll_id INT AUTO_INCREMENT PRIMARY KEY,
        emp_id INT NOT NULL,
        salary DECIMAL(15,2) NOT NULL,
        overtime_pay DECIMAL (15,2) NOT NULL,
        sss DECIMAL(15,2) NOT NULL,
        philhealth DECIMAL(15,2) NOT NULL,
        pagibig DECIMAL(15,2) NOT NULL,
        taxable_income DECIMAL(15,2) NOT NULL,
        withholding_tax DECIMAL(15,2) NOT NULL,
        net_salary DECIMAL(15,2) NOT NULL,
        FOREIGN KEY (emp_id) REFERENCES employee(emp_id) ON DELETE CASCADE
    )");

    // Create sss table
    $conn->exec("CREATE TABLE IF NOT EXISTS sss_bracket (
        id INT AUTO_INCREMENT PRIMARY KEY,
        min_salary DECIMAL(10,2) NOT NULL,
        max_salary DECIMAL(10,2) NULL,
        monthly_salary_credit DECIMAL(10,2) NOT NULL,
        rate DECIMAL(5,4) NOT NULL DEFAULT 0.05
    )");         
    
    // Insert sss data if table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM sss_bracket");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO sss_bracket (min_salary, max_salary, monthly_salary_credit, rate) VALUES
            (0.00, 5249.00, 500.00,0.05),
            (5250.00, 5749.99, 5500.00, 0.05),
            (5750.00, 6249.99, 6000.00, 0.05),
            (6250.00, 6749.99, 6500.00, 0.05),
            (6750.00, 7249.99, 7000.00, 0.05),
            (7250.00, 7749.99, 7500.00, 0.05),
            (7750.00, 8249.99, 8000.00, 0.05),
            (8250.00, 8749.99, 8500.00, 0.05),
            (8750.00, 9249.99, 9000.00, 0.05),
            (9250.00, 9749.99, 9500.00, 0.05),
            (9750.00, 10249.99, 10000.00, 0.05),
            (10250.00, 10749.99, 10500.00, 0.05),
            (10750.00, 11249.99, 11000.00, 0.05),
            (11250.00, 11749.99, 11500.00, 0.05),
            (11750.00, 12249.99, 12000.00, 0.05),
            (12250.00, 12749.99, 12500.00, 0.05),
            (12750.00, 13249.99, 13000.00, 0.05),
            (13250.00, 13749.99, 13500.00, 0.05),
            (13750.00, 14249.99, 14000.00, 0.05),
            (14250.00, 14749.99, 14500.00, 0.05),
            (14750.00, 15249.99, 15000.00, 0.05),
            (15250.00, 15749.99, 15500.00, 0.05),
            (15750.00, 16249.99, 16000.00, 0.05),
            (16250.00, 16749.99, 16500.00, 0.05),
            (16750.00, 17249.99, 17000.00, 0.05),
            (17250.00, 17749.99, 17500.00, 0.05),
            (17750.00, 18249.99, 18000.00, 0.05),
            (18250.00, 18749.99, 18500.00, 0.05),
            (18750.00, 19249.99, 19000.00, 0.05),
            (19250.00, 19749.99, 19500.00, 0.05),
            (19750.00, 20249.99, 20000.00, 0.05),
            (20250.00, 20749.99, 20500.00, 0.05),
            (20750.00, 21249.99, 21000.00, 0.05),
            (21250.00, 21749.99, 21500.00, 0.05),
            (21750.00, 22249.99, 22000.00, 0.05),
            (22250.00, 22749.99, 22500.00, 0.05),
            (22750.00, 23249.99, 23000.00, 0.05),
            (23250.00, 23749.99, 23500.00, 0.05),
            (23750.00, 24249.99, 24000.00, 0.05),
            (24250.00, 24749.99, 24500.00, 0.05),
            (24750.00, 25249.99, 25000.00, 0.05),
            (25250.00, 25749.99, 25500.00, 0.05),
            (25750.00, 26249.99, 26000.00, 0.05),
            (26250.00, 26749.99, 26500.00, 0.05),
            (26750.00, 27249.99, 27000.00, 0.05),
            (27250.00, 27749.99, 27500.00, 0.05),
            (27750.00, 28249.99, 28000.00, 0.05),
            (28250.00, 28749.99, 28500.00, 0.05),
            (28750.00, 29249.99, 29000.00, 0.05),
            (29250.00, 29749.99, 29500.00, 0.05),
            (29750.00, 30249.99, 30000.00, 0.05),
            (30250.00, 30749.99, 30500.00, 0.05),
            (30750.00, 31249.99, 31000.00, 0.05),
            (31250.00, 31749.99, 31500.00, 0.05),
            (31750.00, 32249.99, 32000.00, 0.05),
            (32250.00, 32749.99, 32500.00, 0.05),
            (32750.00, 33249.99, 33000.00, 0.05),
            (33250.00, 33749.99, 33500.00, 0.05),
            (33750.00, 34249.99, 34000.00, 0.05),
            (34250.00, 34749.99, 34500.00, 0.05),
            (34750.00, 999999, 35000.00, 0.05)");

    }    

    // Create philhealth table
    $conn->exec("CREATE TABLE IF NOT EXISTS philhealth_bracket (
        id INT AUTO_INCREMENT PRIMARY KEY,
        min_salary DECIMAL(10,2) NOT NULL,
        max_salary DECIMAL(10,2) NULL,
        rate DECIMAL(5,4) NOT NULL DEFAULT 0.00,
        fixed_contribution DECIMAL(10,2) NOT NULL DEFAULT 0.00
    )");

    // Insert philhealth data if table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM philhealth_bracket");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO philhealth_bracket (min_salary, max_salary, rate, fixed_contribution) VALUES
            (0.00, 10000.00, 0.00, 500.00),
            (10000.01, 99999.99, 0.05, 0.00),
            (100000.00, NULL, 0.00, 5000.00)");
            }
    
    // Create pagibig table
    $conn->exec("CREATE TABLE IF NOT EXISTS pagibig_bracket (
        id INT AUTO_INCREMENT PRIMARY KEY,
        min_salary DECIMAL(10,2) NOT NULL,
        max_salary DECIMAL(10,2) NULL,
        rate DECIMAL(5,4) NOT NULL DEFAULT 0.00,
        fixed_contribution DECIMAL(10,2) NOT NULL DEFAULT 0.00)");
        
    // Insert pagibig data if table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM pagibig_bracket");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO pagibig_bracket (min_salary, max_salary, rate, fixed_contribution) VALUES
            (0.00, 1500.00, 0.01, 0.00),
            (1500.01, 10000.00, 0.02, 0.00),
            (10000.01, NULL, 0.00, 200.00)");
    } 

    // Create withholding_brackets table
    $conn->exec("CREATE TABLE IF NOT EXISTS withholding_tax_bracket (
        id INT AUTO_INCREMENT PRIMARY KEY,
        min_income DECIMAL(15,2) NOT NULL,
        max_income DECIMAL(15,2) NOT NULL,
        base_tax DECIMAL(15,2) NOT NULL,
        rate DECIMAL(5,2) NOT NULL
    )");
    
    // Insert withholding bracket data if table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM withholding_tax_bracket");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO withholding_tax_bracket (min_income, max_income, base_tax, rate) VALUES
            (-1.00, 20833.00, 0.00, 0.00),
            (20833.00, 33333.00, 0.00, 0.15),
            (33333.00, 66667.00, 1875.00, 0.20),
            (66667.00, 166667.00, 8541.80, 0.25),
            (166667.00, 666667.00, 33541.80, 0.30),
            (666667.00, 99999999.99, 183541.80, 0.35)");
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>