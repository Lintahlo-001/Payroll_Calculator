<?php
session_start();
include("db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll System</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        async function processRecord() {
            let name = document.getElementById("name").value;
            let contact = document.getElementById("contact").value;
            let email = document.getElementById("email").value;
            let city = document.getElementById("city/municipality").value;
            let province = document.getElementById("province").value;
            let position = document.getElementById("position").value;
            let department = document.getElementById("department").value;
            let hoursWorked = parseFloat(document.getElementById("hoursWorked").value);
            let hourlyRate = parseFloat(document.getElementById("hourlyRate").value);

            if (!name || !contact || !email || !city || !province || !position || !department || isNaN(hoursWorked) || isNaN(hourlyRate)) {
                document.getElementById("result").innerHTML = `<p style="color: red;">Please fill in all fields correctly.</p>`;
                return;
            }

            let overtime = parseFloat(document.getElementById("overtime").value);
            if (isNaN(overtime)) {
                overtime = 0;
            }
            
            let salary = hoursWorked * hourlyRate + (overtime * 1.5 * (hourlyRate));

            try {
                let response = await fetch("process_record.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ name: name, salary: salary })
                });

                let result = await response.json();
                result.pagibig = Number(result.pagibig) || 0;
                result.sss = Number(result.sss) || 0;
                result.philhealth = Number(result.philhealth) || 0;
                result.taxable_income = Number(result.taxable_income) || 0;
                result.withholding_tax = Number(result.withholding_tax) || 0;
                result.net_salary = Number(result.net_salary) || 0;

                if (response.ok) {
                    document.getElementById("result").innerHTML = ` 
                        <p><strong>Name:</strong> ${result.name}</p>
                        <p><strong>Gross Salary:</strong> ${result.salary.toFixed(2)}</p>
                        <p><strong>SSS Deduction:</strong> ${result.sss.toFixed(2)}</p>
                        <p><strong>PhilHealth Deduction:</strong> ${result.philhealth.toFixed(2)}</p>
                        <p><strong>Pag-IBIG Deduction:</strong> ${result.pagibig.toFixed(2)}</p>
                        <p><strong>Taxable Income:</strong> ${result.taxable_income.toFixed(2)}</p>
                        <p><strong>Withholding Tax:</strong> ${result.withholding_tax.toFixed(2)}</p>
                        <p><strong>Net Salary:</strong> ${result.net_salary.toFixed(2)}</p>
                    `;
                    loadRecords();
                } else {
                    document.getElementById("result").innerHTML = `<p style="color:red;">${result.error}</p>`;
                }
            } catch (error) {
                console.error("Error:", error);
            }
        }

        async function loadRecords() {
            try {
                let response = await fetch("fetch_records.php");
                let data = await response.text();
                document.getElementById("records").innerHTML = data;
            } catch (error) {
                console.error("Error loading records:", error);
            }
        }

        async function toggleView(id) {
            let detailsRow = document.getElementById(`details-${id}`);
            let resultDiv = document.getElementById(`result-${id}`);
            let button = document.querySelector(`button[onclick="toggleView(${id})"]`);

            if (detailsRow.style.display === "none") {
                try {
                    let response = await fetch(`view_record.php?id=${id}`);
                    let result = await response.json();

                    if (result.error) {
                        alert(result.error);
                    } else {
                        resultDiv.innerHTML = `
                            <p><strong>Name:</strong> ${result.name}</p>
                            <p><strong>Gross Salary:</strong> ₱${parseFloat(result.salary).toFixed(2)}</p>
                            <p><strong>SSS Deduction:</strong> ₱${parseFloat(result.sss).toFixed(2)}</p>
                            <p><strong>PhilHealth Deduction:</strong> ₱${parseFloat(result.philhealth).toFixed(2)}</p>
                            <p><strong>Pag-IBIG Deduction:</strong> ₱${parseFloat(result.pagibig).toFixed(2)}</p>
                            <p><strong>Taxable Income:</strong> ₱${parseFloat(result.taxable_income).toFixed(2)}</p>
                            <p><strong>Withholding Tax:</strong> ₱${parseFloat(result.withholding_tax).toFixed(2)}</p>
                            <p><strong>Net Salary:</strong> ₱${parseFloat(result.net_salary).toFixed(2)}</p>
                        `;
                        detailsRow.style.display = "table-row";
                        button.innerText = "Hide";
                    }
                } catch (error) {
                    console.error("Error fetching record:", error);
                }
            } else {
                detailsRow.style.display = "none";
                button.innerText = "View";
            }
        }

        async function deleteRecord(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                try {
                    let response = await fetch("delete_record.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "id=" + id
                    });
                    let result = await response.text();
                    alert(result);
                    loadRecords();
                } catch (error) {
                    console.error("Error deleting record:", error);
                }
            }
        }

        window.onload = loadRecords;
    </script>
</head>
<body>
    <div class="container">
        <h2>Payroll System</h2>
        <form class="form-container">
            <label for="name">Employee Name:</label>
            <input type="text" id="name" placeholder="Enter name">

            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" placeholder="Enter Contact Number">

            <label for="email">Email Address:</label>
            <input type="text" id="email" placeholder="Enter Email Address">

            <label for="city/municipality">City/Municipality:</label>
            <input type="text" id="city/municipality" placeholder="Enter City/Municipality">

            <label for="province">Province:</label>
            <input type="text" id="province" placeholder="Enter Province">

            <label for="position">Position:</label>
            <input type="text" id="position" placeholder="Enter Position">

            <label for="department">Department:</label>
            <input type="text" id="department" placeholder="Enter Department">

            <label for="hoursWorked">Hours Worked:</label>
            <input type="number" id="hoursWorked" placeholder="Enter Hours Worked">

            <label for="hourlyRate">Hourly Rate:</label>
            <input type="number" id="hourlyRate" placeholder="Enter Hourly Rate">

            <label for="overtime">Overtime:</label>
            <input type="number" id="overtime" placeholder="Enter Overtime(optional)">

            <button type="button" onclick="processRecord()">Submit</button>
        </form>

        <div id="result"></div>
        <h3>Employee Records</h3>
        <div id="records"></div>
    </div>
</body>
</html>