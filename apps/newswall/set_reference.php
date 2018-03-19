<?php
  if(file_exists("varreferences.php.ini")) include("varreferences.php.ini");
  if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  include("functions.php");
  $refdir = 'images/references/';
  $_POST["varreference"]=htmlspecialchars($_POST["varreference"],ENT_QUOTES);
  $_GET["varreference"]=htmlspecialchars($_GET["varreference"],ENT_QUOTES);

/* --- Unlink image (all links) --------------------------------------------- */
  if(!empty($_POST["varreference"])
    && $_POST["mode"]=="unlink") {
    $reftext ="<"."?php
\$references=array(
";
      foreach($references as $key=>$value) {
        if($_POST["varreference"]!=$value) {
          $reftext.='"'.$key.'" => "'.$value.'",
';
        }
      }
      $reftext.=");
?".">";
    if(file_put_contents("varreferences.php.ini", $reftext)) {
        echo "success";
    } else {
      echo "error";
    }
    exit;
  }

/* --- Unlink image (one link) ---------------------------------------------- */
  if(!empty($_POST["varreference"])
    && $_POST["mode"]=="droplink") {
    $reftext ="<"."?php
\$references=array(
";
      foreach($references as $key=>$value) {
        if($_POST["varreference"]!=$key) {
          $reftext.='"'.$key.'" => "'.$value.'",
';
        }
      }
      $reftext.=");
?".">";
    if(file_put_contents("varreferences.php.ini", $reftext)) {
        echo "success";
    } else {
      echo "error";
    }
    exit;
  }
    
/* --- Delete image --------------------------------------------------------- */
  if(!empty($_POST["varreference"])
    && !preg_match("/^\//",$_POST["varreference"])
    && !preg_match("/^\\/",$_POST["varreference"])
    && !preg_match("/^\./",$_POST["varreference"])
    && $_POST["mode"]=="delete") {
    if(unlink($refdir.$_POST["varreference"])) {
      echo "success";
    } else {
      echo "error";
    }
    exit;
  }

/* --- Link text to an existing image --------------------------------------- */
  if(!empty($_POST["varreference"])
    && !empty($_POST["name"])) {
    $references[$_POST["varreference"]]=basename($_POST['name']);
    $reftext ="<"."?php
\$references=array(
";
      foreach($references as $key=>$value) {
        $reftext.='"'.$key.'" => "'.$value.'",
';
      }
      $reftext.=");
?".">";
    if(file_put_contents("varreferences.php.ini", $reftext)) {
      echo "success";
    } else {
      echo "error";
    }
    exit;
  }

/* --- Link text to a new upload file --------------------------------------- */
  if(is_array($_FILES["userfile"])) {
    if ((($_FILES["userfile"]["type"] == "image/gif")
      || ($_FILES["userfile"]["type"] == "image/png")
      || ($_FILES["userfile"]["type"] == "image/jpeg")
      || ($_FILES["userfile"]["type"] == "image/pjpeg"))
      && ($_FILES["userfile"]["size"] < 100000)) {
      $uploadfile = $refdir . basename($_FILES['userfile']['name']);
      if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
        if($_POST["varreference"]) {
          $references[$_POST["varreference"]]=basename($_FILES['userfile']['name']);
          $reftext ="<"."?php
  \$references=  array(
  ";
          foreach($references as $key=>$value) {
            $reftext.='    "'.$key.'" => "'.$value.'",
  ';
          }
          $reftext.="  );
  ?".">";
          file_put_contents("varreferences.php.ini", $reftext);
        }
        echo "success";
      } else {
        echo "error";
      }
    } else {
      echo "error";
    }
    exit;
  }

/* --- Show image selection form -------------------------------------------- */
    if($_SESSION["panelpass"]==$vars["panelpass"] || empty($vars["panelpass"])):
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
    <script type="text/javascript" src="js/jquery.ajaxupload.js"></script>
    <script type="text/javascript" src="js/jquery.set_reference.js"></script>

    <link rel="stylesheet" type="text/css" media="all" href="css/standard.css">
  </head>
  <body id="setreferences">
<?php
  $dir = opendir ("images/references");
    while (false !== ($file = readdir($dir))) {
      if (strpos($file, '.gif',1)||strpos($file, '.jpg',1) ) {
        echo "<div class='refpic'><img class=\"image\" src=\"".$refdir.$file."\" alt=\"".$file."\" title=\"".$file."\" onclick=\"setref('".$_GET["varreference"]."',this.src);\" />";
        if(in_array($file,$references))
          echo"<img src='images/ref_unlink.png' title='".t('Unlink')."' alt='".t('Unlink')."' class='icon' onclick='unlinkref(\"".$file."\")' />";
        else
 echo"<img src='images/ref_delete.png' title='".t('Delete image')."' alt='".t('Delete image')."' class='icon' onclick='delref(\"".$file."\")' />";
        echo "</div>";
      }
    }
  closedir($dir);
  echo "<div class='refpic' style='background:#999'><img src='images/addref.png' alt='".t('Upload image')."' title='".t('Upload image')."' id='upload_button' /></div>";
?>
  </body>
</html>
<?php
/* --- Show Panel login form ------------------------------------------------ */
  else:
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
    <script type="text/javascript" src="js/jquery.set_reference.js"></script>

    <link rel="stylesheet" type="text/css" media="all" href="css/standard.css">
  </head>
  <body id="setfeeds" class="login">
    <div class="feedline"><span class="loginsetting"><?php print t("Login password"); ?></span></div>
  <div id="loginsetting" class="tabin">
    <div class="newline"><div class='fav'><img src="images/icons/login.png"></div><div class="action"><img src='images/ref_next.png' alt='' class='icon' onclick='login();'></div><div class="feedurl"><form onsubmit="return false;"><input type="password" value="" id="panelpass"></form></div></div>
    </div>
  </body>
</html>
<?php endif; ?>