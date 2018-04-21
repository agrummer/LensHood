<?php
// add the user to the membership system.

session_start();

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$problems = array();	// array to add problems onto

// validate the username and password
$username = validateUsername($problems,$_POST['username']);
$password = validatePassword($problems,$_POST['password'],$_POST['password2'],$_POST['username'], $_POST['lname'], $_POST['fname']);

$typeid = getTypeID($_POST['type']);
$startingPoints = 0;
$rankid = 1; // new user starting rank ID value
$birthday = getDateFormat($problems,$_POST['birthday']);
$gender = getGenderID($problems,$_POST['gender']);
$email = validateEmail($problems,$_POST['email']);

if(count($problems) == 0){	// only proceed if there were no validation problems
	// insert the data
	$table = "ARTISTS";
	$insert = mysql_query("INSERT INTO $table (`ART_USERID` ,
											   `TYP_TYPEID` ,
											   `ART_POINTS` ,
											   `RNK_RANKID` ,
											   `ART_BIRTH` ,
											   `ART_ISMALE` ,
											   `ART_EMAIL`
											   )
									  VALUES ('NULL',
											  '$typeid',
											  '$startingPoints',
											  '$rankid',
											  '$birthday',
											  '$gender',
											  '$email'
											  )")
	or die(writeError("Unable to process due to an error with the database. Please try again."));
	$userid = mysql_insert_id($dblink);
	$table = "USERS";
	$insert = mysql_query("INSERT INTO $table VALUES ('$userid', '" . $username . "', '" . $password . "')")
	or die(writeError("There was an error creating your account. Please double-check your information and try again"));
	$table = "NAMES";
	$insert = mysql_query("INSERT INTO $table VALUES ('$userid', '"  . mysql_real_escape_string($_POST['lname']) . "', '" . mysql_real_escape_string($_POST['fname']) . "')")
	or die(writeError("There was an error creating your account. Please double-check your information and try again"));

	$_SESSION['registered'] = $userid;	// save the userid to a session variable to access on the next page
}

dbClose($dblink);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Registration</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
</head>

<body class="page">

    <?php
writeTableBegin($userid,0);
if(count($problems) == 0){	// only proceed if there were no validation problems
	echo '<p class="title">Welcome to the hood, ' . $_POST['fname'] . '</p>';
	echo '<p class="subtitle">Getting Started</p>';
	echo '<p class="paragraph">Starting with <b>zero</b> points, you can officially call yourself a starving artist. You will need to interact with other artists on this site to earn points and increase your ranking. The easiest way to do this is to start browsing the works of others and leaving helpful comments on their work. If they decide your comment is constructive, you will receive points. There are many ways to earn points but you will have to discover most of them on your own.</p><br/>';
	echo '<p class="paragraph">You\'ll want to start by getting the word on <a href="main.php">the street</a> about what\'s going on today. Normally when you sign on, this will be the first page you see.</p><br/>';
}else{	// report the validation problems to the user
	echo '<p class="title">Not so fast...</p>';
	echo '<p class="subtitle">Problems with your information</p>';
	echo '<ul class="problemList">';
	foreach($problems as $problem){
		echo "<li>";
		switch($problem){
			case 1: echo "Username field was left blank. You must choose a username to identify yourself.";
			break;
			case 2: echo "The username $username is already taken.";
			break;
			case 3: echo "Your password needs to be a little more original than that. It cannot simply be a part of your name.";
			break;
			case 4: echo "You made a typeo when verrifying your password. The second time you type your password must match the first exactly.";
			break;
			case 5: echo "When entering your birthday, enter the date in 'mm/dd/yyyy' format";
			break;
			case 6: echo "You claim to have been born in an unreasonable year. Please enter a year between 1900-" . date("Y");
			break;
			case 7: echo "Please enter your e-mail address in case you lose your password. Don't worry, I'm not going to sell it to my uncle Vinny.";
			break;
			case 8: echo "The e-mail address you entered does not appear to be valid. If this is in fact your e-mail address, please contact support.";
			break;
			case 9: echo "Please select your gender... or at least the closest one you identify with.";
			break;
			case 10: echo "Your password must be at least six characters. Step your game up.";
			break;
			case 11: echo "I can't stop you from using a fake name, but I can certainly stop you for giving no name.";
			break;
			case 12: echo "Please wash your mouth out with soap and try a different username.";
			break;
			default: echo "There was an error with the information you provided. Please verify you have entered everything correctly.";
		}
		echo "</li>";
	}
	echo '</ul>';
}
writeTableEnd();
?>

</body>
</html>
<?php
// translation and input validation functions
function validateUsername(&$problems,$input){ // make sure the username is not already taken
	$output = mysql_real_escape_string($input);
	if((strlen($output) < 1) || ($output == "none")){
		array_push($problems,1);
		return $output;
	}
	$illegal = array("fuck","fag");
	foreach($illegal as $word){
		if (eregi($word,$output)){
			array_push($problems,12);
			return $output;
		}
	}
	$table = "USERS";
	$qry = "SELECT ART_USERID FROM $table WHERE USR_USERNAME = '". $output ."';"; 
	$result = mysql_query($qry) or die (writeError("There was an error working with the database."));
	$num_rows = mysql_num_rows($result); 
	if ($num_rows != 0) { 
		array_push($problems,2);
		return $output;
	} else {
		return $output;	
	}
}

function validatePassword(&$problems,$input,$verify,$username,$lname,$fname){ // make sure the password is legal and at least moderately secure
	if(strlen($input) < 6){array_push($problems,10);}
	if((strlen($lname) < 1) || (strlen($fname) < 1)){array_push($problems,11);}
	$illegal = array("fuck","fag");
	foreach($illegal as $word){
		if (eregi($word,$lname) || eregi($word,$fname)){
			array_push($problems,12);
			return $output;
		}
	}
	if($input == $username || $input == $lname || $input == $fname){
		array_push($problems,3);
	}
	if($input != $verify){
		array_push($problems,4);
	}
	$output = mysql_real_escape_string($input);
	return md5($output);
}


function getDateFormat(&$problems,$input){ // validate the date entered by user and format it correctly for db
	$input = mysql_real_escape_string($input);
	
	// check the length of the entered date value 
	if((strlen($input)<10)OR(strlen($input)>10)){
		array_push($problems,5);
	}
	else{
	  // check for proper date format 
	  if((substr_count($input,"/"))!=2){
		array_push($problems,5);
	  }
	  else{
		$pos=strpos($input,"/");
		$month=substr($input,0,($pos));
		$result=ereg("^[0-9]+$",$month,$regs);
		$date=substr($input,($pos+1),($pos));
	  	if(!($result)){array_push($problems,5);}
		else{
		  if(($month<=0)OR($month>12)){array_push($problems,5);}
		  else{
			  $result=ereg("^[0-9]+$",$date,$regs);
			  if(!($result)){array_push($problems,5);}
		  }
		  if(($date<=0)OR($date>31)){array_push($problems,5);}
		}
		
		$year=substr($input,($pos+4),strlen($input));
		$result=ereg("^[0-9]+$",$year,$regs);
		if(!($result)){array_push($problems,5);}
		else{
		  if(($year<1900)OR($year>date("Y"))){array_push($problems,6);}
		}
	  }
	}
	$output = $year . "-" . $month . "-" . $date;
	return $output;
}
function getGenderID(&$problems,$input){ // translate male/female to 1/0 for db
	if($input == "male" || $input == "female"){
		return $input == "male";
	} else {
		array_push($problems,9);
		return 0;
	}
	
}
function validateEmail(&$problems,$input){ // validate e-mail address entered by user
	if(strlen($input) < 5){array_push($problems,7);}
	if(!(eregi("^[a-z0-9._-]+@+[a-z0-9-]+\.[a-z.]{2,5}$",$input))){
		array_push($problems,8);
	}
	$output = mysql_real_escape_string($input);
	return $output;
}


?>