<?php
// Include necessary utility functions
// include "utility_functions.php";

// Get sessionid and username from the URL parameters
$sessionid = $_GET["sessionid"];
$username = $_GET["username"];

// Verify the session if needed
// verify_session($sessionid);

// Start HTML output
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
        <h1>Student Dashboard</h1>
    </header>

    <div class="container">
        <h2>Welcome <?php echo htmlspecialchars($username); ?></h2>

        <?php
        // Form the query to fetch student information along with their undergrad/grad details

        $sql = "SELECT u.username, u.first_name, u.last_name, su.address, su.student_type, su.probation_status, su.student_id"
        ."CASE WHEN su.student_type = 0 THEN (SELECT standing FROM UnderGrad WHERE student_id = su.student_id)"
        ."ELSE(SELECT concentration FROM Grad WHERE student_id = su.student_id)"
        ."END AS status_or_concentration"
        ."FROM Users u"
        ."JOIN StudentUsers su ON u.username = su.username"
        ."WHERE u.username = :username";

        // Execute the query
        $result_array = execute_sql_in_oracle($sql, [':username' => $username]);
        $result = $result_array["flag"];
        $cursor = $result_array["cursor"];

        if ($result == false) {
            display_oracle_error_message($cursor);
            die("Client Query Failed.");
        }

        // Display the query results
        echo "<table>";
        echo "<tr><th>Username</th><th>First Name</th><th>Last Name</th><th>Address</th><th>Student Type</th><th>Probation Status</th><th>Student ID</th><th>Standing/Concentration</th><th>Update</th></tr>";

        // Fetch the result from the cursor one by one
        while ($values = oci_fetch_array($cursor)) {
            echo "<tr>" . 
                 "<td>" . htmlspecialchars($values[0]) . "</td>" .
                 "<td>" . htmlspecialchars($values[1]) . "</td>" .
                 "<td>" . htmlspecialchars($values[2]) . "</td>" .
                 "<td>" . htmlspecialchars($values[3]) . "</td>" .
                 "<td>" . ($values[4] == 0 ? 'Undergrad' : 'Grad') . "</td>" .
                 "<td>" . ($values[5] ? htmlspecialchars($values[5]) : 'Not applicable') . "</td>" .
                 "<td>" . htmlspecialchars($values[6]) . "</td>" .
                 "<td>" . htmlspecialchars($values[7]) . "</td>" .
                 "<td><a href=\"user_update.php?sessionid=$sessionid&username=" . urlencode($values[0]) . "\" class=\"update-link\">Update</a></td>" .
                 "</tr>";
        }

        // Free the statement
        oci_free_statement($cursor);
        echo "</table>";
        ?>

        <form action="changepassword.php" method="get" style="display:inline;">
            <input type="hidden" name="sessionid" value="<?php echo htmlspecialchars($sessionid); ?>">
            <button type="submit" class="button">Change Password</button>
        </form>

        <form action="logout_action.php" method="get" style="display:inline;">
            <input type="hidden" name="sessionid" value="<?php echo htmlspecialchars($sessionid); ?>">
            <button type="submit" class="button">Logout</button>
        </form>
    </div>
</body>
</html>
