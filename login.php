<?php
// Authenticate the user and store their information for the session

include("connections.php");
$dblink = dbConnect();

date_default_timezone_set('America/Los_Angeles');

$table = "USERS";
$qry = "SELECT ART_USERID,USR_USERNAME FROM $table WHERE USR_USERNAME = '".mysql_real_escape_string($_POST['username'])."'
AND USR_PASSWORD = '".md5(mysql_real_escape_string($_POST['password']))."';"; 

$result = mysql_query($qry)
or die (writeError("There was an error working with the database."));
$num_rows = mysql_num_rows($result); 

if ($num_rows <= 0) { // login failed, send back to prompt
	$URL = "index.php?login=$username&status=fail";
	header ("Location: $URL");
} else {	// login success
	$row = mysql_fetch_assoc($result);
	$userid = $row['ART_USERID'];
	//$username = $row['USR_USERNAME'];
	//$userHash = md5($username);	// encrypt the userid using MD5 algorithm
	setcookie("loggedin", "TRUE");	// store the cookie to keep them logged in
	setcookie("LensHood_ID", "$userid");	// store the cookie to identify them
	
	$table = "LOGINLOG";
	$qry = "INSERT INTO $table (`ART_USERID`) VALUES ('$userid')";
	$result = mysql_query($qry);
	
	$URL = "main.php";
	header ("Location: $URL");
}

dbClose($dblink);

?>