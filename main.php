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
	//$qry = "SELECT USR_USERNAME FROM USERS WHERE ART_USERID = $userid";
	//$result = mysql_query($qry) or die("ONE: ".mysql_error());
	//$row = mysql_fetch_assoc($result);
	//$username = $row['USR_USERNAME'];
	//$userHash = md5($username);
	setcookie("LensHood_ID", "$userid");	// store the cookie to identify them
} else {
	$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);
}

if(isset($_GET['rateCommentID']) && isset($_GET['rateCommentValue'])){	// rate a comment received and award points to appropriate user
	$table = "COMMENTS";
	$qry = "SELECT * FROM $table WHERE CMT_COMMENTID=". mysql_real_escape_string($_GET['rateCommentID']);
	$result = mysql_query($qry) or die(writeError("There was a problem working with the database."));
	$rateComment = mysql_fetch_assoc($result);
	if($rateComment['CMT_FEEDBACK'] == NULL){
		$rateComment['CMT_FEEDBACK'] = mysql_real_escape_string($_GET['rateCommentValue']);
		$qry = "UPDATE $table SET CMT_FEEDBACK = '1' WHERE CMT_COMMENTID = " . $rateComment['CMT_COMMENTID'];
		$result = mysql_query($qry);
		if($rateComment['ART_USERID'] != $userid){	// don't award points if the user is just commenting on their own work.
			$rateComment['pointValue'] = $commentPoints[$rateComment['CMT_FEEDBACK']];
			$notifyName = getName($userid);
			$notify = '<b><a href="profile.php?userid='.$userid.'">'. $notifyName[2] .'</a></b> ';
			if($rateComment['pointValue'] > 1){
				$notify .= 'gave props to your comment.';
			} else {
				$notify = NULL;
			}
			addPoints($rateComment['ART_USERID'],$rateComment['pointValue'],$notify);
		}
	}
}

if(isset($_GET['rateAnswerID']) && isset($_GET['rateAnswerValue'])){	// rate an answer received and award points to appropriate user
	$table = "ANSWERS";
	$qry = "SELECT * FROM $table WHERE ANS_ANSWERID=". mysql_real_escape_string($_GET['rateAnswerID']);
	$result = mysql_query($qry) or die(writeError("There was a problem working with the database."));
	$rateAnswer = mysql_fetch_assoc($result);
	if($rateAnswer['ANS_FEEDBACK'] == NULL){
		$rateAnswer['ANS_FEEDBACK'] = mysql_real_escape_string($_GET['rateAnswerValue']);
		$qry = "UPDATE $table SET ANS_FEEDBACK = '1' WHERE ANS_ANSWERID = " . $rateAnswer['ANS_ANSWERID'];
		$result = mysql_query($qry);
		if($rateAnswer['ART_USERID'] != $userid){	// don't award points if the user is just answering their own question.
			$rateAnswer['pointValue'] = $answerPoints[$rateAnswer['ANS_FEEDBACK']];
			$notifyName = getName($userid);
			$notify = '<b><a href="profile.php?userid='.$userid.'">'. $notifyName[2] .'</a></b> accepted your answer to their question.'; 
			addPoints($rateAnswer['ART_USERID'],$rateAnswer['pointValue'],$notify);
		}
		if($rateAnswer['ANS_FEEDBACK'] == 'maybe' || $rateAnswer['ANS_FEEDBACK'] == 'rejected'){	// if the user rejected or chose to re-list the question, the answer must be cleared
			$table = "QUESTIONS";
			$qry = "SELECT QST_QUESTID FROM $table WHERE ANS_ANSWERID=" . $rateAnswer['ANS_ANSWERID'];
			$result = mysql_query($qry);
			$row = mysql_fetch_assoc($result);
			$rateAnswer['QST_QUESTID'] = $row['QST_QUESTID'];
			$qry = "UPDATE $table SET ANS_ANSWERID = NULL WHERE QST_QUESTID = " . $rateAnswer['QST_QUESTID'];
			$result = mysql_query($qry);
		}
	}
}

if(isset($_GET['deleteMsg'])){	// delete message
	$deleteMessage = mysql_real_escape_string($_GET['deleteMsg']);
	$table = "MESSAGES";
	$qry = "UPDATE $table SET MSG_READ = 1 WHERE MSG_TO = $userid AND MSG_ID = ". $deleteMessage;
	//$qry = "DELETE FROM $table WHERE MSG_TO = $userid AND MSG_ID = ". $deleteMessage;
	$result = mysql_query($qry);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#navMain a:link,
#navMain a:visited {
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
#navMain a:hover {
	color:#000;
	background:#FF8204;
}
</style>
</head>

<body class="page">
<?php
writeTableBegin($userid,0);

// DISPLAY FEATURED WORKS
$table1 = "MAINST";
$table2 = "IMAGES";
$qry = "SELECT $table1.MNS_ID,$table1.WRK_WORKID,$table1.MNS_STARTDATE,$table2.IMG_THUMB FROM $table1 JOIN $table2 ON $table1.WRK_WORKID = $table2.WRK_WORKID ORDER BY MAINST.MNS_ID LIMIT 4";
$resultFeature = mysql_query($qry);
if(mysql_num_rows($resultFeature) > 0){
	echo '<table width=100% id="featureTable"><tr>';
	while($feature = mysql_fetch_assoc($resultFeature)){
		if($feature['MNS_STARTDATE'] == NULL){
			$qry = "UPDATE MAINST SET MNS_STARTDATE = NOW() WHERE MNS_ID = ". $feature['MNS_ID'];
			$result = mysql_query($qry);
		}
		echo '<td align="center" id="featureThumb"><a href="works/work.php?workid='. $feature['WRK_WORKID'] .'"><img border="0" src="works/'. $feature['IMG_THUMB'] .'"></a></td>';
	}
	echo '</tr><table>';
	unset($feature);
	unset($resultFeature);
}

$result = mysql_query("SELECT NMS_FIRST FROM NAMES WHERE ART_USERID = $userid");
$row = mysql_fetch_row($result);
$fname = $row[0];
echo '<p class="title">Welcome, ' . $fname . writeSearchBox() .'.</p>';

// check to see if user is new, and if so, provide help
$table = "ARTISTS";
$qry = "SELECT RNK_RANKID FROM $table WHERE ART_USERID = $userid";
$result = mysql_query($qry);
$row = mysql_fetch_assoc($result);
$rankid = $row['RNK_RANKID'];
if($rankid < 2 || isset($_GET['help'])){
	echo '<p class="help"><i>Note from Alex Grummer: </i><b>Please help me test the site by using all of the available features and reporting any bugs via the "Questions" feature or e-mail.</b></p>';
	echo '<p class="help"><b>This is Main Street, where you enter the hood.</b><br/>Any new messages for you will show up here. If someone comments on your work, it will show up here and you will be asked to rank the comment\'s value to you. If someone responds to a question you ask, it will show up here as well. If you are ever selected for Judge Duty for a contest, you will be notified here.<br/><i>There\'s plenty more that goes down on Main Street, but you will see for yourself in time.</i></p>';
}

// DISPLAY ANY NEW NOTIFICATIONS FOR THE USER
$table = "NOTIFY";
$qry = "SELECT * FROM $table WHERE ART_USERID = $userid AND NTF_READ = 0 ORDER BY -NTF_CREATED";
$resultNotifications = mysql_query($qry);
if(mysql_num_rows($resultNotifications) > 0){
	while ($newNotify = mysql_fetch_assoc($resultNotifications)){
		echo '<div class="newNotify">';
		echo '</span><span id="newNotifyText">'. stripslashes($newNotify['NTF_MESSAGE']) .'</span></div>';
		$qry = "UPDATE NOTIFY SET NTF_READ = 1 WHERE NTF_ID = ". $newNotify['NTF_ID'];
		$result = mysql_query($qry);
	}
	unset($newNotify);
	unset($resultNotifications);
}

// DISPLAY ANY CONTESTS THE USER HAS BEEN ASSIGNED TO JUDGE
$table1 = "CONTEST-JUDGES";
$table2 = "CONTESTS";
$qry = "SELECT `$table1`.CTS_CONTESTID,CTS_NAME,TYP_TYPEID FROM `$table1` JOIN `$table2` ON `$table1`.CTS_CONTESTID = `$table2`.CTS_CONTESTID WHERE `$table1`.ART_USERID = ". $userid ." AND `$table2`.CTS_FINISHDATE > NOW()";
$qry .= " AND (ISNULL(`$table1`.`CTS-JDG_FIRSTWORKID`))";
$resultContests = mysql_query($qry) or die(writeError("There was a problem working with the database. Please try again in a moment."));
if(mysql_num_rows($resultContests) > 0){
	echo '<p class="subtitle">Contests to Judge</p>';
	echo '<p class="help"><b>You have been selected to judge these contests.</b> Please do so as quickly as possible.</p>';
	echo '<table width=100%><tr><td width=100% class="listing">';
	while($contest = mysql_fetch_assoc($resultContests)){
		$linkContest = "contests/view.php?contestid=" . $contest['CTS_CONTESTID'];
		if($contest['TYP_TYPEID'] == 0){
			echo " [Anything] ";
		} else {
			echo " [" . getTypeName($contest['TYP_TYPEID']) . "] ";
		}
		echo '<div><b><a href="' . $linkContest . '">' . stripslashes($contest['CTS_NAME']) . '</b>';
		echo "</a></div>";
	}
	echo '</td></tr></table>';
	unset($contest);
	unset($resultContests);
}

// DISPLAY ANY NEW COMMENTS THE USER HAS RECEIVED ON THEIR WORKS
$table1 = "WORKS";
$table2 = "COMMENTS";
$qry = "SELECT $table1.WRK_WORKID,$table1.ART_USERID,$table2.CMT_COMMENTID,$table2.CMT_FEEDBACK,$table2.ART_USERID as CMT_USERID,$table2.CMT_CONTENT FROM $table1 JOIN $table2 ON $table1.WRK_WORKID = $table2.WRK_WORKID WHERE $table1.ART_USERID = $userid AND ISNULL($table2.CMT_FEEDBACK) AND $table1.ART_USERID != $table2.ART_USERID";
$resultComments = mysql_query($qry) or die (writeError("Sorry, there was a problem working with the database. Please try again later."));
$place = 0;
if(mysql_num_rows($resultComments) > 0){
	echo '<p class="subtitle">new comments on your work</p>';
	if($rankid < 3 || isset($_GET['help'])){
		echo '<p class="help"><b>Please rate these comments on their relevancy by clicking the numbers below them.</b><br/>0 = irrelevant, 1 = not helpful, 3 = very helpful</p>';
	}
	echo '<table><tr>';
	while($newComment = mysql_fetch_assoc($resultComments)){
		$newCommentName = getName($newComment['CMT_USERID']);
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="works/work.php?workid='. $newComment['WRK_WORKID'] .'"><img src="works/'. getThumbURL($newComment['WRK_WORKID']) .'"></a>';
		echo '<div id="thumbTitle" align="left"><a href="profile.php?userid='. $newComment['CMT_USERID'] .'">'. stripslashes($newCommentName[2]) .'</a> says: '. stripslashes($newComment['CMT_CONTENT']) .'</div>';
		echo '<div id="rateComment"><ul id="rateComment">';
		echo '<li><a href="main.php?rateCommentID='. $newComment['CMT_COMMENTID'] .'&rateCommentValue='. rejected .'">0</a></li>';
		echo '<li><a href="main.php?rateCommentID='. $newComment['CMT_COMMENTID'] .'&rateCommentValue='. low .'">1</a></li>';
		echo '<li><a href="main.php?rateCommentID='. $newComment['CMT_COMMENTID'] .'&rateCommentValue='. medium .'">2</a></li>';
		echo '<li><a href="main.php?rateCommentID='. $newComment['CMT_COMMENTID'] .'&rateCommentValue='. high .'">3</a></li>';
		echo '</div></td>';
		if($place++ == 3){
			echo '</tr><tr>';
			$place = 0;
		}
	}
	echo '</tr></table>';
	unset($newCommentName);
	unset($newComment);
	unset($resultComments);
}

// DISPLAY ANY NEW ANSWERS THE USER HAS RECEIVED TO QUESTIONS THEY HAVE ASKED
$table1 = "QUESTIONS";
$table2 = "ANSWERS";
$qry = "SELECT $table1.QST_QUESTID,$table1.ART_USERID,$table1.ANS_ANSWERID,$table1.QST_QUEST,$table2.ANS_ANSWERID,$table2.ART_USERID as ANS_USERID,$table2.ANS_FEEDBACK, $table2.ANS_ANSWER FROM $table1 JOIN $table2 ON $table1.ANS_ANSWERID = $table2.ANS_ANSWERID WHERE $table1.ART_USERID = $userid AND ISNULL($table2.ANS_FEEDBACK) AND $table1.ART_USERID != $table2.ART_USERID";
$resultAnswers= mysql_query($qry) or die (writeError("Sorry, there was a problem working with the database. Please try again later."));
if(mysql_num_rows($resultAnswers) > 0){
	if($rankid < 3 || isset($_GET['help'])){
		echo '<p class="subtitle">new answers to your questions</p>';
		echo '<p class="help"><b>Does this answer your question?</b> "Keep Open" will give the user a point for trying but your question will stay active for others to respond (Rejecting it gives them no points). If you accept their answer, your question will be moved to the "answers" page for others to see.</p>';
	}
	echo '<table width=100%>';
	while($newAnswer = mysql_fetch_assoc($resultAnswers)){
		echo '<tr><td class="listing">';
		$questionAuthor = getName($newAnswer['ART_USERID']);
		$answerAuthor = getName($newAnswer['ANS_USERID']);
		echo '<div><a href=profile.php?userid='. $newAnswer['ART_USERID'] .'><b>'. stripslashes($questionAuthor[2]) .'</b></a> asked: ' . stripslashes($newAnswer['QST_QUEST']) .'</div>';
		echo '<div id="answer"><a href=profile.php?userid='. $newAnswer['ANS_USERID'] .'><b>'. stripslashes($answerAuthor[2]) .'</b></a> said: ' . stripslashes($newAnswer['ANS_ANSWER']);
		echo '<ul id="rateAnswer">';
		echo '<li><a href="main.php?rateAnswerID='. $newAnswer['ANS_ANSWERID'] .'&rateAnswerValue=accepted"> Accept </a></li>';
		echo '<li><a href="main.php?rateAnswerID='. $newAnswer['ANS_ANSWERID'] .'&rateAnswerValue=maybe"> Keep Open </a></li>';
		echo '<li><a href="main.php?rateAnswerID='. $newAnswer['ANS_ANSWERID'] .'&rateAnswerValue=rejected"> Reject </a></li>';
		echo '</ul></div>';
		echo '</td></tr>';
	}
	echo '</table>';
	unset($questionAuthor);
	unset($answerAuthor);
	unset($newAnswer);
	unset($resultAnswers);
}

// DISPLAY ANY NEW MESSAGES THE USER HAS RECEIVED
$table = "MESSAGES";
$qry = "SELECT * FROM $table WHERE MSG_TO = $userid AND MSG_READ = 0 ORDER BY -MSG_SENT";
$resultMessages = mysql_query($qry);
if(mysql_num_rows($resultMessages) > 0){
	echo '<p class="subtitle">New Messages</p>';
	while ($newMessage = mysql_fetch_assoc($resultMessages)){
		$fromName = getName($newMessage['MSG_FROM']);
		echo '<div class="newMessage">';
		echo '<span id="newMessageDelete"><a href="?deleteMsg='. $newMessage['MSG_ID'] .'">close <b>x</b></a></span>';
		echo '<span id="newMessageDelete"><a href="profile.php?userid='. $newMessage['MSG_FROM'] .'">reply</a></span>';
		echo '<span id="newMessageFrom"><a href="profile.php?userid='. $newMessage['MSG_FROM'] .'">from <b>'. stripslashes($fromName[2]) .' '. stripslashes($fromName[1]) . '</b></a> ';
		echo '</span><span id="newMessageText">'. stripslashes($newMessage['MSG_MESSAGE']) .'</span></div>';
	}
	unset($fromName);
	unset($newMessage);
	unset($resultMessages);
}

// DISPLAY COMMUNITY NEWS
echo '<p class="subtitle">What\'s new in the Hood</p>';
echo '<div class="communityNews">';
echo '<span id="communityNewsHeader">Going back to Sea-town</span>';
echo '<span id="communityNewsText">I\'m traveling back to Seattle so I won\'t online until June. Feel free to keep using the site though!</span></div>';

dbClose($dblink);
writeTableEnd();
?>
</body>
</html>