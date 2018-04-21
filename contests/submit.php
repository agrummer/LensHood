<?php
include("../connections.php");
include("../pointValues.php");
include("../formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);


$contestid = mysql_real_escape_string($_GET['contestid']);

$table = "CONTESTS";
$qry = "SELECT * FROM $table WHERE CTS_CONTESTID = $contestid";
$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
if(mysql_num_rows($result) > 0){
	$contest = mysql_fetch_assoc($result);
	if($contest['CTS_ASSIGNED']){
		die(writeError("Sorry, but this ship has already sailed."));
	}
	$typeid = $contest['TYP_TYPEID'];
} else {
	die(writeError("This contest does not exist."));
}

if(isset($_GET['portid'])){
	$portid = mysql_real_escape_string($_GET['portid']);
	$table = "WORKS";
	$qry = "SELECT * FROM $table WHERE ART_USERID = $userid AND PRT_PORTID = $portid";
	$resultWorks = mysql_query($qry) or die(writeError("There was a problem working with the database."));
	$workCount = mysql_num_rows($resultWorks);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood</title>
<link href="../lenshood.css" rel="stylesheet" type="text/css" />
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
writeTableBegin($userid,1);
echo '<p class="title">Submit to ' . stripslashes($contest['CTS_NAME']) .'</p>';
echo '<p class="subtitle">' . getTypeName($contest['TYP_TYPEID']) .'</p>';
echo '<p class="message">' . stripslashes($contest['CTS_DESCRIPTION']) .'</p>';

if($workCount > 0){
	// display the works in the chosen portfolio
	echo '<table>';
	$place=0;	// marker to keep track of which cell of the table the loop is in
	echo '<tr>';
	while($work = mysql_fetch_assoc($resultWorks)){
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="view.php?contestid='. $contestid .'&workid='. $work['WRK_WORKID'] .'"><img src="../works/'. getThumbURL($work['WRK_WORKID']) .'"></a>';
		echo '<div id="thumbTitle" align="left">'. stripslashes($work['WRK_TITLE']) .'</div>';
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
			$place = 0;
		}
	}
	echo '</table>';
} else {
	// display user's portfolios
	$table = "PORTFOLIOS";
	$qry = "SELECT PRT_PORTID,PRT_NAME FROM $table WHERE ART_USERID = $userid";
	if($typeid != 0){
		$qry .= " AND TYP_TYPEID = $typeid";
	}
	$resultPortfolios = mysql_query($qry) or die(writeError("There was a problem working with the database."));
	$portCount = mysql_num_rows($resultPortfolios);
	if($portCount > 0){
		echo '<p class="subtitle">Select a portfolio to choose a work from:</p>';
		echo '<table width=100%><tr><td width=100% class="listing">';
		while($port = mysql_fetch_assoc($resultPortfolios)){
			echo '<div><b><a href="submit.php?contestid='. $contestid .'&portid='. $port['PRT_PORTID'] .'">'. stripslashes($port['PRT_NAME']) .'</a></b></div>';
		}
		echo '</td></tr></table>';
	} else {
		echo '<p class="message">You have no ';
		if($contest['TYP_TYPEID'] != 0){
			echo getTypeName($typeid) .' ';
		}  
		echo 'works to submit to this contest.</p>';
	}
}


writeTableEnd();

echo '</body>';
echo '</html>';
?>