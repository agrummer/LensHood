<?php
// purchase a duration of time to be feature on the main page

include("connections.php");
include("formatting.php");
include("pointValues.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

if(isset($_GET['workid'])){	// feature work
	$table = "WORKS";
	$qry = "SELECT WRK_WORKID,ART_USERID FROM $table WHERE WRK_WORKID = ". mysql_real_escape_string($_GET['workid']);
	$result = mysql_query($qry);
	$work = mysql_fetch_assoc($result);
	if($userid != $work['ART_USERID']){
		die(writeError("You must choose a valid work, portfolio, question or contest of your own to feature."));
	} else {
		if(isset($_POST['time'])){
			$days = substr($_POST['time'],0,1);
			$qry = "INSERT INTO MAINST (WRK_WORKID,MNS_DAYS) VALUES ('". $work['WRK_WORKID'] ."','". $days ."')";
			$result = mysql_query($qry) or die(writeError("There was a probelm with the transaction. Please try again."));
			subPoints($userid,$featureCost[$days]);
			header ("Location: main.php");
		}
	}
} elseif(isset($_GET['portid'])){	// feature portfolio
	$table = "PORTFOLIOS";
	$qry = "SELECT PRT_PORTID,ART_USERID FROM $table WHERE PRT_PORTID = ". mysql_real_escape_string($_GET['portid']);
	$result = mysql_query($qry);
	$port = mysql_fetch_assoc($result);
	if($userid != $port['ART_USERID']){
		die(writeError("You must choose a valid work, portfolio, question or contest of your own to feature."));
	} else {
		if(isset($_POST['time'])){
			$days = substr($_POST['time'],0,1);
			$qry = "INSERT INTO MAINST (PRT_PORTID,MNS_DAYS) VALUES ('". $port['PRT_PORTID'] ."','". $days ."')";
			$result = mysql_query($qry) or die(writeError("There was a probelm with the transaction. Please try again."));
			subPoints($userid,$featureCost[$days]);
			header ("Location: main.php");
		}
	}
} elseif(isset($_GET['questid'])){	// feature question
	$table = "QUESTIONS";
	$qry = "SELECT QST_QUESTID,ART_USERID FROM $table WHERE QST_QUESTID = ". mysql_real_escape_string($_GET['questid']);
	$result = mysql_query($qry);
	$quest = mysql_fetch_assoc($result);
	if($userid != $quest['ART_USERID']){
		die(writeError("You must choose a valid work, portfolio, question or contest of your own to feature."));
	} else {
		if(isset($_POST['time'])){
			$days = substr($_POST['time'],0,1);
			$qry = "INSERT INTO MAINST (QST_QUESTID,MNS_DAYS) VALUES ('". $quest['QST_QUESTID'] ."','". $days ."')";
			$result = mysql_query($qry) or die(writeError("There was a probelm with the transaction. Please try again."));
			subPoints($userid,$featureCost[$days]);
			header ("Location: main.php");
		}
	}
} elseif(isset($_GET['contestid'])){	// feature contest
	$table = "CONTESTS";
	$qry = "SELECT CTS_CONTESTID,ART_USERID FROM $table WHERE CTS_CONTESTID = ". mysql_real_escape_string($_GET['contestid']);
	$result = mysql_query($qry);
	$contest = mysql_fetch_assoc($result);
	if($userid != $contest['ART_USERID']){
		die(writeError("You must choose a valid work, portfolio, question or contest of your own to feature."));
	} else {
		if(isset($_POST['time'])){
			$days = substr($_POST['time'],0,1);
			$qry = "INSERT INTO MAINST (CTS_CONTESTID,MNS_DAYS) VALUES ('". $contest['CTS_CONTESTID'] ."','". $days ."')";
			$result = mysql_query($qry) or die(writeError("There was a probelm with the transaction. Please try again."));
			subPoints($userid,$featureCost[$days]);
			header ("Location: main.php");
		}
	}
} else{
	die(writeError("You must choose a valid work, portfolio, question or contest of your own to feature."));
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

// check to see how many points the user has
$qry = "SELECT ART_POINTS FROM ARTISTS WHERE ART_USERID = $userid";
$resultPoints = mysql_query($qry);
$points = mysql_fetch_assoc($resultPoints);

if($points['ART_POINTS'] >= $featureCost[1]){	// can they afford the cheapest option?
	echo '<p class="title">Feature your stuff on Main St.</p>';
	echo '<p class="subtitle">Get noticed.</p>';
	echo '<p class="help"><b>Display your work, portfolio, question, or contest on Main St.</b><br/>For example, if you\'re looking for a lot of feedback on one of your works, you can purchase a day of time on Main St so everyone will see your work when they enter the hood. Of course, you\'re also more than welcome to buy time just to show off your skillz...</p>';
	echo '<p class="message">Select how many days you would like to feature this item on Main St. (costs are listed):</p>';
	echo '</td></tr><tr><td class="registerForm">';
	echo '<form id="feature" name="feature" method="post" action="">
		<label><span>duration of feature</span>
			<select name="time" id="time" accesskey="t" tabindex="2" class="textInput">';
			for($i=1;$i<4;$i++){
				if($points['ART_POINTS'] >= $featureCost[$i]){	// can they afford it?
					echo '<option>'. $i .' day = '. $featureCost[$i] .' pts</option>';
				}
			}
			echo '</select>
		<input type="submit" name="submit" id="submit" value="Submit" tabindex="3" class="submitForm" /></label>
	</form>';
} else {
	echo '<p class="help"><b>You do not have enough points to purchase featured time on Main St.</b><br/>You can earn points by giving constructive feedback on other peoples\' work, answering questions, and competing in contests.</p>';
}

dbClose($dblink);
writeTableEnd();
?>

</body>
</html>