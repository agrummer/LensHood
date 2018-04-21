<?php
// functions related to the formatting of layouts and styles
function writeTableBegin($userid,$depth){	// returns the HTML for the start of a page layout with a navigation menu
	switch($depth){
		case 0: $depth = '';
		break;
		case 1: $depth = '../';
		break;
		case 2: $depth = '../../';
		break;
		default: $depth = '';
	}
	echo '<table class="page" width="600" align="center">
		<tr>
		<th id="nav">
		<img src="'. $depth .'images/header1.jpg" width="600" alt="LensHood" />
		<ul id="nav">
		<li id="navMain"><a href="'. $depth .'main.php">Street</a></li>
		<li id="navProfile"><a href="'. $depth .'profile.php?userid='. $userid .'">Profile</a></li>
		<li id="navHood"><a href="'. $depth .'hood.php">Hood</a></li>
		<li id="navContests"><a href="'. $depth .'contests.php">Contests</a></li>
		<li id="navQuestions"><a href="'. $depth .'questions.php">Questions</a></li>
		<li id="navHelp"><a href="';
		if(empty($_SERVER['QUERY_STRING'])){
			echo '?help=on';
		} else {
			echo '?'. $_SERVER['QUERY_STRING'];
			echo '&help=on';
		}
		echo '">Quick Help</a></li>
		<li id="navLogout"><a href="'. $depth .'index.php?login=none">Log off</a></li>
		</ul>
		</th>
		</tr>
		<tr>
		<td>';
}
function writeTableEnd(){	// returns the HTML for the end of a page layout
	echo '</td></tr></table><table align="center" width="600"><tr><td id="footer">';
	echo '&copy; Alex Grummer 2009';
	echo '</td></tr></table>';
  
}
function writeSearchBox(){
	echo '<div class="searchDiv">
		<form id="search" name="search" method="get" action="search.php">
		  <label>
			<input type="text" name="search" id="search" size="25" maxlength="255" class="searchBox" />
		  </label>
		  <label>
			<input type="submit" name="submit" id="submit" value="Search" class="submitSearch" />
		  </label>
		</form></div>';
}

?>