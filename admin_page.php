<?php
// include "utility_functions.php";
// Get the sessionid and username from the URL parameters
$sessionid = ($_GET["sessionid"]);
$username = ($_GET["username"]);

// verify_session($session_id);

// Interpret the query requirements
$q_username = isset($_POST["q_username"]) ? trim($_POST["q_username"]) : "";
$q_firstname = isset($_POST["q_firstname"]) ? $_POST["q_firstname"] : "";
$q_lastname = isset($_POST["q_lastname"]) ? $_POST["q_lastname"] : "";

// Generate the query section
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <style>
        /* Styles omitted for brevity */
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
    </header>

    <div class="container">
        <h2>Welcome <?php echo htmlspecialchars($username); ?></h2>

        <!-- Search Form -->
        <form method="post" action="admin_page.php?sessionid=<?php echo htmlspecialchars($sessionid); ?>">
            <label for="q_username">Username:</label>
            <input type="text" name="q_username" id="q_username" value="<?php echo htmlspecialchars($q_username); ?>">
            <label for="q_firstname">First Name:</label>
            <input type="text" name="q_firstname" id="q_firstname" value="<?php echo htmlspecialchars($q_firstname); ?>">
            <label for="q_lastname">Last Name:</label>
            <input type="text" name="q_lastname" id="q_lastname" value="<?php echo htmlspecialchars($q_lastname); ?>">
            <input type="submit" value="Search">
        </form>

        <!-- Navigation Buttons -->
        <form method="post" action="add.php?sessionid=<?php echo htmlspecialchars($sessionid); ?>">
            <input type="submit" value="Add a new user" class="button">
        </form>

        <?php
        // Build where clause
        $whereClause = "1=1";

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
        $sql = "SELECT u.username, u.first_name, u.last_name, u.user_status, su.admission_date, au.start_date 
                FROM Users u 
                LEFT JOIN StudentUsers su ON u.username = su.username 
                LEFT JOIN AdminUsers au ON u.username = au.username 
                WHERE $whereClause 
                ORDER BY u.username";
        
        $result_array = execute_sql_in_oracle($sql);
        $result = $result_array["flag"];
        $cursor = $result_array["cursor"];

        if ($result == false) {
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

        <!-- Change Password and Logout Buttons -->
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
