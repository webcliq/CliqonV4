<?php
// AjaxController

/**
* The AjaxController only permits access from a web page invoked on the Webserver
*
**/
loadFile('controllers/Controller.php');

final class AjaxController extends Controller
{
	public $thisclass = "AjaxController";

	/** All AJAX requests are handled as an API returning JSON or JSONP
	 * All Calls to this method assume client is web based and local - only a valid Session User is required for security
	 * @param - string - 2 char language code
	 * @param - string - action or Class method
	 * @param - string - table, defaults to dbcollection
	 * @param - string - tabletype (optional)
	 * @return - array as JSON
	 **/
	protected function ajax_exec($idiom, $action, $table = "dbcollection", $tabletype = "")
	{
		try {
			
			global $clq; 
			$ajax = $clq->resolve('Ajax');
			$lcd = $clq->set('idiom', $idiom);
			$clq->set('lcd', $idiom);
			$rq = $this->inputs();

			if($action != 'login' && $action != 'logout') {if(!$_SESSION['UserName']) {
				throw new Exception("Not a valid user!");
			}}; 

			method_exists($ajax, $action) ? $method = $action : $method = "ajaxdefault";
			$vars = [
				'idiom' => $idiom,
				'table' => $table,
				'tabletype' => $tabletype,
				'rq' => $rq,
			];
			$result = $ajax->$method($vars);

			// Development
			$msg = [
				'method' => $method,
				'table' => $table,
				'tabletype' => $tabletype,
				'idiom' => $idiom,
				'request' => $this->inputs()
			];
			// L::log($msg);

			if($result['callBack'] != "") {
				F::echoJsonp($result['content'], $result['callBack']);
			} else {
				F::echoJson($result['content']);
			}	

		} catch (Exception $e) {
			
			$err = [
				'flag' => 'NotOk',
				'errmsg' => $e->getMessage(),
				'action' => $action,
				'table' => $table,
				'tabletype' => $tabletype,
				'idiom' => $idiom,
				'request' => $this->inputs()
			];
			L::cLog($err);
			F::echoJson($err);
		}		
	}

	function get($idiom, $action, $table = "dbcollection", $tabletype= "") {
		global $clq; $clq->set('model', 'clean');
		return $this->ajax_exec($idiom, $action, $table, $tabletype);
	}
	function get_xhr($idiom, $action, $table = "dbcollection", $tabletype= "") {
		global $clq; $clq->set('model', 'clean');
		return $this->ajax_exec($idiom, $action, $table, $tabletype);
	}
	function post($idiom, $action, $table = "dbcollection", $tabletype= "") {
		return $this->ajax_exec($idiom, $action, $table, $tabletype);	
	}
	function post_xhr($idiom, $action, $table = "dbcollection", $tabletype= "") {
		return $this->ajax_exec($idiom, $action, $table, $tabletype);	
	}



}