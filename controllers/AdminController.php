<?php
// Admin Controller

class AdminController
{
	public $thisclass = "AdminController";
	private $cfg;
	private $idiom;
	private $page;
	private $table;
	private $tabletype;
	private $rq = [];

	function admin_exec($idiom, $page, $table, $tabletype)
	{

		global $clq; $clq->set('model', 'clean');
		$this->cfg = $clq->get('cfg');
		$clq->set('idiom', $idiom);	
		$clq->set('lcd', $idiom);
		$clq->set('bingkey', $this->cfg['site']['bingkey']);
		$clq->set('gmapsapi', $this->cfg['site']['gmapsapi']);
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
			'rq' => $this->rq,
			'idioms' => $this->cfg['site']['idioms']
		];	

		if(!isset($_SESSION['UserName']) and empty($_SESSION['UserName'])) {

			$cms = $clq->resolve('Cms');	
			$this->page = 'login';
			$vars = array_merge($vars, [
				'viewpath' => $clq->get('rootdir').'admin/',
				'page' => 'login'	
			]);	

			// Load Template Engine 
			$tpl = new Engine(new FilesystemLoader($clq->get('basedir')."admin"), $clq->get('basedir')."admin/cache/".$idiom);
		} else {
			
			/*
			// Introduce JWT security here
			$token = F::decode($rq['token'], $cfg['site']['secret'], false);
			if($token['id'] == "") {
				throw new Exception("Not a valid token!");
			} else if($token['id'] !== $_SESSION['UserName']) {
				throw new Exception("Not a valid user!");
			};
			*/				

			$admin = $clq->resolve('Admin');
			$config = $clq->resolve('Config');
			$mnu = $clq->resolve('Menu');
			$token = array(); $token['id'] = $_SESSION['UserName'];

			$this->page = 'admindesktop';
			method_exists($admin, $page) ? $method = $page : $method = "dashboard";
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
			$vars = array_replace($vars, [
				'viewpath' => $clq->get('rootdir').'admin/',
				'jwt' => F::encode($token, $this->cfg['site']['secret']),
				'page' => $this->page,
				'admincontent' => $admin->$method($args),
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
		// or
		// $tpl->publishtpl($template, $vars);	
	}

	function get($idiom, $page, $table = "", $tabletype = "") {$this->admin_exec($idiom, $page, $table, $tabletype);}
	function get_xhr($idiom, $page, $table = "", $tabletype = "") {$this->admin_exec($idiom, $page, $table, $tabletype);}
	// Never exist
	function post() {}
	function post_xhr() {}

}

