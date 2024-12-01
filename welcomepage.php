<?php
include "utility_functions.php";

// Get the sessionid and username from the URL parameters
$sessionid = $_GET["sessionid"];

// echo "$sessionid";
$username = $_GET["username"];

// $password = $_GET["password"]

// echo "<br>";

// echo "$username";
// Verify the session
verify_session($sessionid);

// echo "verified session";
// Query to retrieve the username from the UserSessions table
$sql = "SELECT username FROM UserSessions WHERE sessionid ='$sessionid'";
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
  echo "Error fetching username from UserSessions.";
} else {
  // Fetch the username
  $values = oci_fetch_array($cursor);
  if ($values) {
      $username = $values[0];
  } else {
      echo "No user found for this session.";
      exit;
  }
}

// Free the cursor
oci_free_statement($cursor);

// Get the user information from the database
$sql = "SELECT user_status FROM Users WHERE username='$username'";
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
  echo("Error fetching the user information");
} else {
  // Fetch the user information
  $values = oci_fetch_array($cursor);
  if ($values) {
      $user_status = $values[0];
  } else {
      echo "No user information found.";
      exit;
  }
}

// Free the cursor
oci_free_statement($cursor);

// Check the user_status and navigate accordingly


if ($user_status == 0){  // Student
    include "student_page.php";
} elseif ($user_status == 1) {  // Admin
    include "admin_page.php";
} elseif ($user_status == 2) {  // Student-admin
    include "student_admin_page.php";
}
?>

