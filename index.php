<?php
include("connections.php");
$dblink = dbConnect();

$username = $_GET['login'];
$status = $_GET['status'];
		
if($username == "none"){	// check to see if the user just logged out
	// expire cookie
	setcookie ("loggedin", "", time() - 3600);
} else {
	if (isset($_COOKIE['loggedin'])){	// check user's cookies to see if the user is logged in already
		if (isset($HTTP_COOKIE_VARS["LensHood_ID"])){
			$userid = $HTTP_COOKIE_VARS["LensHood_ID"];
			$URL = "main.php";
			header ("Location: $URL");
		}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood by Alex Grummer</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
</head>

<body class="login">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" style="background-image:url(splash/<?php
// randomize which splash photo is displayed
$choice = rand(1,5);
switch($choice){
	case 1: echo 'one';
	break;
	case 2: echo 'two';
	break;
	case 3: echo 'three';
	break;
	case 4: echo 'four';
	break;
	default: echo 'one';
}
?>.jpg); background-repeat:no-repeat;">
  <tr>
    <td height="135px">&nbsp;</td>
  </tr>
  <tr>
    <td width="110px">
    </td>
    <td valign="top" align="left" class="loginForm">
    <form id="login" name="login" method="post" action="login.php">
		<?php		
		if($status == "fail"){
			echo '<p class="message"><b>Incorrect login credentials.</b></p>';
		}
		elseif($username == "none"){
			echo '<p class="message"><b>You\'re out of the hood (you have logged out)</b></p>';
		}
		echo '<p class="welcome">Please <b>identify</b> or <a href="newUser.php" class="welcome">create</a> yourself</p>';
		if(strlen($username) > 1 && $username != "none"){
			echo '<label><span>username</span>
					  <input name="username" type="text" id="username" size="32" maxlength="255" tabindex="1" value=' . $username . ' class="textInput" />
					</label>';
		}
		else{
			echo '<label><span>username</span>
					  <input name="username" type="text" id="username" size="32" maxlength="255" tabindex="1" class="textInput" />
					</label>';
		}
		?>
        <label><span>password</span>
          <input name="password" type="password" id="password" size="23" maxlength="255" tabindex="2" class="textInput"/>
          <input type="submit" name="submit" id="submit" value="Submit" tabindex="3" class="submit"/>
        </label>
        <br />
    </form>
    </td>
  </tr>
  <tr>
  <td height="500px">
  </td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>