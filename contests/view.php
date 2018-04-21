<?php
include("../connections.php");
include("../pointValues.php");
include("../formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

if($_POST['name'] != NULL){		// check to see if a new contest was created from 'new.php'
	$problems = array();	// array to add problems onto
	
	$newContest['creator'] = $userid;
	$newContest['name'] = validateName($problems,mysql_real_escape_string($_POST['name']));
	$newContest['type'] = $_POST['type'];
	if($newContest['type'] == 'Anything'){
		$newContest['typeid'] = 0;
	} else {
		$newContest['typeid'] = getTypeID($newContest['type']);
	}
	$newContest['description'] = mysql_real_escape_string($_POST['description']);
	$newContest['deadline'] = getDeadlineDate($_POST['deadline']);
	
	if(count($problems) == 0){	// only proceed if there were no validation problems
		// insert the contest information into the database
		$table = "CONTESTS";
		$insert = mysql_query("INSERT INTO $table (`CTS_CONTESTID` ,
												  `TYP_TYPEID` ,
												  `CTS_FIRSTPTS` ,
												  `CTS_SECONDPTS` ,
												  `CTS_THIRDPTS` ,
												  `ART_USERID` ,
												  `CTS_DEADLINE` ,
												  `CTS_DESCRIPTION` ,
												  `CTS_NAME`
												   )
										  VALUES ('NULL',
												  '" . $newContest['typeid'] . "',
												  '" . $contestPoints['first'] . "',
												  '" . $contestPoints['second'] . "',
												  '" . $contestPoints['third'] . "',
												  '" . $newContest['creator'] . "',
												  '" . $newContest['deadline'] . "',
												  '" . $newContest['description'] . "',
												  '" . $newContest['name'] . "'
												  )")or die(writeError("Unable to process due to an error with the database. Please try again."));
		$contestid = mysql_insert_id($dblink);
		
		$qry = "UPDATE CONTESTS SET CTS_FINISHDATE = ADDDATE(CTS_DEADLINE, INTERVAL $contestJudgePeriod DAY) WHERE CTS_CONTESTID = $contestid";
		$result = mysql_query($qry);
		
	} else {
		die(writeError("There was a problem with the information you provided. Please make sure you filled out everything correctly."));
	}
} else {
	$contestid = mysql_real_escape_string($_GET['contestid']);
	if(isset($_GET['workid'])){	// check to see if user has submitted a new work to the contest
		// submit the chosen work to the contest
		$workid = mysql_real_escape_string($_GET['workid']);
		$table = "WORKS";
		$qry = "SELECT ART_USERID FROM $table WHERE WRK_WORKID = $workid";
		$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
		$work = mysql_fetch_assoc($result);
		if($work['ART_USERID'] == $userid){	// check to make sure the user is submitting their own work
			$table = "CONTEST-WORKS";
			$qry = "SELECT * FROM `$table` WHERE CTS_CONTESTID = $contestid AND WRK_WORKID = $workid";
			$result = mysql_query($qry) or die (writeError("There was an error submitting your work to the database. Please try again later."));
			if(mysql_num_rows($result) == 0){
				$qry = "INSERT INTO `$table` (CTS_CONTESTID, WRK_WORKID) VALUES ('". $contestid ."','". $workid ."')";
				$result = mysql_query($qry) or die (writeError("There was an error submitting your work to the database. Please try again later."));
			} else {
				die(writeError("This work has already been submitted to this contest"));
			}
		} else {
			die(writeError("You must submit your own, original work."));
		}
	}
}
$table = "CONTESTS";
$qry = "SELECT * FROM $table WHERE CTS_CONTESTID = $contestid";
$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
if(mysql_num_rows($result) > 0){
	$contest = mysql_fetch_assoc($result);
} else {
	die(writeError("This contest does not exist."));
}

// check to see if the user has been assigned to judge the contest
$isJudge = false;
$table = "CONTEST-JUDGES";
$qry = "SELECT * FROM `$table` WHERE CTS_CONTESTID = ". $contestid ." AND ART_USERID = ". $userid;
$resultJudge = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
if(mysql_num_rows($resultJudge) > 0){
	$isJudge = true;
}

if($isJudge && $contest['CTS_FIRSTWORKID'] == NULL){
	// if the user is a judge, and they have voted on a submition, record it
	$judge = mysql_fetch_assoc($resultJudge);
	if(isset($_GET['first'])){
		if($judge['CTS-JDG_FIRSTWORKID'] == NULL){	// don't allow them to vote more than once
			$table = "CONTEST-WORKS";
			$qry = "UPDATE `$table` SET `CTS-WRK_VOTES` = `CTS-WRK_VOTES` + ". $contestPoints['first'] ." WHERE CTS_CONTESTID = $contestid AND WRK_WORKID = ". mysql_real_escape_string($_GET['first']);
			$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
			$table = "CONTEST-JUDGES";
			$qry = "UPDATE `$table` SET `CTS-JDG_FIRSTWORKID` = ". mysql_real_escape_string($_GET['first']) ." WHERE CTS_CONTESTID = $contestid AND ART_USERID = $userid";
			$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
		}
	}
	if(isset($_GET['second'])){
		if($judge['CTS-JDG_SECONDWORKID'] == NULL){
			$table = "CONTEST-WORKS";
			$qry = "UPDATE `$table` SET `CTS-WRK_VOTES` = `CTS-WRK_VOTES` + ". $contestPoints['second'] ." WHERE CTS_CONTESTID = $contestid AND WRK_WORKID = ". mysql_real_escape_string($_GET['second']);
			$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
			$table = "CONTEST-JUDGES";
			$qry = "UPDATE `$table` SET `CTS-JDG_SECONDWORKID` = ". mysql_real_escape_string($_GET['second']) ." WHERE CTS_CONTESTID = $contestid AND ART_USERID = $userid";
			$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
		}
	}
	if(isset($_GET['third'])){
		if($judge['CTS-JDG_THIRDWORKID'] == NULL){
			$table = "CONTEST-WORKS";
			$qry = "UPDATE `$table` SET `CTS-WRK_VOTES` = `CTS-WRK_VOTES` + ". $contestPoints['third'] ." WHERE CTS_CONTESTID = $contestid AND WRK_WORKID = ". mysql_real_escape_string($_GET['third']);
			$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
			$table = "CONTEST-JUDGES";
			$qry = "UPDATE `$table` SET `CTS-JDG_THIRDWORKID` = ". mysql_real_escape_string($_GET['third']) ." WHERE CTS_CONTESTID = $contestid AND ART_USERID = $userid";
			$result = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
		}
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Contests</title>
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
echo '<p class="title">' . stripslashes($contest['CTS_NAME']) .'</p>';
echo '<p class="subtitle">' . getTypeName($contest['TYP_TYPEID']) .'</p>';
echo '<p class="message">Submission deadline: ' . $contest['CTS_DEADLINE'] .'</p>';
echo '<p class="message">' . stripslashes($contest['CTS_DESCRIPTION']) .'</p>';
if($contest['CTS_ASSIGNED'] == 0){	// submisssion period is still active
	echo '<div id="smallLinks"><a href="submit.php?contestid='. $contestid .'">Submit to this contest</a></div>';
} elseif($isJudge && $contest['CTS_FIRSTWORKID'] == NULL){	// user is a judge and the judging period is still open
	echo '<p class="help"><b>You have been selected to judge this contest.</b><br/> Please review all the submitions and then select your votes for first, second and third place.</p>';
	echo '<p class="help"><i>(and yes, you will be paid in points for your judge duty)</i></p>';
} elseif($contest['CTS_FIRSTWORKID'] == NULL) {	// judging period is still open
	echo '<p class="help"><b>The judges are still casting their votes for the winners of this contest.</b> Please check back soon!</p>';
}
echo '<hr/>';
// display submitted works
$table = "CONTEST-WORKS";
$qry = "SELECT * FROM `$table` WHERE CTS_CONTESTID = $contestid";
$resultWorks = mysql_query($qry) or die (writeError("There was an error with the database. Please try again later."));

if(mysql_num_rows($resultWorks) > 0){
	echo '<table><tr>';
	$place=0;	// marker to keep track of which cell of the table the loop is in
	while($work = mysql_fetch_assoc($resultWorks)){
		$table = "CONTEST-JUDGES";
		$qry = "SELECT * FROM `$table` WHERE CTS_CONTESTID = $contestid AND ART_USERID = $userid";
		$resultJudge = mysql_query($qry) or die(writeError("There was an error with the database. Please try again."));
		$judge = mysql_fetch_assoc($resultJudge);
		if($work['WRK_WORKID'] == $contest['CTS_FIRSTWORKID']){	// format differently for winning works
			echo '<td width="140px" id="thumbPlace" align="center">';
			echo '<div id="thumbTitlePlace" align="left">First Place</div>';
		} elseif($work['WRK_WORKID'] == $contest['CTS_SECONDWORKID']){
			echo '<td width="140px" id="thumbPlace" align="center">';
			echo '<div id="thumbTitlePlace" align="left">Second Place</div>';
		} else if($work['WRK_WORKID'] == $contest['CTS_THIRDWORKID']){
			echo '<td width="140px" id="thumbPlace" align="center">';
			echo '<div id="thumbTitlePlace" align="left">Third Place</div>';
		} else {
			echo '<td width="140px" id="thumb" align="center">';
		}
		echo '<a href="../works/work.php?workid='. $work['WRK_WORKID'] .'"><img border="0" src="../works/'. getThumbURL($work['WRK_WORKID']) .'"></a>';
		if($isJudge && $contest['CTS_FIRSTWORKID'] == NULL){	// if the user is a judge, display buttons for which places they have not yet voted for
			if($judge['CTS-JDG_FIRSTWORKID'] == NULL){
				echo '<div id="voteLabel" align="center"><a href="view.php?contestid='. $contestid .'&first='. $work['WRK_WORKID'] .'">VOTE 1st PLACE</a></div>';
			}
			if($judge['CTS-JDG_SECONDWORKID'] == NULL){
				echo '<div id="voteLabel" align="center"><a href="view.php?contestid='. $contestid .'&second='. $work['WRK_WORKID'] .'">VOTE 2nd PLACE</a></div>';
			}
			if($judge['CTS-JDG_THIRDWORKID'] == NULL){
				echo '<div id="voteLabel" align="center"><a href="view.php?contestid='. $contestid .'&third='. $work['WRK_WORKID'] .'">VOTE 3rd PLACE</a></div>';
			}
		}
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
			$place = 0;
		}
	}
	echo '</tr></table>';
} else {
	echo '<p class="help">This contest has no entries.</p>';
}
writeTableEnd();

echo '</body>';
echo '</html>';

function validateName($problems,$input){	// make sure the name is valid and not already taken.
	if(strlen($input) < 1){
		array_push($problems,2);
		return $input;
	}
	$table = "CONTESTS";
	$qry = "SELECT ART_USERID FROM $table WHERE CTS_NAME = '". $input."';";
	$result = mysql_query($qry);
	$num_rows = mysql_num_rows($result); 
	if ($num_rows != 0) { 
		array_push($problems,2);
		return $input;
	} else {
		return $input;	
	}
}

function getDeadlineDate($input){	// drop the day of the week and format the date in the correct order for MySQL
	$pos = strpos($input," ") + 1;
	$month = substr($input,$pos,2);
	$date = substr($input,$pos + 3,2);
	$year = substr($input,$pos + 6,4);
	return $year . "-" . $month . "-" . $date;
}
?>