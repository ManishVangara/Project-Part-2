<?php
// Include necessary utility functions
include "utility_functions.php";

// Get sessionid and username from the URL parameters
$sessionid = $_GET["sessionid"];
$username = $_GET["username"];

// Verify the session if needed
// verify_session($sessionid);

// Start HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Page</title>
    <style>
        body {
            font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
            margin: 0;
            padding: 20px;
        }

        header {
            background-color: #007BFF; 
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        h2 {
            margin-top: 20px;
            color: #333; 
        }

        .button {
            padding: 10px 15px;
            font-size: 16px;
            color: white;
            background-color: #007BFF; 
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none; 
            display: inline-block;
            margin: 10px; 
            transition: background-color 0.3s; 
        }

        .button:hover {
            background-color: #0056b3; 
        }

        .container {
            width: auto; 
            max-width: 100%; 
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse; 
        }

        th, td {
            padding: 8px; 
            text-align: left; 
            border: 1px solid #ddd; 
        }

        th {
            background-color: #007BFF; 
            color: white; 
        }

        .update-link {
            color: #007BFF;
            text-decoration: none;
        }

        .update-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Academic Information</h1>
    </header>

    <div class="container">
        <h2>Welcome <?php echo $username; ?></h2>

        <?php
        // Query to collect student academic information

        $sql = "SELECT 
                    su.username,
                    COUNT(e.enrollment_id) AS courses_completed,
                    SUM(c.credit_hours) AS total_credit_hours,
                    CASE 
                        WHEN SUM(c.credit_hours) > 0 THEN 
                            SUM(e.grade * c.credit_hours) / SUM(c.credit_hours)
                        ELSE 
                            NULL 
                    END AS gpa
                FROM 
                    StudentUsers su
                JOIN 
                    Enrollment e ON su.student_id = e.student_id
                JOIN 
                    Section s ON e.section_id = s.section_id
                JOIN 
                    Course c ON s.course_number = c.course_number
                WHERE 
                    su.username = :username
                GROUP BY 
                    su.username";



        // Execute the query
        $result_array = execute_sql_in_oracle($sql, [':username' => $username]);
        $result = $result_array["flag"];
        $cursor = $result_array["cursor"];

        if ($result == false){
            display_oracle_error_message($cursor);
            die("Client Query Failed.");
        }

        // Display the query results
        echo "<table>";
        echo "<tr><th>Username</th><th>Courses Completed</th><th>Total Credit hours</th><th>GPA</th></tr>";

        // Fetch the result from the cursor one by one
        while ($values = oci_fetch_array($cursor)) {
            echo "<tr>" . 
                 "<td>" . htmlspecialchars($values[0]) . "</td>" .
                 "<td>" . htmlspecialchars($values[1]) . "</td>" .
                 "<td>" . htmlspecialchars($values[2]) . "</td>" .
                 "<td>" . htmlspecialchars($values[3]) . "</td>" .
                 "</tr>";
        }
        
        oci_free_statement($cursor);
        echo "</table>";

        // For course information details
        $sql = "SELECT 
            s.section_id,
            c.course_number,
            c.course_title,
            s.semester,
            c.credit_hours,
            e.grade
        FROM 
            StudentUsers su
        JOIN 
            Enrollment e ON su.student_id = e.student_id
        JOIN 
            Section s ON e.section_id = s.section_id
        JOIN 
            Course c ON s.course_number = c.course_number
        WHERE 
            su.username = :username";

        // Execute the query
        $result_array = execute_sql_in_oracle($sql, [':username' => $username]);
        $result = $result_array["flag"];
        $cursor = $result_array["cursor"];

        if ($result == false) {
            echo "<p>Error retrieving data. Please try again later.</p>";
        } else {
            echo "<table>";
            echo "<thead><tr><th>Section ID</th><th>Course Number</th><th>Course Title</th><th>Semester</th><th>Credits</th><th>Grade</th></tr></thead>";
            echo "<tbody>";

            while ($values = oci_fetch_array($cursor)) {
                echo "<tr>" . 
                     "<td>" . htmlspecialchars($values[0]) . "</td>" .
                     "<td>" . htmlspecialchars($values[1]) . "</td>" .
                     "<td>" . htmlspecialchars($values[2]) . "</td>" .
                     "<td>" . htmlspecialchars($values[3]) . "</td>" .
                     "<td>" . htmlspecialchars($values[4]) . "</td>" .
                     "<td>" . htmlspecialchars($values[5]) . "</td>" .
                     "</tr>";
            }

        }
        oci_free_statement($cursor);
        echo "</table>";

        ?>

    </div>   
</body>