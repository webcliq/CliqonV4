<?php
/**
 * Api methods for Cliq
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@cliqon.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

/**
	Deal with JSONP and non JSONP 
	All Use JSON Web Token

**/
class Api
{

	private static $thisclass = "Api";

	/** Callable API Functions
	 * getnewuserform()
	 * getpasswordresetform()
	 * valueexists()
	 * register()
	 * activate()
	 * lostpassword()
	 * resetpassword()
	 *
	 *
	 **************************************** API Functions ******************************************************/

		/** Display a simple user signup form
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function getnewuserform($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->publishNewUserForm($vars),
				'callBack' => ""
			];				
		}

		/** Display a simple for to arrange to reset a password
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function getpasswordresetform($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->forgotPassword($vars),
				'callBack' => ""
			];
		}

		/** Checks to see if a value exists in dbuser
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function valueexists($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->valueExists($vars),
				'callBack' => ""
			];
		}

		/***********  Action forms  *****************************************************/

		/** Post Form to register a new user
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function register($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->userRegister($vars),
				'callBack' => ""
			];
		}

		/** Post Form to activate a User after registration
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function activate($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->userActivate($vars),
				'callBack' => ""
			];
		}

		/** Reset password after request to reset
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function lostpassword($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->identifyUser($vars),
				'callBack' => ""
			];
		}

		/** Reset password after request to reset
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function resetpassword($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->changeUserPassword($vars),
				'callBack' => ""
			];
		}

	/** RESTful API services support
	 *
	 * apidefault()
	 * apilogin()
	 * apilogout()
	 *
	 **************************************** API Service Functions *********************************************/

		/** Default or generic call to Apiservices Class
		 * saves the bother of having to write lots of methods in this Class
		 * @param - array - all the arguments, including the REQUEST
		 * @return - array - which the controller encodes as JSONP
		 * @todo - work out the Callback
		 **/
		 function apidefault($vars)
		 {
			global $clq;
			$api = $clq->resolve('Apiservices');
			$method = $vars['action'];
			return [
				'content' => $api->$method($vars),
				'callBack' => ""
			];				
		 }

		/** Login  
		 * 
		 * @param - Request string
		 * @return - string - JSON data - Ok or NotOk with message
		 **/
		 function apilogin($vars)
		 {
			// table == dbuser, tabletype == "", $rq == username, password
			global $clq;
			$api = $clq->resolve('Apiservices');
			return [
				'content' => $api->apilogin($vars['rq']),
				'callBack' => ""
			];				
		 }

		/** Logout  
		 * 
		 * @param - Request string
		 * @return - string - JSON data - clears everything down
		 **/
		 function apilogout($vars)
		 {
			global $clq;
			$api = $clq->resolve('Apiservices');
			return [
				'content' => $api->apilogout(),
				'callBack' => ""
			];	
		 }


}