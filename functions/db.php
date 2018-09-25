<?php
/*
to connect to your database
check if you're connected if not an error will display
 */

// Create connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "medi";

$con = new mysqli($servername, $username, $password, $dbname);
// Check connection
/*
if ($con->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}
//else{echo "success";}
*/

//a funtion to help us retrieve the count of entries
function row_count($result)
{
	return mysqli_num_rows($result);
}

//a function to help us secure our code
function escape($string)
{
 	global $con;
 	return mysqli_real_escape_string($con, $string);
}
//a function to help us excute query by calling it
function query($query) 
{
 	global $con;
 	return mysqli_query($con, $query);
}
//a function to  help us fetch data from the db
function fetch_array($result)
{
	global $con;
	return mysqli_fetch_array($result);
}
//a function to help confirm everyting is a-ok
function confirm($result)
{
	global $con;
	if(!$result)
	{
		die("Query failed. check what's up". mysqli_error($con));
	}
}


?>

