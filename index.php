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
            let phone_number = document.getElementById("phone_number").value;
            let email_address = document.getElementById("email_address").value;
            let city_municipality = document.getElementById("city_municipality").value;
            let province = document.getElementById("province").value;
            let position = document.getElementById("position").value;
            let department = document.getElementById("department").value;
            let hours_worked = document.getElementById("hours_worked").value;
            let hourly_rate = document.getElementById("hourly_rate").value;
            let overtime_hours = document.getElementById("overtime_hours").value;

            if (!name || !phone_number || !email_address || !city_municipality || !province || !position || !department || isNaN(hours_worked) || isNaN(hourly_rate)) {
                document.getElementById("result").innerHTML = `<p style="color: red;">Please fill in all fields correctly.</p>`;
                return;
            }

            if (isNaN(overtime_hours)) {
                overtime_hours = 0;
            }

            try {
                let response = await fetch("process_record.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
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

                let result = await response.json();

                if (response.ok) {
                    document.getElementById("result").innerHTML = ` 
                        <p><strong>Name:</strong> ${result.name}</p>
                        <p><strong>Gross Salary:</strong> ₱${parseFloat(result.salary).toFixed(2)}</p>
                        <p><strong>Overtime Pay:</strong> ₱${parseFloat(result.overtime_pay).toFixed(2)}</p>
                        <p><strong>SSS Deduction:</strong> ₱${parseFloat(result.sss).toFixed(2)}</p>
                        <p><strong>PhilHealth Deduction:</strong> ₱${parseFloat(result.philhealth).toFixed(2)}</p>
                        <p><strong>Pag-IBIG Deduction:</strong> ₱${parseFloat(result.pagibig).toFixed(2)}</p>
                        <p><strong>Taxable Income:</strong> ₱${parseFloat(result.taxable_income).toFixed(2)}</p>
                        <p><strong>Withholding Tax:</strong> ₱${parseFloat(result.withholding_tax).toFixed(2)}</p>
                        <p><strong>Net Salary:</strong> ₱${parseFloat(result.net_salary).toFixed(2)}</p>
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
                            <p><strong>Salary Computations:</strong></p>
                            <p><strong>Gross Salary:</strong> ₱${parseFloat(result.salary).toFixed(2)}</p>
                            <p><strong>Overtime Pay:</strong> ₱${parseFloat(result.overtime_pay).toFixed(2)}</p>
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
            <input type="text" id="name" placeholder="Enter name" maxlength="60">

            <label for="phone_number">Contact Number:</label>
            <input type="text" id="phone_number" placeholder="Enter Phone Number" maxlength="20">

            <label for="email_address">Email Address:</label>
            <input type="text" id="email_address" placeholder="Enter Email Address" maxlength="50">

            <label for="city_municipality">City/Municipality:</label>
            <input type="text" id="city_municipality" placeholder="Enter City/Municipality" maxlength="20">

            <label for="province">Province:</label>
            <input type="text" id="province" placeholder="Enter Province" maxlength="20">

            <label for="position">Position:</label>
            <input type="text" id="position" placeholder="Enter Position" maxlength="20">

            <label for="department">Department:</label>
            <input type="text" id="department" placeholder="Enter Department" maxlength="20">

            <label for="hours_worked">Hours Worked:</label>
            <input type="number" id="hours_worked" placeholder="Enter Hours Worked" onKeyPress="if(this.value.length==3) return false;">

            <label for="hourly_rate">Hourly Rate:</label>
            <input type="number" id="hourly_rate" placeholder="Enter Hourly Rate" onKeyPress="if(this.value.length==6) return false;">

            <label for="overtime_hours">Overtime:</label>
            <input type="number" id="overtime_hours" placeholder="Enter Overtime(optional)" onKeyPress="if(this.value.length==3) return false;">

            <button type="button" class="submit" onclick="processRecord()">Submit</button>
        </form>

        <div id="result"></div>
        <h3>Employee Records</h3>
        <div id="records"></div>
    </div>
</body>
</html>