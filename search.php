<?php

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

if (strlen($_GET['search']) > 0) {
	$search = mysql_real_escape_string($_GET['search']);
} elseif (strlen($_GET['tag']) > 0) {
	$search = mysql_real_escape_string($_GET['tag']);
} else {
	die(writeError("Searching for nothing is only acceptable in Philosophy and Religion."));
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Search</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
</head>

<body class="page">
<?php
writeTableBegin($userid,0);

$results = 0;	// count total search results
$writtenWorks = array();

// check for search results within list of artist usernames and names
$qry = "SELECT ARTISTS.ART_USERID, ARTISTS.WRK_WORKID FROM ARTISTS JOIN NAMES ON ARTISTS.ART_USERID = NAMES.ART_USERID WHERE MATCH (NMS_FIRST) AGAINST ('". $search ."') OR MATCH (NMS_LAST) AGAINST ('". $search ."') ORDER BY NAMES.NMS_LAST";
$resultArtists = mysql_query($qry);
$results += mysql_num_rows($resultArtists);

// check for search results within work titles and locations
$table = "WORKS";
$qry = "SELECT WRK_WORKID FROM $table WHERE MATCH (WRK_TITLE) AGAINST ('". $search ."') OR MATCH (WRK_LOCATION) AGAINST ('". $search ."') ORDER BY -WRK_WORKID";
$resultWorks = mysql_query($qry);
$results += mysql_num_rows($resultWorks);

// check for search results within list of existing tags
$table1 = "TAGS";
$table2 = "WORK-TAGS";
$qry = "SELECT `$table2`.WRK_WORKID FROM `$table1` JOIN `$table2` ON `$table1`.TAG_TAGID = `$table2`.TAG_TAGID WHERE MATCH (`$table1`.TAG_WORD) AGAINST ('". $search ."') ORDER BY -`$table2`.WRK_WORKID";
$resultTags = mysql_query($qry) or die(writeError("I wasn't expecting that."));
$results += mysql_num_rows($resultTags);

echo '<p class="title">'. stripslashes($search) . ' <span class="counter">'. $results .' results</span>' . writeSearchBox() .'</p>';

// display artists
if(mysql_num_rows($resultArtists) > 0){
	echo '<p class="message">Artists</p>';
	echo '<table><tr>';
	$place=0;	// marker to keep track of which cell of the table the loop is in
	while($artist = mysql_fetch_assoc($resultArtists)){
		$artist['name'] = getName($artist['ART_USERID']);
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="profile.php?userid='. $artist['ART_USERID'] .'"><img border="0" src="works/';
		echo getThumbURL($artist['WRK_WORKID']);
		echo '"></a>';
		echo '<div id="thumbTitle" align="left">'. stripslashes($artist['name'][2]) .' '. stripslashes($artist['name'][1]) .'</div>';
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
		}
		elseif($place >= 8){
			break;
		}
	}
	echo '</tr></table>';
}

if(mysql_num_rows($resultWorks) > 0 || mysql_num_rows($resultTags) > 0){
	echo '<p class="message">Works</p>';
}
// display works
if(mysql_num_rows($resultWorks) > 0){
	$place=0;	// marker to keep track of which cell of the table the loop is in
	echo '<table><tr>';
	while($work = mysql_fetch_assoc($resultWorks)){
		$writtenWorks[$work['WRK_WORKID']] = true;
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="works/work.php?workid='. $work['WRK_WORKID'] .'"><img border="0" src="works/'. getThumbURL($work['WRK_WORKID']) .'"></a>';
		//echo '<div id="thumbTitle" align="left">'. stripslashes($work['WRK_TITLE']) .'</div>';
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
			$place = 0;
		}
	}
	echo '</tr></table>';
}
if(mysql_num_rows($resultTags) > 0){
	$place=0;	// marker to keep track of which cell of the table the loop is in
	echo '<table><tr>';
	while($work = mysql_fetch_assoc($resultTags)){
		if($writtenWorks[$work['WRK_WORKID']] != true){
			echo '<td width="140px" id="thumb" align="center">';
			echo '<a href="works/work.php?workid='. $work['WRK_WORKID'] .'"><img border="0" src="works/'. getThumbURL($work['WRK_WORKID']) .'"></a>';
			//echo '<div id="thumbTitle" align="left">'. stripslashes($work['WRK_TITLE']) .'</div>';
			echo '</td>';
			if($place++ == 3){
				echo '</tr><tr>';
				$place = 0;
			}
		}
	}
	echo '</tr></table>';
}

// if nothing was found
if($results == 0){
	echo '<p class="message">Sorry kid, we\'re all out of '. $search .' here.</p>';
}

writeTableEnd();
?>
</body>
</html>