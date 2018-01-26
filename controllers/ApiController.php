<?php
// Api Controller

/**
* The ApiController assumes and permits Remote Cross Domain requests and responses
*
**/
loadFile('controllers/Controller.php');

final class ApiController extends Controller
{
	public $thisclass = "ApiController";

	/** All API requests are handled as API returning JSON or JSONP
	 * All Calls to this method must include JSON Web Token
	 * @param - string - 2 char language code
	 * @param - string - action or Class method
	 * @param - string - table, defaults to dbcollection
	 * @param - string - tabletype (optional)
	 * @return - array as JSON
	 **/
	 protected function api_exec($idiom, $action, $table, $tabletype)
	 {
		try {
			global $clq; 
			$api = $clq->resolve('Api');
			$lcd = $clq->set('idiom', $idiom);
			$clq->set('lcd', $lcd);
			$cfg = $clq->get('cfg');
			$rq = $this->inputs();

			// Introduce JWT security here
			$token = F::decode($rq['token'], $cfg['site']['secret'], false);
			if($token['id'] == "") {
				throw new Exception("Not a valid token!");
			} else if(!Q::cUname($token['id'])) { // $_SESSION['UserName'] or User = False
				throw new Exception("Not a valid user!");
			};

			method_exists($api, $action) ? $method = $action : $method = "apidefault";
			$vars = [
				'idiom' => $idiom,
				'table' => $table,
				'tabletype' => $tabletype,
				'rq' => $rq,
			];
			$result = $api->$method($vars);

			// Development
			$msg = [
				'method' => $method,
				'table' => $table,
				'tabletype' => $tabletype,
				'idiom' => $idiom,
				'request' => $this->inputs()
			];
			// L::cLog($msg);

			if($result['callBack'] != "") {
				F::echoJsonp($result['content'], $result['callBack']);
			} else {
				F::echoJson($result['content']);
			}	

		} catch (Exception $e) {
			
			$err = [
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
		return $this->api_exec($idiom, $action, $table, $tabletype);
	}
	function get_xhr($idiom, $action, $table = "dbcollection", $tabletype= "") {
		global $clq; $clq->set('model', 'clean');
		return $this->api_exec($idiom, $action, $table, $tabletype);
	}
	function post($idiom, $action, $table = "dbcollection", $tabletype= "") {
		return $this->api_exec($idiom, $action, $table, $tabletype);	
	}
	function post_xhr($idiom, $action, $table = "dbcollection", $tabletype= "") {
		return $this->api_exec($idiom, $action, $table, $tabletype);	
	}

}