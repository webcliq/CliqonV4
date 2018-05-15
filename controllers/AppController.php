<?php
// Apps Controller

final class AppController
{
	public $thisclass = "AppController";
	private $cfg;
	private $app;

	function get($app, $rq = "?")
	{

		global $clq;
		$this->cfg = $clq->get('cfg');	
		$this->app = $app;	

		if(isset($_SESSION['UserName']) and !empty($_SESSION['UserName'])) {
			switch($app) {
				case "adminer":
					header("Location: https://adminer.cliqon.net/");
				break;

				default:
					header("Location: ".$clq->get('rootdir')."apps/".$app."/index.php".$rq);
				break;
			}
			
		} else {
			die();
		}
	}

}