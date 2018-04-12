<?php
/**
 * Frontend Blog Class
 * This Class is self contained and is used in different ways for different Sites
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

class Blog extends Db
{
	const THISCLASS = "Blog";
	private static $idioms;
	const CLIQDOC = "c_document";

	function __construct() 
	{
		global $clq;
		global $cfg;
		self::$idioms = $cfg['site']['idioms'];
	}

    /** Front end  
     *
     **************************************************************************************************************************/

        /** Display Blog  
         * @param - array - usual variables
         * @return - array - consisting of Flag (Ok or NotOk) and HTML content generated from template
         **/
         function displayBlog($vars)
         {

            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $rq = $vars['rq'];
                $idiom = $vars['idiom'];
                $table = $vars['table'];
                $this->cfg = $clq->get('cfg');
                $extn = $this->cfg['site']['extension'];
                $thisvars = ['rq' => $rq, 'idiom' => $idiom, 'idioms' => $clq->get('idioms'), 'viewpath' => $clq->get('rootpath').'views/'];
                $tpl = "blog.".$extn;
                $content = Q::publishTpl($tpl, $thisvars, "views/components", "cache/".$idiom);

                if($rq['search'] != 'false') {
                    $sql = "SELECT * FROM dbitem WHERE c_type = ? AND c_options LIKE ? ORDER BY c_reference";
                    $rawset = R::getAll($sql, ['blog', '%'.$rq['search'].'%']);          
                } else {
                    $sql = "SELECT * FROM dbitem WHERE c_type = ? ORDER BY c_reference";
                    $rawset = R::getAll($sql, ['blog']);                    
                }

                $db = $clq->resolve('Db');
                $rs = D::extractAndMergeRecordset($rawset);

                for($r = 0; $r < count($rs); $r++) {
                    $rs[$r]['d_date'] = Q::fDate($rs[$r]['d_date']);
                    $rs[$r]['d_author'] = Q::fList($rs[$r]['d_author'], 'operators');
                }          

                if(!is_array($rs)) {
                    throw new Exception("No recordsetas an array: ".$rs);
                } 

                $js = "
                    console.log('Blog data loaded');
                "; 

                $clq->set('js', $js);       
                return ['flag' => 'Ok', 'msg' => $content, 'data' => $rs];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'vars' => $vars,
                ];
                L::cLog($err);
                return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            } 
         }

    /** Administration
     *
     * blogform()
     * getBlogData
     * blogView()
     *
     **************************************************************************************************************************/

        /** Display Blog article insert and update form
         * @param - array - usual variables
         * @return - array - consisting of Flag (Ok or NotOk) and HTML content generated from template
         **/
         function blogForm($vars)
         {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $rq = $vars['rq'];
                $idiom = $vars['idiom'];
                $table = $vars['table'];
                $tabletype = $vars['tabletype'];
                $cfg = $clq->get('cfg');
                $idioms = $cfg['site']['idioms'];

                $rq['recid'] == 0 ? $action = 'Create Article' : $action = 'Update Article: '.$rq['recid'] ; 
                $model = $clq->resolve('Model'); 
                $dbcfg = $model->stdModel('form', $table, $tabletype);   

                // Top Buttons
                $topbuttons = Q::topButtons($dbcfg, $vars, 'blogform');
                unset($dbcfg['topbuttons']); 

                // Name of the Admin Component template which will be loaded from /admin/components/
                $tpl = "admblogform.tpl"; // This component uses Vue

                // Template variables these are used and converted by the template
                $thisvars = [
                    'table' => $table,
                    'tabletype' => $tabletype,
                    'topbuttons' => $topbuttons,
                    'idioms' => $idioms,
                    'action' => $action,
                    'formopts' => $dbcfg,
                    'xtrascripts' => "",
                    'status' => Q::cList('statustypes'),
                    'author' => Q::cList('operators'),
                    'group' => Q::cList('documenttypes')

                ];  

                // Sort out content of form
                if($rq['recid'] > 0) {

                    $sql = "SELECT * FROM dbitem WHERE id = ?";
                    $rawrow = R::getRow($sql, [$rq['recid']]);
                    $db = $clq->resolve('Db');
                    $row = D::extractAndMergeRow($rawrow);
                    $dbcfg['pageform']['fields'] = $row;
                } else {
                    unset($dbcfg['pageform']['fields']['d_title']);
                    unset($dbcfg['pageform']['fields']['d_description']);
                    unset($dbcfg['pageform']['fields']['d_text']);    

                    foreach($idioms as $lcdcode => $lcdname) {
                        $dbcfg['pageform']['fields']['d_title'][$lcdcode] = $lcdname;
                        $dbcfg['pageform']['fields']['d_description'][$lcdcode] = $lcdname;
                        $dbcfg['pageform']['fields']['d_text'][$lcdcode] = $lcdname;
                    }                                    
                }

                // Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
                $js = "    
                    Cliq.set('table', '".$table."');
                    Cliq.set('tabletype', '".$tabletype."');
                    Cliq.set('displaytype', 'blogform');
                    Cliq.set('formtype', 'pageform');
                    Cliq.set('idioms', ".object_encode($idioms).");
                    Cliq.set('langcd', '".$idiom."');
                    Cliq.set('lcd', '".$idiom."');
                    Cliq.set('recid', '".$rq['recid']."'); 
                    Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
                    Cliq.set('bingkey', '".$clq->get('bingkey')."');
                    console.log('Blog form loaded');
                    Cliqb.actionForm(".F::jsonEncode($dbcfg).");

                    $('#pill_common').tab('show');
                ";
                
                $set = [
                    'js' => $js,
                    'tpl' => $tpl,
                    'thisvars' => $thisvars
                ];

                return $set;

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'vars' => $vars,
                ];
                L::cLog($err);
                
                return $set['js'] = "Cliq.error('".$e->getMessage()."')";  
            }               
         }              

        /** Get Recordset, suitable for the blog listing which is a modified version of datatable 
         * 
         * $dgcfg = Q::cModel('datatable', $vars['table'], $vars['tabletype']);
         * @param - array - variables
         * @return - array - Recordset
         **/ 
         static function getBlogData(array $vars) 
         {
            $vars['service'] = 'blogarticle';
            $r = self::getPagedData($vars);
            $result = [
                'total' => $r['total'], 
                'rows' => $r['rows'],
                'offset' => $r['offset'],
                'limit' => $r['limit'], 
                'search' => $r['search'],           
                'query' => $r['sql']
            ];                      
            return $result;         
         }  

        /** View blog article 
         * @param - array - usual variables
         * @return - array - consisting of Flag (Ok or NotOk) and HTML content generated from template
         **/
         function blogView($vars)
         {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $rq = $vars['rq'];
                $idiom = $vars['idiom'];
                $table = $vars['table'];
                $tabletype = $vars['tabletype'];
                $cfg = $clq->get('cfg');
                $idioms = $cfg['site']['idioms'];

                $model = $clq->resolve('Model'); 
                $dbcfg = $model->stdModel('view', $table, $tabletype);   

                // Name of the Admin Component template which will be loaded from /admin/components/
                $tpl = "admblogview.tpl"; // This component uses Vue

                $sql = "SELECT * FROM dbitem WHERE id = ?";
                $rawrow = R::getRow($sql, [$rq['recid']]);
                $db = $clq->resolve('Db');
                $row = D::extractAndMergeRow($rawrow);

                $row['c_status'] = Q::fList($row['c_status'], 'statustypes');
                $row['c_category'] = Q::fList($row['c_category'], 'documenttypes');
                $row['d_author'] = Q::fList($row['d_author'], 'operators');
                $row['d_date'] = Q::fDate($row['d_date']);

                // Template variables these are used and converted by the template
                $thisvars = [
                    'table' => $table,
                    'tabletype' => $tabletype,
                    'idioms' => $idioms,
                    'row' => $row,
                    'lcd' => $idiom
                ];            

                $html = Q::publishTpl($tpl, $thisvars, "admin/components", "admin/cache");

                return ['flag' => 'Ok', 'html' => $html, 'title' => $row['d_title'][$idiom]];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'vars' => $vars,
                ];
                L::cLog($err);
                
                return ['flag' => 'NotOk', 'html' => $e->getMessage()];
            }      
         }         

	/** Blog API Calls 
	 * where Blog.Php acts like an API Service
	 *
	 * - loadlistingdata()
	 *
	 *****************************************************************************************************/

		/** Post Form to activate a User after registration
		 * @param - array of variables
		 * @return - array of message and content
		 **/
		function loadblogdata($vars)
		{
			return [
				'content' => self::getBlogData($vars),
				'callBack' => ""
			];
		}

} // Class Ends