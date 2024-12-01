<?php
include "utility_functions.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the form submission
    $username = $_POST["username"];
    $password = $_POST["password"];

    // SQL query to check the username and password in the database
    $sql = "SELECT username FROM Users WHERE username = '$username' AND password='$password'";

    // Execute the query
    $result_array = execute_sql_in_oracle($sql);
    $result = $result_array["flag"];
    $cursor = $result_array["cursor"];

    // Check for errors in the query execution
    if ($result == false) {
        display_oracle_error_message($cursor);
        die("Client Query Failed.");
    }

    // Fetch the result and validate credentials
    if ($values = oci_fetch_array($cursor)) {
        oci_free_statement($cursor);

        // Credentials are correct; create a new session
        $sessionid = md5(uniqid(rand()));

        // Insert the session ID into the UserSessions table
        $sql = "INSERT INTO UserSessions (sessionid, sessiondate, username) " .
               "VALUES ('$sessionid', sysdate, '$username')";

        $result_array = execute_sql_in_oracle($sql);
        $result = $result_array["flag"];
        $cursor = $result_array["cursor"];

        // Check if session creation succeeded
        if ($result == false) {
            display_oracle_error_message($cursor);
            die("Failed to create a new session");
        } else {
            // Successful login, redirect to the welcome page
            header("Location:welcomepage.php?sessionid=$sessionid&username=$username");
            exit();
        }
    } else {
        // Invalid username or password
        die('Login failed. Click <a href="login.php">here</a> to go back to the login page.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page - DB Project</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ccc; /* Clear border around the form */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); /* Added shadow for depth */
            width: 350px; /* Wider form for better spacing */
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc; /* Input border */
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box; /* Ensures padding and border are inside width */
        }

        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #007bff; /* Blue border on focus */
            outline: none; /* Removes default outline */
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3); /* Glowing effect on focus */
        }

        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-container input[type="submit"]:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .login-container p {
            text-align: center;
            margin-top: 20px;
        }

        .login-container a {
            color: #007bff;
            text-decoration: none;
        }

        .login-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login</h2>
        <form name="login" method="POST" action="login.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your Username" maxlength="10" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your Password" maxlength="12" required>

            <input type="submit" name="submit" value="Login">
        </form>
    </div>

</body>
</html>

