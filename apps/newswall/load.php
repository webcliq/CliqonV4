<?php
  $vars=array(); if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  $references=array(); if(file_exists("varreferences.php.ini")) include("varreferences.php.ini");
  $feeds=array(); if(file_exists("varfeeds.php.ini")) include("varfeeds.php.ini");
  $accounts=array(); if(file_exists("varmails.php.ini")) include("varmails.php.ini");
  include("functions.php");

  if(phpversion()<5) {
    print "<div class='js'>".t("There's no newswall without PHP5 - please configure the server...")."</div>";
    exit;
  }
  
  $allfeeds=array();
  $allsorts=array();

  $nowload=time();
  if($vars["fade"]==0)
    $fadeload=0;
  else
    $fadeload=($vars["fade"] * 60 * 60);
  if($vars["highlight"]==0)
    $lastload=0;
  else
    $lastload=$nowload-($vars["highlight"] * 60 * 60);
  if($vars["range"]==0)
    $maxload=0;
  else
    $maxload=$nowload-($vars["range"] * 60 * 60 * 24);
  if($vars["feedsite"]=="right-left")
    {$onRclick="onclick";$onLclick="oncontextmenu";}
  else
    {$onLclick="onclick";$onRclick="oncontextmenu";}

// -- E-MAIL -------------------------------------------------------------------
  $count=0;
  foreach($accounts as $acc):
    $count--;
    $accdata=explode(":",$acc);
    $accdate="";
    if($accdata[3]=="imap")
      $ServerName = "{".$accdata[0]."/imap:143}INBOX"; // For a IMAP connection    (PORT 143)
    else
      $ServerName = "{".$accdata[0]."/pop3:110}INBOX"; // For a POP3 connection    (PORT 110)

    if($accdata[0]=="imap.gmail.com")
      $ServerName = "{".$accdata[0].":993/imap/ssl/novalidate-cert}INBOX"; // gmail IMAP
    if($accdata[0]=="pop.gmail.com")
      $ServerName = "{".$accdata[0].":995/pop3/ssl/novalidate-cert}INBOX"; // gmail POP3

    $UserName = $accdata[1];
    $PassWord = $accdata[2];
    $error="";
    $mbox = imap_open($ServerName, $UserName,$PassWord) or $mbox = imap_open(ereg_replace("}INBOX","/notls}INBOX",$ServerName), $UserName,$PassWord) or $error="true";
    if($error=="true") {

//      print "<div class='js'>".t("Error: Can not connect to e-mail server")."</div>";
//      exit; // stop newswall to report error information

   		array_push($allfeeds,array("title"=>$accdata[1],"info"=>$accdata[0],"descnohtml"=>t("Error: Can not connect to e-mail server"),"type"=>"email","icon"=>"email"));
   	  array_push($allsorts,0);
    } else {
      if ($hdr = imap_check($mbox)) {
     	  $msgCount = $hdr->Nmsgs;
      } else {
     	  $msgCount = 0;
      }
      $MN=$msgCount;
      if($MN>$vars["maxitems"] AND $vars["maxitems"]!=0) $MN=$vars["maxitems"];
      if($MN>0) {
        $overview=imap_fetch_overview($mbox,"1:$MN",0);
        $size=sizeof($overview);
        for($i=$size-1;$i>=0;$i--){
          $val=$overview[$i];
      	  $msg=$val->msgno;
          $from=$val->from;
            $fr2=ximap_utf8($from);
          if($fr2!="") $from=$fr2;
          $from=getname($from);
          $date=$val->date;
      	  $subj=$val->subject;
            $sp2=ximap_utf8($subj);
          if($sp2!="") $subj=$sp2;
          if($accdate=="")$accdate=$date;
          $seen=$val->seen;
      		$itemRSS = array (
            'id'    => $msg,
      			'title' => $from,
      			'desc'  => $subj,
      			'date'  => $date,
            'type'  => "email",
            'icon'  => "email",
            'source'=> $count,
            'info'  => $accdata[0],
            'fav'   => "",
            'bid'   => "bid".$count."-".$msg,
      			);
          $itemRSS["date"]=strtotime($itemRSS["date"]);
          $itemRSS['titlenohtml']=trim(strip_tags($itemRSS['title']));
          $itemRSS['descnohtml']=trim(strip_tags($itemRSS['desc']));
          $titlecut=preg_replace("![^a-z0-9]+!","",strtolower($itemRSS['titlenohtml']));
          if(!empty($references[$titlecut]) AND file_exists("images/references/".$references[$titlecut])) {
            $itemRSS["image"]="images/references/".$references[$titlecut];
            if($_SESSION["panelpass"]==$vars["panelpass"] || empty($vars["panelpass"]))
              $itemRSS["right"]=' oncontextmenu="dropreference(\''.$titlecut.'\');return false;" ';
            if($itemRSS['titlenohtml']!="")
              $itemRSS["imagetitle"]=$itemRSS['titlenohtml'];
            else
              $itemRSS["imagetitle"]=$itemRSS['date'];
            $itemRSS['title']=$itemRSS['descnohtml'];
          }
          $_SESSION["bid"][$itemRSS["bid"]]=$itemRSS["titlenohtml"]."|".$itemRSS["descnohtml"]."|".date("d.m.Y",$itemRSS["date"])."|".$itemRSS["type"]."|".$itemRSS["info"];
      		array_push($allfeeds,$itemRSS);
      	  array_push($allsorts,$itemRSS["date"]);
        }
      }
  	}
    imap_close($mbox);
  endforeach;

// -- RSS-FEEDS ----------------------------------------------------------------

  define('MAGPIE_INPUT_ENCODING', 'UTF-8');
  define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
  define('MAGPIE_CACHE_AGE', $vars["reload"] * 60);
  require_once('magpierss/rss_fetch.inc');

  $countf=0;
  foreach($feeds as $onefeed):
    $memdate="";
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

      $memdate=$feedinfo["date"];  // remember in case no individual date is available for the messages
      unset($feedinfo["date"]);
      if(!$feedinfo['desc']) $feedinfo['desc']=$feedinfo['sub'];
    }
    if($memdate!="" AND strtotime($memdate)<$maxload) {$countf++;continue;}
    $feedinfo['source']=$countf;
    $feedinfo['image']= $doc->image['url'];

    $urlparts=parse_url($onefeed);
    $urlbase=$urlparts["scheme"]."://".$urlparts["host"]."/favicon.ico";
    $urlsave="images/favicons/".$urlparts["host"]."-favicon.ico";
    if(file_exists($urlsave))
      $favicon=$urlsave;
    else
      if($feedinfo["image"]!="" AND $favicon=file_get_contents($feedinfo["image"])) {
        file_put_contents($urlsave,$favicon);
        $favicon=$urlsave;
      } else if($favicon=file_get_contents($urlbase)) {
        file_put_contents($urlsave,$favicon);
        $favicon=$urlsave;
      } else
      $favicon="";

    $count=0;
  	foreach ($doc->items as $node) {
  		$itemRSS = array (
        'id'    => $count,
  			'title' => $node['title'],
  			'desc'  => $node['description'],
  			'link'  => $node['link'],
  			'date'  => $node['pubdate'],
  			'dcdate'=> $node['dc']['date'],
        'lbd'   => $node['lastbuilddate'],
        'upd'   => $node['updated'],
        'pud'   => $node['pubdate'],
        'pub'   => $node['published'],
  			'cont'  => $node['content'],
  			'atom'  => $node['atom_content'],
  			'enurl' => $node['enclosure_url'],
  			'author'=> getname($node['author']),
        'type'  => "feed",
        'source'=> $countf,
        'info'  => $feedinfo['title'],
        'fav'   => $favicon,
  			'clink' => $feedinfo['link'],
        'bid'   => "bid".$countf."-".$count,
  		);
      if($itemRSS['date']=="")
        if($itemRSS['dcdate']!="")
          $itemRSS["date"]=$itemRSS['dcdate'];
        else if($itemRSS['lbd']!="")
          $itemRSS["date"]=$itemRSS['lbd'];
        else if($itemRSS['pud']!="")
          $itemRSS["date"]=$itemRSS['pud'];
        else if($itemRSS['pub']!="")
          $itemRSS["date"]=$itemRSS['pub'];
        else if($itemRSS['upd']!="")
          $itemRSS["date"]=$itemRSS['upd'];
        else if($feedinfo['date']!="")
          $itemRSS['date']=$feedinfo["date"];

      if($feedinfo['date']=="")
        $feedinfo['date']=$itemRSS['date'];
      if($feedinfo['date']=="")
        $feedinfo['date']=$memdate; // only if there's no date available
	    $itemRSS["date"]=strtotime($itemRSS["date"]);
      if($itemRSS["date"]<$maxload) {$count++;continue;}

      if($itemRSS['title']=="")
        $itemRSS['title']=$feedinfo['title'];
      if($itemRSS['title']=="")
        $itemRSS['title']=$feedinfo['author'];

      if($itemRSS['atom'])
        $itemRSS['desc']=$itemRSS['atom'];
      else if($itemRSS['cont'])
        $itemRSS['desc']=$itemRSS['cont'];
      if(eregi("^http.*\.(jpg|jpeg|gif|png)$",$itemRSS["link"]))
        $itemRSS["image"]=$itemRSS["link"];
      else if(eregi("^http.*\.(jpg|jpeg|gif|png)$",$itemRSS["enurl"]))
        $itemRSS["image"]=$itemRSS["enurl"];
      else if($itemRSS["desc"]){
// get image from description
        $pattern = '/(<img[^>]+src[\\s=\'"]+([^"\'>\\s]+\.(jpg|jpeg|gif|png)))/i';
        preg_match($pattern,$itemRSS["desc"],$result);
        if($result[2] and ereg("^http",$result[2]) and !eregi("tracker|doubleclick|feeds.feedburner.com|ads.pheedo.com|a.triggit.com|pixel.quant|browse.php|mf.gif|assoc-amazon",$result[2]))
           $itemRSS["image"]=$result[2];
      }
      if($itemRSS["image"]=="" AND $itemRSS["desc"]){
// get vimeo video from description
        $pattern = '/(vimeo\.com\/(moogaloop\.swf\?clip_id=|video\/)?([0-9]+))/i';
        preg_match($pattern,$itemRSS["desc"],$result);
        if($result[3]) {
          $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$result[3].".php"));
          $itemRSS["image"]=$hash[0]['thumbnail_large'];
        }
        if($itemRSS["image"]==""){
// get youtube video from description
          $pattern = '/youtube\.com\/(watch\?v=|[^\/]+\/)([a-zA-Z0-9-_]+)/i';
          preg_match($pattern,$itemRSS["desc"],$result);
          if($result[2]) {
            $itemRSS["image"]="http://img.youtube.com/vi/".$result[2]."/0.jpg";
          }
        }
        if($itemRSS["image"]==""){
// get google video from description
          $pattern = '/video\.google\.com\/googleplayer\.swf\?docid=([^\'"][a-zA-Z0-9-_]+)[&\'"]/i';
          preg_match($pattern,$itemRSS["desc"],$result);
          if($result[1]) {
            $itemRSS["image"]="http://video.google.com/videofeed?docid=".$result[1];
          }
        }
      }

      if(eregi("smileys",$itemRSS["image"]) OR eregi("smilies",$itemRSS["image"])) $itemRSS["image"]="";
      if($itemRSS["image"]=="" AND $feedinfo["image"]) {
        $itemRSS["image"]=$feedinfo["image"];
      }
      
      if(eregi("twitter\.com",$onefeed)) {
        $parts=explode(": ",$itemRSS["title"]);
        if(count($parts)>1) {
          $itemRSS['author']=$parts[0];
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
      
      $itemRSS['descnohtml']=trim(strip_tags($itemRSS['desc']));

      if($itemRSS["image"]!="" AND $itemRSS["title"]!="" AND $itemRSS['descnohtml']!="") {
        $itemRSS["imagetitle"]=$itemRSS['descnohtml'];
      } else if($itemRSS['author']!="")
        $itemRSS["imagetitle"]=$itemRSS['author'];
      else if($itemRSS['descnohtml']!="")
        $itemRSS["imagetitle"]=$itemRSS['descnohtml'];
      else
        $itemRSS["imagetitle"]=$itemRSS['title'];
      if (strlen($itemRSS["imagetitle"]) > 150) {
        $itemRSS["imagetitle"] = substr($itemRSS["imagetitle"],0,149) ."...";
      }

      if($itemRSS["image"]=="") {
        $desccut=preg_replace("![^a-z0-9]+!","",strtolower(strip_tags($itemRSS['title'])));
        if(!empty($references[$desccut]) AND file_exists("images/references/".$references[$desccut])) {
          $itemRSS["image"]="images/references/".$references[$desccut];
          if($_SESSION["panelpass"]==$vars["panelpass"] || empty($vars["panelpass"]))
            $itemRSS["right"]=' oncontextmenu="dropreference(\''.$desccut.'\');return false;" ';
          unset($itemRSS['title']);
        }
      }

      $itemRSS["icon"]=$feedinfo["icon"];

      if($favicon=="") {
        if($favicon=file_get_contents('images/icons/'.$feedinfo["icon"].'.png')) {
          file_put_contents($urlsave,$favicon);
          $favicon=$urlsave;
          $itemRSS["fav"]=$urlsave;
        }
      }

      $_SESSION["bid"][$itemRSS["bid"]]=$node["title"]."|".$itemRSS["title"]."|".$itemRSS["atom"]."|".$itemRSS["cont"]."|".$itemRSS["descnohtml"]."|".date("d.m.Y",$itemRSS["date"])."|".$itemRSS["type"]."|".$itemRSS["info"]."|".$itemRSS["author"]."|".$itemRSS["link"]."|".$node['description'];
  		array_push($allfeeds,$itemRSS);
  	  array_push($allsorts,$itemRSS["date"]);
      $count++;
  	  if($count==$vars["maxitems"]) break;
  	}
  	$countf++;
  endforeach;

  array_multisort($allsorts,SORT_DESC,$allfeeds);

  $count=0;
  $bing=0;
  if($allfeeds):
  echo'
  <div class="box"><div class="stripe">
    <ul>
';
  foreach ($allfeeds as $feeditem) {
      $extraclass="";
      $titleclass="";
      $context=" ";
      $hstyle="";
      $cstyle="";
      if($feeditem["title"]) $lstitle=$feeditem["title"]; else
      if($feeditem["info"]) $lstitle=$feeditem["info"]; else
      if($feeditem["author"]) $lstitle=$feeditem["author"]; else
      $lstitle="";

      if($feeditem["source"]<0)
        $click="onclick"; else $click=$onLclick;

      $directlink=$feeditem["link"];
      if($feeditem["icon"]=="facebook" OR $feeditem["icon"]=="twitter") {
        $click="onclick";
        $directlink="";
      }

      if($directlink!="")
        $context=' '.$onRclick.'="loadsite(\''.$feeditem["link"].'\',\''.htmlspecialchars(str_replace("\n"," ",addslashes($lstitle)),ENT_QUOTES).'\');return false;" ';
      if($fadeload==0) $extraclass="";
      else if($feeditem['date']<($nowload-$fadeload)) $extraclass=" l24";
      else if($feeditem['date']<($nowload-$fadeload/2)) $extraclass=" l12";
      $extraclass=" class='".$feeditem["icon"].$extraclass."'";

      if($feeditem["image"]):
        if(strlen($feeditem["title"])>0 AND strlen($feeditem["title"])<15 AND $count!=0 AND $feeditem["icon"]!="email" AND $feeditem["icon"]!="facebook" AND ($count+1)<count($allfeeds)) {

/* --- message with full size header image --- */
          $imagetitle=trim($feeditem["title"]." ".$feeditem["descnohtml"]);
          if (strlen($imagetitle) > 150)
            $imagetitle = str_replace("\"","'",substr($imagetitle,0,149) ."...");
          echo'
          <li'.$extraclass.' id="'.$feeditem["bid"].'">
            <div class="head double"><a ';
          if($feeditem["link"]) echo'target="_blank" href="'.$feeditem["link"].'" ';
          echo $click.'="loadrss('.$feeditem["source"].','.$feeditem["id"].');return false;"'.$context.'><img src="'.$feeditem["image"].'" class="headimg hand" xtitle="<img src=\''.$feeditem["image"].'\' class=\'titimg\'><br>'.$imagetitle.'" alt=""></a></div>
            <div class="content" style="display:none;"></div>
            <a class="head transparent" href="'.$feeditem["image"].'" rel="prettyPhoto[\'gal\']" title="<img src=\''.$feeditem["image"].'\' class=\'titimg\'><br>'.str_replace("\"","'",$feeditem["imagetitle"]).'"></a>
    ';
        } else {

/* --- message with half size header image --- */
          echo'
          <li'.$extraclass.' id="'.$feeditem["bid"].'">
            <div class="head" '.$hstyle.'><a href="'.$feeditem["image"].'" rel="prettyPhoto[\'gal\']" title="<img src=\''.$feeditem["image"].'\' class=\'titimg\'><br>'.str_replace("\"","'",$feeditem["imagetitle"]).'"><img src="'.$feeditem["image"].'" class="headimg" alt="" '.$feeditem["right"].'></a></div>';
          if($feeditem["title"]!="") {
            $titleclass="title";
            $feeditem["descnohtml"]=$feeditem["title"];
          } else {
            $titleclass="";
            if($feeditem["descnohtml"]=="") $feeditem["descnohtml"]=t("more...");
          }
          if($feeditem["descnohtml"]==t("more...") && preg_match("/youtube\.com/\?watch/",$feeditem["link"])) $context.=' rel="prettyPhoto"';
          echo'
          <div class="content" '.$cstyle.'><a ';
          if($feeditem["link"]) echo'target="_blank" href="'.$feeditem["link"].'" ';
          echo $click.'="loadrss('.$feeditem["source"].','.$feeditem["id"].');return false;"'.$context.'><div class="text '.$titleclass.'">'.$feeditem["descnohtml"].'</div></a></div>
    ';
        }
      else:

/* --- message with title and text --- */
        $refcut=preg_replace("![^a-z0-9]+!","",strtolower(strip_tags($feeditem['title'])));

//        if($_SESSION["panelpass"]==$vars["panelpass"] || empty($vars["panelpass"])) /* add this line to hide tooltip if not logged in */
        $onclick='onclick="setreference(\''.$refcut.'\');" title="'.t("Link this title to an image").'"';

        if($feeditem["descnohtml"]==t("Error: Can not connect to e-mail server")) {
          $onclick="";
        } else if($feeditem["descnohtml"]=="") {
          $feeditem["descnohtml"]=t("more...");
//          $feeditem["descpreview"]="";
        } else {
//          $feeditem["descpreview"]=" title=\"".$feeditem["descnohtml"]."\"";
        }
        if($feeditem["descnohtml"]==t("more...") && preg_match("/youtube\.com/\?watch/",$feeditem["link"])) $context.=' rel="prettyPhoto"';
        echo'
        <li'.$extraclass.' id="'.$feeditem["bid"].'">
          <div class="head"><div class="text title" '.$onclick.'>'.$feeditem['title'].'</div></div>
          <div class="content"><a ';
        if($feeditem["link"]) echo'target="_blank" href="'.$feeditem["link"].'" ';
        echo $click.'="loadrss('.$feeditem["source"].','.$feeditem["id"].');return false;"'.$context.$feeditem["descpreview"].'><div class="text">'.$feeditem["descnohtml"].'</div></a></div>
  ';
      endif;
      if($lastload<$feeditem['date'] AND $lastload!=0) {
        $prozent=($feeditem['date']-$lastload)/($nowload-$lastload)*10;
        $opa='opacity:'.($prozent*.1).';-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity='.(10*$prozent).')";filter:alpha(opacity='.(10*$prozent).');';
        echo'<div class="new" style=\''.$opa.'\'></div>';
      }
      if($feeditem['date']>=$nowload-($vars["reload"] * 60)) $bing=1;

      $miniLclick="";
      $miniRclick="";
      if($feeditem['icon']!="email" AND $feeditem['icon']!="facebook" AND $feeditem['icon']!="twitter")
      $miniLclick='onclick="loadsite(\''.$feeditem['clink'].'\',\''.addslashes($feeditem["info"]).'\');"';
      if($feeditem['icon']!="email")
        $miniRclick='oncontextmenu="$.prettyPhoto.open(\'set_share.php?vartitle='.rawurlencode($lstitle).'&varurl='.rawurlencode($feeditem['link']).'&varencode=true&iframe=true&width=420&height=50\',\''.t("Share").'\',\'\');return false;"';

      $favtitle=str_replace("\"","'",$feeditem["info"]);
      if($miniRclick!="") $favtitle.=" | ".t("Share");
      if($feeditem['fav']!="")
        echo'<img src="'.$feeditem["fav"].'" alt="" title="'.$favtitle.'" class="icon" '.$miniLclick.' '.$miniRclick.'>';
      else
        echo'<img src="images/icons/'.$feeditem["icon"].'.png" alt="" title="'.$favtitle.'" class="icon" '.$miniLclick.' '.$miniRclick.'>';
      echo'</li>';
      $count++;
 }
  echo'
    </ul>
  </div></div>
';
  endif;
//  }
  if($bing==1) echo'<audio src="bing.ogg" autoplay></audio>';
?>