<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
</head>
<body>
    <form name='change-password' method='POST'>
        Password: <input type='password' name='password' size='12' maxlength='10'> 
        <input type='submit' name='submit' value='update password'> 
    </form>
</body>
</html>

<?php

include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the new password from the form
    $new_password = $_POST["password"];

    // Construct the SQL query to update the password
    $sql = "UPDATE Users SET password = '$new_password' WHERE username IN (SELECT username FROM UserSessions WHERE sessionid = '$sessionid')";

    // Execute the SQL query
    $result_array = execute_sql_in_oracle($sql);
    $result = $result_array["flag"];

    if ($result) {
        echo "Password updated successfully.";
        echo"<br>";
        echo("<a href='login.php'> Click here to go back to login page</a>");
    } else {
        echo "Failed to update password.";
    }
}


?>