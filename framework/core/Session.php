<?php
/**
 * @title           Session Class
 *
 * @author    		Webcliq    	   
 * @copyright        
 * @license          
 * @package          
 */
class Session {

	const THISCLASS = "Session";
	
	public function __construct() {
		
		// Set handler to overide SESSION
		@session_set_save_handler(
			array($this, "_open"),
			array($this, "_close"),
			array($this, "_read"),
			array($this, "_write"),
			array($this, "_destroy"),
			array($this, "_gc")
		);
	}

	function _open()
	{
		// can we read database
		return true;
	}

	function _close()
	{
		return true;
	}

	function _read($ref)
	{
		
		$sql = "SELECT c_datavalue FROM dbsession WHERE c_reference = ?";
		$val = R::getCell($sql, [$ref]);
		if($val) {
			return $val;
		} else {
			return "";
		}
	}

	function _write($ref, $data)
	{
		
		$sql = "SELECT id FROM dbsession WHERE c_reference = ?";
		$id = R::getCell($sql, [$ref]);
		if($id) {
			$db = R::load("dbsession", $id);
		} else {
			$db = R::dispense("dbsession");
			$db->c_reference = $ref;
		}
		date_default_timezone_set('Europe/Madrid');	
		$dt = new DateTime(); 
		$db->c_access = $dt->format('Y-m-d H:i:s');
		$db->c_datavalue = $data;
		$result = R::store($db);
		if(is_numeric($result) === true) {
			return true;		
		} else {
			return false;
		}
	}

	function _destroy($ref)
	{
	  	$sql = "DELETE FROM dbsession WHERE c_reference = ?";
	    $result = R::exec($sql, [$ref]);
		if(is_numeric($result) === true) {
			return true;		
		} else {
			return false;
		}
	}

	/**
	 * Garbage Collection
	 */
	function _gc($max){
	  
	  	// Calculate what is to be deemed old
	  	$old = time() - $max;
	  	$sql = "DELETE * FROM dbsession WHERE c_access < ?";
	    $result = R::exec($sql, [$old]);
		if(is_numeric($result) === true) {
			return true;		
		} else {
			return false;
		}
	}

} // Class Ends

# alias +f+ class
if(!class_exists("S")){ class_alias('Session', 'S'); };