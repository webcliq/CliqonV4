<?php
// Proxy Controller

loadFile('controllers/Controller.php');
class ProxyController extends Controller
{
	public $thisclass = "ProxyController";

	/**
	 * AJAX Controller acting as Proxy to get an outside web page
	 *
	 * @param - array - Variables from Construct
	 * @return - string HTML content
	 **/	
	protected function proxy_exec()
	{
		
		global $clq; 
		$rq = $this->inputs();
		header("Access-Control-Allow-Origin: *");
		// File Name: proxy.php
		if (!isset($rq['url'])) {
		    die(); // Don't do anything if we don't have a URL to work with
		}
		// $url = urldecode($rq['url']);
		$url = 'http://' . str_replace('http://', '', $url); // Avoid accessing the file system
		return file_get_contents($url); // You should probably use cURL. The concept is the same though

	}

	function get() {
		return $this->proxy_exec();
	}
	function get_xhr() {
		return $this->proxy_exec();
	}
	function post() {
		return $this->proxy_exec();	
	}
	function post_xhr() {
		return $this->proxy_exec();	
	}
}