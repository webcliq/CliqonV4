<?php
/**
 * Ajax methods for Cliq
 * Deal with only non JSONP 
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@cliqon.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

class Ajax
{

	private static $thisclass = "Ajax";

	public function __construct() 
	{
		global $cfg;
		global $clq;
	}

	/**
	 * Callable Ajax Functions
	 * 
	 *
	 **************************************** Internal AJAX derived API Functions *********************************/

		function ajaxdefault($vars)
		{
			return [
				'content' => "",
				'callBack' => ""
			];				
		}

		function login($vars)
		{
			// table == dbuser, tabletype == "", $rq == username, password
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->login($vars['rq']),
				'callBack' => ""
			];				
		}

		/**
		 * Logout 
		 * @param - Request string
		 * @return - Template and initial data
		 **/
		function logout($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->logout(),
				'callBack' => ""
			];	
		}

		/**
		 * Either does a test of the import or does a live import of language variables
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function doidiomtemplatedownload($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->doIdiomTemplateDownload($vars),
				'callBack' => ""
			];
		}

		/**
		 * Either does a test of the convert or does a live convert
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function doconvertarray($vars)
		{
			
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->doConvertArray($vars),
				'callBack' => ""
			];
		}

		/**
		 * Does a test of the array in total
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function dotestarray($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->doTestArray($vars),
				'callBack' => ""
			];
		}

		/**
		 * Either does a test of the convert or does a live convert
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function dolcdimport($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->doIdiomImport($vars),
				'callBack' => ""
			];	
		}		

		/** Add a new option on the fly, to dropdown list
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function addnewoption($vars)
		{
			global $clq;
			$db = $clq->resolve('Db');
			return [
				'content' => $db->addNewOption($vars),
				'callBack' => ""
			];	
		}	

		/**
		 * Add new language to whole site
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function addnewidiom($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->addIdiom($vars),
				'callBack' => ""
			];	
		}		

		/**
		 * Delete existing language
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function deleteidiom($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->deleteIdiom($vars),
				'callBack' => ""
			];
		}

		/**
		 * Either does a test of the import of does a live import
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function doimportdata($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->doImportData($vars),
				'callBack' => ""
			];
		}

		/**
		 * File Upload from immediate Drag and Drop form
		 * @param - array - Variables from Construct
		 * @return - JSON - Message
		 **/	
		function fileupload($vars)
		{		
			// Get header "subdir"
			global $clq;

			// initialize FileUploader
		    $fu = new Fileupload('file', array(
		        'limit' => null,
		        'maxSize' => null,
				'fileMaxSize' => null,
		        'extensions' => null,
		        'required' => false,
		        'uploadDir' => $clq->get('basedir').$_SERVER['HTTP_SUBDIR'],
		        'title' => 'name',
				'replace' => false,
		        'listInput' => true,
		        'files' => null
		    ));
			
			// call to upload the files
			return [
				'content' => $data,
				'callBack' => ""
			];
		}

		/**
		 * Clear Admin and Front end Caches
		 * function uses addition function called glob_recursive which is in /includes/functions/
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function clearcache($vars = [])
		{
		    try {
				
				global $clq;
				$dir = $clq->get('basedir')."cache";
				foreach (glob_recursive($dir."/*.*") as $filename) {
				    if (is_file($filename)) {
				        unlink($filename);
				    }
				};

				$dir = $clq->get('basedir')."admin/cache";
				foreach (glob_recursive($dir."/*.*") as $filename) {
				    if (is_file($filename)) {
				        unlink($filename);
				    }
				};

				return ['content' => ['flag' => 'Ok', 'msg' => ''], 'callBack' => ''];
		    } catch(Exception $e) {
		    	return ['content' => ['flag' => 'NotOk', 'msg' => $e->getMessage()], 'callBack' => ''];
		    }
		}

		/**
		 * Delete all Log files
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function clearlogs($vars = [])
		{
		    try {
		    	global $clq;
				$dir = $clq->get('basedir')."log";
				foreach (glob($dir."/*.*") as $filename) {
				    if (is_file($filename)) {
				        unlink($filename);
				    }
				};
				return ['content' => ['flag' => 'Ok', 'msg' => ''], 'callBack' => ''];
		    } catch(Exception $e) {
		    	return ['content' => ['flag' => 'NotOk', 'msg' => $e->getMessage()], 'callBack' => ''];
		    }		
		}

		/**
		 * Create a file and return true or false
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function createfile($vars)
		{	
			global $clq;
			$fl = $clq->resolve('Files');
			return [
				'content' => ['flag' => 'Ok', 'data' => Y::creatFile($vars['rq']['filepath'])],
				'callBack' => ""
			];			
		}

		/**
		 * Open and read a file
		 *
		 * @param - array - Variables from Construct
		 * @return - string - File contents
		 **/	
		function openfile($vars)
		{	
			global $clq;
			$fl = $clq->resolve('Files');
			$tomlmap = Y::readFile($vars['rq']['filepath']);
			// $tomlmap = preg_replace("/\t/", " ", $tomlmap); // tabs with spaces
	        // $tomlmap = preg_replace("/\s+/", " ", $tomlmap); // Multiple spaces with single space
			$tomlmap = preg_replace("/\r\n/", "\n", $tomlmap); // Carriage return and newline (not respected by CodeEditor display) with just Newline

			return [
				'content' => ['flag' => 'Ok', 'data' => $tomlmap],
				'callBack' => ""
			];
		}

		/**
		 * Create a file if it doesn't already exists and write content to it
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function writefile($vars)
		{	
			global $clq;
			$fl = $clq->resolve('Files');
			return [
				'content' => ['flag' => 'Ok', 'data' => Y::writeFile($vars['rq']['filepath'], $vars['rq']['content'])],
				'callBack' => ""
			];
		}

		/**
		 * Delete an existing file
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function deletefile($vars)
		{	
			global $clq;
			$fl = $clq->resolve('Files');
			return [
				'content' => ['flag' => 'Ok', 'data' => Y::deleteFile($vars['rq']['filepath'])],
				'callBack' => ""
			];
		}

		/**
		 * Rename an existing file
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/	
		function renamefile($vars)
		{	
			global $clq;
			$fl = $clq->resolve('Files');
			return [
				'content' => ['flag' => 'Ok', 'data' => Y::renameFile($vars['rq']['oldfilepath'], $vars['rq']['newfilepath'])],
				'callBack' => ""
			];
		}

		/**
		 * Write altered Javascript strings back to their respective files
		 *
		 * @param - array - Variables from Construct
		 * @return - string - Message
		 **/
		function postadminjstrings($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->writeAdminJStrings($vars),
				'callBack' => ""
			];	
		}

		function fileeditor($vars)
		{
			global $clq; $fi = $clq->resolve('Files');
			return [
				'content' => $fi->displayFileEditor($vars),
				'callBack' => ""
			];		
		}

		function tomlconverter($vars)
		{
			global $clq; $fi = $clq->resolve('Files');
			return [
				'content' => $fi->displayFiles($vars),
				'callBack' => ""
			];		
		}

		function dotomlconvert($vars)
		{
			global $clq; $fi = $clq->resolve('Files');
			return [
				'content' => $fi->convertFile($vars),
				'callBack' => ""
			];		
		}
		
		function dofilesdownload($vars)
		{
			global $clq; $adm = $clq->resolve('Admin');
			return [
				'content' => $adm->doFilesDownload($vars),
				'callBack' => ""
			];		
		}

		function dofilescopy($vars)
		{
			global $clq; $adm = $clq->resolve('Admin');
			return [
				'content' => $adm->doFilesCopy($vars),
				'callBack' => ""
			];		
		}

		function getsitemap($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getSiteMap($vars),
				'callBack' => ""
			];		
		}

		function postsitemap($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->setSiteMap($vars),
				'callBack' => ""
			];		
		}

		function dictionaryedit($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->dictionaryEdit($vars),
				'callBack' => ""
			];	
		}

		function dictionarycopy($vars)
		{
			global $clq;
			$adm = $clq->resolve('Admin');
			return [
				'content' => $adm->dictionaryCopy($vars),
				'callBack' => ""
			];	
		}

		function restorerecord($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->restoreRecord($vars),
				'callBack' => ""
			];	
		}

		function getfields($vars)
		{
			global $clq; $rep = $clq->resolve('Report');
			return [
				'content' => $rep->getFields($vars),
				'callBack' => ""
			];				
		}

		/**
		 * Serverside translation call
		 *
		 **/
		function translate($vars)
		{
			global $clq; $adm = $clq->resolve('Admin');
			return [
				'content' => $adm->getTranslation($vars),
				'callBack' => ""
			];				
		}

		/**
		 * Get single row
		 *
		 **/
		function getdata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => D::getRowData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in grid format
		 *
		 **/
		function getgriddata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getGridData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in table format
		 *
		 **/
		function gettabledata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getTableData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in list format
		 *
		 **/
		function getlistdata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getListData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in tree format for jqTree
		 *
		 **/
		function gettreedata($vars)
		{
			$vars['type'] = 'reload';
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getTreeData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in cards format
		 *
		 **/
		function getcarddata($vars)
		{
			$vars['type'] = 'reload';
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getCardData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in tree format for Gijgo tree
		 *
		 **/
		function getgjtreedata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getGjTreeData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in tree format
		 *
		 **/
		function treenodedrop($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->treeNodeDrop($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get recordset in calendar format
		 *
		 **/
		function getcalendardata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getCalendarData($vars),
				'callBack' => ""
			];	
		}	

		function getform($vars)
		{
			global $clq; $frm = $clq->resolve('Form');
			return [
				'content' => $frm->publishForm($vars),
				'callBack' => ""
			];	
		}

		function getformletdata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->formletData($vars),
				'callBack' => ""
			];
		}		

		function getnextref($vars)
		{
			global $clq; $db = $clq->resolve('Db'); 
			return [
				'content' => $db->getNextRef($vars),
				'callBack' => ""
			];
		}

		function isunique($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->isUnique($vars),
				'callBack' => ""
			];
		}

		function bootcomplete($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getAutoCompleteData($vars),
				'callBack' => ""
			];
		}

		function postform($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->postForm($vars),
				'callBack' => ""
			];
		}	

		function postvalue($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->postValue($vars),
				'callBack' => ""
			];
		}	

		function viewrecord($vars)
		{
			global $clq; $vw = $clq->resolve('View');
			return [
				'content' => $vw->viewContent($vars),
				'callBack' => ""
			];		
		}

		function deleterecord($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->deleteRecord($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Delete records from the Archive table before a certain date
		 * @param - array - usual parameters
		 **/
		function deletebefore($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->deleteBefore($vars),
				'callBack' => ""
			];	
		}

		/**
		 * 
		 *
		 * @param - array - Variables from Construct
		 * @return - string - 
		 **/
		function modeleditor($vars)
		{
			global $clq; $mdl = $clq->resolve('Model');
			return [
				'content' => $mdl->displayModelEditor($vars),
				'callBack' => ""
			];		
		}

		/**
		 * 
		 *
		 * @param - array - Variables from Construct
		 * @return - string - 
		 **/
		function modelwrite($vars)
		{
			global $clq; $mdl = $clq->resolve('Model');
			return [
				'content' => $mdl->writeModel($vars),
				'callBack' => ""
			];	
		}

		/**
		 * 
		 *
		 * @param - array - Variables from Construct
		 * @return - string - 
		 **/
		function modelview($vars)
		{
			global $clq; $mdl = $clq->resolve('Model');
			return [
				'content' => $mdl->viewModel($vars),
				'callBack' => ""
			];	
		}		

		/**
		 * 
		 *
		 * @param - array - Variables from Construct
		 * @return - string - 
		 **/
		function modeldelete($vars)
		{
			global $clq; $mdl = $clq->resolve('Model');
			return [
				'content' => $mdl->deleteModel($vars),
				'callBack' => ""
			];		
		}

		function gethelp($vars)
		{
			global $clq; $vw = $clq->resolve('View');
			return [
				'content' => $vw->displayHelp($vars),
				'callBack' => ""
			];			
		}

		function viewcontent($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->displayContent($vars),
				'callBack' => ""
			];			
		}

		function editcontent($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->editContent($vars),
				'callBack' => ""
			];			
		}
		function editcode($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->editCode($vars),
				'callBack' => ""
			];			
		}

		function savecontent($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->saveContent($vars),
				'callBack' => ""
			];			
		}

		function savecode($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->saveCode($vars),
				'callBack' => ""
			];			
		}

		function getreport($vars)
		{
			global $clq; $rpt = $clq->resolve('Report');
			return [
				'content' => $rpt->getReport($vars),
				'callBack' => ""
			];			
		}

		function printreport($vars)
		{
			global $clq; $rpt = $clq->resolve('Report');
			return [
				'content' => $rpt->displayReport($vars),
				'callBack' => ""
			];			
		}

		function displayimages($vars)
		{
			global $clq; $rpt = $clq->resolve('Report');
			return [
				'content' => $rpt->displayImages($vars),
				'callBack' => ""
			];			
		}

		function listcompanies($vars)
		{
			global $clq; $dir = $clq->resolve('BusinessDirectory');
			return [
				'content' => $dir->listCompanies($vars),
				'callBack' => ""
			];			
		}	

		/** Change Password
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

		function changeuserstatus($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->changeStatus($vars),
				'callBack' => ""
			];
		}

		function dochangeuserstatus($vars)
		{
			global $clq;
			$auth = $clq->resolve('Auth');
			return [
				'content' => $auth->doChangeStatus($vars),
				'callBack' => ""
			];
		}

		/**
		 * Get recordset in grid format for the Record Creator generic grid
		 *
		 **/
		function getrecorddata($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->getRecordData($vars),
				'callBack' => ""
			];	
		}

		/**
		 * Get form for the Record Creator generic edit template
		 *
		 **/
		function getcreatorform($vars)
		{
			global $clq; $frm = $clq->resolve('Form');
			return [
				'content' => $frm->publishCreatorForm($vars),
				'callBack' => ""
			];		
		}

		/**
		 * Get form for the Record Creator generic edit template
		 *
		 **/
		function postcreatorform($vars)
		{
			global $clq; $db = $clq->resolve('Db');
			return [
				'content' => $db->saveCreatorRecord($vars),
				'callBack' => ""
			];	
		}	

		// Always Last
		protected function get_status_message(){
			$status = array(
						100 => 'Continue',  
						101 => 'Switching Protocols',  
						200 => 'OK',  
						201 => 'Created',  
						202 => 'Accepted',  
						203 => 'Non-Authoritative Information',  
						204 => 'No Content',  
						205 => 'Reset Content',  
						206 => 'Partial Content',  
						300 => 'Multiple Choices',  
						301 => 'Moved Permanently',  
						302 => 'Found',  
						303 => 'See Other',  
						304 => 'Not Modified',  
						305 => 'Use Proxy',  
						306 => '(Unused)',  
						307 => 'Temporary Redirect',  
						400 => 'Bad Request',  
						401 => 'Unauthorized',  
						402 => 'Payment Required',  
						403 => 'Forbidden',  
						404 => 'Not Found',  
						405 => 'Method Not Allowed',  
						406 => 'Not Acceptable',  
						407 => 'Proxy Authentication Required',  
						408 => 'Request Timeout',  
						409 => 'Conflict',  
						410 => 'Gone',  
						411 => 'Length Required',  
						412 => 'Precondition Failed',  
						413 => 'Request Entity Too Large',  
						414 => 'Request-URI Too Long',  
						415 => 'Unsupported Media Type',  
						416 => 'Requested Range Not Satisfiable',  
						417 => 'Expectation Failed',  
						500 => 'Internal Server Error',  
						501 => 'Not Implemented',  
						502 => 'Bad Gateway',  
						503 => 'Service Unavailable',  
						504 => 'Gateway Timeout',  
						505 => 'HTTP Version Not Supported');
			return ($status[$this->_code])?$status[$this->_code]:$status[500];
		}

}
