<?php
/**
 * Plugin methods for Cliqon
 Ctrl K3 to fold
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@cliqon.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

class Plugin extends Admin
{
	const THISCLASS = "Plugin extends Admin";
	const CLIQDOC = "c_document";
	private static $thisclass = "Plugin";

	function __construct() 
	{

	}

	/** Callable API Functions
	 * Deal with JSONP and non JSONP 
	 * All Use JSON Web Token
	 *
	 * plugindefault()
	 * getsoftware()
	 * getsoftwaredetails()
	 *
	 **************************************** Plugin API Functions ********************************************/

		function plugindefault($vars)
		{
			global $clq;
			return [
				'content' => ['flag' => 'Ok', 'msg' => self::$thisclass],
				'callBack' => ""
			];			
		}


}
