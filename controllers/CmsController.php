<?php
// CmsController

/**
* The CmsController only permits access from a web page invoked on the Webserver
*
**/
loadFile('controllers/Controller.php');

final class CmsController extends Controller
{
	public $thisclass = "CmsController";

	/** All AJAX requests are handled as an API returning JSON or JSONP
	 * All Calls to this method assume client is web based and local - no user security this Controller is for Front End SPA support only
	 * @param - string - 2 char language code
	 * @param - string - action or Class method
	 * @param - string - table, defaults to dbitem
	 * @param - string - tabletype (optional)
	 * @return - array as JSON
	 **/
	protected function ajax_exec($idiom, $action, $table = "dbitem", $tabletype = "")
	{
		try {
			
			global $clq; 
			$cms = $clq->resolve('Cms');
			$lcd = $clq->set('idiom', $idiom);
			$clq->set('lcd', $idiom);
			$rq = $this->inputs();

			if(!array_key_exists('UserName', $_SESSION)) {
				$_SESSION['UserName'] = "cliqonguest";
				$_SESSION['UserLevel'] = "20:20:20";
			}; 

			method_exists($cms, $action) ? $method = $action : $method = "ajaxdefault";
			$vars = [
				'idiom' => $idiom,
				'table' => $table,
				'tabletype' => $tabletype,
				'rq' => $rq,
			];
			$result = $cms->$method($vars);

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

	function get($idiom, $action, $table = "dbitem", $tabletype= "") {
		global $clq; $clq->set('model', 'clean');
		return $this->ajax_exec($idiom, $action, $table, $tabletype);
	}
	function get_xhr($idiom, $action, $table = "dbitem", $tabletype= "") {
		global $clq; $clq->set('model', 'clean');
		return $this->ajax_exec($idiom, $action, $table, $tabletype);
	}
	function post($idiom, $action, $table = "dbitem", $tabletype= "") {
		return $this->ajax_exec($idiom, $action, $table, $tabletype);	
	}
	function post_xhr($idiom, $action, $table = "dbitem", $tabletype= "") {
		return $this->ajax_exec($idiom, $action, $table, $tabletype);	
	}

}