<?php

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

$table = "CONTESTS";
$qry = "SELECT * FROM $table WHERE CTS_DEADLINE < NOW() ORDER BY -CTS_DEADLINE";
$resultContests = mysql_query($qry);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Contests</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#navContests a:link,
#navContests a:visited {
	color:#FF8204;
	background:#000;
	padding:18px 17px 5px 17px;
	float:left;
	width:auto;
	text-decoration:none;
	font-family: Gill Sans, Verdana;
	font-size:9px;
	text-transform:uppercase;
}
#navContests a:hover {
	color:#000;
	background:#FF8204;
}
</style>
</head>

<body class="page">

<?php
writeTableBegin($userid,0);
echo '<table width=100%><tr><td width=50%>';
echo '<p class="title">Finished Contests</P>
	<p class="subtitle">Learn from the past.</p></td>';
echo '<td><div id="smallLinks"><a href="contests/new.php">Create a new contest </a></div><br/><div id="smallLinks"><a href="contests.php"><< Current contests</a></div>';
echo '</td></tr></table>';

if(isset($_GET['help'])){	// QUICK HELP SYSTEM
	echo '<p class="help">You can no longer submit entries to these contests. However, you can see the winners for a contest by clicking it. If it says "judgement pending", there are still judges who have not yet voted for that contest, so check back soon to see the results!</p>';
}

if(mysql_num_rows($resultContests) > 0){
	echo '<table width=100%><tr><td width=100% class="listing">';
	while($contest = mysql_fetch_assoc($resultContests)){
		$linkContest = "contests/view.php?contestid=" . $contest['CTS_CONTESTID'];
		if($contest['TYP_TYPEID'] == 0){
			echo " [Anything] ";
		} else {
			echo " [" . getTypeName($contest['TYP_TYPEID']) . "] ";
		}
		echo '<div><b><a href="' . $linkContest . '">' . stripslashes($contest['CTS_NAME']) . '</b>';
		echo " - deadline: " . $contest['CTS_DEADLINE'] . "</a>";
		if($contest['CTS_FIRSTWORKID'] == NULL){
			echo ' <i><b> - JUDGEMENT PENDING...</b></i>';
		}
		echo "</div>";
	}
	echo '</td></tr></table>';
} else {
	echo '<p class="message">There are no finished contests yet.</p>';
}

writeTableEnd();
?>


</body>
</html>