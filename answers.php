<?php

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

$perPage = 5;	// number of questions to display per page

$table = "QUESTIONS";
$qry = "SELECT * FROM $table WHERE ANS_ANSWERID IS NOT NULL ORDER BY -QST_CREATED";
if(isset($_GET['page'])){
	$qry .= ' LIMIT '. (mysql_real_escape_string($_GET['page']) - 1) * $perPage .', '. $perPage;
} else {
	$qry .= ' LIMIT 0 , '. $perPage;
}
$resultQuestions = mysql_query($qry);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Answers</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#navQuestions a:link,
#navQuestions a:visited {
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
#navQuestions a:hover {
	color:#000;
	background:#FF8204;
}
</style>
</head>

<body class="page">
<?php
writeTableBegin($userid,0);

echo '<p class="title">Answers</p>
	  <p class="subtitle">See what people are saying.</p>';
echo '<div id="smallLinks"><a href="questions.php"><< Return to unanswered questions</a></div>';

if(isset($_GET['help'])){	// QUICK HELP SYSTEM
	echo '<p class="help">Each question is followed by the answer that was ultimately accepted by the question asker. You can click on a user\'s name to see their profile.</p>';
}

if(mysql_num_rows($resultQuestions) > 0){
	echo '<hr><table width=100%>
		<tr>
		<td class="listing">';
	while($question = mysql_fetch_assoc($resultQuestions)){	// display all questions that have been answered
		$answerID = $question['ANS_ANSWERID'];
		$table = "ANSWERS";
		$qry = "SELECT * FROM $table WHERE ANS_ANSWERID='". $answerID ."'";
		$resultAnswers = mysql_query($qry);
		$answer = mysql_fetch_assoc($resultAnswers);
		$questionAuthor = getName($question['ART_USERID']);
		$answerAuthor = getName($answer['ART_USERID']);
		echo '<div><a href=profile.php?userid='. $question['ART_USERID'] .'><b>'. stripslashes($questionAuthor[2]) .'</b></a> asked: ' . stripslashes($question['QST_QUEST']) .'</div>';
		echo '<div id="answer"><a href=profile.php?userid='. $answer['ART_USERID'] .'><b>'. stripslashes($answerAuthor[2]) .'</b></a> said: ' . stripslashes($answer['ANS_ANSWER']) .'</div>';
		echo '<hr>';
	}
	echo '</td></tr><tr><td>';
	echo '<p id="smallLinks">';
	if($_GET['page'] > 1){
		echo '<a href="?showAll='. $_GET['showAll'] .'&sort='. $_GET['sort'] .'&page='. ($_GET['page'] - 1) .'">previous page</a>';
	}
	if(mysql_num_rows($resultQuestions) >= $perPage){
		echo ' ';
		echo '<a href="?showAll='. $_GET['showAll'] .'&sort='. $_GET['sort'] .'&page=';
		if (isset($_GET['page'])){
			echo ($_GET['page'] + 1);
		} else {
			echo '2';
		}
		echo '">next page</a>';
	}
	echo '</p></td></tr></table>';
} else {
	echo '<p class="message">It\'s like a political debate! No one has given any acceptable answers to questions yet.</p>';
}
writeTableEnd();
?>
</body>
</html>