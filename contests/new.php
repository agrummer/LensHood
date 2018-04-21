<?php
include("../connections.php");
include("../formatting.php");
include("../pointValues.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Contests</title>
<link href="../lenshood.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#navContests a:link,
#navContests a:visited {
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
#navContests a:hover {
	color:#000;
	background:#FF8204;
}
</style>
</head>

<body class="page">
<?php
writeTableBegin($userid,1);
?>
<p class="title">Create a contest</p>
<p class="subtitle">Challenge everyone. Challenge yourself.</p>
	</td>
    </tr>
    <tr>
      <td class="registerForm">
      
      <form id="newcontest" name="newcontest" method="post" action="view.php">
          <label><span>contest name</span>
            <input name="name" type="text" size="40" id="name" accesskey="u" tabindex="1" size="100%" maxlength="255" class="textInput" />
          </label>
           <label><span>type of artwork</span>
             <select name="type" id="type" accesskey="t" tabindex="2" class="textInput">
               <option>Anything</option>
			   <?php
               // populate the drop-down list with available artist types
               $table = "TYPES";
               $qry = "SELECT * FROM $table";
               $result = mysql_query($qry) or die(writeError("There was an error reading from the database. It might be too busy. Please try again in a moment."));
               while($types = mysql_fetch_array($result)){
                   echo "<option>" . $types['TYP_NAME'] . "</option>";
               }
               dbClose($dblink);
               ?>
             </select>
           </label>
           <label><span>description & rules</span>
          	<textarea name="description" cols="60" rows="4" tabindex="7" maxlength="1023" class="textInput"></textarea>
          </label>
           <label><span>deadline</span>
             <select name="deadline" id="deadline" accesskey="d" tabindex="8" class="textInput">
               <?php
               // populate the drop-down list with available deadline options
			   
               $day = date("d");
			   $month = date("m");
			   $year = date("Y");
			   $today = mktime(0,0,0,$month,$day,$year);	// today's date for reference
			   
			   $options[0] = mktime(19,0,0,$month,$day+1,$year);	// one day contest
			   $options[1] = mktime(19,0,0,$month,$day+3,$year);	// three day contest
			   $options[2] = mktime(19,0,0,$month,$day+5,$year);	// five day contest
			   $options[3] = mktime(19,0,0,$month,$day+7,$year);	// one week contest
			   $options[4] = mktime(19,0,0,$month,$day+14,$year);	// two week contest
			   $options[5] = mktime(19,0,0,$month+1,$day,$year);	// one month contest
			   
			   foreach($options as $option){
                   echo "<option>" . date("l m-d-Y",$option) . "</option>";
               }
               ?>
             </select> for submissions
           </label>
           <label>
             <input type="submit" name="submit" id="submit" value="Submit" tabindex="11" class="submitForm" />
           </label>
      </form>
      </td>
    </tr>
  </table>

</body>
</html>