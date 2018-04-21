<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LensHood | Register</title>
<link href="lenshood.css" rel="stylesheet" type="text/css" />
</head>

<body class="page">
<table class="page" width="600" cellspacing="10" cellpadding="0" align="center">
    <tr>
      <td id="nav" ><img src="images/headerRegister.jpg" width="600" height="143" alt="LensHood" /></td>
    </tr>
    <tr>
      <td class="registerForm">
      <p class="title">Create yourself.</p>
      <form id="registration" name="registration" method="post" action="registerUser.php">
          <label><span>username</span>
            <input name="username" type="text" id="username" accesskey="u" tabindex="1" size="30" maxlength="255" class="textInput"/>[you will use this to login]
          </label>
        
          <label ><span >password</span>
            <input name="password" type="password" id="password" accesskey="p" tabindex="2" size="30" maxlength="255" class="textInput"/>[at least six characters long]
          </label>
        
          <label><span>verify password</span>
            <input name="password2" type="password" id="password2" tabindex="3" size="30" maxlength="255" class="textInput"/>
          </label>
          <label><span>first name</span>
            <input name="fname" type="text" id="fname" accesskey="u" tabindex="4" size="30" maxlength="255" class="textInput"/>
          </label>
          <label><span>last name</span>
            <input name="lname" type="text" id="lname" accesskey="u" tabindex="5" size="30" maxlength="255" class="textInput"/>
          </label>
          <label><span>birthday</span>
            <input name="birthday" type="text" id="birthday" accesskey="u" tabindex="6" size="30" maxlength="255" class="textInput"/>[mm/dd/yyyy]
          </label>
        
          <label><span>e-mail</span>
            <input name="email" type="text" id="email" accesskey="u" tabindex="7" size="60" maxlength="255" class="textInput"/>
          </label>
          <label><span>gender</span>
            <input type="radio" name="gender" value="male" id="gender_0" tabindex="8" />male
            <input type="radio" name="gender" value="female" id="gender_1" tabindex="9" />female</label>
           <label><span>artist type</span>
             <select name="type" id="type" accesskey="t" tabindex="10">
               <?php
               // populate the drop-down list with available artist types
               include("connections.php");
               $dblink = dbConnect();
               $table = "TYPES";
               $qry = "SELECT * FROM $table";
               $result = mysql_query($qry) or die(writeError("There was an error reading from the database, please try again later."));
               while($types = mysql_fetch_array($result)){
                   echo "<option>" . $types['TYP_NAME'] . "</option>";
               }
               dbClose($dblink);
               ?>
             </select>[your primary focus] 
             <input type="submit" name="submit" id="submit" value="Create" tabindex="11" class="submitForm" />
           </label>
      </form>
      
      </td>
    </tr>
  </table>
  <p>&nbsp;</p>

</body>
</html>