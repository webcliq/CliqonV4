<?php
  $vars=array(); if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  $references=array(); if(file_exists("varreferences.php.ini")) include("varreferences.php.ini");
  $feeds=array(); if(file_exists("varfeeds.php.ini")) include("varfeeds.php.ini");
  include("functions.php");

  define('MAGPIE_INPUT_ENCODING', 'UTF-8');
  define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
  require_once('magpierss/rss_fetch.inc');

  $maxload =$nowload-($var["range"] * 60 * 60 * 24);
  $onefeed=$feeds[$_GET["source"]];
  $onemess=$_GET["id"];
  $doc = fetch_rss($onefeed);
  $feedinfo = array (
      'title' => $onefeed,
      'icon' => "rss",
  );
  if($doc->channel) {
    $feedinfo = array (
      'title' => $doc->channel['title'],
      'link'  => $doc->channel['link'],
      'desc'  => $doc->channel['description'],
      'sub'   => $doc->channel['subtitle'],
      'icon'  => "rss",
      'lbd'   => $doc->channel['lastbuilddate'],
      'upd'   => $doc->channel['updated'],
      'pud'   => $doc->channel['pubdate'],
      'pub'   => $doc->channel['published'],
    );
    if(!$feedinfo['desc']) $feedinfo['desc']=$feedinfo['sub'];
    if($feedinfo['title']=="")
      $feedinfo['title']=eregi_replace("^[a-z]+\://[www\.]*","",$feedinfo['link']);
    if($feedinfo['lbd']!="")
      $feedinfo["date"]=$feedinfo['lbd'];
    else if($feedinfo['upd']!="")
      $feedinfo["date"]=$feedinfo['upd'];
    else if($feedinfo['pud']!="")
      $feedinfo["date"]=$feedinfo['pud'];
    else if($feedinfo['pub']!="")
      $feedinfo["date"]=$feedinfo['pub'];
  }
  $feedinfo['image']= $doc->image['url'];

  $urlparts=parse_url($onefeed);
  $urlbase=$urlparts["scheme"]."://".$urlparts["host"]."/favicon.ico";
  $urlsave="images/favicons/".$urlparts["host"]."-favicon.ico";
  if(file_exists($urlsave))
    $favicon=$urlsave;
  else
    $favicon="";

  $count=0;
	foreach ($doc->items as $node) {
    if($count==$onemess) {
  		$itemRSS = array (
  			'title' => $node['title'],
  			'desc'  => $node['description'],
  			'link'  => $node['link'],
  			'date'  => $node['pubdate'],
  			'dcdate'=> $node['dc']['date'],
        'lbd'   => $node['lastbuilddate'],
  //    'upd'   => $node['updated'],
        'pud'   => $node['pubdate'],
        'pub'   => $node['published'],
  			'cont'  => $node['content'],
  			'atom'  => $node['atom_content'],
  			'enurl' => $node['enclosure_url'],
  			'author'=> getname($node['author']),
  			'fav'   => $favicon,
  		);
      if($itemRSS['date']=="")
        if($itemRSS['dcdate']!="")
          $itemRSS["date"]=$itemRSS['dcdate'];
        else if($itemRSS['lbd']!="")
          $itemRSS["date"]=$itemRSS['lbd'];
        else if($itemRSS['upd']!="")
          $itemRSS["date"]=$itemRSS['upd'];
        else if($itemRSS['pud']!="")
          $itemRSS["date"]=$itemRSS['pud'];
        else if($itemRSS['pub']!="")
          $itemRSS["date"]=$itemRSS['pub'];
        else if($feedinfo['date']!="")
          $itemRSS['date']=$feedinfo["date"];

      if($itemRSS['title']=="")
        $itemRSS['title']=$feedinfo['title'];
      if($itemRSS['title']=="")
        $itemRSS['title']=$feedinfo['author'];

      if($itemRSS['atom'])
        $itemRSS['desc']=$itemRSS['atom'];
      else if($itemRSS['cont'])
        $itemRSS['desc']=$itemRSS['cont'];

      $itemRSS['desc'] = preg_replace("/<script.*<\/script>/i", "", $itemRSS['desc']);

      if(eregi("twitter\.com",$onefeed)) {
        $parts=explode(": ",$itemRSS["title"]);
        if(count($parts)>1) {
          $itemRSS['title']=$parts[0];
          unset($parts[0]);
          $itemRSS['desc']=join(": ",$parts);
        }
        $feedinfo["icon"]="twitter";
      }
      if(eregi("flickr\.com",$onefeed)) {
        $feedinfo["icon"]="flickr";
      }
      if(eregi("facebook\.com",$onefeed)) {
        $feedinfo["icon"]="facebook";
        if(!empty($itemRSS['author']))
          $itemRSS['title']=$itemRSS['author'];
        else {
          if(preg_match("/>([^<]+)</",$itemRSS["desc"],$name))
            $itemRSS['title']=$name[1];
        }
      }
      if($itemRSS['author']!="")
        $itemRSS["imagetitle"]=$itemRSS['author'];
      else
        $itemRSS["imagetitle"]=$itemRSS['title'];
      $itemRSS['descnohtml']=strip_tags($itemRSS['desc']);

      if($itemRSS["image"]=="") {
        $desccut=preg_replace("![^a-z0-9]+!","",strtolower($itemRSS['title']));
        if(!empty($references[$desccut]) AND file_exists("images/references/".$references[$desccut])) {
          $itemRSS["image"]="images/references/".$references[$desccut];
          unset($itemRSS['title']);
        }
      }
      if($itemRSS["image"]=="") {
      if(eregi("^http.*\.(jpg|jpeg|gif|png)$",$itemRSS["link"]))
        $itemRSS["image"]=$itemRSS["link"];
      else if(eregi("^http.*\.(jpg|jpeg|gif|png)$",$itemRSS["enurl"]))
        $itemRSS["image"]=$itemRSS["enurl"];
      }
      
      if(eregi("smileys",$itemRSS["image"]) OR eregi("smilies",$itemRSS["image"])) $itemRSS["image"]="";
      if($itemRSS["image"]=="" AND $feedinfo["image"]) {
        $itemRSS["image"]=$feedinfo["image"];
        unset($itemRSS['title']);
      }

      if($itemRSS["date"]!="") $itemRSS["date"]=strtotime($itemRSS["date"]);
      if($itemRSS["date"]<$maxload) continue;

      echo '<div id="detailtext"><div class="msgtitle"><div class="text"><div class="data">';

      if($favicon!="")
        echo'<img src="'.$favicon.'" alt="" title="'.$feedinfo["title"].'" class="icon">';
      else
        echo'<img src="images/icons/'.$feeditem["icon"].'.png" alt="" title="'.$feedinfo["title"].'" class="icon">';

      echo '<a href="'.$feedinfo["link"].'">'.$feedinfo["title"].'</a>';
      if($feedinfo["desc"] AND $feedinfo["desc"]!=$feedinfo["title"]) echo "<br>".$feedinfo["desc"];

      $shareurl=$itemRSS["link"];
      if($feedinfo["icon"]=="facebook" OR $feedinfo["icon"]=="twitter")
        $itemRSS["link"]="";

      if(!empty($itemRSS["link"]))
        echo '</div><div class="title"><a href="'.$itemRSS["link"].'" target="_blank">'.$itemRSS["title"].'</a></div>';
      else
        echo '</div><div class="title">'.$itemRSS["title"].'</div>';

      if($itemRSS["image"]) echo'<img src="'.$itemRSS["image"].'" class="data">';
      echo '<div class="data">';
      if($itemRSS["author"]) echo t("Editor").': '.$itemRSS["author"].'<br>';
      if($itemRSS["date"]) echo t("Date").': '.date(t("Y/m/d H:i"),$itemRSS["date"]).t("h")."<br>";
      if($itemRSS["title"]) $titleadd=$itemRSS["title"]; else
      if($feedinfo["title"]) $titleadd=$feedinfo["title"]; else
      if($itemRSS["author"]) $titleadd=$itemRSS["author"]; else
      $titleadd="";
      if($titleadd!="") {
        $titleadd=str_replace("\n"," ",addslashes($titleadd));
        $titledoc=": ".$titleadd;
      } else $titledoc="";
      echo '</div></div></div>';
      echo '<div class="msgtext"><div class="text">'.$itemRSS["desc"].'</div></div>';
      echo '<br clear="all">';
      echo '<script type="text/javascript">document.title="newswall'.$titledoc.'";$("#panweb").data("url","'.$itemRSS["link"].'");$("#pansha").data("url","'.rawurlencode($shareurl).'");$("#pansha").data("title","'.rawurlencode($titleadd).'");</script>';
      echo '</div>';
      break;
    }
    $count++;
	}
?>