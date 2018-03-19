<?php
  $vars=array(); if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
          "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <title>newswall</title>
    <link rel="stylesheet" type="text/css" media="all" href="css/standard.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/themes/<?php print $vars["theme"]; ?>.css">
  </head>
  <body onload="document.form.loginpassword.select();">
    <div id="panel">
      <form action="index.php" method="post" name="form">
        <div id="panedi" class="screenbutton" onclick="document.form.submit();"></div>
        <input type="password" value="***" name="loginpassword">
      </form>
    </div>
    <div id="screen"></div>
  </body>
</html>