<?php
session_start();

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

if($_SESSION['registered']){
	$userid = $_SESSION['registered'];
	unset($_SESSION['registered']);
	setcookie("loggedin", "TRUE");	// store the cookie to keep them logged in
	setcookie("LensHood_ID", "$userid");	// store the cookie to identify them
} else {
	$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);
}

if(isset($_GET['userid'])){
	$profileid = mysql_real_escape_string($_GET['userid']);	// get the userid of the profile to display
} else {
	die(writeError("You must specify a profile to view."));
}

if(isset($_GET['workid'])){
	$defaultWorkID = mysql_real_escape_string($_GET['workid']);	// get the workid of a work to set as the user's showcase piece
	
	$table = "WORKS";
	$qry = "SELECT WRK_WORKID FROM $table WHERE ART_USERID=$userid AND WRK_WORKID=$defaultWorkID";
	$result = mysql_query($qry) or die(writeError("Your showcase piece must be your own work!"));
	
	if(mysql_num_rows($result)){
		$table = "ARTISTS";
		$qry = "UPDATE $table SET WRK_WORKID=". $defaultWorkID ." WHERE ART_USERID=$userid";
		$result = mysql_query($qry) or die(writeError("Unable to write to the database at the moment."));
	}
}

if(strlen($_POST['messageText']) > 0){	// if the user is sending a message, save it to the database
	sendMessage($userid,$profileid,mysql_real_escape_string($_POST['messageText']));
	$newMessage['isSent'] = 1;
}

$table = "NAMES";
$qry = "SELECT * FROM $table WHERE ART_USERID = " . $profileid;
$result = mysql_query($qry) or die(writeError("There was a problem working with the database."));
if(mysql_num_rows($result) < 1){
	die(writeError("This profile no longer exists."));
}
$row = mysql_fetch_row ($result);
$profile['last'] = stripslashes($row[1]);
$profile['first'] = stripslashes($row[2]);

$table = "USERS";
$qry = "SELECT * FROM $table WHERE ART_USERID = " . $profileid;
$result = mysql_query($qry) or die(writeError("There was a problem working with the database."));
$row = mysql_fetch_assoc ($result);
$profile['username'] = $row['USR_USERNAME'];

$table = "ARTISTS";
$qry = "SELECT * FROM $table WHERE ART_USERID = " . $profileid;
$result = mysql_query($qry) or die(writeError("There was a problem working with the database."));
$row = mysql_fetch_assoc ($result);
$profile['typeid'] = $row['TYP_TYPEID'];
$profile['defaultWork'] = $row['WRK_WORKID'];
$profile['points'] = $row['ART_POINTS'];
$profile['birthday'] = $row['ART_BIRTH'];
$profile['gender'] = $row['ART_ISMALE'];
$profile['email'] = $row['ART_EMAIL'];
$profile['rank'] = getRankName($row['RNK_RANKID']);

$table = "TYPES";
$qry = "SELECT TYP_NAME FROM $table WHERE TYP_TYPEID = " . $profile['typeid'];
$resultType = mysql_query($qry) or die(writeError("There was a problem working with the database."));
$rowType = mysql_fetch_assoc($resultType);
$profile['type'] = $rowType['TYP_NAME'];

if($profile['defaultWork'] != NULL){
	$profile['defaultWorkURL'] = 'works/' . getThumbURL($profile['defaultWork']);
}

$table = "PORTFOLIOS";
$qry = "SELECT * FROM $table WHERE ART_USERID = " . $profileid . " ORDER BY PRT_NAME";
$resultPortfolios = mysql_query($qry) or die(writeError("There was a problem working with the database."));
$portCount = mysql_num_rows($resultPortfolios);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#navProfile a:link,
#navProfile a:visited {
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
#navProfile a:hover {
	color:#000;
	background:#FF8204;
}
</style>
</head>

<body class="page">
<?php
writeTableBegin($userid,0);

if($newMessage['isSent'] == 1){
	echo '<p class="help">Your message was sent successfully</p>';
} elseif ($newMessage['isSent'] == 2){
	echo '<p class="help">Your message failed to send. Please try again.</p>';
}

if($profile['defaultWorkURL'] != NULL){
	echo '<img src="'. $profile['defaultWorkURL'] .'" class="showcaseWork" />';
} elseif($userid == $profileid) {
	echo '<p class="help">To select a profile image, view or upload a work and click "set as profile image"</p>';
}
echo '<p class="title">';
echo $profile['first'] . " " . $profile['last'];
echo '</p>';
echo '<p class="subtitle">';
echo stripslashes($profile['rank']) . ' ' . stripslashes($profile['type']);
if($userid == $profileid){
	echo ' <span class="pointBox">'. $profile['points'] .' pts</span>';
}
echo '</p>';

if($userid == $profileid){	// if this is the profile of the user who is logged in
	echo '<p id="smallLinks"><a href="newPortfolio.php">Create a new portfolio</a> ';
	if($portCount > 0){
		echo ' <a href="works/upload.php">Upload new work</a></p>';
	} else {
		echo '</p><p class="help">You must create a portfolio before you can upload works.</p>';
	}
}
if($portCount > 0){
	echo '<p class="subtitle">Portfolios</p>';
	echo '<table width="400"><tr><td class="portList">';
	while($port = mysql_fetch_assoc($resultPortfolios)){
		$qry = "SELECT WRK_WORKID FROM WORKS WHERE PRT_PORTID = ". $port['PRT_PORTID'];
		$resultWorks = mysql_query($qry);
		$workCount = mysql_num_rows($resultWorks);
		echo '<div class="portList"><b><a href="portfolio.php?portid='. $port['PRT_PORTID'] .'">'. stripslashes($port['PRT_NAME']) .'</b> '. stripslashes(getTypeName($port['TYP_TYPEID'])) .'</a> <span class="counterLight">';
		if($workCount > 1){
			echo $workCount .' works';
		} elseif($workCount == 1){
			echo $workCount .' work';
		} else {
			echo 'empty';
		}
		echo '</span></div>';
	}
	echo '</td></tr></table>';
} elseif($userid == $profileid) {
	echo '<p class="help"><b>Welcome to your profile.</b> You can create portfolios here, which will let you start uploading works to get feedback and enter in contests.</p>';
} else{
	echo '<p class="message">This user has not created any portfolios yet.</p>';
}

if($userid != $profileid){	// if this is not the profile of the user who is logged in
	echo '<br/><p class="subtitle">Send '. stripslashes($profile['first']) .' a message</p>';
	if(isset($_GET['help'])){
		echo '<p class="help">If you submit a message to this user, they will see it on their "street" page the next time they login.</p>';
	}
	echo '<table><tr><td class="registerForm">';
	echo '<form id="sendMessage" name="sendMessage" method="post" action="" >
			<label><span>Message</span>
				<textarea name="messageText" cols="65" rows="2" maxlength="1023" class="textInput"></textarea>
		<input type="submit" name="submit" id="submit" value="Send" tabindex="3" class="submitForm" /></label>
	</form>';
}

echo '<br/>';
if($userid == $profileid){	// if this is the profile of the user who is logged in
	echo '<p id="smallLinks"><a href="suicide.php">Delete account</a> ';
	echo ' <a href="changePassword.php">Change password</a></p>';
}

writeTableEnd();
?>
</body>
</html>