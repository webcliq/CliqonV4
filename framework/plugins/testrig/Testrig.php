<?php
/**
 * Testrig Class - add your additions to this file
 * Ctrl K3 to fold
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Testrig
{

	const THISCLASS = "Testrig";

	function __construct()
	{
		global $clq;
	}

	function publish($vars) 
	{
		global $clq;
		$rq = $vars['rq'];
		$config = $clq->resolve('Config');
		$plcfg = $config->cfgReadFile('framework/plugins/testrig/config.cfg');

		$topbuttons = Q::topButtons($plcfg, $vars, 'testrig');
		unset($plcfg['topbuttons']);	

		// Javascript required by this method
		$js = "
			console.log('Test Rig Loaded');
			Cliq.set('displaytype', 'testrig');
			Cliq.testRig();
		"; 
		$clq->set('js', $js);	
		// Name of the Admin Component template which will be loaded from /admin/components/
		$tpl = "admtestrig.tpl"; // This component uses Vue		

		// Template variables these are used and converted by the template
		$thisvars = [
			'title' => Q::cStr('9999:Test Rig'),
			// 'table' => $rq['table'],
			// 'tabletype' => $rq['tabletype'],
			'topbuttons' => $topbuttons,
			// Set the Javascript into the system to be used at the base of admscript.tpl
			'xtrascripts' => ""
		];	

		return self::publishTpl($tpl, $thisvars);			
	}
				
	/** Additional Methods pages
	 * Plugin functions that display as component templates on a desktop page
	 *
	 * doTestRig()
	 * 
	 ********************************************************************************************************/

		function doTestRig(array $vars)
		{	
			try {
				$method = "doTestRig()";
				// Insert ACL here	

				$msg = "";		
				$thislcd = $clq->get('idiom');

				if(is_array($vars['rq'])) {
					$rq = $vars['rq'];
				} else {
					$error = "No request array";
					throw new Exception($error);
				}
				
				// Confirm upload of file and write to disk - solution is not right but it works
				if(isset($_FILES)) {
					$fn = $rq['filename'];  
					$fn = str_replace('.','_',$fn);
					$filename = $_FILES[$fn]['name'];
					if(!move_uploaded_file($_FILES[$fn]['tmp_name'], "tmp/".$filename)) {
						$error = "File not moved and written";
						throw new Exception($error);
					}
				} else {
					$error = "No input file";
					throw new Exception($error);
				};

				// Test
				$check = [
					'method' => self::THISCLASS.'->'.$method,
					'filename' => $filename,
					'files' => $_FILES,
					'request' => $rq
				];

				// Set to comment when completed
				// L::cLog($check);  
				
				// If not returned already 
				return ['flag' => 'Ok', 'result' => $check];                

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => self::THISCLASS.'->'.$method,
					'files' => $_FILES,
					'request' => $rq
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'msg' => $err]; 
			}				
		}


		/**
		 * Common Template publishing function
		 * 
		 * @param - string - name of template
		 * @param - array - array of data to be converted to JSON to accompany the template HTML
		 * @param - array - variables for the template that will be mounted on the template before it is converted to an HTML string
		 * @return - Array - Consisting of three elements - an Ok flag, Html as a string to be rendered into the ID Admin Content 
		 * and Data to be consumed by any Vue JS template functions
		 **/
		private function publishTpl($tpl, $vars)
		{
			// Template engine
	    	return Q::publishTpl($tpl, $vars, "framework/plugins/testrig", "framework/plugins/cache");

		}

} // Plugins Class ends

