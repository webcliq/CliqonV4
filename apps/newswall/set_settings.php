<?php
  if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  if(file_exists("varfeeds.php.ini")) include("varfeeds.php.ini");
  if(file_exists("varmails.php.ini")) include("varmails.php.ini");
  include("functions.php");
  foreach(array("feed","mail","mailhost","mailuser","mailpass","mailtype") as $onevar) if(isset($_POST["var".$onevar])) $_POST["var".$onevar]=preg_replace("/&amp;/","&",htmlspecialchars($_POST["var".$onevar],ENT_QUOTES));

/* --- Delete feed ---------------------------------------------------------- */
    if(!empty($_POST["varfeed"])
      && $_POST["mode"]=="delete") {
      $urlparts=parse_url($_POST["varfeed"]);
      $urlbase=$urlparts["scheme"]."://".$urlparts["host"]."/favicon.ico";
      $urlsave="images/favicons/".$urlparts["host"]."-favicon.ico";
      if(file_exists($urlsave)) unlink($urlsave);
      $reftext ="<"."?php
\$feeds=array(
";
        foreach($feeds as $onefeed) {
          if($onefeed!=$_POST["varfeed"]) {
            $reftext.='  "'.$onefeed.'",
';
          }
        }
        $reftext.=");
?".">";
      if(file_put_contents("varfeeds.php.ini", $reftext))
        echo "success"; else echo "error";
      exit;
    }
/* --- Add feed ------------------------------------------------------------- */
    if(!empty($_POST["varfeed"])
      && $_POST["varfeed"]!="http://"
      && !in_array($_POST["varfeed"],$feeds)
      && $_POST["mode"]=="add") {
      $feeds[]=$_POST["varfeed"];
      $reftext ="<"."?php
\$feeds=array(
";
        foreach($feeds as $onefeed) {
          $reftext.='  "'.$onefeed.'",
';
        }
        $reftext.=");
?".">";
      if(file_put_contents("varfeeds.php.ini", $reftext))
        echo "success"; else echo "error";
      exit;
    }
    
/* --- Delete mail account -------------------------------------------------- */
    if(!empty($_POST["varmail"])
      && $_POST["mode"]=="delete") {
      $reftext ="<"."?php
\$accounts=array(
";
        foreach($accounts as $oneaccount) {
          if($oneaccount!=$_POST["varmail"]) {
            $reftext.='  "'.$oneaccount.'",
';
          }
        }
        $reftext.=");
?".">";
      if(file_put_contents("varmails.php.ini", $reftext))
        echo "success"; else echo "error";
      exit;
    }
/* --- Add mail account ----------------------------------------------------- */
    if(!empty($_POST["varmailhost"])
      && !empty($_POST["varmailuser"])
      && !empty($_POST["varmailpass"])
      && !empty($_POST["varmailtype"])
      && $_POST["mode"]=="add") {
      $accounts[]=join(":",array($_POST["varmailhost"],$_POST["varmailuser"],$_POST["varmailpass"],$_POST["varmailtype"]));
      $reftext ="<"."?php
\$accounts=array(
";
        foreach($accounts as $oneaccount) {
          $reftext.='  "'.$oneaccount.'",
';
        }
        $reftext.=");
?".">";
      if(file_put_contents("varmails.php.ini", $reftext))
        echo "success"; else echo "error";
      exit;
    }
/* --- Save settings -------------------------------------------------------- */
    if($_POST["mode"]=="save") {
      $reftext ="<"."?php
\$vars=array(
";
        foreach($_POST as $key=>$value) {
          if(ereg("^var",$key))
            $reftext.='  "'.ereg_replace("^var","",$key).'" => "'.htmlspecialchars($value,ENT_QUOTES).'",
';
        }
        $reftext.=");
?".">";
      if(file_put_contents("variable.php.ini", $reftext))
        echo "success"; else echo "error";
      exit;
    }
 /* --- Panel login --------------------------------------------------------- */
    if($_POST["mode"]=="login") {
      if(htmlspecialchars($_POST["varpanelpass"],ENT_QUOTES)==$vars["panelpass"]) {
        $_SESSION["panelpass"]=$vars["panelpass"];
        echo "success";
      } else echo "error";
      exit;
    }
    
/* --- Show setting forms --------------------------------------------------- */
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
    <script type="text/javascript" src="js/jquery.set_settings.js"></script>

    <link rel="stylesheet" type="text/css" media="all" href="css/standard.css">
  </head>
  <body id="setfeeds">

<div class="feedline"><span class="feedssetting"><?php print t("Feeds"); ?></span> &nbsp; <span class="pop3setting"><?php print t("POP3 accounts"); ?></span> &nbsp; <span class="imapsetting"><?php print t("IMAP mails"); ?></span> &nbsp; <span class="generalsetting"><?php print t("General"); ?></span> &nbsp; <span onclick="parent.location.reload();"><?php print t("Refresh"); ?></span></div>

  <div id="calculate" class="tabin"><div class="feedurl">&nbsp;</div></div>

<?php /* --- Feeds --------------------------------------------------------*/ ?>
  <div id="feedssetting" class="tabin hide">
    <div class="newline"><div class='fav'><img src="images/icons/neu.png"></div><div class="action"><img src='images/ref_add.png' title='<?php print t("Add feed"); ?>' alt='' class='icon' onclick='addfeed();'></div><div class="feedurl"><form onsubmit="return false;"><input type="text" value="http://" id="newfeedurl"></form></div></div>

<?php
  asort($feeds);
  foreach($feeds as $onefeed) {

    $urlparts=parse_url($onefeed);
    $urlbase=$urlparts["scheme"]."://".$urlparts["host"]."/favicon.ico";
    $urlsave="images/favicons/".$urlparts["host"]."-favicon.ico";
    if(file_exists($urlsave))
      $favicon=$urlsave;
    else
      $favicon="images/icons/neu.png";


    echo "<div class=\"feedline\"><div class='fav'><img src=\"".$favicon."\"></div>";
    echo "<div class=\"action\"><img src=\"images/ref_delete.png\" title=\"".t('Remove feed')."\" alt=\"\" class=\"icon\" onclick=\"delfeed('".$onefeed."');\"></div>";
    echo "<div class=\"feedurl\" title=\"".$onefeed."\">".$onefeed."</div></div>";
  }
?>
  </div>
  
<?php /* --- POP3 accounts ------------------------------------------------*/ ?>
  <div id="pop3setting" class="tabin hide">
    <form onsubmit="return false;">
    <div class="newline"><div class='fav'><img src="images/icons/neu.png"></div><div class="action"><img src='images/ref_add.png' title='<?php print t("Add account"); ?>' alt='' class='icon' onclick='addmail("pop3");'></div><div class="mailbox"><input type="text" value="<?php print t("POP3 host"); ?>" id="newmailhostpop3"> | <input type="text" value="<?php print t("Username"); ?>" id="newmailuserpop3"> | <input type="password" value="<?php print t("Password"); ?>" id="newmailpasspop3"></div></div>
    </form>
<?php
  asort($accounts);
  foreach($accounts as $oneaccount) {

    $mailparts=explode(":",$oneaccount);
    if($mailparts[3]=="pop3") {
    $favicon="images/icons/email.png";

    echo "<div class=\"feedline\"><div class='fav'><img src=\"".$favicon."\"></div>";
    echo "<div class=\"action\"><img src=\"images/ref_delete.png\" title=\"".t('Remove account')."\" alt=\"\" class=\"icon\" onclick=\"delmail('".$oneaccount."','pop3');\"></div>";
    echo "<div class=\"mailbox\" title=\"".$mailparts[0]." | ".$mailparts[1]."\">".$mailparts[0]." | ".$mailparts[1]."</div></div>";
    }
  }
?>
  </div>

<?php /* --- IMAP accounts ------------------------------------------------*/ ?>
  <div id="imapsetting" class="tabin hide">
    <form onsubmit="return false;">
    <div class="newline"><div class='fav'><img src="images/icons/neu.png"></div><div class="action"><img src='images/ref_add.png' title='<?php print t("Add account"); ?>' alt='' class='icon' onclick='addmail("imap");'></div><div class="mailbox"><input type="text" value="<?php print t("IMAP host"); ?>" id="newmailhostimap"> | <input type="text" value="<?php print t("Username"); ?>" id="newmailuserimap"> | <input type="password" value="<?php print t("Password"); ?>" id="newmailpassimap"></div></div>
    </form>
<?php
  asort($accounts);
  foreach($accounts as $oneaccount) {

    $mailparts=explode(":",$oneaccount);
    if($mailparts[3]=="imap") {
    $favicon="images/icons/email.png";

    echo "<div class=\"feedline\"><div class='fav'><img src=\"".$favicon."\"></div>";
    echo "<div class=\"action\"><img src=\"images/ref_delete.png\" title=\"".t('Remove account')."\" alt=\"\" class=\"icon\" onclick=\"delmail('".$oneaccount."','imap');\"></div>";
    echo "<div class=\"mailbox\" title=\"".$mailparts[0]." | ".$mailparts[1]."\">".$mailparts[0]." | ".$mailparts[1]."</div></div>";
    }
  }
?>
  </div>

<?php /* --- Settings -----------------------------------------------------*/ ?>
  <div id="generalsetting" class="tabin hide">
    <form onsubmit="return false;">
    <div class="newline"><div class="feedurl"><?php print t("Language"); ?> <select id="varlanguage">
<?php
if ($handle = opendir('languages')) {
    while (false !== ($file = readdir($handle))) {
        $file=str_replace(".php.ini","",$file);
        if ($file != "." && $file != "..") {
          if($vars["language"]==$file) $selected="selected='selected'";
            else $selected="";
          echo "<option ".$selected.">".$file."</option>\n";
        }
    }
    closedir($handle);
}
?>
    </select></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Theme"); ?> <select id="vartheme">
<?php
if ($handle = opendir('css/themes')) {
    while (false !== ($file = readdir($handle))) {
        $file=str_replace(".css","",$file);
        if ($file != "." && $file != "..") {
          if($vars["theme"]==$file) $selected="selected='selected'";
            else $selected="";
          echo "<option ".$selected.">".$file."</option>\n";
        }
    }
    closedir($handle);
}
?>
    </select></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Mouse click (left|right)"); ?> <select id="varfeedsite">
<?php
  $clickvars=array();
  if($vars["feedsite"]=="left-right") $selected="selected='selected'";
    else $selected="";
  echo "<option value='left-right' ".$selected.">".t("Feed | Website")."</option>\n";
  if($vars["feedsite"]=="right-left") $selected="selected='selected'";
    else $selected="";
  echo "<option value='right-left' ".$selected.">".t("Website | Feed")."</option>\n";
?>
    </select></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Refresh messages after (minutes):"); ?> <input type="text" value="<?php print $vars['reload']; ?>" id="varreload" maxlength="3"></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Highlight messages younger than (hours):"); ?> <input type="text" value="<?php print $vars['highlight']; ?>" id="varhighlight" maxlength="2"></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Fade messages older than (hours):"); ?> <input type="text" value="<?php print $vars['fade']; ?>" id="varfade" maxlength="2"></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Hide messages older than (days):"); ?> <input type="text" value="<?php print $vars['range']; ?>" id="varrange" maxlength="3"></div></div>
        <div class="newline"><div class="feedurl"><?php print t("Maximum number of messages per source:"); ?> <input type="text" value="<?php print $vars['maxitems']; ?>" id="varmaxitems" maxlength="2"></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Login password")." (".t("newswall")."):"; ?> <input type="password" value="<?php print $vars['password']; ?>" id="varpassword"></div></div>
    <div class="newline"><div class="feedurl"><?php print t("Login password")." (".t("Settings")."):"; ?> <input type="password" value="<?php print $vars['panelpass']; ?>" id="varpanelpass"></div></div>
    <div class="feedline hand" onclick='saveset();'><?php print t("Save settings"); ?></div>
    </form>
  </div>
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
    <script type="text/javascript" src="js/jquery.set_settings.js"></script>

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