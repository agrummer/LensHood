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

if (isset($_POST['question']) && strlen($_POST['question']) > 0) {	// save any newly created questions to the database
	$newQuestion = mysql_real_escape_string($_POST['question']);
	$table = "QUESTIONS";
	$qry = "INSERT INTO $table (`ART_USERID`,`QST_QUEST`) VALUES ('$userid','$newQuestion')";
	$result = mysql_query($qry);
}

if (isset($_POST['answer']) && strlen($_POST['answer']) > 0) {	// save any newly created answers to the database
	$newAnswer = mysql_real_escape_string($_POST['answer']);
	$oldQuestionID = mysql_real_escape_string($_POST['questid']);
	$table = "ANSWERS";
	$qry = "INSERT INTO $table (`ART_USERID`,`ANS_ANSWER`) VALUES ('$userid','$newAnswer')";
	$result = mysql_query($qry);
	$newAnswerID = mysql_insert_id();
	$table = "QUESTIONS";
	$qry = "UPDATE $table SET ANS_ANSWERID = '$newAnswerID' WHERE QST_QUESTID = '$oldQuestionID'";
	$result = mysql_query($qry);
}

$table = "QUESTIONS";
$qry = "SELECT * FROM $table";
$resultQuestions = mysql_query($qry);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Questions</title>
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

echo '<p class="title">Ask or Answer a Question</p>
	  <p class="subtitle">You might learn something.</p>';
echo '<div id="smallLinks"><a href="answers.php">See answered questions >></a></div>';

// check to see if user is new, and if so, provide help
$table = "ARTISTS";
$qry = "SELECT RNK_RANKID FROM $table WHERE ART_USERID = $userid";
$result = mysql_query($qry);
$row = mysql_fetch_assoc($result);
if($row['RNK_RANKID'] < 3 || isset($_GET['help'])){
	echo '<p class="help"><b>Here you can ask a question or answer someone else\'s. </b><br/>When someone answers your question, their response will show up on your Main Street page and you will be asked to accept or reject their response. If you accept it, your question will be removed from the list and the user who answered it will receive points. If you reject it, the user receives nothing and the question goes back up on the board for others to answer. You can also choose to "keep open" the question, which gives the user who responded a few points, but still re-lists your question to get more answers from different people.</p>';
}

echo '</td>
	  </tr>
	  <tr>
	  <td class="registerForm">';
if(isset($_GET['answer'])){	// if the user wants to answer a question, display that question and a field to type a response
	$table = "QUESTIONS";
	$qry = "SELECT * FROM $table WHERE QST_QUESTID = '" . mysql_real_escape_string($_GET['answer']) . "'";
	$resultQtoA = mysql_query($qry);
	$questionToAnswer = mysql_fetch_assoc($resultQtoA);
	echo '<p class="message">'. stripslashes($questionToAnswer['QST_QUEST']) .'</p>';
	echo '<form id="answerQuestion" name="answerQuestion" method="post" action="questions.php">
		<input type="hidden" name="questid" value="'. $questionToAnswer['QST_QUESTID'] .'">
		<label><span>answer question</span>
			<textarea name="answer" cols="40" rows="2" tabindex="1" maxlength="1023" class="inputText"></textarea>
		<input type="submit" name="submit" id="submit" value="Submit" tabindex="3" class="submitForm" /></label>
		</form>';
} else {	// if no question has been chosen to answer, prompt the user to ask a question
	echo '<form id="newQuestion" name="newQuestion" method="post" action="">
		<label><span>ask question</span>
			<textarea name="question" cols="40" rows="2" tabindex="1" maxlength="1023" class="inputText"></textarea>
		<input type="submit" name="submit" id="submit" value="Submit" tabindex="3" class="submitForm" /></label>
		</form>';
}
echo '</td>
	</tr>
	<tr>
	<td class="listing">';

if(mysql_num_rows($resultQuestions) > 0){
	while($question = mysql_fetch_assoc($resultQuestions)){
		if($question['ANS_ANSWERID'] == NULL){
			if(!$writtenMessage){
				echo '<p class="message">Click on a question to answer it</p>';
				$writtenMessage = true;
			}
			$questionAuthor = getName($question['ART_USERID']);
			echo '<div><a href=profile.php?userid='. $question['ART_USERID'] .'><b>'. stripslashes($questionAuthor[2]) .'</b></a> asks: <a href="questions.php?answer='. $question['QST_QUESTID'] .'">' . stripslashes($question['QST_QUEST']) .'</a></div>';
		}
	}
} else {
	echo '<p class="message">There are currently no unanswered questions... bet you never thought you\'d see the day.</p>';
}
writeTableEnd();
?>
</body>
</html>