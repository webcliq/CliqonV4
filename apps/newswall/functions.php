<?php
  error_reporting(0);
  session_start();
  if(!empty($_POST["loginpassword"]) AND htmlspecialchars($_POST["loginpassword"],ENT_QUOTES)==$vars["password"]) {
    $_SESSION["newswall"]=md5($vars["password"]);
    header("location:index.php");
    exit;
  }
  if($vars["password"]!="" AND $_SESSION["newswall"]!=md5($vars["password"])) {
    header("location:login.php");
    exit;
  }
  if($vars["language"]=="") $vars["language"]="english";
  if(file_exists("languages/".$vars["language"].".php.ini"))
    include("languages/".$vars["language"].".php.ini");
  else if(file_exists("../languages/".$vars["language"].".php.ini"))
    include("../languages/".$vars["language"].".php.ini");

  function getname($from) {
    $name1=trim(ereg_replace("[a-zA-Z0-9äöüÄÖÜ_.-]+@[a-zA-Z0-9äöüÄÖÜ.-]+.[a-zA-Z]+","",$from));
    $name2=trim(ereg_replace("<>","",$name1));
    if($name2=="") $name=$name1;
    else {
      $name=ereg_replace("^\(","",$name2);
      $name=ereg_replace("\)$","",$name);
      $name=ereg_replace("^\"","",$name);
      $name=ereg_replace("\"$","",$name);
    }
    if(trim($name)=="") $name=$from;
    return trim($name);
  }
  function ximap_utf8($string) {
    $array = imap_mime_header_decode($string);
    $str = "";
    foreach ($array as $key => $part) {
      $str .= $part->text;
    }
    if(!preg_match('%(?:
          [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
          |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
          |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
          |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
          |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
          |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
          |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
          )+%xs',
      $str))
      $str=utf8_encode($str);
    return $str;
  }
  function t($text) {
    global $t;
    if($t[$text]!="")
      return $t[$text];
    else
      return $text;
  }
?>