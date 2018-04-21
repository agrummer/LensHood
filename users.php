<?php
include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$table = "USERS";
$qry = "SELECT * FROM $table";
$resultUsers = mysql_query($qry);

$table = "NAMES";
$qry = "SELECT * FROM $table";
$resultNames = mysql_query($qry);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Users</title>

</head>

<body>
<?php
//writeTableBegin(1,0);
echo '<table>';
while($users = mysql_fetch_assoc($resultUsers)){
	$linkprofile = "profile.php?userid=" . $users['ART_USERID'];
	echo "<tr><td><a href=\"$linkprofile\">" . $users['USR_USERNAME'] . "</a></td>";
	$names = mysql_fetch_assoc($resultNames);
	echo "<td>" . $names['NMS_FIRST'] . " " . $names['NMS_LAST'] . "</td></tr>";
}
echo '</table>';
//writeTableEnd();
?>

</body>
</html>