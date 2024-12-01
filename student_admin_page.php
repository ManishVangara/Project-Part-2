<?php
// include "utility_functions.php";
// Get the sessionid and username from the URL parameters
$sessionid = ($_GET["sessionid"]);
$username = ($_GET["username"]);

// Testing for sessionid and username variables 
// echo "$sessionid";
// echo "<br>";
// echo "$username";

// Verify session if needed
// verify_session($sessionid);

// Generate the query section for user details
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admin Page</title>
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

        h2, h3 {
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
            width: auto; /* Adjusts based on content */
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
    </style>
</head>
<body>
    <header>
        <h1>Student Admin Dashboard</h1>
    </header>

    <div class="container">
        <!-- User Details Section -->
        <h2>User Details for <?php echo htmlspecialchars($username); ?></h2>
        <?php
        // Query for user details (no password field)
        $sql = "SELECT username, first_name, last_name, user_status, admission_date, start_date ".
                "FROM Users WHERE username = '$username'";
        $result_array = execute_sql_in_oracle($sql);
        $result = $result_array["flag"];
        $cursor = $result_array["cursor"];

        if ($result == false) {
            display_oracle_error_message($cursor);
            die("Client Query Failed.");
        }

        // Display the user details in a table (no password column)
        echo "<table>";
        echo "<tr><th>Username</th><th>First Name</th><th>Last Name</th><th>User Status</th><th>Admission Date</th><th>Start Date</th></tr>";

        // Fetch the result from the cursor one by one
        while ($values = oci_fetch_array($cursor)){
            echo "<tr>" . 
                 "<td>" . htmlspecialchars($values[0]) . "</td>" .
                 "<td>" . htmlspecialchars($values[1]) . "</td>" .
                 "<td>" . htmlspecialchars($values[2]) . "</td>" .
                 "<td>" . htmlspecialchars($values[3]) . "</td>" .
                 "<td>" . htmlspecialchars($values[4]) . "</td>" .
                 "<td>" . htmlspecialchars($values[5]) . "</td>" .
                 "</tr>";
        }
        oci_free_statement($cursor);
        echo "</table>";
        ?>

        <!-- Admin Functionality Section -->
        <h2>Admin Functions</h2>
        <!-- Search Form (no password field) -->
        <form method="post" action="student_admin_page.php?sessionid=<?php echo $sessionid; ?>">
            <label for="q_username">Username:</label>
            <input type="text" size="10" maxlength="40" name="q_username" id="q_username"> 
            <label for="q_firstname">Firstname:</label>
            <input type="text" size="20" maxlength="25" name="q_firstname" id="q_firstname"> 
            <label for="q_lastname">Lastname:</label>
            <input type="text" size="20" maxlength="25" name="q_lastname" id="q_lastname"> 
            <input type="submit" value="Search">
        </form>

        <!-- Navigation Buttons -->
        <form method="post" action="admin_add.php?sessionid=<?php echo $sessionid; ?>">
            <input type="submit" value="Add a new user" class="button">
        </form>

        <?php
        // Handle the admin functionality search
        // Build where clause
        $whereClause = "1=1";

        $q_username = isset($_POST["q_username"]) ? trim($_POST["q_username"]) : "";
        $q_firstname = isset($_POST["q_firstname"]) ? $_POST["q_firstname"] : "";
        $q_lastname = isset($_POST["q_lastname"]) ? $_POST["q_lastname"] : "";

        if (!empty($q_username)) { 
            $whereClause .= " and username = '$q_username'"; 
        }

        if (!empty($q_firstname)) { 
            $whereClause .= " and first_name like '%$q_firstname%'"; 
        }

        if (!empty($q_lastname)) { 
            $whereClause .= " and last_name like '%$q_lastname%'"; 
        }

        // Form the query and execute it
        $sql = "SELECT u.username, u.first_name, u.last_name, u.user_status, su.admission_date, au.start_date". 
        "FROM Users u LEFT JOIN StudentUsers su ON u.username = su.username" . 
        "LEFT JOIN AdminUsers au ON u.username = au.username". 
        "WHERE $whereClause ORDER BY u.username";
        $result_array = execute_sql_in_oracle($sql);
        $result = $result_array["flag"];
        $cursor = $result_array["cursor"];

        if ($result == false){
            display_oracle_error_message($cursor);
            die("Client Query Failed.");
        }

        // Display the query results
        echo "<table>";
        echo "<tr><th>Username</th><th>First Name</th><th>Last Name</th><th>User Status</th><th>Admission Date</th><th>Start Date</th><th>Update</th><th>Delete</th></tr>";

        // Fetch the result from the cursor one by one
        while ($values = oci_fetch_array($cursor)){
            echo "<tr>" . 
                 "<td>" . htmlspecialchars($values[0]) . "</td>" .
                 "<td>" . htmlspecialchars($values[1]) . "</td>" .
                 "<td>" . htmlspecialchars($values[2]) . "</td>" .
                 "<td>" . htmlspecialchars($values[3]) . "</td>" .
                 "<td>" . htmlspecialchars($values[4]) . "</td>" .
                 "<td>" . htmlspecialchars($values[5]) . "</td>" .
                 "<td><a href=\"user_update.php?sessionid=$sessionid&username=" . urlencode($values[0]) . "\">Update</a></td>" .
                 "<td><a href=\"delete.php?sessionid=$sessionid&username=" . urlencode($values[0]) . "\">Delete</a></td>" .
                 "</tr>";
        }
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
