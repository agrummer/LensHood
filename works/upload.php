<?php
include("../connections.php");
include("../formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);

if (isset($_GET['portid'])){
	$portid = $_GET['portid'];	// get the ID of the portfolio to send the image to if previous page specified it
}

if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) { 	// check to see if user has submitted a file yet
	
	define ("MAX_SIZE","5000"); 	// max file size in KB
	
	$workWidth = 600;
	$workHeight = 700;
	$thumbWidth = 140;
	$thumbHeight = 180;
	
	$errors=0;
	//checks if the form has been submitted
		
	$image=$_FILES['image']['name'];	// reads the name of the file the user submitted for uploading
	
	if ($image) 
	{
		$filename = stripslashes($_FILES['image']['name']);		// get the original name of the file from the clients machine
		$extension = getExtension($filename);				// get the extension of the file in a lower case format
		$extension = strtolower($extension);
		if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png")){
				echo '<h1>Unknown extension!</h1>';
				$errors=1;
		}
		else{
			$imageSize=filesize($_FILES['image']['tmp_name']);	// get the size of the image in bytes
			
			if ($imageSize > MAX_SIZE*1024){
				die(writeError('You have exceeded the size limit!'));
				$errors=1;
			}
			
			if(!$errors){
				$imageName=time().'.'.$extension;		// unique name for file using the time in unix time code format
				$imageURL="temp/"."temp".$imageName;		// full file path and name of where file is to be stored
				$copied = copy($_FILES['image']['tmp_name'], $imageURL);
				if (!$copied){
					$errors=1;
				}
			}
		}	
	}
	
	$workURL="images/"."work".$imageName;
	$thumbURL="thumbs/"."thumb".$imageName;
	makeThumb($imageURL,$workURL,$workWidth,$workHeight);
	unlink($imageURL);
	unset($_FILES);
	makeThumb($workURL,$thumbURL,$thumbWidth,$thumbHeight);
	
	if(!$errors){
		$table = "WORKS";
		$qry = "INSERT INTO $table (`ART_USERID` , `WRK_VIEWS`)
							VALUES ('$userid','0')";
		$result = mysql_query($qry) or die(writeError("There was an error working with the database."));
		$workid = mysql_insert_id();
		
		// store image URL in database
		$table = "IMAGES";
		$qry = "INSERT INTO $table (`WRK_WORKID` , `IMG_SIZE` , `IMG_IMAGE` , `IMG_THUMB`) VALUES ('$workid','$imageSize','$workURL','$thumbURL')";
		$results = mysql_query($qry) or die(writeError("There was an error working with the database."));
		
		unset($table);
		unset($qry);
		unset($result);
		unset($results);
		
		$imageUploaded = true;
	} else {
		echo die(writeError("There was an error uploading your image. Make sure it is a JPEG or PNG file under 5 MB."));
	}

}

function getExtension($str) {		// reads the extension of the file
	$i = strrpos($str,".");
	if (!$i) { return ""; }
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return $ext;
}

function makeThumb($imageName,$filename,$new_w,$new_h){	// create the thumbnail image from the uploaded image
   
	$ext=getExtension($imageName);
	//creates the new image using the appropriate function from gd library
	if(!strcmp("jpg",$ext) || !strcmp("jpeg",$ext)){
		$src_img=imagecreatefromjpeg($imageName) or die(writeError("Sorry! There is heavy traffic on the server right now. Either wait a few minutes or resize your image to less than 1 MB."));
	} elseif(!strcmp("png",$ext)){
		$src_img=imagecreatefrompng($imageName) or die(writeError("Sorry! There is heavy traffic on the server right now. Either wait a few minutes or resize your image to less than 1 MB."));
	}
	
	$old_x=imageSX($src_img);
	$old_y=imageSY($src_img);
	
	$ratio1=$old_x/$new_w;
	$ratio2=$old_y/$new_h;
	if($ratio1>$ratio2)	{
		$thumb_w=$new_w;
		$thumb_h=$old_y/$ratio1;
	}
	else	{
		$thumb_h=$new_h;
		$thumb_w=$old_x/$ratio2;
	}
	
	// create new image with the new dimmensions
	$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
	
	// resize the big image to the new created one
	imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
	
	// save the thumbnail to location $filename
	if(!strcmp("png",$ext)){
		imagepng($dst_img,$filename);
	}
	else{
		imagejpeg($dst_img,$filename);
	}
	
	//destroys source and destination images. 
	imagedestroy($dst_img); 
	imagedestroy($src_img);
	unset($dst_img);
	unset($src_img);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Upload Works</title>
<link href="../lenshood.css" rel="stylesheet" type="text/css" />
</head>

<body class="page">
<p>
  <?php
writeTableBegin($userid, 1);
if ($imageUploaded) { 	// check to see if user has submitted a file yet
	echo '<img src="'.$workURL.'">';
	
	echo '<p class="title">Upload your work</p>
		  <p class="subtitle">Describe your piece.</p>';
	echo '</td></tr><tr><td class="registerForm">';
	echo '<form id="newwork" name="newwork" method="post" action="work.php?workid=' . $workid . '">
          <label><span>title</span>
            <input name="title" type="text" id="title" accesskey="t" tabindex="1" size="40" maxlength="255" class="textInput" /></label>';
           if($portid){
			   $table = "PORTFOLIOS";
               $qry = "SELECT PRT_NAME FROM $table WHERE ART_USERID = $userid AND PRT_PORTID = $portid";
               $result = mysql_query($qry) or die(writeError("You can only upload works to your own portfolio."));
			   if(mysql_num_rows($result) > 0){
                   $port = mysql_fetch_assoc($result);
				   echo '<input name="portfolioid" id="portfolioid" value="'. $portid .'" type="hidden">';
				   echo '<p class="message"> '. stripslashes($port['PRT_NAME']) .' portfolio</p>';
				   //$qry = "UPDATE WORKS SET PRT_PORTID = $portid WHERE WRK_WORKID = $workid";
				   //$result = mysql_query($qry) or die(writeError("You can only upload works to your own portfolio."));
               } else {
				   die(writeError("You can only upload works to your own portfolio."));
			   }
			   dbClose($dblink);
		   } else {
			   echo '
		   <label><span>portfolio</span>
             <select name="portfolio" id="portfolio" accesskey="p" tabindex="2" class="textInput">
               ';
			   // populate the drop-down list with available artist types
               $table = "PORTFOLIOS";
               $qry = "SELECT PRT_NAME FROM $table WHERE ART_USERID = $userid ORDER BY PRT_NAME";
               $result = mysql_query($qry) or die(writeError("There was an error reading from the database, please try again later."));
			   while($ports = mysql_fetch_array($result)){
                   echo "<option>" . stripslashes($ports['PRT_NAME']) . "</option>";
               }
			   dbClose($dblink);
			   echo '
             </select>
           </label>';
		   }
		   echo '
           <label><span>location</span>
          	<input name="location" id="location" type="text" size="40" tabindex="3" maxlength="255" class="textInput"></textarea>
          </label>
			<label><span>tags</span>
          	<input name="tags" id="tags" type="text" size="40" tabindex="4" maxlength="255" class="textInput"></textarea> [separate by commas]
          </label>
           <label>
             <input type="submit" name="submit" id="submit" value="Submit" tabindex="11" class="submitForm" />
           </label>
      </form>';
	
} else {
	echo '<p class="title">Upload your work</p>
		  <p class="subtitle">Maximum 5 MB JPEG or PNG</p>
		  <p class="help">People have been reporting problems with an "out of memory" error. If this happens, wait a few seconds and try again. It will help if you make the image smaller before uploading it.</p>
		  </td></tr><tr><td class="registerForm">
		  <form enctype="multipart/form-data" action="" method="post" name="upload">
			<input name="MAX_FILE_SIZE" value="50240000" type="hidden" />
			<label><span>image file</span>
			<input name="image" size="70" type="file" class="textInput" />
			</label>
			<input name="submit" value="Upload" type="submit" class="submitForm">
		  </form>
		  </p>';
}
writeTableEnd();
?>

</p>
 
</body>
</html>