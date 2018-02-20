<?php
/**
 * Cliqon Install Controller and associated methods/functions
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2017 Mark Richards, Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

loadFile('controllers/Controller.php');

final class InstallController extends Controller
{
	public $thisclass = "Install Controller";

	/** Get, Post, Get_xhr, Post_xhr 
	 * Displays the Install template page
	 * @param - array - Request
	 * @return -
	 **/
	 function get($idiom = "", $action = "") 
	 {

		if($action == "") {
			global $clq; global $istr;
			$_SESSION['UserName'] = "cliqoninstaller";
			$cfg = $clq->get('cfg');

			// Load Template Engine 
			$tpl = new Engine(new FilesystemLoader($clq->get('basedir')."install"), $clq->get('basedir')."install/cache");

			$vars = [
				'protocol' => $clq->get('protocol'),
				'rootpath' => $clq->get('rootpath'),
				'basedir' => $clq->get('basedir'),
				'viewpath' => $clq->get('rootpath').'install/',
				'includepath' => $clq->get('rootpath').'includes/',
				'lcd' => $cfg['site']['defaultidiom'],
				'cfg' => $cfg,
				'action' => 'install',
				'istr' => $istr
			];
			$template = "install.tpl";
			echo $tpl->render($template, $vars);
		} else {
			global $clq; $clq->set('model', 'clean');
			$rq = $_REQUEST;
			return self::$action($idiom, $rq);			
		}
	 }

	 // Not used
	 function get_xhr($idiom, $action) 
	 {
		global $clq; $clq->set('model', 'clean');
		$rq = $_REQUEST;
		return self::$action($idiom, $rq);
	 }

	 // Not used
	 function post() {}

	 function post_xhr($idiom, $action) 
	 {
		global $clq; $clq->set('model', 'clean');
		$rq = $_REQUEST;
		return self::$action($idiom, $rq);	
	 }

	/** Checks that the necessary subdirectories exist and are writable 
	 * @param - array - Request
	 * @return -
	 **/
	 function directories($idiom, $rq)
	 {
		$dirs = ""; global $clq;
		$dir = array('tmp', 'cache', 'log', 'data', 'config', 'admin/cache', 'admin/config', 'public/uploads', 'public/thumbs', 'public/images');
		foreach($dir as $d => $dn) {

			$row = H::div(['class' => 'clqtable-cell bluec'], '/'.$dn.'/ - ');
			if(is_dir($clq->get('basedir').$dn)) { // True

				$exists = H::span(['class' => 'greenc'], 'Exists ');
				$writable = H::span(['class' => 'greenc'], 'and is writeable');
				$notwritable = H::span(['class' => 'redc'], 'but is not writeable');
	
				if(is_writeable($clq->get('basedir').$dn)) {
					$row .= H::div(['class' => 'clqtable-cell greenc'], $exists.$writable);
				} else {
					$row .= H::div(['class' => 'clqtable-cell greenc'], $exists.$notwritable);
				}
			} else {
				$row .= H::div(['class' => 'clqtable-cell redc'], 'Does not exist');
			}			

			$dirs .= H::div(['class' => 'clqtable-row'], $row);
		}
		echo $dirs;		
	 }

	/** Copy the Dummy Config Text file to an active Config file 
	 * 
	 * @param - array - Request
	 * @return -
	 **/
	 function createconfigfile($idiom, $rq)
	 {
		global $clq;

		$rootpath = $clq->get('basedir');
        $filename = $rootpath.'install/config/config.txt';	// This is at root of the file using this script.
        $fd = fopen($filename, "r");                   		// opening the file in read mode
        $contents = fread($fd, filesize($filename));   		// reading the content of the file
        fclose ($fd);                                   	// Closing the file pointer	
	
        $qrepl = array(
            
            '{type}',
            '{server}',
            '{dbname}',
            '{rootuser}',
            '{rootpassword}',
            '{username}',       
            '{password}',
            '{portno}',  

            '{name}',
            '{description}',

            '{idiom_array}',
			'{idiom_flags}',

			'{siteurl}',
			'{adminemail}'
        );
        
        // Handle languages
        $idms = explode(',', $rq['idiomarray']);  $idioms = "";  
        foreach($idms as $n => $llcd) {
           $idm = explode("|", $llcd);
		   $idioms .= $idm[0]." = '".$idm[1]."'".PHP_EOL;
        }
        $idioms = trim($idioms, "', ");
		
        $idmfs = explode(',', $rq['idiomflags']);  $idiomfs = "";  
        foreach($idmfs as $n => $llcd) {
           $idmf = explode("|", $llcd);
		   $idiomfs .= $idmf[0]." = '".$idmf[1]."'".PHP_EOL;
        }
        $idiomfs = trim($idiomfs, "', ");
		
        $qwith = array(
           	$rq['type'], 
           	$rq['server'],
           	$rq['dbname'],
           	$rq['rootuser'],
			$rq['rootpassword'],
           	$rq['username'],
           	$rq['password'],
           	$rq['portno'],

           	$rq['name'],
			$rq['description'],

           	$idioms,
		   	$idiomfs,

           	$rq['siteurl'],
			$rq['adminemail']             
        );
        
        $newconfig = str_replace($qrepl, $qwith, $contents);
        
        // Open the file in write mode, if file does not exist then it will be created.
        $configfile = $rootpath."config/config.cfg";
        $fp = fopen ($configfile, "w"); 
        fwrite ($fp, $newconfig);         		// entering data to the file
        $result = fclose($fp);                       	// closing the file pointer    

        if(file_exists($configfile)) {
        	echo json_encode(['File creation successful']);
        } else {
        	echo json_encode(['File creation failed: '.$result]);
        }	
	 }

	/** Creates the initial databases if required 
	 * 
	 * @param - array - Request
	 * @return -
	 **/
	 function createdatabase($idiom, $rq)
	 {
	    try {
	    	global $clq;
			// Execute the SQL using the new Config File	 
	        $cfg = C::cfgReadFile("config/config.cfg");
	        $dbcfg = $cfg['database'];
	    	$result = $this->dbSetup($dbcfg);
	    	if($result == "Ok") {
	        	echo "<img src='".$clq->get('rootpath')."install/img/tick.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' title='".$result."' />";
	    	} else {
	        	echo "<img src='".$clq->get('rootpath')."install/img/cross.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' /> ".$result;
	    	}

	    } catch(Exception $e) {
	    	echo "<img src='".$clq->get('rootpath')."install/img/cross.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' /> ".$e->getMessage();
	    }		
	 }

	/** Creates the initial tables 
	 * @param - array - Request
	 * @return -
	 **/
	 function createtables($idiom, $rq)
	 {

	    try {
			global $clq;
	        $cfg = C::cfgReadFile("config/config.cfg");
	        $f = $clq->resolve('Files');
	        $dbcfg = $cfg['database'];		
			$db = $clq->resolve('Db');
    		$rootpath = $clq->get('basedir');
    		$sitepath = $clq->get('rootpath');	 
    		$ok = "<img src='".$sitepath."install/img/tick.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;'/>";
    		$notok = "<img src='".$sitepath."install/img/cross.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' />";

    		// Read structure from cliqon.sql here
			// Settings to run long jobs
			set_time_limit(600);				 
			$dbconn = D::pdoConn($dbcfg);

			$filename = "/data/cliqon.sql";
	        $file = Y::readFile($filename, 'r');
			try {
		    	$result = $dbconn->exec($file);
			    if ($result === false) { // even if success, it may also return some code
			        die(print_r($dbconn->errorInfo(), true));
			    }
			} catch (Exception $e){
				echo $notok.$e->getMessage();
			    exit();	     
			}

	    	if($result == 0) {
	        	echo $ok;
	    	} else {
	        	echo $notok.$result;
	    	}

	    } catch(Exception $e) {
	    	echo $notok.$e->getMessage();
	    }	
	 }

	/** Populates the initial tables with the initial data 
	 * 
	 * @param - array - Request
	 * @return -
	 **/
	 function createbasedata($idiom, $rq)
	 {
		try {
			
			global $clq;
			$db = $clq->resolve('Db');
			$f = $clq->resolve('Files');
			$auth = $clq->resolve('Auth');
    		$rootpath = $clq->get('basedir');
    		$sitepath = $clq->get('rootpath');	 
    		$ok = "<img src='".$sitepath."install/img/tick.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;'/>";
    		$notok = "<img src='".$sitepath."install/img/cross.png' style='height: 24px; margin: 5px 0px 0px 10px; padding: 0px;' />";
			// Execute the SQL using the new Config File	
		    $cfg = C::cfgReadFile("config/config.cfg");
		    $dbcfg = $cfg['database'];
		    $usr = $cfg['site']['users'][0];
		    $site = $cfg['site'];
	    	$dbh = self::rbConnect($dbcfg);
	    	if($dbh === "Ok") {

	    		// Run dbcollection.sql and dbitem.sql
				// Settings to run long jobs
				set_time_limit(600);				 
				$dbconn = D::pdoConn($dbcfg);

				$filename = "/data/dbcollection.sql";
		        $file = Y::readFile($filename, 'r');
				try {
			    	$res1 = $dbconn->exec($file);
				    if ($res1 === false) { // even if success, it may also return some code
				        die(print_r($dbconn->errorInfo(), true));
				    }
				} catch (Exception $e){
					echo $notok.$e->getMessage();
				    exit();	     
				}

				$filename = "/data/dbitem.sql";
		        $file = Y::readFile($filename, 'r');
				try {
			    	$res2 = $dbconn->exec($file);
				    if ($res2 === false) { // even if success, it may also return some code
				        die(print_r($dbconn->errorInfo(), true));
				    }
				} catch (Exception $e){
					echo $notok.$e->getMessage();
				    exit();	     
				}

				/*
				try {
				    D::sqlImport('/data/dbitem.sql', $dbcfg);
				} catch (Exception $e){
					echo $notok.$e->getMessage();
				    exit();	     
				}
				*/

	    		// Administrative User
	            $userarray = array(
	                // Main
	                'group' => $usr['c_group'],
	                'username' => $rq['adminuser'],
	                'password' => $rq['adminpassword'],
	                'level' => $usr['c_level'],
	                'status' => $usr['c_status'],
	                'email' => $usr['c_email'],	     
	                'notes' => 'Created during installation process',	  
	                // Document
	                'firstname' => $usr['d_firstname'],
	                'midname' => $usr['d_midname'],
	                'lastname' => $usr['d_lastname'],
	                'langcd' => $usr['d_langcd'],
	                'avatar' => '',
	                'comments' => 'Created during installation process',
	            );

	            $res3 = $auth->createUser($userarray);

		    	if($res3 == "Ok") {
		        	echo $ok;
		    	} else {
		        	echo $notok.$res1.$res2.$res3;
		    	}   	

	    	} else {
	    		throw new Exception($dbh, 1);
	    	}

	    } catch(Exception $e) {
	    	echo $notok.$e->getMessage();
	    }	    		
	 }

	/** Edit the config file with an Ace Editor in a popup 
	 *
	 * @param - Request - primarily the instruction to save or not
	 * @return - string of HTML to act as content for the iFrame inside the TinyBox popup
	 **/
	 function editconfigfile($idiom, $rq)
	 {
		global $clq;
		$rootpath = $clq->get('basedir');
        $filename = $rootpath.'config/config.cfg';     // This is at root of the file using this script.
        $fd = fopen($filename, "r");                   // opening the file in read mode
        $contents = fread($fd, filesize($filename));   // reading the content of the file
        fclose ($fd);                                  // Closing the file pointer	
		
		$tpl = "codeeditor.tpl";
		$thisvars = [
			'contents' => $contents,
			'lcd' => $idiom,
			'save' => 'Save',
			'rootpath' => $clq->get('rootpath')
		];
		echo self::publishTpl($tpl, $thisvars);			
	 }

	/** Save the Config file 
	 * 
	 * @param - array - Request
	 * @return -
	 **/
	 function saveconfigfile($idiom, $rq)
	 {
		global $clq;
		$rootpath = $clq->get('basedir');
        // Open the file in write mode, if file does not exist then it will be created.
        $configfile = $rootpath."config/config.cfg";
        $fp = fopen ($configfile, "w"); 
        fwrite ($fp, urldecode($rq['filecontents']));         		// entering data to the file
        $result = fclose ($fp);	 								// closing the file pointer 
        if($result) {
        	echo json_encode(['Update successful']);
        } else {
        	echo json_encode(['Update failed']);
        }
     }

    /** Installer is completed
     *
     **/
	 function deleteinstaller($idiom, $rq)
	 {   
		global $clq;
    	$rootpath = $clq->get('basedir');
    	$sitepath = $clq->get('rootpath');	
	    $f = $clq->resolve('Files');
		$oldname = "notinstalled";
		$newname = "notinstalled.completed";
	    $ren = Y::renameFile($oldname, $newname);
	    $controller = "controllers/InstallController.php";
	    $del1 = Y::deleteFile($controller);
	    $install = "includes/install.php";
	    $del2 = Y::deleteFile($install);
	    $installdir = "install";
	    $del3 = Y::deleteDirectory($installdir);
	    $result = $ren.$del1.$del2.$del3;
        if($result) {
        	echo json_encode(['Install successful']);
        } else {
        	echo json_encode(['Install failed']);
        }
	 }


	/************************ Utilities **************************************************************/
		
		/** Publish Javascript strings
		 * not in use yet
		 *
		 *
		 **/		
		 function jstr() 
		 {

		 }

		/** Database setup
		 *
		 * @param - array - Request
		 * @return -
		 **/
		 protected function dbSetup($dbcfg) 
		 {
		    
		    try {
		    	global $clq;
				switch($dbcfg['type']) {

				    case"mysql":
				    case"pgsql":
				        $dbh = new PDO($dbcfg['type'].':host='.$dbcfg['server'], $dbcfg['rootuser'], $dbcfg['rootpassword']);
				        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				        
				        $res = $dbh->exec(
				        	" CREATE DATABASE IF NOT EXISTS `".$dbcfg['dbname']."`"
				        ) or die(print_r($dbh->errorInfo(), true));

				        if($dbcfg['rootuser'] != $dbcfg['username']) {
				        	$res .= $dbh->exec(
					        	" CREATE USER '".$dbcfg['username']."'@'".$dbcfg['server']."' IDENTIFIED BY '".$dbcfg['password']."'; 
					        	 GRANT ALL ON '".$dbcfg['dbname']."'.* TO '".$dbcfg['username']."'@'".$dbcfg['server']."'; 
					        	 FLUSH PRIVILEGES;"
					        ) or die(print_r($dbh->errorInfo(), true));
				        };

				    break;
				    case"sqlite":
						$dbh = new PDO('sqlite:'.$clq->get('basedir').'data/'.$dbcfg['dbname'].'.sqlite');
						$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						if(!file_exists($clq->get('basedir').'data/'.$dbcfg['dbname'].'.sqlite')) {return "NotOk";}
				    break;
				}    	
		        
				return "Ok";

		    } catch (PDOException $e) {
		        die("NotOk: ". $e->getMessage());
		    }
		 }

		/** Redbean connect
		 *
		 * @param - array - Request
		 * @return -
		 **/
		 function rbConnect($dbcfg) 
		 {
			global $clq;
			$db = $clq->resolve('Db');
			// This gets the DB Handler loaded
			try {
				global $clq;
				switch($dbcfg['type']){
				    case"mysql":
				       R::setup('mysql:host='.$dbcfg['server'].';dbname='.$dbcfg['dbname'],$dbcfg['username'],$dbcfg['password']);
				    break;
				    case"pgsql":
				        R::setup('pgsql:host='.$dbcfg['server'].';dbname='.$dbcfg['dbname'],$dbcfg['username'],$dbcfg['password']);
				    break;
				    case"sqlite":		    	
				        R::setup('sqlite:'.$clq->get('basedir').'data/'.$dbcfg['dbname'].'.sqlite');
				    break;
				}
				R::useWriterCache(true); 
				return "Ok";
			} catch (Exception $e) {
				return 'PDO Error: '.$e->getMessage();
			}
		 }

		/** Publish a Template 
		 * Common Template publishing function
		 * 
		 * @param - string - name of template
		 * @param - array - array of data to be converted to JSON to accompany the template HTML
		 * @param - array - variables for the template that will be mounted on the template before it is converted to an HTML string
		 * @return - Array - Consisting of three elements - an Ok flag, Html as a string to be rendered into the ID Admin Content 
		 * and Data to be consumed by any Vue JS template functions
		 **/
		 protected function publishTpl($tpl, $vars)
		 {
			// Template engine
	    	return Q::publishTpl($tpl, $vars, "install/components", "install/cache");
		 }

} // Install Controller ends

