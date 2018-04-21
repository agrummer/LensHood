<?php
include("../connections.php");
include("../pointValues.php");
include("../formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);


if(isset($_GET['workid'])){
	$workid = $_GET['workid'];	// get the ID of the image to be displayed from the URL
} else {
	die(writeError("You must specify a work to view."));
}

if (isset($_POST['title'])) {	// save any newly created works to the database
	$newWork['title'] = mysql_real_escape_string($_POST['title']);
	if($_POST['portfolioid'] > 0){
		$newWork['portfolioid'] = $_POST['portfolioid'];
	}
	else{
		//echo 'portfolio = '. $_POST['portfolio'];
		$newWork['portfolioid'] = getPortfolioID($userid,$_POST['portfolio']);
		//echo 'portfolioid = '. $newWork['portfolioid'];
	}
	$newWork['location'] = mysql_real_escape_string($_POST['location']);
	$newWork['tags'] = mysql_real_escape_string($_POST['tags']);
	
	$table = "WORKS";
	$qry = "UPDATE $table SET PRT_PORTID='" . $newWork['portfolioid'] . "' , WRK_TITLE='". $newWork['title'] . "' , WRK_LOCATION='". $newWork['location'] . "' WHERE WRK_WORKID='" . $workid . "'";
	$result = mysql_query($qry) or die(writeError("You waited too long, so the robots came and destroyed your work. Please re-upload it."));
	
	$tagList = explode(',',$newWork['tags']);	// break the list of tags separated by commas into an array
	foreach($tagList as $tag){
		$qry = "SELECT TAG_TAGID FROM TAGS WHERE TAG_WORD = '". $tag ."'";
		$result = mysql_query($qry) or die(writeError("There was a problem working with the database"));
		if(mysql_num_rows($result) > 0){
			$row = mysql_fetch_assoc($result);
			$tagid = $row['TAG_TAGID'];
		} else {
			$qry = "INSERT INTO TAGS (TAG_WORD) VALUES ('$tag')";
			$result = mysql_query($qry) or die(writeError("There was a problem working with the database"));
			$tagid = mysql_insert_id();
		}
		
		$qry = "INSERT INTO `WORK-TAGS` (WRK_WORKID,TAG_TAGID) VALUES ('$workid','$tagid')";
		$result = mysql_query($qry) or die(writeError("There was a problem working with the database"));
	}
	
	$qry = "SELECT WRK_WORKID FROM ARTISTS WHERE ART_USERID = $userid AND ISNULL(WRK_WORKID)";
	$result = mysql_query($qry);
	if($artistWithoutWork = mysql_fetch_assoc($result)){
		$qry = "UPDATE ARTISTS SET WRK_WORKID = $workid WHERE ART_USERID = $userid";
		$result = mysql_query($qry);
	}
	
	// reward the user with points for uploading the image
	addPoints($userid,$uploadPoints,'you created a new work.');
}

if(isset($_GET['action'])){	// if user has specified an action
	$action = $_GET['action'];
	if($action == 'delete'){	// if user wants to delete their work
		$qry = "SELECT ART_USERID FROM WORKS WHERE WRK_WORKID = $workid";
		$resultWork = mysql_query($qry);
		$work = mysql_fetch_assoc($resultWork);
		if($userid == $work['ART_USERID']){
			$qry = "DELETE FROM `CONTEST-WORKS` WHERE WRK_WORKID = $workid";	// delete any contest entries associated with this work
			$result = mysql_query($qry) or die (writeError("There was an error trying to delete this work. Make sure it exists and try again.<br/>(try poking it with a stick, for example)"));
			$qry = "DELETE FROM `WORK-TAGS` WHERE WRK_WORKID = $workid";	// delete all work-tag relationships associated with this work
			$result = mysql_query($qry) or die (writeError("There was an error trying to delete this work. Make sure it exists and try again.<br/>(try poking it with a stick, for example)"));
			$qry = "DELETE FROM `MAINST` WHERE WRK_WORKID = $workid";	// delete all work-tag relationships associated with this work
			$result = mysql_query($qry) or die (writeError("There was an error trying to delete this work. Make sure it exists and try again.<br/>(try poking it with a stick, for example)"));
			$qry = "DELETE IMAGES.*, WORKS.* FROM IMAGES JOIN WORKS ON IMAGES.WRK_WORKID = WORKS.WRK_WORKID WHERE WORKS.WRK_WORKID = $workid";	// delete the target work
			$result = mysql_query($qry) or die (writeError("There was an error trying to delete this work. Make sure it exists and try again.<br/>(try poking it with a stick, for example)"));
			
			header ("Location: ../main.php");
		} else {
			die(writeError("It's not very nice to try to ruin other people's work."));
		}
	}
}

$table = "IMAGES";	// load the URL of the images for the work from the database
$qry = "SELECT * FROM $table WHERE WRK_WORKID = $workid";
$result = mysql_query($qry) or die(writeError("This work does not exist."));
$row = mysql_fetch_assoc($result);
$imageURL = $row['IMG_IMAGE'];
$thumbURL = $row['IMG_THUMB'];

$table = "WORKS";	// load information about the work from the database
$qry = "UPDATE $table SET WRK_VIEWS=WRK_VIEWS+1 WHERE WRK_WORKID=$workid";
$result = mysql_query($qry) or die(writeError("This work does not exist."));
$qry = "SELECT * FROM $table WHERE WRK_WORKID = $workid";
$result = mysql_query($qry) or die(writeError("This work does not exist."));
$row = mysql_fetch_assoc($result);
$work['portfolioid'] = $row['PRT_PORTID'];
$work['artistid'] = $row['ART_USERID'];
$work['artist'] = getName($row['ART_USERID']);
$work['portfolio'] = stripslashes(getPortfolioName($work['artistid'],$work['portfolioid']));
$work['created'] = $row['WRK_CREATED'];
$work['views'] = $row['WRK_VIEWS'];
$work['title'] = stripslashes($row['WRK_TITLE']);
$work['location'] = stripslashes($row['WRK_LOCATION']);
$work['involved'] = stripslashes($row['WRK_INVOLVED']);

if (isset($_POST['comment'])) {	// save any newly created comments to the database
	$newComment = mysql_real_escape_string($_POST['comment']);
	$table = "COMMENTS";
	$qry = "INSERT INTO $table (`ART_USERID`,`WRK_WORKID`,`CMT_CONTENT`) VALUES ('$userid','$workid','$newComment')";
	$result = mysql_query($qry);
	addPoints($work['artistid'],$commentPoints['received'],NULL);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Work</title>
<link href="../lenshood.css" rel="stylesheet" type="text/css" />
</head>

<body class="page">
<?php
writeTableBegin($userid,1);
// display the large image and information about the work
echo '<center><img src="' . $imageURL . '"></center>';
echo '</td></tr>';
echo '<tr><td class="workInfo">';
echo '<p class="title">'. $work['title'] .'</p>
		<p class="subtitle">by <a href="../profile.php?userid='. $work['artistid'] .'">'. $work['artist'][2] .' '. $work['artist'][1] .'</a></p>';
echo '<p><span>portfolio</span><a href="../portfolio.php?portid='. $work['portfolioid'] .'">'. $work['portfolio'] .'</a></p>';
echo '<p><span>location</span>'. $work['location'] .'</p>';
echo '<p><span>views</span>'. $work['views'] .'</p>';

// display any tags associated with the work
$table1 = "TAGS";
$table2 = "WORK-TAGS";
$qry = "SELECT TAG_WORD FROM `$table1` JOIN `$table2` ON `$table1`.TAG_TAGID = `$table2`.TAG_TAGID WHERE WRK_WORKID = $workid";
$resultTags = mysql_query($qry) or die("There was an error when trying to read from the database. Please try again.");
if(mysql_num_rows($resultTags) > 0){
	echo '<p><span>tags</span>';
	while($tag = mysql_fetch_assoc($resultTags)){
		echo '<a href="../search.php?tag='. stripslashes($tag['TAG_WORD']) .'" class="tag">' . stripslashes($tag['TAG_WORD']) . '</a> ';
	}
	echo '</p>';
}
if($userid == $work['artistid']){
	echo '<div id="smallLinks"><a href="../profile.php?userid='. $userid .'&workid='. $workid .'">Set as profile image</a>';
	echo '<a href="../feature.php?workid='. $workid .'">Feature on Main St</a>';
	echo '<a href="work.php?workid='. $workid .'&action=delete">Delete this Work</a>';
	echo '</div>';
}
?>
</td>
    </tr>
    <tr>
    <td class="registerForm">
    <p class="subtitle">Comments</p>
    <form id="newcomment" name="newcomment" method="post" action="">
           <label><span>post a comment</span>
           <textarea name="comment" cols="65" rows="2" maxlength="1023" class="textInput"></textarea>
             <input type="submit" name="submit" id="submit" value="Submit" class="submitForm" />
           </label>
      </form>
    </td>
    </tr>
    <?php
	// display all comments for the selected work
	$table = "COMMENTS";
	$qry = "SELECT * FROM $table WHERE WRK_WORKID = $workid";
	$result = mysql_query($qry);
	while($comment = mysql_fetch_assoc($result)){
		
		$commentName = getName($comment['ART_USERID']);
		
		echo '<tr><td class="comment">';
		echo '<a href="../profile.php?userid='. $comment['ART_USERID'] .'" class="commentName">'. stripslashes($commentName[2]) . ' ' . stripslashes($commentName[1]) .'</a>';
		echo '<p>'. stripslashes($comment['CMT_CONTENT']) .'</p>';
		echo '</tr></td>';
	}
	echo '<tr><td>';
	writeTableEnd();
	?>
</body>
</html>