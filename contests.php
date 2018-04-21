<?php
session_start();

include("connections.php");
include("formatting.php");
include("pointValues.php");
$dblink = dbConnect();

if($_SESSION['registered']){
	$userid = $_SESSION['registered'];
	unset($_SESSION['registered']);
	setcookie("loggedin", "TRUE");	// store the cookie to keep them logged in
	setcookie("LensHood_ID", "$userid");	// store the cookie to identify them
} else {
	$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);
}

$table = "CONTESTS";
$qry = "SELECT * FROM $table WHERE CTS_DEADLINE >= NOW() ORDER BY CTS_DEADLINE";
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
echo '<p class="title">Contests</P>
	<p class="subtitle">Prove yourself.</p></td>';
echo '<td><div id="smallLinks"><a href="contests/new.php">Create a new contest </a></div><br/><div id="smallLinks"><a href="contestsOld.php">See finished contests >></a></div>';
echo '</td></tr></table>';

// check to see if user is new, and if so, provide help
$table = "ARTISTS";
$qry = "SELECT RNK_RANKID FROM $table WHERE ART_USERID = $userid";
$result = mysql_query($qry);
$row = mysql_fetch_assoc($result);
if($row['RNK_RANKID'] < 3 || isset($_GET['help'])){
	echo '<p class="help"><b>This is where you challenge yourself and others.</b><br/>Submit your own work to a contest or create a new contest. After the contest deadline passes, judges have '. $contestJudgePeriod .' days to select the winners. Winners receive points and the winning work or art will be featured on the Street for a day for free, but you will also receive points for creating a contest that more than three artists submit to. Judges are selected randomly from all intermediate and advanced artists who do not have works submitted to the contest.</p>';
}

if(mysql_num_rows($resultContests) > 0){
	echo '<table width=100%><tr><td width=100% class="listing">';
	while($contest = mysql_fetch_assoc($resultContests)){
		$qry = "SELECT WRK_WORKID FROM `CONTEST-WORKS` WHERE CTS_CONTESTID = ". $contest['CTS_CONTESTID'];
		$resultWorks = mysql_query($qry);
		$count = mysql_num_rows($resultWorks);	// number of works submitted to the contest
		$linkContest = "contests/view.php?contestid=" . $contest['CTS_CONTESTID'];
		if($contest['TYP_TYPEID'] == 0){
			echo " [Anything] ";
		} else {
			echo " [" . getTypeName($contest['TYP_TYPEID']) . "] ";
		}
		echo '<div><b><a href="' . $linkContest . '">' . stripslashes($contest['CTS_NAME']) . '</b>';
		echo " - deadline: " . $contest['CTS_DEADLINE'] . "</a> <span class=\"counter\">". $count ." works</span></div>";
	}
	echo '</td></tr></table>';
} else {
	echo '<p class="message">There are no open contests at the moment.</p>';
}

writeTableEnd();
?>


</body>
</html>