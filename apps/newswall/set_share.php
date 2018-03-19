<?php
  if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  include("functions.php");

  $networks=array(
    array("facebook","http://www.facebook.com/sharer.php?u=!u!&amp;t=!t!"),
    array("twitter","http://twitter.com/home?status=!u!%20-%20!t!"),
    array("gplus","https://m.google.com/app/plus/x/?v=compose&amp;content=!t!%20!u!"),
    array("delicious","http://del.icio.us/post?v=4&amp;noui&amp;jump=close&amp;url=!u!&amp;title=!t!"),
    array("stumbleupon","http://www.stumbleupon.com/submit?url=!u!&amp;title=!t!"),
//    array("technorati","http://technorati.com/faves?add=!u!"),
    array("linkedin","http://www.linkedin.com/shareArticle?mini=true&url=!u!&title=!t!"),
    array("xing","https://www.xing.com/app/user?op=share;url=!u!;title=!t!;"),
    array("email","mailto:?subject=!t!&amp;body=!u!"),
  );

  $refdir = 'images/icons32/';
  $url=$_GET["varurl"];
  $title=$_GET["vartitle"];
  if($_GET["varencode"]=="true") {
    $url=rawurlencode($url);
    $title=rawurlencode($title);
  }

/* Show selection form */
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
          "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <title>newswall</title>
    <script type="text/javascript">var varreference="<?php print $_GET['varreference']; ?>";</script>
		<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="js/jquery.blockUI.js"></script>
    <script type="text/javascript" src="js/jquery.qtip.min.js"></script>

    <link rel="stylesheet" type="text/css" media="all" href="css/standard.css">
  </head>
  <body id="setshare">
<?php
  foreach($networks as $network) {
    $u=str_replace("!t!",$title,$network[1]);
    $u=str_replace("!u!",$url,$u);
    echo "<div class='refpic'><a href=\"".$u."\" target=\"_blank\"><img class=\"image\" src=\"".$refdir.$network[0].".png\" alt=\"".$network[0]."\" title=\"".$network[0]."\" onclick=\"parent.$.prettyPhoto.close();\" /></a></div>";
  }
?>
  </body>
</html>