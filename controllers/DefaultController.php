<?php
// Default Controller

class DefaultController
{
	public $thisclass = "DefaultController";
	private $cfg = [];
	private $idiom ;
	private $page;

	function get()
	{

		global $clq;
		$this->cfg = $clq->get('cfg');		
		$this->idiom = $this->cfg['site']['defaultidiom'];
		$clq->set('idiom', $this->idiom);
		$clq->set('js', '');
		$clq->set('lcd', $this->idiom);
		Z::zset('Langcd', $this->idiom);
		$this->page = "index";
		$cms = $clq->resolve('Cms');
		$mnu = $clq->resolve('Menu');

		// Load Template Engine 
		$tpl = new Engine(new FilesystemLoader($clq->get('basedir')."views"), $clq->get('basedir')."cache/".$this->idiom);		
		$template = $this->page.'.tpl';
		$vars = [
			'rootpath' => $clq->get('rootpath'),
			'basedir' => $clq->get('basedir'),
			'viewpath' => $clq->get('rootpath').'views/',
			'includepath' => $clq->get('rootpath').'includes/',
			'languageoptions' => $cms->idiomOptions($idiom),
			'page' => $this->page,
			'cfg' => $this->cfg,
			'idiom' => $this->idiom,
			'action' => $this->page,
			'jwt' => '{}',
			'scripts' => $clq->get('js')
		];	
		
		echo $tpl->render($template, $vars);
	}
}