<?php

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

if(isset($_GET['portid'])){
	$portid = mysql_real_escape_string($_GET['portid']);	// get the portfolio ID of the portfolio to display
} else {
	die(writeError("You must specify a portfolio to view."));
}

$table = "PORTFOLIOS";
$qry = "SELECT * FROM $table WHERE PRT_PORTID = " . $portid;
$result = mysql_query($qry);
if(mysql_num_rows($result) < 1){
	die(writeError("This portfolio does not exist."));
}
$port = mysql_fetch_assoc($result);

$table = "NAMES";
$qry = "SELECT * FROM $table WHERE ART_USERID = " . $port['ART_USERID'];
$result = mysql_query($qry);
$row = mysql_fetch_row ($result);
$user['last'] = stripslashes($row[1]);
$user['first'] = stripslashes($row[2]);

$table = "TYPES";
$qry = "SELECT TYP_NAME FROM $table WHERE TYP_TYPEID = " . $port['TYP_TYPEID'];
$result = mysql_query($qry);
$row = mysql_fetch_row ($result);
$port['type'] = $row[0];


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

echo '<table><tr><td width="250px">';
echo '<p class="title">'. stripslashes($port['PRT_NAME']) .'</p>';
echo '<p class="subtitle">'. $port['type'] .'</p>';
echo '</td>
		<td>
			<div id="smallLinks" align="right"><a href="profile.php?userid='. $port['ART_USERID'] .'">'. $user['first'] .'\'s profile</a> ';
			if($userid == $port['ART_USERID']){
				echo ' <a href="works/upload.php?portid='. $portid .'">Upload new work</a></div>';
			}
echo	'</td>
	</tr>';
echo '</table><table>';
$table = "WORKS";	// display all works which are members of the portfolio
$qry = "SELECT * FROM $table WHERE PRT_PORTID = " . $portid ." ORDER BY -WRK_VIEWS";
$resultWorks = mysql_query($qry);
if(mysql_num_rows($resultWorks) > 0){
	$place=0;	// marker to keep track of which cell of the table the loop is in
	echo '<tr>';
	while($work = mysql_fetch_assoc($resultWorks)){
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="works/work.php?workid='. $work['WRK_WORKID'] .'"><img border="0" src="works/'. getThumbURL($work['WRK_WORKID']) .'"></a>';
		echo '<div id="thumbTitle" align="left">'. stripslashes($work['WRK_TITLE']) .'</div>';
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
			$place = 0;
		}
	}
} else {
	if($userid == $port['ART_USERID']){
		echo '<p class="help"><b>You have not uploaded any works to this portfolio yet.</b> <br />You can use the button above to do so.</p>';
	} else {
		echo '<p class="message">There are no works in this portfolio yet.</p>';
	}
}
echo '</tr></table><table><tr><td>';
writeTableEnd();
?>

</body>
</html>