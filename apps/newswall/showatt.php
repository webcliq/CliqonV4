<?php
  $vars=array(); if(file_exists("variable.php.ini")) include("variable.php.ini"); else include("standard.php.ini");
  $references=array(); if(file_exists("varreferences.php.ini")) include("varreferences.php.ini");
  $accounts=array(); if(file_exists("varmails.php.ini")) include("varmails.php.ini");
  include("functions.php");
  
  $acc=$accounts[($_GET["source"]*-1)-1];
  $msgno=$_GET["id"];
  $filno=$_GET["file"];

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
  if($mbox = imap_open($ServerName, $UserName,$PassWord)) {
    $dataAtt = get_att($mbox, $msgno);
    if(is_array($dataAtt)) {
      $attcount=0;
      foreach($dataAtt as $oneAtt){
        $attcount++;
        if($attcount==$filno) {
        	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        	header("Content-Type: application/octet-stream");
        	header("Content-Disposition: attachment; filename=".$oneAtt["name"]);
          print $oneAtt["attachment"];
        }
      }
    }
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
?>