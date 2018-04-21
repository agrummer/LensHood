<?php
// MAINTENANCE FILE EXECUTES EVERY HOUR

include("../connections.php");
include("../pointValues.php");
$dblink = dbConnect();

updateRanks();

assignJudges();

closeContests($contestPoints);

clearBrokenWorks();

cleanMainStreet();

function updateRanks(){		// calculate ranks of users based on their current points and update their database records
	$table = "ARTISTS";
	$qry = "SELECT ART_USERID,ART_POINTS FROM $table";
	$resultArtists = mysql_query($qry);
	while($artist = mysql_fetch_assoc($resultArtists)){
		$qry = "UPDATE $table SET RNK_RANKID = ". getRankID($artist['ART_POINTS']) ." WHERE ART_USERID = ". $artist['ART_USERID'] ." AND RNK_RANKID < ". getRankID($artist['ART_POINTS']);
		$result = mysql_query($qry);
	}
}

function assignJudges(){	// randomly select the judges for the contest
	$qry = "SELECT CTS_CONTESTID FROM CONTESTS WHERE CTS_DEADLINE < NOW() AND CTS_ASSIGNED = 0";
	$resultContests = mysql_query($qry);
	
	while($contest = mysql_fetch_assoc($resultContests)){
		$contestid = $contest['CTS_CONTESTID'];
		
		$table = "ARTISTS";
		$qry = "SELECT ART_USERID FROM $table WHERE RNK_RANKID > 3";	// only take judges of a certain experience level (defined in pointValues.php)
		$resultUsers = mysql_query($qry);
		
		$table1 = "CONTEST-WORKS";
		$table2 = "WORKS";
		$qry = "SELECT ART_USERID FROM `$table1` JOIN `$table2` ON `$table1`.`WRK_WORKID` = `$table2`.`WRK_WORKID` WHERE `$table1`.CTS_CONTESTID = $contestid";
		$resultArtists = mysql_query($qry);
		
		if(mysql_num_rows($resultArtists) > 0){	// if the contest has entries, assign judges
			$i = 0;
			while($row = mysql_fetch_assoc($resultUsers)){
				$judges[$i] = $row['ART_USERID'];
				$i++;
			}
			$k = 0;
			while($row = mysql_fetch_assoc($resultArtists)){	// dismiss any judges who have submitted works in the contest
				$artists[$k] = $row['ART_USERID'];
				for($j=0;$j < count($judges);$j++){
					if($judges[$j] == $artists[$k]){
						unset($judges[$j]);
					}
				}
				$k++;
			}
			shuffle($judges);	// randomize the list of potential judges
			
			$table = "CONTEST-JUDGES";
			for($i=0;$i < count($judges) && $i < 10;$i++){	// take the first 10 randomly selected judges and assign them to the contest
				$qry = "INSERT INTO `$table` (`CTS_CONTESTID`,`ART_USERID`) VALUES ('$contestid','". $judges[$i] ."')";
				$result = mysql_query($qry);
			}
			
			$table = "CONTESTS";	// update contest record to show judges have been assigned
			$qry = "UPDATE $table SET CTS_ASSIGNED = 1 WHERE CTS_CONTESTID = $contestid";
			$result = mysql_query($qry);
			
		} else {	// if the contest has no entries, delete it
			$qry = "DELETE FROM CONTESTS WHERE CTS_CONTESTID = $contestid";
			$result = mysql_query($qry);
		}
		
	}
}

function closeContests($contestPoints){	// determine the winners of recently judged contests
	$qry = "SELECT CTS_CONTESTID,CTS_NAME,ART_USERID,CTS_FIRSTPTS,CTS_SECONDPTS,CTS_THIRDPTS FROM CONTESTS WHERE CTS_FINISHDATE < NOW() AND ISNULL(CTS_FIRSTWORKID)";
	$resultContests = mysql_query($qry);
	while($contest = mysql_fetch_assoc($resultContests)){
		$qry = "SELECT WRK_WORKID,`CTS-WRK_VOTES` FROM `CONTEST-WORKS` WHERE CTS_CONTESTID = ". $contest['CTS_CONTESTID'] ." ORDER BY -`CTS-WRK_VOTES`";
		$resultWorks = mysql_query($qry);
		if(mysql_num_rows($resultWorks) > 0){
			$notify = "your contest, ". $contest['CTS_NAME'] ." closed with ". mysql_num_rows($resultWorks) ." entries.";
			addPoints($contest['ART_USERID'],$contestPoints['creator'],$notify);	// give a few points to the user who created the contest for their idea if people submitted works
			// pay the judges for helping with the contest
			$qry = "SELECT ART_USERID FROM `CONTEST-JUDGES` WHERE CTS_CONTESTID = ". $contest['CTS_CONTESTID'] ." AND `CTS-JDG_FIRSTWORKID` IS NOT NULL";
			$resultJudges = mysql_query($qry);
			while($judge = mysql_fetch_assoc($resultJudges)){
				$notify = "you judged the ". $contest['CTS_NAME'] ." contest.";
				addPoints($judge['ART_USERID'],$contestPoints['judgePay'],$notify);
			}
			for($i=0;$i<3 && $i<mysql_num_rows($resultWorks);$i++){	// award artists of the top three works
				$work = mysql_fetch_assoc($resultWorks);
				$qry = "SELECT ART_USERID FROM WORKS WHERE WRK_WORKID = ". $work['WRK_WORKID'];
				$resultArtist = mysql_query($qry);
				$artist = mysql_fetch_assoc($resultArtist);
				switch($i){
					case 0: // notify first place winner and reward them with points
					$notify = 'you placed first in the <a href="contests/view.php?contestid='. $contest['CTS_CONTESTID'] .'">'. $contest['CTS_NAME'] .'</a> contest.';
					addPoints($artist['ART_USERID'],$contest['CTS_FIRSTPTS'],$notify);
					$qry = "UPDATE CONTESTS SET CTS_FIRSTWORKID = ". $work['WRK_WORKID'] ." WHERE CTS_CONTESTID = ". $contest['CTS_CONTESTID'];
					$result = mysql_query($qry);
					$days = 1;	// feature the winning work on main street for free
					$qry = "INSERT INTO MAINST (WRK_WORKID,MNS_DAYS) VALUES ('". $work['WRK_WORKID'] ."','". $days ."')";
					$result = mysql_query($qry);
					break;
					case 1: 
					$notify = 'you placed second in the <a href="contests/view.php?contestid='. $contest['CTS_CONTESTID'] .'">'. $contest['CTS_NAME'] .'</a> contest.';
					addPoints($artist['ART_USERID'],$contest['CTS_SECONDPTS'],$notify);
					$qry = "UPDATE CONTESTS SET CTS_SECONDWORKID = ". $work['WRK_WORKID'] ." WHERE CTS_CONTESTID = ". $contest['CTS_CONTESTID'];
					$result = mysql_query($qry);
					break;
					case 2: 
					$notify = 'you placed third in the <a href="contests/view.php?contestid='. $contest['CTS_CONTESTID'] .'">'. $contest['CTS_NAME'] .'</a> contest.';
					addPoints($artist['ART_USERID'],$contest['CTS_THIRDPTS'],$notify);
					$qry = "UPDATE CONTESTS SET CTS_THIRDWORKID = ". $work['WRK_WORKID'] ." WHERE CTS_CONTESTID = ". $contest['CTS_CONTESTID'];
					$result = mysql_query($qry);
					break;
					default:
				}
			}
		} else {	// close the empty contest, award points to no one
			$qry = "UPDATE CONTESTS SET CTS_FIRSTWORKID = 0 WHERE CTS_CONTESTID = ". $contest['CTS_CONTESTID'];
			$result = mysql_query($qry);
		}
		
	}
}

function clearBrokenWorks(){	// find any works that were not completely uploaded and destroy them
	$qry = "SELECT WRK_WORKID FROM WORKS WHERE ISNULL(WORKS.WRK_TITLE)";
	$resultWorks = mysql_query($qry);
	while($work = mysql_fetch_assoc($resultWorks)){
		$qry = "DELETE FROM `WORK-TAGS` WHERE WRK_WORKID = ". $work['WRK_WORKID'];
	}
	$qry = "DELETE IMAGES.*, WORKS.* FROM IMAGES JOIN WORKS ON IMAGES.WRK_WORKID = WORKS.WRK_WORKID WHERE ISNULL(WORKS.WRK_TITLE)";
	$result = mysql_query($qry);
}

function cleanMainStreet(){
	$qry = "DELETE FROM MAINST WHERE ADDDATE(MNS_STARTDATE,MNS_DAYS) < NOW()";
	$result = mysql_query($qry);
}

?>