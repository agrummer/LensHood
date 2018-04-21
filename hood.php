<?php
session_start();

// page for searching and browsing for works and other users
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

if(isset($_GET['showAll'])){	// show all of certtain content
	$displayCategory = $_GET['showAll'];
	switch($displayCategory){
		case 'artists': $pageArtists = true;
		break;
		case 'works': $pageWorks = true;
		break;
		default: $pageAll = true;
	}
} else {
	$pageAll = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Hood</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#navHood a:link,
#navHood a:visited {
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
#navHood a:hover {
	color:#000;
	background:#FF8204;
}
</style>
</head>

<body class="page">
<?php
writeTableBegin($userid,0);

echo '<p class="title">The hood'. writeSearchBox() .'</p>';

if($pageAll){
	// check to see if user is new, and if so, provide help
	$table = "ARTISTS";
	$qry = "SELECT RNK_RANKID FROM $table WHERE ART_USERID = $userid";
	$result = mysql_query($qry);
	$row = mysql_fetch_assoc($result);
	if($row['RNK_RANKID'] < 3 || isset($_GET['help'])){
		echo '<p class="help"><b>See what\'s going on around you in the hood.</b><br/>Anyone new to the hood will show up here. You will also see several of the most recently active artists in the hood, and the most recently uploaded works to check out. If you\'re looking for something in particular, use the search box to find artists and works that interest you.</p>';
	}
	
	$writtenEntries = array();	// array to keep track of which users have already been displayed on the page
	
	// NAV OPTIONS
	echo '<p id="smallLinks"><a href="?showAll=artists">BROWSE ALL ARTISTS</a> <a href="?showAll=works">BROWSE ALL WORKS</a></p>';
	
	// NEW ARTISTS TODAY
	$table = "ARTISTS";
	$qry = "SELECT ART_USERID,WRK_WORKID FROM $table WHERE ART_CREATED > SUBDATE(NOW(),1) ORDER BY -ART_CREATED LIMIT 4";
	$resultNewUsers = mysql_query($qry) or die(writeError("There was a problem working with the database."));
	if(mysql_num_rows($resultNewUsers) > 0){
		echo '<p class="subtitle">New Artists in the Hood</p>';
		echo '<table>';
		$place=0;	// marker to keep track of which cell of the table the loop is in
		echo '<tr>';
		while($userEntry = mysql_fetch_assoc($resultNewUsers)){
			$userEntry['name'] = getName($userEntry['ART_USERID']);
			echo '<td width="140px" id="thumb" align="center">';
			echo '<a href="profile.php?userid='. $userEntry['ART_USERID'] .'"><img border="0" src="works/'. getThumbURL($userEntry['WRK_WORKID']) .'"></a>';
			echo '<div id="thumbTitle" align="left">'. stripslashes($userEntry['name'][2]) .' '. stripslashes($userEntry['name'][1]) .'</div>';
			echo '</td>';
			$writtenEntries[$userEntry['ART_USERID']] = true;
			if($place++ == 3){
				echo '</tr><tr>';
			}
			elseif($place >= 8){
				break;
			}
		}
		echo '</tr></table>';
		echo '<p id="smallLinks"><a href="?showAll=artists&sort=least+time+in+the+hood">view all new artists</a></p>';
	}
	
	// 8 MOST RECENTLY LOGGED IN USERS
	echo '<p class="subtitle">Recently Active Artists</p>';
	$table = "LOGINLOG";
	$qry = "SELECT ART_USERID FROM $table ORDER BY -LGN_TIME LIMIT 60";
	$resultLogins = mysql_query($qry) or die(writeError("There was a problem working with the database."));
	echo '<table>';
	$place=0;	// marker to keep track of which cell of the table the loop is in
	echo '<tr>';
	while($loginEntry = mysql_fetch_assoc($resultLogins)){
		if($writtenEntries[$loginEntry['ART_USERID']] != true){
			$loginEntry['name'] = getName($loginEntry['ART_USERID']);
			$table = "ARTISTS";
			$qry = "SELECT WRK_WORKID FROM $table WHERE ART_USERID=". $loginEntry['ART_USERID'];
			$resultTemp = mysql_query($qry);
			$arrayTemp = mysql_fetch_assoc($resultTemp);
			$loginEntry['WRK_WORKID'] = $arrayTemp['WRK_WORKID'];
			echo '<td width="140px" id="thumb" align="center">';
			echo '<a href="profile.php?userid='. $loginEntry['ART_USERID'] .'"><img border="0" src="works/';
			echo getThumbURL($loginEntry['WRK_WORKID']);
			echo '"></a>';
			echo '<div id="thumbTitle" align="left">'. stripslashes($loginEntry['name'][2]) .' '. stripslashes($loginEntry['name'][1]) .'</div>';
			echo '</td>';
			$writtenEntries[$loginEntry['ART_USERID']] = true;
			if($place++ == 3){
				echo '</tr><tr>';
			}
			elseif($place >= 8){
				break;
			}
		}
	}
	echo '</tr></table>';
	echo '<p id="smallLinks"><a href="?showAll=artists">view all active artists</a></p>';
	
	// 8 MOST RECENTLY UPLOADED WORKS
	echo '<p class="subtitle">Recently Uploaded Works</p>';
	$table = "WORKS";
	$qry = "SELECT WRK_WORKID,WRK_TITLE FROM $table WHERE WRK_TITLE IS NOT NULL ORDER BY -WRK_CREATED LIMIT 20";
	$resultWorks = mysql_query($qry) or die(writeError("There was a problem working with the database."));
	echo '<table><tr>';
	$place=0;	// marker to keep track of which cell of the table the loop is in
	while($workEntry = mysql_fetch_assoc($resultWorks)){
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="works/work.php?workid='. $workEntry['WRK_WORKID'] .'"><img border="0" src="works/'. getThumbURL($workEntry['WRK_WORKID']) .'"></a>';
		echo '<div id="thumbTitle" align="left">'. stripslashes($workEntry['WRK_TITLE']) .'</div>';
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
		}
		elseif($place >= 8){
			break;
		}
	}
	echo '</tr></table>';
	echo '<p id="smallLinks"><a href="?showAll=works&sort=most+recent">view all recently uploaded works</a></p>';

} elseif ($pageArtists){	// DISPLAY ALL ARTISTS
	echo '<table><tr>';
	echo '<td><p class="subtitle">Artists in the hood</p></td>';
	echo '<td class="registerForm">
		<form id="sortForm" name="sortForm" method="get" action="">';
	echo '<input name="showAll" id="showAll" value="artists" type="hidden">
			<input name="page" id="page" value="1" type="hidden">
		<label><span>view artists by </span>
    	<select name="sort" id="sort" accesskey="v" tabindex="2" class="textInput">';
	echo '<option>points</option>';
	echo '<option>artist type</option>';
	echo '<option>least time in the hood</option>';
	echo '<option>most time in the hood</option>';
	echo '<option>youngest</option>';
	echo '<option>oldest</option>';
	echo '<option>random</option>';
	echo '</select>';
	echo '<input type="submit" name="submit" id="submit" value="sort" tabindex="3" class="submitForm" /></label>';
	echo '</form></td></tr></table>';
	$qry = "SELECT ART_USERID,TYP_TYPEID,ART_POINTS,RNK_RANKID,ART_BIRTH,ART_CREATED,ART_ISMALE FROM ARTISTS ORDER BY ";
	switch($_GET['sort']){
		case 'artist type': $qry .= 'TYP_TYPEID';
		break;
		case 'least time in the hood': $qry .= '-ART_CREATED';
		break;
		case 'most time in the hood': $qry .= 'ART_CREATED';
		break;
		case 'oldest': $qry .= 'ART_BIRTH';
		break;
		case 'youngest': $qry .= '-ART_BIRTH';
		break;
		case 'gender': $qry .= 'ART_ISMALE';
		break;
		case 'random': $qry .= 'RAND()';
		break;
		case 'points': 
		default: $qry .= '-ART_POINTS';
	}
	if(isset($_GET['page'])){
		$qry .= ' LIMIT '. (mysql_real_escape_string($_GET['page']) - 1) * 16 .', 16';
	} else {
		$qry .= ' LIMIT 0 , 16';
	}
	$resultArtists = mysql_query($qry) or die (writeError("Sorry, there was an error communicating with the database. Please try again."));
	echo '<table><tr>';
	$place=0;	// marker to keep track of which cell of the table the loop is in
	while($artist = mysql_fetch_assoc($resultArtists)){
		$artist['name'] = getName($artist['ART_USERID']);
		$table = "ARTISTS";
		$qry = "SELECT WRK_WORKID FROM $table WHERE ART_USERID=". $artist['ART_USERID'];
		$resultTemp = mysql_query($qry);
		$arrayTemp = mysql_fetch_assoc($resultTemp);
		$artist['WRK_WORKID'] = $arrayTemp['WRK_WORKID'];
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="profile.php?userid='. $artist['ART_USERID'] .'"><img border="0" src="works/';
		echo getThumbURL($artist['WRK_WORKID']);
		echo '"></a>';
		echo '<div id="thumbTitle" align="left">'. stripslashes($artist['name'][2]) .' '. stripslashes($artist['name'][1]) .'</div>';
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
			$place = 0;
		}
	}
	echo '</tr></table>';
	echo '<p id="smallLinks">';
	if($_GET['page'] > 1){
		echo '<a href="?showAll='. $_GET['showAll'] .'&sort='. $_GET['sort'] .'&page='. ($_GET['page'] - 1) .'">previous page</a>';
	}
	if(mysql_num_rows($resultArtists) >= 16){
		echo ' ';
		echo '<a href="?showAll='. $_GET['showAll'] .'&sort='. $_GET['sort'] .'&page=';
		if (isset($_GET['page'])){
			echo ($_GET['page'] + 1);
		} else {
			echo '2';
		}
		echo '">next page</a>';
	}
	echo '</p>';
} elseif ($pageWorks){	// DISPLAY ALL WORKS
	echo '<table><tr>';
	echo '<td><p class="subtitle">Works of Art</p></td>';
	echo '<td class="registerForm">
		<form id="sortForm" name="sortForm" method="get" action="">';
	echo '<input name="showAll" id="showAll" value="works" type="hidden">
			<input name="page" id="page" value="1" type="hidden">
		<label><span>view artists by </span>
    	<select name="sort" id="sort" accesskey="v" tabindex="2" class="textInput">';
	echo '<option>most recent</option>';
	echo '<option>oldest</option>';
	echo '<option>most views</option>';
	echo '<option>least views</option>';
	echo '<option>artist</option>';
	echo '<option>random</option>';
	echo '</select>';
	echo '<input type="submit" name="submit" id="submit" value="sort" tabindex="3" class="submitForm" /></label>';
	echo '</form></td></tr></table>';
	$qry = "SELECT WRK_WORKID,ART_USERID,WRK_CREATED,WRK_VIEWS,WRK_TITLE FROM WORKS ORDER BY ";
	switch($_GET['sort']){
		case 'most views': $qry .= '-WRK_VIEWS';
		break;
		case 'least views': $qry .= 'WRK_VIEWS';
		break;
		case 'artist': $qry .= 'ART_USERID';
		break;
		case 'oldest': $qry .= 'WRK_CREATED';
		break;
		case 'random': $qry .= 'RAND()';
		break;
		case 'most recent': 
		default: $qry .= '-WRK_CREATED';
	}
	if(isset($_GET['page'])){
		$qry .= ' LIMIT '. (mysql_real_escape_string($_GET['page']) - 1) * 16 .', 16';
	} else {
		$qry .= ' LIMIT 0 , 16';
	}
	$resultWorks = mysql_query($qry) or die (writeError("Sorry, there was an error communicating with the database. Please try again."));
	echo '<table><tr>';
	$place=0;	// marker to keep track of which cell of the table the loop is in
	while($workEntry = mysql_fetch_assoc($resultWorks)){
		echo '<td width="140px" id="thumb" align="center">';
		echo '<a href="works/work.php?workid='. $workEntry['WRK_WORKID'] .'"><img border="0" src="works/'. getThumbURL($workEntry['WRK_WORKID']) .'"></a>';
		echo '<div id="thumbTitle" align="left">'. stripslashes($workEntry['WRK_TITLE']) .'</div>';
		echo '</td>';
		if($place++ == 3){
			echo '</tr><tr>';
			$place = 0;
		}
	}
	echo '</tr></table>';
	echo '<p id="smallLinks">';
	if($_GET['page'] > 1){
		echo '<a href="?showAll='. $_GET['showAll'] .'&sort='. $_GET['sort'] .'&page='. ($_GET['page'] - 1) .'">previous page</a>';
	}
	if(mysql_num_rows($resultWorks) >= 16){
		echo ' ';
		echo '<a href="?showAll='. $_GET['showAll'] .'&sort='. $_GET['sort'] .'&page=';
		if (isset($_GET['page'])){
			echo ($_GET['page'] + 1);
		} else {
			echo '2';
		}
		echo '">next page</a>';
	}
	echo '</p>';
}

writeTableEnd();
?>
</body>
</html>