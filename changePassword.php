<?php

include("connections.php");
include("formatting.php");
$dblink = dbConnect();

$userid = loginUser($HTTP_COOKIE_VARS["LensHood_ID"]);


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

echo '<p class="message">I\'m working on it...</p>';

writeTableEnd();
?>