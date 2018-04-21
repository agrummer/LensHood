<?php

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

if (isset($_POST['title'])) {	// check to see if user has already submitted information
	$newPort['userid'] = $userid;
	$newPort['title'] = mysql_real_escape_string($_POST['title']);
	$newPort['type'] = mysql_real_escape_string($_POST['type']);
	$newPort['typeid'] = getTypeID($newPort['type']);
	
	$table = "PORTFOLIOS";
	$qry = "INSERT INTO $table (`ART_USERID` , `TYP_TYPEID` , `PRT_NAME`)
						VALUES ('". $newPort['userid'] ."','". $newPort['typeid'] ."','". $newPort['title'] ."')";
	$result = mysql_query($qry) or die(writeError("There was an error inserting your information into the database. Please try again."));
	$portid = mysql_insert_id($dblink);
	$portCreated = TRUE;	// flag success
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
</head>

<body class="page">
<?php
writeTableBegin($userid,0);

if($portCreated){
	echo '<p class="help">Portfolio '. stripslashes($newPort['title']) .' created successfully.</p>';
	echo '<div id="smallLinks"><a href="works/upload.php?portid='. $portid .'">Upload a new work to '. stripslashes($newPort['title']) .'.</a></div>';
	echo '<p class="title">Create another portfolio</p>';
} else {
	echo '<p class="title">Create a portfolio</p>';
}
echo '<p class="subtitle">Give your works a home.</p>';
echo '</td></tr><tr><td class="registerForm">';
echo '<form id="newPortfolio" name="newPortfolio" method="post" action="" >
	<label><span>portfolio title</span>
		<input name="title" type="text" size="50" maxlength="255" accesskey="t" tabindex="1" class="textInput" />
	</label>
    <label><span>type of artwork</span>
    	<select name="type" id="type" accesskey="t" tabindex="2" class="textInput">
		';
		 // populate the drop-down list with available artist types
		 $table = "TYPES";
		 $qry = "SELECT * FROM $table";
		 $result = mysql_query($qry) or die(writeError("There was an error reading from the database, please try again later."));
		 while($types = mysql_fetch_array($result)){
			 echo "<option>" . $types['TYP_NAME'] . "</option>";
		 }
		 dbClose($dblink);
		echo '
    	</select>
    
	<input type="submit" name="submit" id="submit" value="Create" tabindex="3" class="submitForm" /></label>
	
</form>';
writeTableEnd();
?>
</body>
</html>