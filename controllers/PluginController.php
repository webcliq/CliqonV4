<?php
// Plugin Controller

/**
* The ApiController assumes and permits Remote Cross Domain requests and responses
*
**/
loadFile('controllers/Controller.php');

class PluginController extends Controller
{
	public $thisclass = "PluginController extends Controller";
	private $cfg;
	private $idiom;
	private $page;
	private $table;
	private $tabletype;
	private $rq = [];

	/** Plugin page request
	 * Assumed to return a page
	 * @param - string - 2 char language code
	 * @param - string - action or Class method
	 * @param - string - table, defaults to dbcollection
	 * @param - string - tabletype (optional)
	 * @return - string HTML - webpage
	 ********************************************** Page Request *********************************************/

		function page_exec($idiom, $page, $table = "dbcollection", $tabletype = "")
		{
			global $clq; $clq->set('model', 'clean');
			$this->cfg = $clq->get('cfg');
			$clq->set('idiom', $idiom);	
			$clq->set('lcd', $idiom);
			$this->idiom = $idiom;
			$this->table = $table;
			$this->tabletype = $tabletype;	
			$this->rq = $_REQUEST;

			$vars = [
				'protocol' => $clq->get('protocol'),
				'rootpath' => $clq->get('rootpath'),
				'basedir' => $clq->get('basedir'),
				'includepath' => $clq->get('rootpath').'includes/',
				'table' => $this->table,
				'tabletype' => $this->tabletype,	
				'cfg' => $this->cfg,
				'idiom' => $this->idiom,
				'rq' => $this->rq
			];	

			if(!$_SESSION['UserName']) {
				$cms = $clq->resolve('Cms');	
				$this->page = 'login';
				$vars = array_merge($vars, [
					'viewpath' => $clq->get('rootdir').'admin/',
					'page' => 'login',
					'cmscontent' => $cms->login($this->idiom)		
				]);	

				// Load Template Engine 
				$tpl = new Engine(new FilesystemLoader($clq->get('basedir')."admin"), $clq->get('basedir')."admin/cache/".$idiom);
			} else {		

				$admin = $clq->resolve('Admin');
				$plugin = $clq->resolve('Plugin');
				$config = $clq->resolve('Config');
				$mnu = $clq->resolve('Menu');
				$token = array(); $token['id'] = $_SESSION['UserName'];
				method_exists($plugin, $page) ? $method = $page : $method = "plugindefault";

				$this->page = "admindesktop";
				$args = [
					'idiom' => $this->idiom,
					'table' => $this->table,
					'tabletype' => $this->tabletype,
					'rq' => $this->rq,
				];
				$vars2 = array(
	                'filename' => 'admin',         // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',   // If file, name of subdirectory
	                'type' => 'service',           // If database, value of c_type
	                'reference' => 'admin',        // If database, value of c_reference
	                'key' => ''
	            );
	            $admcfg = C::cfgRead($vars2);
	            $array = $plugin->$method($args); 
	            // also $array['callBack']
				$vars = array_replace($vars, [
					'viewpath' => $clq->get('rootdir').'admin/',
					'pluginpath' => $clq->get('basedir').'plugin/',
					'jwt' => F::encode($token, $this->cfg['site']['secret']),
					'page' => $this->page,
					'admincontent' => $array['content'],
					'scripts' => $clq->get('js'),
					'admcfg' => $admcfg,
					'navbrand' => $mnu->pubMenu(['type' => 'bootstrap4', 'subtype' => 'navbrand', 'view' => 'admin']),
					'topleftmenu' => $mnu->pubMenu(['type' => 'bootstrap4', 'subtype' => 'topleftmenu', 'view' => 'admin']),
					'toprightmenu' => $mnu->pubMenu(['type' => 'bootstrap4', 'subtype' => 'toprightmenu', 'view' => 'admin']),
					'leftsidemenu' => $mnu->pubMenu(['type' => 'bootstrap4', 'subtype' => 'leftsidemenu', 'view' => 'admin']),
					'rightsidemenu' => $mnu->pubMenu(['type' => 'bootstrap4', 'subtype' => 'rightsidemenu', 'view' => 'admin']),
					'footer' => $mnu->pubMenu(['type' => 'bootstrap4', 'subtype' => 'footer', 'view' => 'admin']),
				]);	

				// Load Template Engine 
				$tpl = new Engine(new FilesystemLoader($clq->get('basedir')."admin"), $clq->get('basedir')."admin/cache/".$idiom);
			}
		
			$template = $this->page.'.tpl';		
			echo $tpl->render($template, $vars);
		}

	/** Plugin All other requests are handled as API returning JSON or JSONP
	 * All Calls to this method must include JSON Web Token
	 * @param - string - 2 char language code
	 * @param - string - action or Class method
	 * @param - string - table, defaults to dbcollection
	 * @param - string - tabletype (optional)
	 * @return - array as JSON
	 ********************************************** Get Request *********************************************/

		protected function api_exec($idiom, $action, $table, $tabletype)
		{
			try {
				
				global $clq; 
				$admin = $clq->resolve('Admin');
				$plugin = $clq->resolve('Plugin');
				$lcd = $clq->set('idiom', $idiom);
				$clq->set('lcd', $idiom);
				$this->cfg = $clq->get('cfg');
				$rq = $this->inputs();

				// Introduce JWT security here
				$token = F::decode($rq['token'], $this->cfg['site']['secret'], false);
				if($token['id'] == "") {
					throw new Exception("Not a valid token!");
				} else if($token['id'] !== $_SESSION['UserName']) {
					throw new Exception("Not a valid user!");
				}; 

				method_exists($plugin, $action) ? $method = $action : $method = "plugindefault";
				$vars = [
					'idiom' => $idiom,
					'table' => $table,
					'tabletype' => $tabletype,
					'rq' => $rq,
				];
				$result = $plugin->$method($vars);

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
			return $this->page_exec($idiom, $action, $table, $tabletype);	
		}
		function get_xhr($idiom, $action, $table = "dbcollection", $tabletype= "") {
			global $clq; $clq->set('model', 'clean');
			return $this->api_exec($idiom, $action, $table, $tabletype);	
		}
		function post($idiom, $action, $table = "dbcollection", $tabletype= "") {
			global $clq; $clq->set('model', 'clean');
			return $this->page_exec($idiom, $action, $table, $tabletype);	
		}
		function post_xhr($idiom, $action, $table = "dbcollection", $tabletype= "") {
			global $clq; $clq->set('model', 'clean');
			return $this->api_exec($idiom, $action, $table, $tabletype);	
		}
}