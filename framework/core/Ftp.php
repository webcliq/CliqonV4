<?php
/**
 * Ftp class
 * Download and upload files to a Ftp Server
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author: paul.ren - e-mail:rsr_cn@yahoo.com.cn - Modified by Mark Richards for Cliqon
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class Ftp
{

	public $host 			= "192.168.1.6";		//FTP HOST
	public $port 			= "21";				//FTP port
	public $user 			= "cliqonftp";		//FTP user
	public $pass 			= "cliqon";			//FTP password
	public $link_id 		= "";				//FTP hand
	public $is_login 		= "";				//is login 
	public $debug 			= 1;
	public $local_dir 		= "/tmp";			//local path for upload or download
	public $ftprootdir		= "/";				//FTP root path of FTP server
	public $dir 			= "/";				//FTP current path	

	public function __construct()
	{
		global $cfg; 
		global $clq; 
	}

	public function __destruct()
	{
		return ftp_close($this->link_id);
	}	
	
	public function attach($user = "Anonymous", $pass="Email", $host="localhost", $port="21") 
	{
		if($host) $this->host = $host;
		if($port) $this->port = $port;
		if($user) $this->user = $user;
		if($pass) $this->pass = $pass;
		$this->login();
	}
	
	function halt($msg, $line = __LINE__)
	{
		// L::cLog("FTP Error in line: ".$line." <br/> FTP Error message: ".$msg);
		echo "FTP Error in line: ".$line." <br/> FTP Error message: ".$msg;
		exit();
	}
	
	function login()
	{
		if(!$this->link_id) {
			$this->link_id = ftp_connect($this->host, $this->port) or $this->halt("Cannot connect to host: ".$this->host.":".$this->port." at ", __LINE__);
		};

		if(!$this->is_login){
			$this->is_login = ftp_login($this->link_id, $this->user, $this->pass) or $this->halt("Ftp login failed. Invalid user or password", __LINE__);
		}
	}

	function systype()
	{
		return ftp_systype($this->link_id);
	}

	function pwd()
	{
		$this->login();
		$dir = ftp_pwd($this->link_id);
		$this->dir = $dir;
		return $dir;
	}

	function cdup()
	{
		$this->login();
		$isok =  ftp_cdup($this->link_id);
		if($isok) $this->dir = $this->pwd();
		return $isok;
	}

	function cd($dir)
	{
		$this->login();
		$isok = ftp_chdir($this->link_id,$dir);
		if($isok) $this->dir = $dir;
		return $isok;
	}

	function nlist($dir="")
	{
		$this->login();
		if(!$dir) $dir = ".";
		$arr_dir = ftp_nlist($this->link_id,$dir);
		return $arr_dir;
	}

	function rawlist($dir="/")
	{
		$this->login();
		$arr_dir = ftp_rawlist($this->link_id,$dir);
		return $arr_dir;
	}

	function mkdir($dir)
	{
		$this->login();
		return @ftp_mkdir($this->link_id,$dir);
	}

	function fileSize($file)
	{
		$this->login();
		$size = ftp_size($this->link_id, $file);
		return $size;
	}

	function chmod($file, $mode = 0666)
	{
		$this->login();
		return ftp_chmod($this->link_id,$file,$mode);
	}

	function delete($remote_file)
	{
		$this->login();
		return ftp_delete($this->link_id,$remote_file);
	}

	function get($local_file, $remote_file)
	{
		$this->login();
		$mode = $this->get_ftp_mode($local_file);
		return ftp_get($this->link_id, $local_file, $remote_file, $mode);
	}

	function put($remote_file, $local_file)
	{
		$mode = $this->get_ftp_mode($remote_file);
		$this->login();
		return ftp_put($this->link_id, $remote_file, $local_file, $mode);
	}

	function put_string($remote_file,$data)
	{
		$mode = $this->get_ftp_mode($remote_file);
		$this->login();
		$tmp = "/tmp"; //ini_get("session.save_path");
		$tmpfile = tempnam($tmp,"tmp_");
		$fp = @fopen($tmpfile,"w+");
		if($fp){
			fwrite($fp,$data);
			fclose($fp);
		}else return 0;
		$isok = $this->put($remote_file,$tmpfile, $mode);
		@unlink($tmpfile);
		return $isok;
	}

	function p($msg)
	{
		echo "<pre>";
		print_r($msg);
		echo "</pre>";
	}

	function close()
	{
		@ftp_quit($this->link_id);
	}

	function ftp_get_contents($remote_file, $mode, $resume_pos = null)
	{
	   
	    $pipes=stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
	    if($pipes===false) return false;
	    if(!stream_set_blocking($pipes[1], 0)){
	        fclose($pipes[0]); fclose($pipes[1]);
	        return false;
	    }
	    $fail=false;
	    $data='';
	    if(is_null($resume_pos)){
	        $ret=ftp_nb_fget($this->link_id, $pipes[0], $remote_file, $mode);
	    } else {
	        $ret=ftp_nb_fget($this->link_id, $pipes[0], $remote_file, $mode, $resume_pos);
	    }
	    while($ret==FTP_MOREDATA){
	        while(!$fail && !feof($pipes[1])){
	            $r=fread($pipes[1], 8192);
	            if($r==='') break;
	            if($r===false){ $fail=true; break; }
	            $data.=$r;
	        }
	        $ret=ftp_nb_continue($this->link_id);
	    }
	    while(!$fail && !feof($pipes[1])){
	        $r=fread($pipes[1], 8192);
	        if($r==='') break;
	        if($r===false){ $fail=true; break; }
	        $data.=$r;
	    }
	    fclose($pipes[0]); fclose($pipes[1]);
	    if($fail || $ret!=FTP_FINISHED) return false;
	    return $data;
	}

	function ftp_sync($dir) 
	{

	    if ($dir != ".") {
	        if (ftp_chdir($this->link_id, $dir) == false) {
	            echo ("Change Dir Failed: $dir<BR>\r\n");
	            return;
	        }
	        if (!(is_dir($dir)))
	            mkdir($dir);
	        chdir ($dir);
	    }

	    $contents = ftp_nlist($this->link_id, ".");
	    foreach ($contents as $file) {
	   
	        if ($file == '.' || $file == '..')
	            continue;
	       
	        if (@ftp_chdir($this->link_id, $file)) {
	            ftp_chdir ($this->link_id, "..");
	            ftp_sync ($file);
	        }
	        else
	            ftp_get($this->link_id, $file, $file, FTP_BINARY);
	    }
	       
	    ftp_chdir ($this->link_id, "..");
	    chdir ("..");
	} 

	protected function get_ftp_mode($file)
	{   
	    $path_parts = pathinfo($file);
	   
	    if (!isset($path_parts['extension'])) return FTP_BINARY;
	    switch (strtolower($path_parts['extension'])) {
	        case 'am':case 'asp':case 'bat':case 'c':case 'cfm':case 'cgi':case 'conf':
	        case 'cpp':case 'css':case 'dhtml':case 'diz':case 'h':case 'hpp':case 'htm':
	        case 'html':case 'in':case 'inc':case 'js':case 'm4':case 'mak':case 'nfs':
	        case 'nsi':case 'pas':case 'patch':case 'php':case 'php3':case 'php4':case 'php5':
	        case 'phtml':case 'pl':case 'po':case 'py':case 'qmail':case 'sh':case 'shtml':
	        case 'sql':case 'tcl':case 'tpl':case 'txt':case 'vbs':case 'xml':case 'xrc':
	            return FTP_ASCII;
	    }
	    return FTP_BINARY;
	}


} // Ftp Class ends

/*
$ftp = new Ftp("root","");

Example Upload File
$ftp->put("put.tmp","1.mid");//put file

Example Download
$ftp->get("1.mid","get.mid");//get file

Example write string to a file on FTP server
$ftp->put_string("putstring.tmp","1.mid");//put string

Common
$ftp->p($ftp->rawlist("."));//list
$ftp->close();
*/