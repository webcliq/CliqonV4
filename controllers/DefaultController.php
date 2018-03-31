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
		// We can decide to set the default idiom statically from the config file	
		if($this->cfg['site']['setdefaultidiom'] == 'static') {
			$this->idiom = $this->cfg['site']['defaultidiom'];
		} else if($this->cfg['site']['setdefaultidiom'] == 'dynamic') {
			// Or get it dynamically
			$this->idiom = F::getDefLanguage();
		}
		$clq->set('idiom', $this->idiom);
		$clq->set('client', F::parseClient());
		$clq->set('js', '');
		$clq->set('lcd', $this->idiom);
		Z::zset('Langcd', $this->idiom);
		$this->page = "index";
		$cms = $clq->resolve('Cms');
		$mnu = $clq->resolve('Menu');
		$extn = $this->cfg['site']['extension'];

		// Load Template Engine - $vars only apply to outside template!!
		$tpl = new Engine(new FilesystemLoader($clq->get('basedir')."views"), $clq->get('basedir')."cache/".$this->idiom);		
		$template = $this->page.'.'.$extn;
		$vars = [
			'protocol' => $clq->get('protocol'),
			'rootpath' => $clq->get('rootpath'),
			'basedir' => $clq->get('basedir'),
			'viewpath' => $clq->get('rootpath').'views/',
			'includepath' => $clq->get('rootpath').'includes/',
			'languageoptions' => $cms->idiomOptions($this->idiom),
			'page' => $this->page,
			'cfg' => $this->cfg,
			'client' => $clq->get('client'),
			'idiom' => $this->idiom,
			'action' => $this->page,
			'jwt' => '{}',
			'scripts' => $clq->get('js')
		];	
		
		echo $tpl->render($template, $vars);
	}
}