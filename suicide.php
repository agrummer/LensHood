<?php

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

if(isset($_GET['trigger'])){	// delete the user's account
	if($_GET['trigger'] == 'pulled'){
		// delete all the user's works
		$qry = "DELETE `CONTEST-WORKS`.* FROM `CONTEST-WORKS` JOIN WORKS ON `CONTEST-WORKS`.WRK_WORKID = WORKS.WRK_WORKID WHERE WORKS.ART_USERID = $userid";	// delete all contest entries associated with this user
		$result = mysql_query($qry) or die (writeError("Sorry, some of your works seem to be corrupted. Please contact support to remove your account."));
		$qry = "DELETE `WORK-TAGS`.* FROM `WORK-TAGS` JOIN WORKS ON `WORK-TAGS`.WRK_WORKID = WORKS.WRK_WORKID WHERE WORKS.ART_USERID = $userid";	// delete all work-tag relationships associated with this user
		$result = mysql_query($qry) or die (writeError("Sorry, some of your works seem to be corrupted. Please contact support to remove your account."));
		$qry = "DELETE IMAGES.*, WORKS.* FROM IMAGES JOIN WORKS ON IMAGES.WRK_WORKID = WORKS.WRK_WORKID WHERE WORKS.ART_USERID = $userid";	// delete the target work
		$result = mysql_query($qry) or die (writeError("Sorry, some of your works seem to be corrupted. Please contact support to remove your account."));
		
		// delete all the user's portfolios
		$qry = "DELETE FROM PORTFOLIOS WHERE ART_USERID = $userid";
		$result = mysql_query($qry) or die (writeError("Sorry, your portfolios seem to be corrupted. Please contact support to remove your account."));
		
		// notify admin of deletion in case of malicious activity
		$qry = "SELECT * FROM ARTISTS WHERE ART_USERID = $userid";
		$result = mysql_query($qry) or die (writeError("Sorry, your account seems to be corrupted. Please contact support to remove your account."));
		$artist = mysql_fetch_assoc($result);
		$nameSave = getName($userid);
		$msgSave = $nameSave[2] .' '. $nameSave[1] . ' has deleted their account.';
		$msgSave .= '<br/>Points = '. $artist['ART_POINTS'];
		$msgSave .= '<br/>Birthday = '. $artist['ART_BIRTH'];
		$msgSave .= '<br/>E-mail = '. $artist['ART_EMAIL'];
		$qry = "INSERT INTO MESSAGES (MSG_TO,MSG_FROM,MSG_MESSAGE,MSG_READ) VALUES ('1','1','". $msgSave ."','0')";
		$result = mysql_query($qry) or die (writeError("Sorry, your account seems to be corrupted. Please contact support to remove your account."));
		
		// delete any log entries involving the user
		$qry = "DELETE FROM LOGINLOG WHERE ART_USERID = $userid";
		$result = mysql_query($qry) or die (writeError("Sorry, your account seems to be corrupted. Please contact support to remove your account."));
		
		// delete the user's account
		$qry = "DELETE FROM NAMES WHERE ART_USERID = $userid";
		$result = mysql_query($qry) or die (writeError("Sorry, your account seems to be corrupted. Please contact support to remove your account."));
		$qry = "DELETE FROM ARTISTS WHERE ART_USERID = $userid";
		$result = mysql_query($qry) or die (writeError("Sorry, your account seems to be corrupted. Please contact support to remove your account."));
		$qry = "DELETE FROM USERS WHERE ART_USERID = $userid";
		$result = mysql_query($qry) or die (writeError("Sorry, your account seems to be corrupted. Please contact support to remove your account."));
		
		header ("Location: index.php?login=none");
	}
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

if(isset($_GET['help'])){
	echo '<p class="help">Don\'t do it!</p>';
}

echo '<center><img src="images/suicide-puppy.jpg">';
echo '<p class="message">Are you sure you want to raise on up out of the hood for good?</p>';
echo '<p id="smallLinks"><a href="?trigger=pulled">Torch my stuff (delete everything)</a></p>';
if(isset($_GET['help'])){
	echo '<p class="help"><b>The button above will delete all of your works, portfolios, and user information forever.</b> The button below will take you back to your profile.</p>';
}
echo '<p id="smallLinks"><a href="profile.php?userid='.$userid.'">Just kidding</a></p><br/>';

writeTableEnd();
?>