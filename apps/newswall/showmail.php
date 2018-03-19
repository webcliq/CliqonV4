<?php
  $vars=array(); if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  $references=array(); if(file_exists("varreferences.php.ini")) include("varreferences.php.ini");
  $accounts=array(); if(file_exists("varmails.php.ini")) include("varmails.php.ini");
  include("functions.php");
  
  $acc=$accounts[($_GET["source"]*-1)-1];
  $msgno=$_GET["id"];
  
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
  if($mbox = imap_open($ServerName, $UserName,$PassWord) or $mbox = imap_open(ereg_replace("}INBOX","/notls}INBOX",$ServerName), $UserName,$PassWord)) {
    $overview=imap_fetch_overview($mbox,"$msgno:$msgno",0);
    $header=$overview[0];

	  $headers["subject"]=$header->subject;
    $headers["from"]=getname($header->from);
      $fr2=ximap_utf8($headers["from"]);
    if($fr2!="") $headers["from"]=$fr2;
    $headers["from"]=getname($headers["from"]);
    $headers["date"]=$header->date;

    $sp2=trim(ximap_utf8(trim($headers["subject"])));
    if($sp2) $headers["subject"]=$sp2;

    $desccut=preg_replace("![^a-z0-9]+!","",strtolower(trim(strip_tags($headers["from"]))));
    if(!empty($references[$desccut]) AND file_exists("images/references/".$references[$desccut])) {
      $headers["image"]="images/references/".$references[$desccut];
    }

    $dataTxt = get_part($mbox, $msgno, "TEXT/PLAIN");
    $dataHtml = get_part($mbox, $msgno, "TEXT/HTML");
    $dataAtt = get_att($mbox, $msgno);
    if ($dataTxt != "") {
      $msgBody = ereg_replace("\n","<br>",$dataTxt);
      $msgBody = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i","$1http://$2",    $msgBody);
      $msgBody = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@%]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</a>", $msgBody);
      $msgBody = preg_replace("/mailto:([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,10}|[0-9]{1,3})(\]?))/i","$1",$msgBody);
      $msgBody = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,10}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\">$1</a>",$msgBody);
    } else {
      $msgBody = preg_replace("/.*<body[^>]*>|<\/body>.*/si", "", $dataHtml);
    }

    $msgBody=fixEncoding($msgBody);

    $headers["date"]=strtotime($headers["date"]);
    echo '<div id="detailtext"><div class="msgtitle"><div class="text"><div class="data"><img src="images/icons/email.png">'.$accdata[0];
    echo '</div><div class="title">'.$headers["subject"].'</div>';
    if($headers["image"]) echo'<img src="'.$headers["image"].'" class="data">';
    echo '<div class="data">';
    if($headers["from"]) echo t("Editor").': '.$headers["from"].'<br>';
    if($headers["date"]) echo t("Date").': '.date(t("Y/m/d H:i"),$headers["date"]).t("h")."<br>";
    if($headers["subject"]) $titleadd=": ".$headers["subject"]; else $titleadd="";
    if(empty($titleadd) && $headers["from"]) $titleadd=": ".$headers["from"];
    echo '</div></div></div>';
    echo '<div class="msgtext"><div class="text">'.$msgBody.'<br><br>';

// --- Show attachments ---
    $browser=$_SERVER['HTTP_USER_AGENT'];
    if(is_array($dataAtt)) {
      $attcount=0;
      foreach($dataAtt as $oneAtt){
          $attcount++;
          $pi=pathinfo($oneAtt["name"]);
          echo'<span class="atttitle"><a href="showatt.php?source='.$_GET["source"].'&id='.$_GET["id"].'&file='.$attcount.'">'.$oneAtt["name"].'</a></span>';
          if(preg_match("/msie (5|6|7|8)/i",$browser)){
          } else {
            if(preg_match('/(jpeg|jpg|gif|png)/i',$pi['extension'])) print"<img src='".showImg($oneAtt["attachment"],$pi['extension'],"image")."' title='".$oneAtt["name"]."' alt='".$oneAtt["name"]."'/><br>";
            if(preg_match('/(wav|m4a|oga|mp3)/i',$pi['extension'])) print"<video src='".showImg($oneAtt["attachment"],$pi['extension'],"audio")."' controls></video><br>";
            if(preg_match('/(m4v|ogv|mp4|ogg)/i',$pi['extension'])) print"<video src='".showImg($oneAtt["attachment"],$pi['extension'],"video")."' controls></video><br>";
          }
          if(preg_match('/(txt)/i',$pi['extension'])) print"<code>".nl2br(fixEncoding($oneAtt["attachment"]))."'</code><br>";
      }
    }
// ---


    echo '</div></div>';
    echo '<br clear="all">';
    echo '<script type="text/javascript">document.title="newswall'.$titleadd.'";$("#panweb").data("url","");$("#pansha").data("url","");$("#pansha").data("title","");</script>';
    echo '</div>';
  } else {
    echo '<div id="detailtext"><div class="msgtitle"><div class="text"><div class="data"><img src="images/icons/email.png">'.$accdata[0];
    echo '</div><div class="title">'.t("Error: Can not connect to e-mail server").'</div>';
    echo '</div></div>';
    echo '<br clear="all">';
    echo '<script type="text/javascript">$("#panweb").data("url","");$("#pansha").data("url","");$("#pansha").data("title","");</script>';
    echo '</div>';
  }

  function get_mime_type(&$structure) {
    $primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
    if($structure->subtype) {
     	return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
    }
   	return "TEXT/PLAIN";
  }
  function get_part($stream, $msg_number, $mime_type, $structure = false,$part_number    = false) {
   	if(!$structure) {
   		$structure = imap_fetchstructure($stream, $msg_number);
   	}
   	if($structure) {
   		if($mime_type == get_mime_type($structure)) {
   			if(!$part_number) {
   				$part_number = "1";
   			}
   			$text = imap_fetchbody($stream, $msg_number, $part_number);
   			if($structure->encoding == 3) {
   				return imap_base64($text);
   			} else if($structure->encoding == 4) {
   				return imap_qprint($text);
   			} else {
   			  return $text;
   		  }
   	  }
  		if($structure->type == 1) /* multipart */ {
     		while(list($index, $sub_structure) = each($structure->parts)) {
     			if($part_number) {
     				$prefix = $part_number . '.';
     			}
     			$data = get_part($stream, $msg_number, $mime_type, $sub_structure,$prefix .    ($index + 1));
     			if($data) {
     				return $data;
     			}
     		}
   		}
   	}
   	return false;
  }
  function get_att($stream, $msg_number) {
    $structure = imap_fetchstructure($stream, $msg_number);
    $attachments = array();
    $retattachments = array();
    if(isset($structure->parts) && count($structure->parts)) {

    	for($i = 0; $i < count($structure->parts); $i++) {

    		$attachments[$i] = array(
    			'is_attachment' => false,
    			'filename' => '',
    			'name' => '',
    			'attachment' => ''
    		);

    		if($structure->parts[$i]->ifdparameters) {
    			foreach($structure->parts[$i]->dparameters as $object) {
    				if(strtolower($object->attribute) == 'filename') {
    					$attachments[$i]['is_attachment'] = true;
    					$attachments[$i]['filename'] = $object->value;
    				}
    			}
    		}

    		if($structure->parts[$i]->ifparameters) {
    			foreach($structure->parts[$i]->parameters as $object) {
    				if(strtolower($object->attribute) == 'name') {
    					$attachments[$i]['is_attachment'] = true;
    					$attachments[$i]['name'] = $object->value;
    				}
    			}
    		}
    		
    		if($attachments[$i]['is_attachment']) {
          if($attachments[$i]['name']=="" AND $attachments[$i]['filename']!="")
            $attachments[$i]['name']=$attachments[$i]['filename'];
          if($attachments[$i]['filename']=="" AND $attachments[$i]['name']!="")
            $attachments[$i]['filename']=$attachments[$i]['name'];
    			$attachments[$i]['attachment'] = imap_fetchbody($stream, $msg_number, $i+1);
    			if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
    				$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
    			}
    			elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
    				$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
    			}
          $retattachments[]=$attachments[$i];
    		}
    	}
    }
    return $retattachments;
  }
  function showImg($contents,$ext="jpg",$typ="image"){
    $base64   = base64_encode($contents);
    return ('data:'.$typ.'/'.$ext.';base64,'.$base64);
  }
  function fixEncoding($in_str) {
    $cur_encoding = mb_detect_encoding($in_str) ;
    if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8"))
      return $in_str;
    else
      return utf8_encode($in_str);
  }
?>