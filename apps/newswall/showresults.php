<?php
  error_reporting(0);
  session_start();
  $results=array();
  if(!empty($_POST["q"])) {
    $qparts=explode(" ",$_POST["q"]);
    foreach($_SESSION["bid"] as $key=>$value) {
      $found=false;
      foreach($qparts as $onepart) {
        if(!empty($onepart)) {
          if(eregi($onepart,$value)) $found=true;
          if($onepart[0]=="+" && strlen($onepart)>1 && eregi(substr($onepart,1),$value)) $found=true; else
          if($onepart[0]=="+" && strlen($onepart)>1 && !eregi(substr($onepart,1),$value)) {$found=false;break;}
          if($onepart[0]=="-" && strlen($onepart)>1 && eregi(substr($onepart,1),$value)) {$found=false;break;}
        }
      }
      if($found==true) $results[]="#".$key;
    }
    echo json_encode($results);
  }
?>