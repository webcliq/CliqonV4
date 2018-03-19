<?php
  $vars=array(); if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  include("functions.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <title>newswall</title>
		<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="js/jquery.blockUI.js"></script>
    <script type="text/javascript" src="js/jquery.nifty.js"></script>
		<script type="text/javascript" src="js/jquery.prettyPhoto.js"></script>
    <script type="text/javascript" src="js/jquery.qtip.min.js"></script>
    <script type="text/javascript" src="js/jquery.load.js.php"></script>

	<link rel="stylesheet" type="text/css" media="all" href="css/prettyPhoto.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/standard.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/themes/<?php print $vars["theme"]; ?>.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/nifty.css">
  </head>
  <body>
    <div id="detail"></div>
    <div id="panel"></div>
    <div id="screen"><noscript><p class="js"><?php print t("There's no newswall without javascript - please activate..."); ?></p></noscript></div>
  </body>
</html>