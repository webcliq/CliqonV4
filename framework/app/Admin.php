<?php
/**
 * Administration system Class
 Ctrl K3 to fold
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Admin 
{
	const THISCLASS = "Admin";
	protected static $idioms;
	const CLIQDOC = "c_document";

	function __construct() 
	{
		global $clq;
		global $cfg;
		self::$idioms = $cfg['site']['idioms'];
	}

	/** Basic Admin Pages 
	 *
	 * page()
	 * plugin()
	 * reportdesigner()
	 * sitedesign() // Hopefully using Grape.Js
	 * recordcreator() - generic record management
	 *
	 *************************************************************************************************************/

		/** Page
		 * Action will be "page", so the template, in which all the programming must be included will 
		 * contain the name of the Template as a request variable named "action"
		 * @param - array - usual collection of variables as an array
		 * @return - HTML string 
		 **/
		 function page($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				// Buttons required by this method which will be printed to the right of Breadcrumb row
	            $args = array(
	                'filename' => $rq['action'],        // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            		// If database, value of c_type
	                'reference' => $rq['action'],       // If database, value of c_reference
	                'key' => ''
	            );
	            $pcfg = C::cfgRead($args);
			    $topbuttons = Q::topButtons($pcfg, $vars, 'page');

			    $js = "";
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					// Set the Javascript into the system to be used at the base of admscript.tpl
					'xtrascripts' => ""
				];		

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = 'adm'.$rq['action'].".tpl"; // This component uses Vue	

			    if(array_key_exists('class', $rq)) {
			    	$method = $rq['action'];
			    	$class = $clq->resolve($rq['class']);
			    	$set = $class->$method($vars);
			    	$js = $set['js'];
			    	$tpl = $set['tpl'];
			    	$thisvars = $set['thisvars'];
			    }

				// Javascript required by this method
				$clq->set('js', $js);

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }

		/** Plugin
		 * Method will be "plugin", so $vars->table will contain action 
		 * @param - array - usual collection of variables as an array
		 * @return - HTML string 
		 **/
		 function plugin(array $vars)
		 {
			try {
				
				global $clq;
				$action = $vars['table'];
				// Case sensitive and /Reflection not working here !!
				$file = ucfirst($action);
				loadFile("/framework/plugins/$action/$file.php");
				$plugin = new $file();
				return $plugin->publish($vars);

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }

		/** Calls reportGenerator()
		 * @param - array - usual collection of variables as an array
		 * @return - HTML string 
		 **/
		 function reportdesigner(array $vars)
		 {
			global $clq;	
			$r = $clq->resolve('Report');
			return $r->reportDesigner($vars);	
		 }

		/** Javascript driven site page designer
		 * pages stored as templates or sections in the database
		 * @param - array - usual array of variables
		 * @return - all the HTML and Jacvascript to implement Grape.Js
		 **/
		 function sitedesign(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];
					
				// Define standard datacard config
				$model = $clq->resolve('Model'); 
				$dcfg = $model->stdModel('sitedesign', $table, $tabletype);

				// Modify $dcfg

				$topbuttons = Q::topButtons($dcfg, $vars, 'sitedesign');
		    	unset($dcfg['topbuttons']);	

				$thisvars = [
					'table' => $table,
					'content' => 'Site Designer',
					'topbuttons' => $topbuttons, 
					'tabletype' => $tabletype, 
					'xtrascripts' => ''
				];

				$js = "
					console.log('Sitedesign JS loaded');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'sitedesign');
			        Cliq.set('formtype', 'popupform');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");

					\$script(['/includes/js/grapes.min.js'], 'dtbundle');
					\$script.ready('dtbundle', function() {
						var editor = grapesjs.init({
					      	container : '#sitedesigner',
					      	height: '95%',
					      	components: '<div class=\"container-fluid\"><div class=\"card\"><h1 class=\"pad\">Header</h1></div></div>',
					  	});
					});

				";
			    $clq->set('js', $js);
			

				// Vars = template, data and template variables
				$tpl = "admsitedesign.tpl";

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
				];

				// Set to comment when completed
				L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'html' => $e->getMessage()];
	        } 
		 }

		/** Generic record manager
		 * Displays a grid of data for a table and facilitates the creation of a record for the table or editing
		 * @param - array - usual array of variables
		 * @return - Javascript and Variables for the Template
		 **/
		 function recordcreator(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
				$idiom = $vars['idiom'];
				$dgcfg = [
					'primaryKey' => 'id',
					'autoLoad' => true,
					'uiLibrary' => 'bootstrap4',
					'iconsLibrary' => 'fontawesome',
					'headerFilter' => ['type' => 'onchange'],
					'fontSize' => '14px',
					'pager' => [
						'limit' => 15,
						'rightControls' => false,
						'sizes' => [10,15,20]
					],
					'dataurl' => '/ajax/'.$idiom.'/getrecorddata/',
					'toolbarTemplate' => H::div(['class' => 'row', 'style' => 'height: 32px;'],
						H::div(['class' => 'col-7 text-left'],
							H::button(['class' => 'btn btn-sm btn-primary mr5 gridbutton', 'type' => 'button', 'data-action' => 'changetable', 'data-table' => 'dbcollection'], 'dbcollection'),
							H::button(['class' => 'btn btn-sm btn-primary mr5 gridbutton', 'type' => 'button', 'data-action' => 'changetable', 'data-table' => 'dbitem'], 'dbitem'),
							H::button(['class' => 'btn btn-sm btn-primary mr5 gridbutton', 'type' => 'button', 'data-action' => 'changetable', 'data-table' => 'dbtransaction'], 'dbtransaction')
						),
						H::div(['data-role' => 'title', 'id' => 'gridtitle', 'class' => 'col-5 text-right h4'])
					),
					'notFoundText' => '144:No records for this table available',
					'columns' => [
						['field' => 'id', 'order' => 'a', 'title' => '9999:Id', 'width' => 45, 'align' => 'right', 'cssClass' => 'bold', 'filterable' => false, 'sortable' => false],
						['field' => 'c_type', 'order' => 'b', 'title' => '128:Type', 'width' => 120, 'align' => '', 'cssClass' => '', 'filterable' => true, 'sortable' => true],
						['field' => 'c_reference', 'order' => 'c', 'title' => '5:Reference', 'width' => 120, 'align' => '', 'cssClass' => '', 'filterable' => true, 'sortable' => true],
						['field' => 'c_common', 'order' => 'z', 'title' => '6:Common', 'align' => '', 'cssClass' => '', 'filterable' => false, 'sortable' => false]
					],
					'rowicons' => [
						'editrecordce' => [
							'icon' => 'pencil',
							'formid' => 'columnform',
						],
						'deleterecord' => [
							'icon' => 'trash'
						]
					]
				];
				$dgcfg['topbuttons'] = [
					'addbuttonce' => [
						'class' => 'danger',
						'icon' => 'plus',
						'title' => '100:Add'
					]
				];

		    	$dgcfg['notFoundText'] = Q::cStr($dgcfg['notFoundText']);
		    	
				// Extend columns for language
				foreach($dgcfg['columns'] as $n => $col) {
					$dgcfg['columns'][$n]['title'] = Q::cStr($col['title']);
				}
				$dgcfg['locale'] = $idiom;

				$rowicons = '';
				foreach($dgcfg['rowicons'] as $i => $icn) {
					
					$href = [
						'data-action' => $i, 
						'href' => '#',
						'onclick' => 'Cliq.rowButton(this); return false;'
					];
					$icnarray = [
						'class' => 'fa fa-fw fa-'.$icn['icon'].' bluec'
					];
					array_key_exists('formid', $icn) ? $icnarray['data-formid'] = $icn['formid'] : null;
					$rowicons .= H::a($href, H::i($icnarray)); unset($icnarray); unset($href);
				};
				$rowmnu = H::span(['class' => 'nowrap'], $rowicons);
				unset($dgcfg['rowicons']);
				$numcols = count($dgcfg['columns']);
				$dgcfg['columns'][$numcols] = [
					'width' => 50,
					'align' => 'right',
					'filterable' => 'false',
					'tmpl' => $rowmnu,
				];

			    $topbuttons = Q::topButtons($dgcfg, $vars, 'recordcreator');
		    	unset($dgcfg['topbuttons']);	
		    			    
				$dataurl = $dgcfg['dataurl'];
				unset($dgcfg['dataurl']);
				$dgcfg['dataSource'] =[
					'url' => $dataurl,
					'data' => false
				];

			    $js = "
			        Cliq.set('dataurl', '".$dataurl."');
			        Cliq.set('displaytype', 'recordcreator');
			        Cliq.set('formtype', 'columnform');
			        Cliq.set('reporttype', 'popupreport');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
	
					gj.grid.messages['".$idiom."'] = ".self::gridMsgs().";	    	
			    	Cliq.datagrid(".F::jsonEncode($dgcfg).");
			    ";
			    $clq->set('js', $js);
			
				// Vars = template, data and template variables
				$tpl = "admrecordcreator.tpl";

				$thisvars = [
					'content' => 'Record management',
					'topbuttons' => $topbuttons, 
					'xtrascripts' => '',
					'admdatagrid' => H::div(['class' => 'mb10'], H::table(['id' => 'datagrid', 'class' => 'table table-striped table-hover table-condensed table-no-bordered table-sm table-responsive'])),
					'admresults' => H::div(['id' => 'columnform'], Q::cStr('363:Results'))
				];				

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
				];

				// Set to comment when completed
				L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'html' => $e->getMessage()];
	        } 
		 }

	/** Dashboard
	 *
	 * dashboard()
	 * 
	 * getDashBoard()
	 * doDashBoard()
	 *
	 *************************************************************************************************************/

		/** Dashboard 
		 * Provides content for admin page Dashboard.tpl
		 * @param - array - usual collection of variables as an array
		 * @return - HTML string 
		 **/
		 function dashboard(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
				$idiom = $vars['idiom'];
	            $args = array(
	                'filename' => 'admdashboard',       // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'admdashboard',      // If database, value of c_reference
	                'key' => ''
	            );
	            $config = $clq->resolve('Config');
	            $dcfg = C::cfgRead($args);					

	            // Icons
				$icncfg = [];
				foreach($dcfg['icons'] as $i => $icon) {
					$icncfg[$i] = $icon;
					$icncfg[$i]['tooltip'] = Q::cStr($icon['tooltip']);	
				};

	            // Panels come from database - each one shall generate a template that the template and Vue will consume
	            $sql = "SELECT * FROM dbcollection WHERE c_type = ? ORDER BY c_order ASC";
	            $rawset = R::getAll($sql, ['admdashboard']);
	            $db = $clq->resolve('Db');
				$rs = D::extractAndMergeRecordset($rawset);
				
				$panelcfg = [];
				for($r = 0; $r < count($rs); $r++) {
					$panel = [];
					$panel['title'] = $rs[$r]['d_text'][$idiom];
					$panel['options'] = C::cfgReadString($rs[$r]['c_options']);
					$panel['reference'] = $rs[$r]['c_reference'];
					$panel['recid'] = $rs[$r]['id'];
					$cardid = $rs[$r]['c_reference'];
					$panelcfg[$cardid] = $panel; unset($panel);
				}

				// Create any Javascript
				$js = "
					Cliq.set('table', 'dbcollection');
			        Cliq.set('tabletype', 'admdashboard');
			        Cliq.set('displaytype', 'dashboard');
			        Cliq.set('formtype', 'popupform');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
					// Insert Vue routines here 
					var options = {
						idioms: ".object_encode($clq->get('idioms')).",
						icons: ".object_encode($icncfg).",
						panels: ".object_encode($panelcfg)."
					};
					Cliqd.dbDisplay(options);
				";

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admdashboard.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = ['admdashboard' => $dcfg];			

				// Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
				global $clq; $clq->set('js', $js);

				// Test
				$test = [
					'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }

		/** Dashboard routines that respond to AJAX GET requests
		 * 
		 * @param - array - usual collection of variables as an array
		 * @return - array for conversion to JSON
		 **/
		 public static function getDashBoard(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
				$idiom = $vars['idiom'];


				return ['flag' => 'Ok', 'msg' => ''];

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
	        } 
	     }

		/** Dashboard routines that respond to AJAX POST requests 
		 * 
		 * @param - array - usual collection of variables as an array
		 * @return - array for conversion to JSON
		 **/
		 public static function doDashBoard(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
				$idiom = $vars['idiom'];


				return ['flag' => 'Ok', 'msg' => ''];

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
	        } 
	     }

	/** Data Display pages 
	 *
	 * datagrid()
	 * datatree()	 
	 * datatable()
	 * datalist()	 
	 * datacard()
	 * calendar()
	 * gallery()
	 * blogarticle()
	 *
	 ********************************************************************************************************/

		/** Datagrid
		 * Displays a Gijgo Table of Data
		 * @param - set of parameters
		 * @return - JSON - Options
		 **/
		 function datagrid($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];
					
				$model = $clq->resolve('Model'); 
				$dgcfg = $model->stdModel('datagrid', $table, $tabletype);
		    	$dgcfg['notFoundText'] = Q::cStr($dgcfg['notFoundText']);
		    	$dgcfg['title'] = Q::cStr($dgcfg['title']);
		    	
				// Extend columns for language
				foreach($dgcfg['columns'] as $n => $col) {
					$dgcfg['columns'][$n]['title'] = Q::cStr($col['title']);
				}
				$dgcfg['locale'] = $idiom;

				$rowicons = '';
				foreach($dgcfg['rowicons'] as $i => $icn) {
					
					$href = [
						'data-action' => $i, 
						'href' => '#',
						'onclick' => 'Cliq.rowButton(this); return false;'
					];
					$icnarray = [
						'class' => 'fa fa-fw fa-'.$icn['icon'].' bluec'
					];
					array_key_exists('formid', $icn) ? $icnarray['data-formid'] = $icn['formid'] : null;
					$rowicons .= H::a($href, H::i($icnarray)); unset($icnarray); unset($href);
				};
				$rowmnu = H::span(['class' => 'nowrap'], $rowicons);
				unset($dgcfg['rowicons']);
				$numcols = count($dgcfg['columns']);
				$dgcfg['columns'][$numcols] = [
					'width' => 80,
					'align' => 'right',
					'filterable' => 'false',
					'tmpl' => $rowmnu,
				];

			    $topbuttons = Q::topButtons($dgcfg, $vars, 'datagrid');
		    	unset($dgcfg['topbuttons']);	
		    			    
				$dataurl = $dgcfg['dataurl'];
				unset($dgcfg['dataurl']);
				$dgcfg['dataSource'] =[
					'url' => $dataurl,
					'data' => false
				];


			    $js = "
			        Cliq.set('dataurl', '".$dataurl."');
			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'datagrid');
			        Cliq.set('formtype', 'columnform');
			        Cliq.set('reporttype', 'popupreport');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
	
					gj.grid.messages['".$idiom."'] = ".self::gridMsgs().";	    	
			    	Cliq.datagrid(".F::jsonEncode($dgcfg).");
			    ";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admdatagrid.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					'admdatagrid' => H::div(['class' => 'mb10'], H::table(['id' => 'datagrid', 'class' => 'table table-striped table-hover table-condensed table-no-bordered table-sm table-responsive'])),
					'admresults' => H::div(['id' => 'columnform'], Q::cStr('363:Results')),
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }

		/** Datatree
		 * Displays a jqTree or a Gijgo Tree
		 * @param - set of parameters
		 * @return - JSON - Options
		 **/
		 function datatree($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				// Testing
				// L::log('Table: '.$table.', Tabletype: '.$tabletype);	
					
				$model = $clq->resolve('Model'); 
				$dtcfg = $model->stdModel('datatree', $table, $tabletype);

				// Modify $dtcfg

			    $topbuttons = Q::topButtons($dtcfg, $vars, 'datatree');
		    	unset($dtcfg['topbuttons']);	

		    	// Generate block of icons
	        	$icons = "";
	        	foreach($dtcfg['icons'] as $action => $icn) {
	        		$icons .= H::i(['class' => 'right treeicon fa fa-fw fa-'.$icn['icon'], 'data-toggle' => 'tooltip', 'title' => Q::cStr($icn['tooltip']), 'style' => 'vertical-align: top; margin-top: 6px;', 'data-action' => $action]);
	        	};

	        	if($dtcfg['treetype'] == 'gjtree') {
					$treeopts = [
						'autoLoad' => $dtcfg['autoLoad'],
						'primaryKey' => 'id',
						'dataSource' => $dtcfg['dataurl'],
						'iconsLibrary' => $dtcfg['iconsLibrary'],
						'uiLibrary' => $dtcfg['uiLibrary'],
						// 'width' => 150 // $dtcfg['width'],
					];
	        	} else if($dtcfg['treetype'] == 'jqtree') {
					$treeopts = [
						'dataurl' => $dtcfg['dataurl'],
						'icons' => $icons
					];	
	        	} else {
	        		throw new Exception("Which tree to use not correctly specified");
	        	};

				$js = "
			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'datatree');
			        Cliq.set('treetype', '".$dtcfg['treetype']."');
			        Cliq.set('formtype', 'columnform');
			        Cliq.set('reporttype', 'popupreport');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

			    	Cliq.datatree(".F::jsonEncode($treeopts).");
				";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admdatatree.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'admlw' => $dtcfg['leftcolwidth'],
					'admrw' => $dtcfg['rightcolwidth'],
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					'admdatatree' => H::div(['id' => 'datatree'], " Loading tree ...."),
					'admresults' => H::div(['id' => 'columnform'], Q::cStr('363:Results')),
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }			

		/** Datatable
		 * Provides template and data for a Table layout
		 * @param - array - variables
		 * @return - array(html, data)
		 * @internal 
		 **/
		 public function datatable(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];
					
				$model = $clq->resolve('Model'); 
				$dtcfg = $model->stdModel('datatable', $table, $tabletype);   

				// Expand and Adjust
				$advsearch = "";

				// Order columns by order
				foreach($dtcfg['columns'] as $key => $config) {
					if(array_key_exists('visible', $config) and $config['visible'] == 'false') {
						unset($dtcfg['columns'][$key]);
						if(!array_key_exists('order', $config)) {
						$dtcfg['columns'][$key]['order'] = 'z';
					}}
				};
				$dtcfg['columns'] = Q::array_orderby($dtcfg['columns'], 'order', SORT_ASC);		

				foreach($dtcfg['columns'] as $fid => $prop) {
					$dtcfg['columns'][$fid]['title'] = Q::cStr($prop['title']);
					array_key_exists('titleTooltip', $prop) ? $dtcfg['columns'][$fid]['titleTooltip'] = Q::cStr($prop['titleTooltip']) : null ;
				};

				// Top Buttons
			    $topbuttons = Q::topButtons($dtcfg, $vars, 'datatable');
		    	unset($dtcfg['topbuttons']);	

		    	// Row icons
				foreach($dtcfg['rowicons'] as $i => $icn) {
					array_key_exists('title', $icn) ? $dtcfg['rowicons'][$i]['title'] = Q::cStr($icn['title']) : $dtcfg['rowicons'][$i]['title'] = "" ;
					array_key_exists('formid', $icn) ? $dtcfg['rowicons'][$i]['formid'] = $icn['formid'] : $dtcfg['rowicons'][$i]['formid'] = "popupform" ;
				};

				// Format pager select
				// pageselect = '5,10,15,20,25'
				$dtcfg['pagerselect'] = [];           
                $pageselect = explode(',', $dtcfg['pageselect']);
				foreach($pageselect as $n => $v) {
					$v = trim($v);
					$dtcfg['pagerselect'][] = ['value' => $v, 'text' => $v];
				};

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admdatatable.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					'tblopts' => $dtcfg,
					'xtrascripts' => ""
				];	

				unset($dtcfg['id']);
				unset($dtcfg['tableclass']);

				// Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
			    $js = "
			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'datatable');
			        Cliq.set('formtype', 'popupform');
			        Cliq.set('reporttype', 'popupreport');
   					Cliq.set('idioms', ".object_encode(self::$idioms).");
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."'); 
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

			    	Cliq.datatable(".F::jsonEncode($dtcfg).");
			    ";
			    
			    $clq->set('js', $js);

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }

		/** Datalist
		 * Provides template and data for a Bootstrap list layout
		 * @param - array - variables
		 * @return - array(html, data)
		 * @internal 
		 **/
		 public function datalist(array $vars)
		 {
			
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {
	
				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				// Testing
				// L::log('Table: '.$table.', Tabletype: '.$tabletype);	
					
				$model = $clq->resolve('Model'); 
				$dlcfg = $model->stdModel('datalist', $table, $tabletype);

			    $topbuttons = Q::topButtons($dlcfg, $vars, 'datalist');
		    	unset($dlcfg['topbuttons']);		

				// Format pager select
				// pageselect = '5,10,15,20,25'
				$dlcfg['pagerselect'] = [];           
				$pageselect = explode(',', $dlcfg['pageselect']);
				foreach($pageselect as $n => $v) {
					$v = trim($v);
					$dlcfg['pagerselect'][] = ['value' => $v, 'text' => $v];
				};

				$thisvars = [
					'table' => $table,
					'topbuttons' => $topbuttons, 
					'tabletype' => $tabletype, 
					'tblopts' => $dlcfg,
					'xtrascripts' => ''
				];

				$js = "
					console.log('Datalist JS loaded');

			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'datalist');
			        Cliq.set('formtype', 'columnform');
			        Cliq.set('reporttype', 'popupreport');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

			    	Cliq.datalist(".F::jsonEncode($dlcfg).");
				";
			    $clq->set('js', $js);
			
				// Vars = template, data and template variables
				$tpl = "admdatalist.tpl";

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
					'processed_list_definition' => $dlcfg
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
					// 'rs' => $rs,
					'processed_listdefinition' => $dlcfg
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'html' => $e->getMessage()];
	        }
		 }		

		/** Datacard
		 * Provides template and data for a Card layout
		 * @param - array - variables
		 * @return - array(html, data)
		 * @internal 
		 **/
		 public function datacard(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];
					
				// Define standard datacard config
				$model = $clq->resolve('Model'); 
				$dcfg = $model->stdModel('datacard', $table, $tabletype);

				// Modify $dcfg

				$topbuttons = Q::topButtons($dcfg, $vars, 'datacard');
		    	unset($dcfg['topbuttons']);	
				unset($dcfg['cardicons']);

				// Extend Options for datacard
				$dcfg['options']['title'] = Q::cStr($dcfg['title']);

				$thisvars = [
					'table' => $table,
					'topbuttons' => $topbuttons, 
					'tabletype' => $tabletype, 
					'xtrascripts' => ''
				];

				$db = $clq->resolve('Db');
				$opts = [
					'data' =>  $db->getCardData($vars),
					'options' => $dcfg['options']
				];

				$js = "
					console.log('Datacard JS loaded');

			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'datacard');
			        Cliq.set('formtype', 'popupform');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

			    	$('.cardbutton').on('click', function(e) {
			    		e.preventDefault(); e.stopImmediatePropagation();
			    		Cliq.topButton(this);
			    	});	 

			    	Cliq.datacard(".object_encode($opts).");
				";
			    $clq->set('js', $js);
			

				// Vars = template, data and template variables
				$tpl = "admdatacard.tpl";

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
					'processed_card_definition' => $dcfg
				];

				// Set to comment when completed
				L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
					// 'rs' => $rs,
					'processed_card_definition' => $dcfg
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'html' => $e->getMessage()];
	        } 
		 }

		/** Gallery
		 * Provides template and data for a Gallery layout
		 * @param - array - variables
		 * @return - array(html, data)
		 * @internal 
		 **/
		 public function gallery(array $vars)
		 {

			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				// Testing
				// L::log('Table: '.$table.', Tabletype: '.$tabletype);	
					
				$model = $clq->resolve('Model'); 
				$gcfg = $model->stdModel('gallery', $table, $tabletype);

			    $topbuttons = self::topButtons($gcfg, $vars, 'gallery');
		    	unset($gcfg['topbuttons']);		

		    	$gallery = H::div(['id' => 'gallery'],
		    		H::div(['class' => 'col-lg-3 col-md-4 col-xs-6 card', 'v-for' => '(img, key) in admimages'],
		    			H::div(['class' => 'card-block', 'style' => 'padding: 10px 4px 6px 4px;'],
			    			H::h5(['class' => 'card-text fit'],'{{img.c_reference}} - {{img.c_common}}'),
			    			H::h6(['class' => 'card-text fit bluec'],'{{img.d_title}}'),
			    			H::a(['href' => '#', 'class' => 'h-100'],
			    				H::img(['class' => 'img-fluid img-thumbnail', 'v-bind:src' => 'img.d_image', 'v-bind:alt' => 'img.d_image', 'v-bind:title' => 'img.d_title'])
			    			),
			    			H::div(['class' => 'card-text'],'{{img.d_description}}'),
			    			H::div(['class' => 'card-text'],
			    				H::span(['class' => 'right'], 
			    					H::i(['class' => 'fa fa-fw fa-lg fa-pencil pointer', 'v-on:click' => 'editRecord($event, img)']),
			    					H::i(['class' => 'fa fa-fw fa-lg fa-trash pointer', 'v-on:click' => 'deleteRecord($event, img)'])
			    				),
			    				H::span(['class' => 'left redc capitalize bold'], '{{img.c_category}}')
			    			)
			    		)
		    		)
		    	);   

				$db = $clq->resolve('Db');
				$opts = [
					'data' =>  $db->getGalleryData($vars),
					'options' => $gcfg
				];

				// Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
			    $js = "
			    	console.log('Gallery JS Loaded');

			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'gallery');
			        Cliq.set('formtype', 'popupform');
			        Cliq.set('reporttype', 'popupreport');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms)."); 
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

					\$script(['/includes/js/galleria.js'], 'dtbundle');
					\$script.ready('dtbundle', function() {
						Cliq.gallery(".F::jsonEncode($opts).");
					});

			    ";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admgallery.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					'gallery' => $gallery,
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 		
		 }	

		/** Calendar
		 * Provides template and data for a Dhtmlxcalendare
		 * 
		 * @param - array - variables
		 * @return - array(html, data)
		 * @internal - needs changing to Full calendar
		 **/
		 public function calendar(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {
				
				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];
					
				$model = $clq->resolve('Model'); 
				$ccfg = $model->stdModel('calendar', $table, $tabletype); 

				$topbuttons = Q::topButtons($ccfg, $vars, 'calendar');
		    	unset($ccfg['topbuttons']);	

				// Extend and translate Calendar Config
				$ccfg['xtra']['title'] = Q::cStr($ccfg['xtra']['title']);
				$ccfg['xtra']['description'] = Q::cStr($ccfg['xtra']['description']);

				// Labels for the popup event form
				foreach($ccfg['locale']['labels'] as $key => $lbl) {
					$ccfg['locale']['labels'][$key] = Q::cStr($lbl);
				}

				// Extend Event categories
				foreach($ccfg['config']['lightbox']['sections'] as $n => $opts) {
					if(array_key_exists('options', $opts)) {

						unset($ccfg['config']['lightbox']['sections'][$n]['options']); // stop it being a string - stops problems in the future to do with strict variable type handling
						$ccfg['config']['lightbox']['sections'][$n]['options'] = []; // recreate it as an array
						$cats = Q::cList($opts['options']);
						
						foreach($cats as $key => $label) {
							$pair = [];
							$pair['key'] = $key;
							$pair['label'] = $label; // maybe an array
							$ccfg['config']['lightbox']['sections'][$n]['options'][] = $pair; unset($pair);
						}
					}
				}

				$calendar = H::div(['id' => 'admincalendar', 'class' => 'dhx_cal_container', 'style' => 'width:100%; height:100%;' ],
					H::div(['class' => 'dhx_cal_navline'],

						// Space for a couple of extra buttons
						H::div(['class' => 'dhx_cal_export pdf', 'id' => 'export_pdf', 'title' => Q::cStr('346:Export to PDF')], '<button type="button" class="btn btn-sm btn-default">PDF</button>'),						

						H::div(['class' => 'dhx_cal_prev_button'], '&nbsp;'),
						H::div(['class' => 'dhx_cal_next_button'], '&nbsp;'),
						H::div(['class' => 'dhx_cal_today_button']),
						H::div(['class' => 'dhx_cal_date']),
						H::div(['class' => 'dhx_cal_tab', 'name' => 'day_tab', 'style' => 'right:204px;']),
						H::div(['class' => 'dhx_cal_tab', 'name' => 'week_tab', 'style' => 'right:140px;']),
						H::div(['class' => 'dhx_cal_tab', 'name' => 'month_tab', 'style' => 'right:76px;'])
					),
					H::div(['class' => 'dhx_cal_header pad h40']),
					H::div(['class' => 'dhx_cal_data'])
				);

				// Vars = template, data and template variables
				$tpl = "admcalendar.tpl";
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'calendar' => $calendar,
					'topbuttons' => $topbuttons,
					'xtrascripts' => ""
				];	

				// Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
			    $js = "
			    	console.log('Calendar JS Loaded');

			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'calendar');
			        Cliq.set('formtype', 'popupform');
			        Cliq.set('reporttype', 'popupreport');
			        Cliq.set('idioms', ".object_encode(self::$idioms)."); 
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

					\$script(['/includes/js/dhtmlxscheduler.js'], 'dtbundle');
					\$script.ready('dtbundle', function() {
						Cliq.calendar(".F::jsonEncode($ccfg).");
					});
			    	
			    ";
			    $clq->set('js', $js);

				// Test
				$test = [
					'method' => self::THISCLASS.'->'.$method,
					'vars' => $vars,
					'processed_calendar_definition' => $ccfg
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.$method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
					'processed_calendar_definition' => $calcfg
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'html' => $e->getMessage(), 'data' => []];
	        } 			
		 }

		/** Blog articles (variation on a Datatable)
		 * Provides template and data for a Blog layout
		 * @param - array - variables
		 * @return - array(html, data)
		 * @internal 
		 **/
		 public function blogarticle(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];
					
				$model = $clq->resolve('Model'); 
				$dtcfg = $model->stdModel('blogarticle', $table, $tabletype);   

				// Expand and Adjust
				$advsearch = "";

				// Order columns by order
				foreach($dtcfg['columns'] as $key => $config) {
					if(array_key_exists('visible', $config) and $config['visible'] == 'false') {
						unset($dtcfg['columns'][$key]);
						if(!array_key_exists('order', $config)) {
						$dtcfg['columns'][$key]['order'] = 'z';
					}}
				};
				$dtcfg['columns'] = Q::array_orderby($dtcfg['columns'], 'order', SORT_ASC);		

				foreach($dtcfg['columns'] as $fid => $prop) {
					$dtcfg['columns'][$fid]['title'] = Q::cStr($prop['title']);
					array_key_exists('titleTooltip', $prop) ? $dtcfg['columns'][$fid]['titleTooltip'] = Q::cStr($prop['titleTooltip']) : null ;
				};

				// Top Buttons
			    $topbuttons = Q::topButtons($dtcfg, $vars, 'blogarticle');
		    	unset($dtcfg['topbuttons']);	

		    	// Row icons
				foreach($dtcfg['rowicons'] as $i => $icn) {
					array_key_exists('title', $icn) ? $dtcfg['rowicons'][$i]['title'] = Q::cStr($icn['title']) : $dtcfg['rowicons'][$i]['title'] = "" ;
					array_key_exists('formid', $icn) ? $dtcfg['rowicons'][$i]['formid'] = $icn['formid'] : $dtcfg['rowicons'][$i]['formid'] = "pageform" ;
				};

				// Format pager select
				// pageselect = '5,10,15,20,25'
				$dtcfg['pagerselect'] = [];           
                $pageselect = explode(',', $dtcfg['pageselect']);
				foreach($pageselect as $n => $v) {
					$v = trim($v);
					$dtcfg['pagerselect'][] = ['value' => $v, 'text' => $v];
				};

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admdatatable.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					'tblopts' => $dtcfg,
					'xtrascripts' => ""
				];	

				unset($dtcfg['id']);
				unset($dtcfg['tableclass']);

				// Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
			    $js = "
			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'blogarticle');
			        Cliq.set('formtype', 'pageform');
   					Cliq.set('idioms', ".object_encode(self::$idioms).");
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."'); 
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

			    	Cliq.datatable(".F::jsonEncode($dtcfg).");
			    ";
			    
			    $clq->set('js', $js);

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }

	/** Administrative pages that deal with Import and Export 
	 * Administrative functions that display as component templates on a desktop page
	 *
	 * convertarray()
	 * - doTestArray()
	 * - doConvertArray()
	 * importdata()
	 * - doImportData()
	 * exportdata()
	 * - doExport()
	 * maintainidiom()
	 * - doIdiomImport()
	 * - doIdiomTemplateDownload()
	 * deleteIdiom()
	 *
	 ********************************************************************************************************/

		/** Convert array 
		 * Displays a form facility to convert a given config file containing data such as strings or list into records
		 * 
		 * This is a technical facility that should appear in Development mode only
		 * it visualises that data for a given table and tabletype has been created in Cliqon Cfg format
		 * and this is to be imported into the database. Not to be confused with a CSV import
		 * 
		 * @param - array - variables
		 * @return - array(html, data)
		 * 
		 **/
		 function convertarray($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];
			    $tplbuttons = ""; 

				// inputNode.value = fileInput.value.replace("C:\\fakepath\\", "");	
				
				// Get list arrays of tables and table types to populate the selects
				$model = $clq->resolve('Model');
				$tbls = $model->get_tables();
				$tbltypes = $model->get_tabletypes();	

				// Javascript required by this method
				$js = "
					console.log('Convert Array JS Loaded');
					Cliq.set('displaytype', 'convertarray');
					var options = {
						idioms: ".json_encode($clq->get('idioms')).", 
						tables: ".json_encode($tbls).",
						tabletypes: ".json_encode($tbltypes)."
					};
					Cliq.convertArray(options);
				";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admconvertarray.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'title' => Q::cStr('83:Convert Array'),
					'tablebuttons' => $tplbuttons,
					// Set the Javascript into the system to be used at the base of admscript.tpl
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }	

			function doTestArray(array $vars)
			{
				try {
					// Insert ACL here	

					global $clq;
					$msg = "";		
					$rq = $vars['rq'];
					$filename = "";
					$res = []; $model = []; $test = []; $written = "";
					
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
						$error = "No input files";
						throw new Exception($error);
					};
					
					// Read file
					$strarray = C::cfgReadFile('tmp/'.$filename);
					if(!is_array($strarray)) {
						$error = "Input file did not create usable array";
						throw new Exception($error);
					}							
					
					// Test
					$check = [
						'method' => self::THISCLASS.'->'.__FUNCTION__,
						'filename' => $filename,
						'files' => $_FILES
					];

					// Set to comment when completed
					L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'result' => $strarray];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => self::THISCLASS.'->'.__FUNCTION__,
						'filename' => $filename,
						'files' => $_FILES
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}					
			}
							
			function doConvertArray(array $vars)
			{
				try {
					// Insert ACL here	

					global $clq;
					$msg = "";		
					$thislcd = $clq->get('idiom');
					$filename = "";
					$rq = $vars['rq'];
					$res = []; $model = []; $tbl = $vars['table']; $testarray = []; $written = "";

					if(is_array($vars['rq'])) {
						array_key_exists('dbwrite', $rq) ? $dbwrite = $rq['dbwrite'] : $dbwrite = "off";
					} else {
						$error = "No request array";
						throw new Exception($error);
					}
					
					// Confirm upload of file and write to disk - solution is not right but it works
					if(isset($_FILES)) {
						$fn = "undefined"; // $rq['filename'];  // works but needs fixing
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
					
					// Read file
					$strarray = C::cfgReadFile('tmp/'.$filename);
					if(!is_array($strarray)) {
						$error = "Input file /tmp/".$filename." did not create usable array";
						throw new Exception($error);
					}
					 	
					// Get model record for defaults etc.
					$model = Q::cModel('fields', $tbl, $vars['tabletype']);
					if(!is_array($model)) {
						$error = "Fields Model for ".$tbl."/".$vars['tabletype']." not created";
						throw new Exception($error);
					}
					
					// Walk through the input array
					foreach($strarray as $q => $row) {

						$ref = $row['c_reference'];
						$recid = $row['id'];

						// If Id does not exists, then it will be created as new record
						// Thus when doing an insert, set the Id to a non-existent Record Id so that the array
						// will parse but a new record will be created

						$db = R::load($tbl, $recid);

						// Logically all the fields both physical (c_) and virtual (d_) should exist in the model, so that we have a type to go by
	 
						$rqc = []; $rqd = []; $submit = []; $doc = []; $test = []; 
						foreach($row as $fld => $val) {
							$chk = strtolower(substr($fld, 0, 2));	
							switch($chk) {
								case "c_": $rqc[$fld] = $val; break;
								case "d_": $rqd[$fld] = $val; break;	
								case "aj": case "id": case "x_": false; break;	// throws ajaxbuster, id and x away
								default: throw new Exception("Request key had no usable starting letters! - ".$chk." - ".$fld);
							}
						};

						// Send $vals for formatting
						foreach($rqc as $fldc => $valc) {
							$props = $model[$fldc];
							if(self::dbFormat($fldc, $tbl, $recid, $valc, $props) != false) {
								$submit[$fldc] = self::dbFormat($fldc, $tbl, $recid, $valc, $props);
							}
						}
					
						// Send $doc for formatting
						foreach($rqd as $fldd => $vald) {
							$props = $model[$fldd];
							if(self::dbFormat($fldd, $tbl, $recid, $vald, $props) != false) {
								$doc[$fldd] = self::dbFormat($fldd, $tbl, $recid, $vald, $props);
							}
						}
					
						// call up the existing record if it exists
						$sql = "SELECT c_document FROM ".$tbl." WHERE id = ?";
						$existing = json_decode(R::getCell($sql, [$recid]), true);
						
						// Replace
						if(is_array($existing) && count($existing) > 0) {
							$doc = array_replace($existing, $doc);
						}
						

						$submit['c_document'] = json_encode($doc);
						
						// Run through the array of records that will be written to the database									
						foreach($submit as $flds => $vals) {
							$test[$flds] = $vals;
							$db->$flds = $vals;
						}		
											
						// Save a new record to the database if a live import
						if($dbwrite == "on") {

							// R::debug(true);
							$result = R::store($db);
							// R::debug(false);

							if(!is_numeric($result)) {
								throw new Exception("No result has been created!");
							} else {

								// If written successfully
								if(count($result) > 0) {

									$sql = "SELECT * FROM ".$tbl." WHERE id = ?";
									$res = R::getRow($sql, [$result]);	
									
									$text = "Array Conversion: ".$result;
									$notes = "Tablename: ".$tbl.", ID: ".$result.", Ref: ".$ref."<br />Request - ".json_encode($submit, JSON_PRETTY_PRINT);
									L::wLog($text, 'dbwrite', $notes);	

								} else {
									$written = "Not Written";
								}								
				
							}			
							
						};

						$testarray[] = $test;		

						unset($rqc); unset($rqd); unset($submit); unset($doc); unset($test);

					} // Completes foreach loop			
			
					// Generate a Test or process result
					$html = array_slice($testarray, 0, 5);		

					// Test
					$check = [
						'method' => self::THISCLASS.'->'.__FUNCTION__,
						'filename' => $filename,
						// 'files' => $_FILES,
						// 'model' => $model,
						'written' => $written,
						// 'test' => $test,
						'result' => $res,
					];

					// Set to comment when completed
					// L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'result' => $html];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => self::THISCLASS.'->'.__FUNCTION__,
						'filename' => $filename,
						'rq' => $rq,
						'files' => $_FILES[$fn],
						'model' => $model,
						'result' => $res,
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}					
			}

		/** Import data 
		 * Populates the popup screen for any CSV Import dialogue
		 * This is a technical facility that should appear in Development mode only
		 * it visualises that data for a given table and tabletype has been created in Cliqon Cfg format
		 * and this is to be imported into the database. Not to be confused with a CSV import
		 *
		 * @param - array - Variables from Construct
		 * @return - string - HTML for the popup
		 **/	
		 function importdata(array $vars)
		 {
			
			try {
				
				// inputNode.value = fileInput.value.replace("C:\\fakepath\\", "");	
				
				// Get list arrays of tables and table types to populate the selects
				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];

				$model = new Model();
				$tbls = $model->get_tables();
				$tbltypes = $model->get_tabletypes();	
				
				$js = "
					console.log('Import Data JS loaded');
					Cliq.set('displaytype', 'importdata');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
					var options = {
						idioms: ".json_encode($clq->get('idioms')).", 
						tables: ".json_encode($tbls).",
						tabletypes: ".json_encode($tbltypes)."
					};
					Cliq.importData(options);
				";
				$clq->set('js', $js);
				$tpl = "admimportdata.tpl";
				$thisvars = ['title' => Q::cStr('75:Import CSV File to Database'), 'xtrascripts' => ""];
				return self::publishTpl($tpl, $thisvars);
	        } catch(Exception $e) {
	            return ['flag' => 'NotOk', 'html' => $e->getMessage(), 'data' => $data];
	        }
		 }
		
			// Data import mechanism
			function doImportData(array $vars)
			{
				try {
					global $clq;

					// Insert ACL here	

					$msg = "";		
					$thislcd = $clq->get('idiom');
					$filename = ""; $rq = $vars['rq'];
					$res = []; $model = []; $tbl = $vars['table']; $testarray = []; $written = "";

					if(is_array($vars['rq'])) {
						array_key_exists('dbwrite', $rq) ? $dbwrite = $rq['dbwrite'] : $dbwrite = "off";
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

					// Read file
					$header = $vars['rq']['header']; $strarray = []; $ll = (int)$vars['rq']['longestline']; // maybe n();
				    if (($handle = fopen('tmp/'.$filename, 'r')) !== FALSE) {
				        while (($row = fgetcsv($handle, $ll, $vars['rq']['delimiter'], $vars['rq']['encloser'], $vars['rq']['escape'])) !== FALSE) {
				            if(!$header) {
				                $header = $row;
				            } else {
				                $strarray[] = array_combine($header, $row);
				            }
				        }
				        fclose($handle);
					} else {
						$error = "Could not open CSV file for reading";
						throw new Exception($error);
					};

					if(!is_array($strarray)) {
						$error = "Input file /tmp/".$filename." did not create usable array";
						throw new Exception($error);
					} 	
					
					// Get model record for defaults etc.
					$model = Q::cModel($tbl, $vars['tabletype'], 'fields');
					if(!is_array($model)) {
						$error = "Fields Model for ".$tbl."/".$vars['tabletype']." not created";
						throw new Exception($error);
					}
					
					// Walk through the input array
					foreach($strarray as $q => $row) {

						$ref = $row['c_reference'];
						$recid = $row['id'];

						// If Id does not exists, then it will be created as new record
						// Thus when doing an insert, set the Id to a non-existent Record Id so that the array
						// will parse but a new record will be created

						$db = R::load($tbl, $recid);

						// Logically all the fields both physical (c_) and virtual (d_) should exist in the model, so that we have a type to go by
	 
						$rqc = []; $rqd = []; $submit = []; $doc = []; $test = []; 
						foreach($row as $fld => $val) {
							$chk = strtolower(substr($fld, 0, 2));	
							switch($chk) {
								case "c_": $rqc[$fld] = $val; break;
								case "d_": $rqd[$fld] = $val; break;	
								case "aj": case "id": case "x_": false; break;	// throws ajaxbuster, id and x away
								default: throw new Exception("Request key had no usable starting letters! - ".$chk." - ".$fld);
							}
						};

						// Send $vals for formatting
						foreach($rqc as $fldc => $valc) {
							$props = $model[$fldc];
							if(self::dbFormat($fldc, $tbl, $recid, $valc, $props) != false) {
								$submit[$fldc] = self::dbFormat($fldc, $tbl, $recid, $valc, $props);
							}
						}
					
						// Send $doc for formatting
						foreach($rqd as $fldd => $vald) {
							$props = $model[$fldd];
							if(self::dbFormat($fldd, $tbl, $recid, $vald, $props) != false) {
								$doc[$fldd] = self::dbFormat($fldd, $tbl, $recid, $vald, $props);
							}
						}
					
						// call up the existing record if it exists
						$sql = "SELECT c_document FROM ".$tbl." WHERE id = ?";
						$existing = json_decode(R::getCell($sql, [$recid]), true);
						
						// Replace
						if(is_array($existing) && count($existing) > 0) {
							$doc = array_replace($existing, $doc);
						}
						

						$submit['c_document'] = json_encode($doc);
						
						// Run through the array of records that will be written to the database									
						foreach($submit as $flds => $vals) {
							$test[$flds] = $vals;
							$db->$flds = $vals;
						}		
											
						// Save a new record to the database if a live import
						if($dbwrite == "on") {

							// R::debug(true);
							$result = R::store($db);
							// R::debug(false);

							if(!is_numeric($result)) {
								throw new Exception("No result has been created!");
							} else {

								// If written successfully
								if(count($result) > 0) {

									$sql = "SELECT * FROM ".$tbl." WHERE id = ?";
									$res = R::getRow($sql, [$result]);	
									
									$text = "Array Conversion: ".$result;
									$notes = "Tablename: ".$tbl.", ID: ".$result.", Ref: ".$ref."<br />Request - ".json_encode($submit, JSON_PRETTY_PRINT);
									L::wLog($text, 'dbwrite', $notes);	

								} else {
									$written = "Not Written";
								}								
				
							}			
							
						};

						$testarray[] = $test;		

						unset($rqc); unset($rqd); unset($submit); unset($doc); unset($test);

					} // Completes foreach loop			
					
					// Generate a Test or process result
					$html = array_slice($testarray, 0, 5);		

					// Test
					$check = [
						'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
						'filename' => $filename,
						// 'files' => $_FILES,
						// 'model' => $model,
						'written' => $written,
						// 'test' => $test,
						'result' => $res,
					];

					// Set to comment when completed
					// L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'result' => $html];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
						'filename' => $filename,
						'files' => $_FILES,
						'model' => $model,
						'result' => $res,
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}	
			}
		
		/** Export data 
		 * Provides template, variables and data for Export Data
		 * @param - array - required parameters as array
		 * @return - template and array of data for msgs and Vue etc. 
		 * @internal - Sets Script on Readyscript
		 **/
		 function exportdata(array $vars)
		 {
			
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				// Get list arrays of tables and table types to populate the selects
				$model = new Model();
				$tbls = $model->get_tables();
				$tbltypes = $model->get_tabletypes();	


				// Buttons required by this method which will be printed to the right of Breadcrumb row
				$methodbtns = [

				];
			    $tplbuttons = "";
		    	foreach($methodbtns as $t => $btn) {
		    		$tplbuttons .= '<button type="button" id="'.$t.'" class="treebutton btn btn-sm btn-'.$btn['class'].' mr5 pointer" data-table="'.$table.'" data-tabletype="'.$tabletype.'" data-idiom="'.$idiom.'">'.Q::cStr($btn['title']).'</button>';
				}; 

				// Javascript required by this method
				$js = "
					console.log('Import Data JS loaded');
					Cliq.set('displaytype', 'exportdata');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
					var options = {
						idioms: ".json_encode($clq->get('idioms')).", 
						tables: ".json_encode($tbls).",
						tabletypes: ".json_encode($tbltypes)."
					};
					Cliq.exportData(options);
				";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admexportdata.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'title' => Q::cStr('73:Export data to CSV or Array file'),
					'tablebuttons' => $tplbuttons,
					// Set the Javascript into the system to be used at the base of admscript.tpl
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }
		
			// Export mechanism
			function doExport(array $vars)
			{
				try {

					// Insert ACL here	
					global $clq;
					$msg = "";		
					$thislcd = $clq->get('idiom');

					// Get model record for defaults etc.
					$model = Q::cModel($tbl, $vars['tabletype'], 'fields');
					if(!is_array($model)) {
						$error = "Fields Model for ".$tbl."/".$vars['tabletype']." not created";
						throw new Exception($error);
					}

					$sql = "";

					// Generate a Test or process result
					$html = array_slice($testarray, 0, 5);		

					// Test
					$check = [
						'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
						// 'model' => $model,
						'written' => $written,
						// 'test' => $test,
						'result' => $res,
					];

					// Set to comment when completed
					// L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'result' => $html];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => self::THISCLASS.'->'.__FUNCTION__.'()',
						'model' => $model,
						'result' => $res,
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}								
			}

		/** Add New Language
		 * Adds a new language to the system by adding code to the config file, then walking through all the 
		 * records of the system and adding new JSON field - could be time consuming
		 * @param - array - arguments, especially RQ containing new language code and name
		 * @return - If successful, will reload page
		 **/
		 function addIdiom(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {
				set_time_limit(240);
				global $clq;
				$rq = $vars['rq'];
				$lcdcode = $rq['lcdcode'];
				$lcdname = $rq['lcdname'];
				$result = []; $tableset = []; $rowset = [];

				// Update Config in the system first	
				$cfg = $clq->get('cfg');
				$idms = $cfg['site']['idioms'];
				$idms[$lcdcode] = $rq['lcdname'];
				$cfg['site']['idioms'] = $idms;
				$clq->set('cfg', $cfg);

				// Update the Config File
				$configarray = C::cfgReadFile('config/config.cfg');
				$configarray['idioms'] = $idms;
				$writecfgfile = C::cfgWrite('config/config.cfg', $configarray);

				// Then walk through record in system, adding another pair to each JSON record found
				// Need to read model.cfg, to get tables and find out which tables and tabletypes have JSON			
				$cfgarray = C::cfgReadFile('models/model.cfg');
				$tables = $cfgarray['tables'];
				$tablearray = [];
				foreach($tables as $table => $fieldlist) {
					if(stristr($fieldlist, 'c_document') != false) {
						$tablearray[] = $table;
					}
				};

				// We have a subset of tables that contain the field c_document
				foreach($tablearray as $t => $table) {
					$sql = "SELECT id, c_document, c_type FROM ".$table;
					$docs = R::getAll($sql);
					// Now we have a large recordset containing all the c_documents by id
					for($d = 0; $d < count($docs); $d++) {
						$id = $docs[$d]['id'];
						$type = $docs[$d]['c_type'];
						$doc = $docs[$d]['c_document'];
						// If $doc has some string content and that content is parseable JSON
						if( $doc != '' and json_decode($doc)) {
							// Turn it into an array
							$doca = json_decode($doc, true);

							// Does the array contain (at a deep level) any key / value pairs where the value is an array and contains a key equal to the default idiom

							// We need to operate at two levels 
							foreach($doca as $fld => $val) {
								if(is_array($val)) {
									// This is a straight d_text, d_title or d_description
									if(array_key_exists($cfg['site']['defaultidiom'], $val)) {
										// $fld = "d_text"
										// $val = array of keys = languages and values = language string 
										// This is a language array and we need to add a key at this level
										$val[$lcdcode] = $val[$cfg['site']['defaultidiom']];
										$newdoca = $val;
									} else {
										// $fld = "d_text"
										// $val = array of keys = options and arrays 
										$newopt = [];
										foreach($val as $opt => $langarray) {
											if(array_key_exists($cfg['site']['defaultidiom'], $langarray)) {
												// $opt = list item key
												// langarray = array of keys = languages and values = language string 
												// This is a language array and we need to add a key at this level
												$langarray[$lcdcode] = $langarray[$cfg['site']['defaultidiom']];
												$newopt[$opt] = $langarray;
											}
										};
										$newdoca = $newopt;
									}
									$rowset[$fld] = $newdoca;
								} else {
									// Field / Value are straight key / value pair, in which case
									$rowset[$fld] = $val;
								}
							
							}

							// Write the info back here
							$updb = R::load($table, $id);
							$updb->c_document = json_encode($rowset);
							$updb->c_lastmodified = Q::lastMod();
							$updb->c_whomodified = Q::whoMod();
							$res = R::store($updb);

							if($res == $id) {
								$tableset[$id] = $rowset;
							} else {
								$tableset[$id] = ['error' => 'Problem Writing'];
							}							
						}
					}
					$result[$table] = $tableset;
				}

				return ['flag' => 'Ok', 'data' => $result];

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method
				];
				// L::cLog($err);
				return ['flag' => 'NotOk', 'msg' => $err]; 
			}
		 }
	
		/** Maintain Idiom
		 * Provides template, variables and data for Maintain Idiom
		 * @param - array - required parameters as array
		 * @return - template and array of data for msgs and Vue etc. 
		 * @internal - Sets Script on Readyscript
		 **/
		 function maintainidiom(array $vars) 
		 {
			try {
				
				// Config file if needed
	            $args = array(
	                'filename' => 'admmaintainidiom',   // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'admmaintainidiom',  // If database, value of c_reference
	                'key' => ''
	            );
	            $fileslist = C::cfgRead($args);	
				// inputNode.value = fileInput.value.replace("C:\\fakepath\\", "");		
				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$js = "
					console.log('Maintain Idiom JS Loaded');
					Cliq.set('displaytype', 'maintainidiom');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
					Cliq.maintainIdiom(".json_encode($clq->get('idioms')).");
				";
			    $clq->set('js', $js);
				$tpl = "admmaintainidiom.tpl";
				$thisvars = ['title' => Q::cStr('253:Maintain Idiom'), 'xtrascripts' => ""];
				return self::publishTpl($tpl, $thisvars);
	        } catch(Exception $e) {
	            return ['flag' => 'NotOk', 'html' => $e->getMessage()];
	        }
		 }		
		
			/** The actual language import 
			 * 
			 * tbd - Access control
			 * Confirm upload and write input file to disk
			 * Read input file in CFG format with numeric keys and textual values
			 * Get the Model for dbcollection->string
			 * For each row in the converted input file
			 * 	For each field in the Model
			 * Does the record already exist?
			 * 	No - create completely new record
			 * 	Yes - do we add new language to c_document->d_text->lcd
			 * 	Yes do we update existing text variable
			 * Create result as test or do database update
			 * If new language and database write (not test), update the configuration file with new language
			 * 
			 * @param - array - request variables
			 * @return - JSON array - for use by template including error messages or results HTML
			 * */
			 function doIdiomImport(array $vars)
			 {
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				try {

					// Insert ACL here
					global $clq;
					$msg = "";		
					$rq = $vars['rq'];
					$thislcd = $rq['lcdcode'];
					$filename = "";
					$res = []; $model = []; $writecfgfile = ""; $test = []; $written = "";
					
					// Confirm upload of file and write to disk - solution is not right but it works
					if(isset($_FILES)) {
						$fn = $rq['filename'];  
						$fn = str_replace('.','_',$fn);
						$filename = $_FILES[$fn]['name'];
						if(!move_uploaded_file($_FILES[$fn]['tmp_name'], "includes/i18n/".$filename)) {
							$error = "File not moved and written";
							throw new Exception($error);
						}
					} else {
						$error = "No input files";
						throw new Exception($error);
					};
					
					// Read file
					$toml = $clq->resolve('Toml');
					$strarray = $toml->parseFile('includes/i18n/'.$filename);
					if(!is_array($strarray)) {
						$error = "Input file did not create usable array";
						throw new Exception($error);
					}
					 	
					// Get model record for defaults etc.
					$mdl = $clq->resolve('Model'); 
					$model = $mdl->stdModel('fields','dbcollection', 'string');
					if(!is_array($model)) {
						$error = "Model not created";
						throw new Exception($error);
					}
					
					// Walk through the input array
					foreach($strarray as $i => $txt) {
						
						$ref = $i;
						// Does existing row exist with this reference?
						$sql = "SELECT * FROM dbcollection WHERE c_type = ? AND c_reference LIKE ?";
						$row = R::getRow($sql, ['string', '%'.$ref.'%']);

						// 2 Possibilities

						// 1 = Record does not exist in any form, so create new record
						// 2a = Record exists, add new language
						// 2b = Change existing language

						if(count($row) < 1) {  // Does not exist

							// Dispense a record
							$rdb = R::dispense('dbcollection');
							
							// Go through each field in the model
							foreach($model as $fld => $spec) {
								$chk = strtolower(substr($fld, 0, 1));	
								if($chk == 'c') {
									if($fld == 'c_type') {
										$rdb->$fld = 'string';
										$test[$i][$fld] = 'string';
									} elseif ($fld == 'c_whomodified') {
										$rdb->$fld = Q::whoMod();
									} elseif ($fld == 'c_lastmodified') {
										$rdb->$fld = Q::lastMod();
									} elseif ($fld == 'c_reference') {
										$rdb->$fld = $ref;
										$test[$i][$fld] = $ref;
									} elseif ($fld == 'c_common') {
										$rdb->$fld = $txt;
										$test[$i][$fld] = $txt;
									} elseif ($fld == 'c_document') {
										$doc = json_encode(['d_text' => [$thislcd => $txt]]);
										$rdb->$fld = $doc;
										$test[$i][$fld] = $doc;
									} elseif ($fld == 'c_notes') {
										$rdb->$fld = 'Record created by import.';
									} else {
										$rdb->$fld = $spec['defval'];
									}
								};
							}
							
						} else {

							// Load a record
							$rdb = R::load('dbcollection', $row['id']);
							$rdb->c_whomodified = Q::whoMod();
							$rdb->c_lastmodified = Q::lastMod();
							$rdb->c_revision = (+$row['c_revision'] + 1);
							
							$doc = json_decode($row['c_document'], true);
							$doc['d_text'] = array_merge($doc['d_text'], [$thislcd => $txt]);
							
							
							$rdb->c_document = json_encode($doc);
							$test[$i]['c_document'] = json_encode($doc);
							
							$test[$i]['c_reference'] = $ref;
						}
						
						// Save a new record to the database if a live import
						if($rq['dbwrite'] != "") {
							
							// R::debug(true);
							$result = R::store($rdb);
							// R::debug(false);
							
							// If written successfully
							if(count($result) > 0) {
								$res[$ref] = $txt;
							} else {
								$res[$ref] = 'error';
							}					
							
						} else {
							$written = "Not Written";
						}
						
						
					} // Completes foreach loop			
					
					// If DB write, update Config file
					if($rq['cfgwrite'] != "") {				
						$cfg = $clq->get('cfg');
						$idms = $cfg['idioms'];
						$idms[$thislcd] = $rq['lcdname'];
						$cfg['idioms'] = $idms;
						$writecfgfile = C::cfgWrite($filename, $cfg);
					}
			
					// Generate a Test or process result
					$resultarray = array_slice($test, 0, 10);		

					// Test
					$check = [
						'method' => $method,
						'request' => $rq,
						// 'filename' => $filename,
						// 'files' => $_FILES,
						// 'model' => $model,
						// 'writecfgfile' => $writecfgfile,
						'written' => $written,
						// 'test' => $test,
						'result' => $res,
					];

					// Set to comment when completed
					L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'result' => $resultarray];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => $method,
						'filename' => $filename,
						'files' => $_FILES,
						'model' => $model,
						'writecfgfile' => $writecfgfile,	
						'result' => $res,
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}	
			 }

			/** Generates a Language template in the Default language of the system
			 *
			 * @param - array -
			 * @return - JSON
			 **/
			 function doIdiomTemplateDownload(array $vars)
			 {
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				try {

					// Insert ACL here	

					global $clq;
					$rq = $vars['rq'];
					$filename = "";
					$cfg = $clq->get('cfg');
					// $defaultidiom = $cfg['defaultidiom'];
					$defaultidiom = $vars['idiom'];
					
					$sql = "SELECT c_reference, c_document FROM dbcollection WHERE c_type = ? ORDER BY c_reference ASC";
					$rs = R::getAll($sql, ['string']);
					
					$res = [];
					for($r = 0; $r < count($rs); $r++) {
						$doc = json_decode($rs[$r]['c_document'], true);
						$val = $doc['d_text'][$defaultidiom];
						$res[$rs[$r]['c_reference']] = "'".$val."'";
					}
					
					$content = C::cfgWriteString($res);
					$filename = 'dbcollection-string-'.$defaultidiom.'.lcd';

					// Test
					$check = [
						'method' => $method,
						'request' => $rq,
						'defaultidiom' => $defaultidiom,
						'filename' => $filename,
						'content' => $content
					];

					// Set to comment when completed
					L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'content' => $content, 'filename' => $filename];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => $method,
						'defaultidiom' => $defaultidiom,
						'filename' => $filename,
					];
					// L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}						
			 }
	
		/** Delete Idiom 
		 * Language delete mechanism 
		 * Not yet tested
		 * will be achieved by unset a Language column from the array of languages
		 * @param - array - arguments, especially RQ containing new language code
		 * @return - If successful, will reload page
		 **/
		 function deleteIdiom(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				// Insert ACL here	

				set_time_limit(240);
				global $clq;
				$rq = $vars['rq'];
				$lcdcode = $rq['lcdcode'];
				$lcdname = $rq['lcdname'];
				$result = []; $tableset = []; $rowset = [];

				// Update Config in the system first	
				$cfg = $clq->get('cfg');
				$idms = $cfg['site']['idioms'];
				unset($idms[$delidmcode]);
				$cfg['site']['idioms'] = $idms;
				$clq->set('cfg', $cfg);

				// Update the Config File
				$configarray = C::cfgReadFile('config/config.cfg');
				$configarray['idioms'] = $idms;
				$writecfgfile = C::cfgWrite('config/config.cfg', $configarray);

				// Then walk through record in system, adding another pair to each JSON record found
				// Need to read model.cfg, to get tables and find out which tables and tabletypes have JSON			
				$cfgarray = C::cfgReadFile('models/model.cfg');
				$tables = $cfgarray['tables'];
				$tablearray = [];
				foreach($tables as $table => $fieldlist) {
					if(stristr($fieldlist, 'c_document') != false) {
						$tablearray[] = $table;
					}
				};

				// We have a subset of tables that contain the field c_document
				foreach($tablearray as $t => $table) {
					$sql = "SELECT id, c_document, c_type FROM ".$table;
					$docs = R::getAll($sql);
					// Now we have a large recordset containing all the c_documents by id
					for($d = 0; $d < count($docs); $d++) {
						$id = $docs[$d]['id'];
						$type = $docs[$d]['c_type'];
						$doc = $docs[$d]['c_document'];
						// If $doc has some string content and that content is parseable JSON
						if( $doc != '' and json_decode($doc)) {
							// Turn it into an array
							$doca = json_decode($doc, true);

							// Does the array contain (at a deep level) any key / value pairs where the value is an array and contains a key equal to the default idiom

							// We need to operate at two levels 
							foreach($doca as $fld => $val) {
								if(is_array($val)) {
									// This is a straight d_text, d_title or d_description
									if(array_key_exists($lcdcode, $val)) {
										// $fld = "d_text"
										// $val = array of keys = languages and values = language string 
										// This is a language array and we need to add a key at this level
										unset($val[$lcdcode]);
										$newdoca = $val;
									} else {
										// $fld = "d_text"
										// $val = array of keys = options and arrays 
										$newopt = [];
										foreach($val as $opt => $langarray) {
											if(array_key_exists($lcdcode, $langarray)) {
												// $opt = list item key
												// langarray = array of keys = languages and values = language string 
												// This is a language array and we need to add a key at this level
												unset($langarray[$lcdcode]);
												$newopt[$opt] = $langarray;
											}
										};
										$newdoca = $newopt;
									}
									$rowset[$fld] = $newdoca;
								} else {
									// Field / Value are straight key / value pair, in which case
									$rowset[$fld] = $val;
								}
							
							}
							
							// Write the info back here
							$updb = R::load($table, $id);
							$updb->c_document = json_encode($rowset);
							$updb->c_lastmodified = Q::lastMod();
							$updb->c_whomodified = Q::whoMod();
							$res = R::store($updb);

							if($res == $id) {
								$tableset[$id] = $rowset;
							} else {
								$tableset[$id] = ['error' => 'Problem Writing'];
							}							
						}
					}
					$result[$table] = $tableset;
				}  

				return ['flag' => 'Ok', 'data' => $result];

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method
				];
				// L::cLog($err);
				return ['flag' => 'NotOk', 'msg' => $err]; 
			}							
		 }

	/** Administrative Methods pages
	 * Administrative functions that display as component templates on a desktop page
	 *
	 * genkeys()
	 * siteupdate()
	 * - doFilesDownload()
	 * - doFilesCopy()
	 * - fileDownload()
	 * - filesList()	 
	 * dbschema()
	 * - getTableCard()
	 * - getTypeCard()
	 * - dbFormatTree()
	 * - dictionaryEdit()
	 * - dictionaryCopy()
	 * - dictionaryWrite()
	 * sitemap() 
	 * jstrings()
	 * - writeAdminJStrings()
	 * codeeditor()
	 * 
	 ********************************************************************************************************/		

		/** Generate API Access keys - not yet completed 
		 * Template and Methods will have necessary HTML and functions to issue and manage Security keys
		 * to be programmed - need to look inUser.php
		 * @param - array -
		 * @return - string - HTML generated by the Template
		 **/
		 function genkeys($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = 'dbuser'; // Must be set
				$tabletype = 'administrator'; // can be empty

				// Testing
				// L::log('Table: '.$table.', Tabletype: '.$tabletype);	
					
				$model = $clq->resolve('Model'); 
				$gkcfg = $model->stdModel('generatekeys', $table, $tabletype);

			    $topbuttons = Q::topButtons($gkcfg, $vars, 'datatree');
		    	unset($gkcfg['topbuttons']);

				// Javascript required by this method
				$js = "
			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'generatekeys');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
			        Cliq.set('opts', '".object_encode($gkcfg)."');

				";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admgenkeys.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					// Set the Javascript into the system to be used at the base of admscript.tpl
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        } 
		 }

		/** Site Update 
		 * Provides template and data for the Site Update
		 * @param - array - variables
		 * @return - array(html, data)
		 * @internal 
		 **/
		 public function siteupdate(array $vars)
		 {
			
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$files = "Files"; $rss = "Rss";

				// Readin SiteUpdate config file
	            $args = array(
	                'filename' => 'siteupdate',         // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'siteupdate',        // If database, value of c_reference
	                'key' => ''
	            );
	            $sucfg = C::cfgRead($args); unset($args);
	            $args = array(
	                'filename' => 'filesupdatelist',         // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'filesupdatelist',        // If database, value of c_reference
	                'key' => ''
	            );
	            $fileslist = C::cfgRead($args);

			    $topbuttons = self::topButtons($sucfg, $vars, 'siteupdate');
		    	unset($sucfg['topbuttons']);

		    	// Create the Files column
		    	$files = H::div(['class' => 'row pad'], H::div(['class' => '', 'id' => 'tree']));
		    	$files .= H::div(['class' => 'row pad'],
		    		H::button(['class' => 'btn btn-primary mr5', 'id' => 'btnSave'], Q::cStr('347:Download selected files')),
		    		H::button(['class' => 'btn btn-success', 'id' => 'btnCopy'], Q::cStr('349:Update files'))
		    	);

		    	$sucfg['data'] = self::filesList($fileslist, '');

		    	// Create the RSS column
		    	$rss = H::div(['class' => ''],
		    		H::h5(['class' => ''], Q::cStr('348:Cliqon software updates')),
		    		H::div(['class' => '', 'id' => 'rssfeed'])
		    	);

				// Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
			    $js = "
			    	console.log('Siteupdate Loaded');

			        Cliq.set('displaytype', 'siteupdate');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
					
			    	Cliqf.siteUpdate(".F::jsonEncode($sucfg).");
			    ";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admsiteupdate.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'topbuttons' => $topbuttons,
					'filelist' => $files,
					'rssfeed' => $rss,
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        }			    
		 }

			function doFilesDownload($vars)
			{
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				try {

					$selectedfiles = $vars['rq']['selectedfiles'];
					$files = explode(',', $selectedfiles);
					foreach($files as $f => $fp) {
						$result = self::fileDownload($fp);
						if(!$result) {
							return ['flag' => 'NotOk', 'msg' => Q::cStr('350:There are problems with the files transfer procedures, see Log')]; 
						}
					};

					// Test
					$check = [
						'method' => $method,
						'selectedfiles' => $selectedfiles
					];

					// Set to comment when completed
					// L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'msg' => Q::cStr('351:Files transferred correctly')];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => $method
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}	
			}

			function doFilesCopy($vars)
			{
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				
				try {
					global $clq;
					// Readin SiteUpdate config file
		            $args = array(
		                'filename' => 'siteupdate',         // If file, name of file without extension (.cfg)
		                'subdir' => 'admin/config/',    	// If file, name of subdirectory
		                'type' => 'service',            	// If database, value of c_type
		                'reference' => 'siteupdate',        // If database, value of c_reference
		                'key' => ''
		            );
		            $sucfg = C::cfgRead($args);
					$dir = $sucfg['ftptempdir'];

					$selectedfiles = $vars['rq']['selectedfiles'];
					$files = explode(',', $selectedfiles);
					$fi = $clq->resolve('Files');	 			
		 			$sp = $fi->setRoot();				
					foreach($files as $f => $fp) {
						$result = self::fileDownload($fp);
						if(!$result) {
							return ['flag' => 'NotOk', 'msg' => Q::cStr('350:There are problems with the files transfer procedures, see Log')]; 
						}

						// copy file to be updated to archive and replace working copy with that in tmp
		 				$oldfile = $sp.$dir.$fp;
		 				$newfile = str_replace('//', '/', $sp.$fp);      
	            		if( !copy($oldfile, $newfile) ) {	
	            			$emsg = Q::cMsg(':File $newfile could not be updated, see log', $newfile);	
							throw new Exception($emsg);
						};		
					};

					// Test
					$check = [
						'method' => $method,
						'selectedfiles' => $selectedfiles
					];

					// Set to comment when completed
					// L::cLog($check);  
					
					// If not returned already 
					return ['flag' => 'Ok', 'msg' => Q::cStr('351:Files transferred correctly')];                

				} catch (Exception $e) {
					$err = [
						'errmsg' => $e->getMessage(),
						'method' => $method
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $err]; 
				}
			}

			protected function fileDownload($fp)
			{
				
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				try {

					global $clq;
					$ftp = $clq->resolve('Ftp');
					// Readin SiteUpdate config file
		            $args = array(
		                'filename' => 'siteupdate',         // If file, name of file without extension (.cfg)
		                'subdir' => 'admin/config/',    	// If file, name of subdirectory
		                'type' => 'service',            	// If database, value of c_type
		                'reference' => 'siteupdate',        // If database, value of c_reference
		                'key' => ''
		            );
		            $sucfg = C::cfgRead($args);
					$dir = $sucfg['ftptempdir'];	

					// $ftp->attach($sucfg['ftpuser'], $sucfg['ftppassword'], $sucfg['ftpserver'], $sucfg['ftpport']);
					$ftp->login();

					$fi = $clq->resolve('Files');
		 			$sp = $fi->setRoot();
		 			$mf = $sp.$dir.$fp;

		 			// Make sure the receiving directory exists
		 			$pp = pathinfo($mf);
		 			if(!mkdir($pp['dirname'], 0777, true)) {
	                    throw new Exception("Receiving subdirectory:".$sp.$dir." does not exist and could not be created");
	                };

		 			// If no valid $filesize, assume file not correctly accessed
		 			$filesize = $ftp->fileSize($fp);
	                if((int)$filesize < 1) {
	                    throw new Exception($fp." does not have a valid filesize and is presumed not to exist");
	                }

		 			// Returns true or false. 
		 			$result = $ftp->get($mf, $fp);  
	                if($result != true) {
	                    throw new Exception('Problem getting and writing '.$fp.':'.$filesize.' to '.$mf);
	                }
		 			
		 			// File should have transferred
		 			return true;
				} catch (Exception $e) {
					$err = [
						'method' => $method."<br />",
						'errmsg' => $e->getMessage()
					];
					L::cLog($err);
					return false; 
				}	 			
			}

			protected function filesList($farray, $subdir = '')
			{
				
				global $clq;
				$ftp = $clq->resolve('Ftp');
				// Readin SiteUpdate config file
	            $args = array(
	                'filename' => 'siteupdate',         // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'siteupdate',        // If database, value of c_reference
	                'key' => ''
	            );
	            $sucfg = C::cfgRead($args);

				$ftp->login();		
				$ftp->attach($sucfg['ftpuser'], $sucfg['ftppassword'], $sucfg['ftpserver'], $sucfg['ftpport']);
				$fi = $clq->resolve('Files');

				$result = [];
				foreach($farray as $key => $file) {

					$row = [];
					if(is_array($file)) {
						$row['id'] = $key;
						$row['checked'] = false;
						$row['text'] = H::span(['class' => 'quiet'], $key);
						$row['children'] = self::filesList($file, $subdir.'/'.$key);
					} else {

						// Do the files checking here
						$a = $ftp->fileSize($subdir.'/'.$file);
						$b = $fi->fileSize($subdir.'/'.$file);
						if(+$a != +$b) {
							$class = "redc";
						} else {
							$class = "bluec";
						};

						$row['id'] = $subdir.'/'.$file;
						$row['checked'] = false;
						$row['text'] = H::span(['class' => 'bold '.$class], $file).H::span(['class' => 'quiet'],' - '.$a.':'.$b);
					}
					$result[] = $row; unset($row);
				}	            
				return $result;
			}

		/** Data Dictionary 
		 * Provides content for Dbschema
		 * @return - string 
		 * @internal - Sets Script on Readyscript
		 **/
		 public function dbschema(array $vars) 
		 {
			
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq; 
				$html = "";
				$model = new Model();

				// Get the tables first
				$tables = $model->get_tables();

				// Iterate through the tables and create a row for each one plus a title
				foreach($tables as $tbl => $label) {
					
					// Set Rowcontent
					$rowcontent = "";

					// Get tabletypes for the table
					$types = $model->get_tabletypes($tbl);

					// Iterate through the table types, if they exist get the type definition
					if($types) {
						ksort($types);
						foreach($types as $type => $typelabel) {
							$rowcontent .= self::getTypeCard($tbl, $type, $typelabel);
						}
					} else {
						// If the table has no types, return the table definition
						$rowcontent = self::getTableCard($tbl, $label);
					}

					// Create a row with a title and popular with one or more cards with definitions
					$html .= H::div(['class' => 'row'],
						H::div(['class' => 'redc h4'], $label.' ('.$tbl.')')
					);
					$html .= H::div(['class' => 'row'],
						H::div(['class' => 'clear card-deck'], $rowcontent)
					);
				};

				$dbcfg = []; $dbcfg['topbuttons'] = [
					'helpbutton' => [
						'class' => 'info',
						'icon' => 'info',
						'title' => '85:Help',
						'order' => 'x'
					]
				];

				$tpl = "admdbschema.tpl";
				
				$js = "
					console.log('Database Schema JS Loaded');	
					Cliq.set('displaytype', 'dbschema');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

					Cliq.dbSchema();
				";
				$xjs = "";
				$clq->set('js', $js);
				$thisvars = ['topbuttons' => Q::topButtons($dbcfg, $vars, 'admdbschema'), 'content' => $html, 'title' => Q::cStr('318:Database and Dictionary Schema'), 'xtrascripts' => $xjs];
				return self::publishTpl($tpl, $thisvars);
	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.$method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars
				];
				L::cLog($err);
				return ['flag' => 'NotOk', 'html' => $e->getMessage(), 'data' => []];
	        } 	
		 }

			protected static function getTableCard($tbl, $label)
			{
				global $clq;
				$model = $clq->resolve('Model');
				$row = $model->stdModel('fields', $tbl);
				$list = self::dbSchemaFormatTree($row);
				$card = H::div(['class' => 'card border1', 'style' => 'min-width: 180px;'],
					H::div(['class' => 'card-block'],
						H::div(['class' => 'card-title redc caps fittext mb5'], $tbl),
						H::div(['class' => 'card-text bluec'], $label),
						H::div('<hr />'),
						H::div(['class' => 'card-text'], $list)
					),
					H::div(['class' => 'card-footer'],
						H::a(['class' => 'btn btn-sm btn-danger', 'href' => '#', 'data-hook' => 'dbschemabutton', 'data-action' => 'dictionaryedit', 'data-table' => $tbl, 'data-type' => ''], Q::cStr('103:Edit')
						),
						H::a(['class' => 'btn btn-sm btn-warning', 'href' => '#', 'data-hook' => 'dbschemabutton', 'data-action' => 'dictionarycopy', 'data-table' => $tbl, 'data-type' => ''], Q::cStr('376:Copy')
						)
					)
				);
				return $card;
			}

			protected static function getTypeCard($tbl, $type, $label)
			{
				global $clq;
				$model = $clq->resolve('Model');
				$row = $model->stdModel('fields', $tbl, $type);
				$list = self::dbSchemaFormatTree($row);
				$card = H::div(['class' => 'card border1', 'style' => 'min-width: 180px;'],
					H::div(['class' => 'card-block'],
						H::div(['class' => 'card-title redc caps fittext mb5'], $type),
						H::div(['class' => 'card-text bluec'], $label),
						H::div('<hr />'),
						H::div(['class' => 'card-text'], $list)
					),
					H::div(['class' => 'card-footer'],
						H::a(['class' => 'btn btn-sm btn-danger', 'href' => '#', 'data-hook' => 'dbschemabutton', 'data-action' => 'dictionaryedit', 'data-table' => $tbl, 'data-type' => $type], Q::cStr('103:Edit')
						),
						H::a(['class' => 'btn btn-sm btn-warning', 'href' => '#', 'data-hook' => 'dbschemabutton', 'data-action' => 'dictionarycopy', 'data-table' => $tbl, 'data-type' => $type], Q::cStr('376:Copy')
						)
					)					
				);
				return $card;
			}

			public static function dbSchemaFormatTree($row)
			{
				$lst = '<ul class="nolist" style="list-style:none; margin-left: -35px;">';
				$a = ""; $b = ""; $c = "";
				foreach($row as $fld => $def) {
					if($fld == 'c_document') {
						$a .= '<li>'.$fld.'</li>';
					} else if(substr($fld, 0, 2) == 'c_') {
						$b .= '<li>'.$fld.'</li>';
					} else if($fld == 'table') {
						false;
					} else if($fld == 'error') {
						false;
					} else {
						if(substr($fld, 0, 2) == 'd_') {
							$c .= '<li class="ml20">'.$fld.'</li>';
						}	
					};
				}
				$lst .= $b.$a.$c;
				$lst .= '</ul>';
				return $lst;
			}

			/** Edit the field definition for a tabletype
			 *
			 * @param - array - usual variables, including table and tabletype
			 * @return - array - converted to JSON format
			 **/
			public static function dictionaryEdit($vars)
			{
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				try {

					global $clq;
					$model = new Model();
					$row = $model->stdModel('fields', $vars['table'], $vars['tabletype']);

					$fields = "";
					foreach($row as $fld => $def) {
						if($fld != 'c_document') {
							$chk = substr($fld, 0, 2);
							if($chk == 'c_' or $chk == 'd_') {
								$fields .= '['.$fld.']'.PHP_EOL;
								foreach($def as $key => $val){
									$fields .= "&nbsp;&nbsp;".$key." = '".$val."'".PHP_EOL;
								}
							} else {
								false;
							}
						};
					};				
					$html = H::div(['class' => 'dbl-pad'],
						H::form(['id' => 'dataform', 'name' => 'dataform', 'action' => '#', 'method' => 'POST', 'class' => '', 'role' => 'form'],
							// Fieldsused
							H::div(['class' => 'form-group'],
								H::label(['class' => 'redc h5'], Q::cStr('512:Fields used')),
								H::textarea(['class' => 'form-control h80 blackc tag', 'id' => 'fieldsused', 'name' => 'fieldsused'], $row['fieldsused'])
							),
							// Fieldsconfig
							H::div(['class' => 'form-group'],
								H::label(['class' => 'redc h5'], Q::cStr('449:Configuration settings in TOML format')),
								H::textarea(['class' => 'form-control h400 blackc', 'id' => 'fieldsconfig', 'name' => 'fieldsconfig'], $fields)
							),
							// Buttons
							H::button(['class' => 'btn btn-sm btn-primary', 'type' => 'button', 'id' => 'submitform'], Q::cStr('105:Submit')),
							H::button(['class' => 'ml10 btn btn-sm btn-danger', 'type' => 'button', 'id' => 'resetform'], Q::cStr('122:Reset'))
						)
					);

					$result = [
						'flag' => 'Ok',
						'data' => $html
					];

					return $result;

		        } catch(Exception $e) {
					$err = [
						'method' => $method,
						'errmsg' => $e->getMessage(),
						'vars' => $vars,
					];
					L::cLog($err);
					$result = [
						'flag' => 'NotOk',
						'data' => $err
					];
					return $result;
		        }
			}

			/** Copy the field definition to clipboard for use by new Record
			 * the fields are read from the tabletype model and displayed on a Noty, where it can be copied to the clipboard
			 * @param - array - usual variables, including table and tabletype
			 * @return - array - converted to JSON format
			 **/
			public static function dictionaryCopy($vars)
			{
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				try {

					global $clq;
					$html = "";
					$model = new Model();
					$row = $model->stdModel('fields', $vars['table'], $vars['tabletype']);
					foreach($row as $fld => $def) {
						if($fld != 'c_document') {
							$chk = substr($fld, 0, 2);
							if($chk == 'c_' or $chk == 'd_') {
								$html .= $fld." = '' <br />";
							} else {
								false;
							}
						};
					};

					$result = [
						'flag' => 'Ok',
						'data' => $html
					];

					return $result;

		        } catch(Exception $e) {
					$err = [
						'method' => $method,
						'errmsg' => $e->getMessage(),
						'vars' => $vars,
					];
					L::cLog($err);
					$result = [
						'flag' => 'NotOk',
						'data' => $err
					];
					return $result;
		        }
			}

			public static function dictionaryWrite($vars)
			{
				$method = self::THISCLASS.'->'.__FUNCTION__.'()';
				try {

					global $clq;
					$rq = $vars['rq']; // 'fieldsused': fieldsused, 'fieldsconfig': fieldsconfig
					$model = new Model();
					$row = $model->stdModel('fields', $vars['table'], $vars['tabletype']);
					
					// Update the fields used with new entered on the form. 
					// OK because this field is discrete and in string format with commas
					$row['fieldsused'] = $rq['fieldsused'];

					// Update the row values for the Fields configuration, can only be wriiten back to Tabletype, 
					// not Table. This is not a problem as gradually each Tabletype will become discrete
					// However, "fieldsconfig" is in TOML format and needs to be converted to an array
					$fields = C::cfgReadString(urldecode($rq['fieldsconfig']));

					// Check that we have an array
					if(!is_array($fields)) {
						$error = "Fields configuration input request conversion did not create usable array";
						throw new Exception($error);
					}					
					foreach($fields as $fld => $def) {
						$chk = substr($fld, 0, 2);
						if($chk == 'c_' or $chk == 'd_') {
							$row[$fld] = $def;
						} else {
							false;
						}
					};	

					// We have a new row, now writeback to tabletype database record
					// c_type = model, c_reference = $vars['table'].'_'.$vars['tabletype']
					if($vars['tabletype'] != '') {
						$ref = $vars['table'].'_'.$vars['tabletype'];
					} else {
						$ref = $vars['table'];
					}
					$r = R::findAndExport($vars['table'], 'c_type = ? AND c_reference = ?', ['model', $ref]);
					$c_options = array_replace_recursive($r['c_options'], $row);
					$result = R::exec("UPDATE ".$vars['table']." SET c_options = '".$c_options."' WHERE id = ?", [$r['id']]);
					if($result > 0) {
						return ['flag' => 'Ok', 'msg' => Q::cStr('370:Record updated successfully')];
					} else {
						return ['flag' => 'NotOk', 'msg' => Q::cStr('371:Error saving record')];
					};

		        } catch(Exception $e) {
					$err = [
						'method' => $method,
						'errmsg' => $e->getMessage(),
						'vars' => $vars,
					];
					L::cLog($err);
					$result = [
						'flag' => 'NotOk',
						'msg' => $err
					];
					return $result;
		        }
			}		
		
		/** Sitemap 
		 * Provides content for admin page Sitemap
		 * to be programmed
		 * @param - array - required parameters as array
		 * @return - template and array of data for msgs and Vue etc. 
		 * @internal - Sets Script on Readyscript
		 **/
		 public function sitemap(array $vars) 
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$map = H::div(['id' => 'sitemap', 'class' => 'mt30', 'style' => 'margin-left: -24px']);

				// Readin SiteUpdate config file
	            $args = array(
	                'filename' => 'sitemap',         // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'sitemap',        // If database, value of c_reference
	                'key' => ''
	            );
	            $sucfg = C::cfgRead($args);

			    $topbuttons = self::topButtons($smcfg, $vars, 'sitemap');
		    	unset($smcfg['topbuttons']);

		    	$tree = H::h5(['class' => 'form-group mb10'], Q::cStr('353:Enter sitemap'));
		    	$tree .= H::form(['id' => $smcfg['options']['formid']],
		    		H::div(['class' => 'form-group'],
		    			H::textarea(['class' => 'form-control codeeditor', 'id' => $smcfg['options']['fieldid'], 'name' => $smcfg['options']['fieldid']])
		    		),
		    		H::input(['class' => 'btn btn-sm btn-primary', 'type' => 'button', 'id' => 'generatebutton', 'value' => Q::cStr('343:Generate')]),
		    		H::input(['class' => 'btn btn-sm btn-info', 'type' => 'button', 'id' => 'resetbutton', 'value' => Q::cStr('122:Reset')])
		    	);

				// Set the Javascript into the system to be used at the base of admscript.tpl, otherwise known as pagescripts
			    $js = "
			        Cliq.set('table', '".$smcfg['table']."');
			        Cliq.set('tabletype', '".$smcfg['tabletype']."');
			        Cliq.set('displaytype', 'sitemap');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

			    	Cliqf.siteMap(".F::jsonEncode($smcfg['options']).");
			    ";
			    $clq->set('js', $js);

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admsitemap.tpl"; // This component uses Vue
				
				// Template variables these are used and converted by the template
				$thisvars = [
					'topbuttons' => $topbuttons,
					'maptree' => $tree,
					'mapdisplay' => $map,
					'xtrascripts' => ""
				];			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
				];
				L::cLog($err);
				return $e->getMessage();
	        }		
		 }	
		
		/** Language strings for the javascript functions 
		 * Provides content for jStrings
		 * @param - array - required parameters as array
		 * @return - template and array of data for msgs and Vue etc. 
		 * @internal - Sets Script on Readyscript
		 **/
		 function jstrings(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
				$fu = $clq->resolve('Files');
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$idioms = $clq->get('idioms');
				$jspath = "includes/js/i18n/";					

				// Readin SiteUpdate config file
	            $args = array(
	                'filename' => 'admjstrings',         // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'admjstring',        // If database, value of c_reference
	                'key' => ''
	            );
	            $dcfg = C::cfgRead($args);
			    $topbuttons = self::topButtons($dcfg, $vars, 'admjstrings');
		    	unset($dcfg['topbuttons']);
		    	$dcfg['datagrid']['notFoundText'] = Q::cStr($dcfg['datagrid']['notFoundText']);

		    	$otheropts['columns'][] = [
		    		'field' => 'id',
		    		'title' => Q::cStr('9999:Id'),
					'width' => '50',
					'sortable' => false,
					'filterable' => false,
		    	];

				// Read in the language files from /includes/js/i18n/cliqon.{lcd}.js
				
				$idm = array(); $jstr = array();
				foreach($idioms as $lcdcode => $lcdname) {
					
					$tmp = $fu->readFile($jspath."cliqon.".$lcdcode.".js");
					$qrepl = array("str['".$lcdcode."']", "=", "[", "]", ";");
					$qwith = array();
					$tmp = str_replace($qrepl, $qwith, $tmp);
					$tmpidm = explode(",", $tmp);
					for($t = 0; $t < count($tmpidm); $t++) {
						$jstr[$t][$lcdcode] = trim(str_replace("'", "",$tmpidm[$t]));
					}
					$names[] = $lcdname;
			    	$otheropts['columns'][] = [
			    		'field' => $lcdcode,
			    		'title' => $lcdname,
			    		'sortable' => true
			    	];
				}

				// Add a numeric ID 
				for($t = 0; $t < count($jstr); $t++) {
					$add = ['id' => $t];
					$jstr[$t] = array_merge($add, $jstr[$t]);
					unset($add);
				}

				$dcfg['datagrid'] = array_merge($dcfg['datagrid'], $otheropts);
				$dcfg['datagrid']['columns'][] = [
					'title' => '',
					'field' => 'id',
					'width' => '44',
					'tmpl' => '<i class="fa fa-edit fa-lg fa-fw editRecord"></i>',
					'tooltip' => Q::cStr('103:Edit'),
					'sortable' => false,
					'filterable' => false
				];
				$dcfg['datagrid']['dataSource'] = $jstr;
				$tpl = "admjstrings.tpl";
				$js = "
					gj.grid.messages['".$idiom."'] = ".self::gridMsgs().";
					Cliq.set('displaytype', 'admjstrings');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');

					Cliq.jsStrings(".json_encode($dcfg['datagrid']).", ".json_encode($clq->get('idioms')).");
				";
				$clq->set('js', $js);

				$thisvars = ['title' => Q::cStr('79:Javascript Language Strings'), 'topbuttons' => $topbuttons, 'xtrascripts' => ""];
				return self::publishTpl($tpl, $thisvars);

	        } catch(Exception $e) {
	            return ['flag' => 'NotOk', 'html' => $e->getMessage(), 'data' => []];
	        }
		 }	

			/**
			 * Write Admin Javascript Language records back to files
			 * @var params - array - Vuedata
			 * @return string - OK or NotOK
			 */
			function writeAdminJStrings($vars) 
			{
				/*
				Arrives as ["id" => 1, "en" => Reset", "es" => Reestablacer"]

				We want ....
				str['en'] = [
					'Storing Cookies', 
					'Reset', 
					.....
				    'Delete Collection'
				];
				*/			
				try {

					$method = THISCLASS.'->'.__FUNCTION__."()";
					global $clq;
					$rq = $vars['rq'];
					// Convert JSON to array
					$strarray = json_decode($rq['postdata'], true); 
					$newarray = json_decode($rq['newdata'], true);

					// Write language files to /includes/js/i18n/cliqon.{lcd}.js
					$idioms = $clq->get('idioms');
					$jspath = "includes/js/i18n/";
					$result = ""; $lang = [];

					// Get arrays into correct format - ['id' => ['en' => 'text', 'es' => 'texto']]
					$datarray = [];
					foreach($strarray as $q => $row) {
						$id = $row['id'];
						unset($row['id']);
						$datarray[$id] = $row;
					}
					$id = $newarray['id'];
					unset($newarray['id']);
					$datarray[$id] = $newarray; // Overwrites or adds		

					// Create a write stream for each language in the array and generate the new file
					foreach($idioms as $lcdcode => $lcdname) {
						$lang = array_column($datarray, $lcdcode, 'id');
						$stream = "";
						for($v = 0; $v < count($lang); $v++) {
							$stream .= "'".$lang[$v]."',".PHP_EOL;
						}
						$stream = trim($stream, ",".PHP_EOL);
						$data = "str['".$lcdcode."'] = [".PHP_EOL;
						$data .= $stream.PHP_EOL;
						$data .= "];";	
						$fi = $clq->resolve('Files');
						$result .= $fi->writeFile($jspath."cliqon.".$lcdcode.".js", $data);
						unset($data); unset($stream);
					}					

					// Test
					$test = [
						'method' => self::THISCLASS.'->'.$method,
						// 'request' => $rq,
						// 'postdata' => $strarray,
						// 'newdata' => $newarray,
						// 'arraytobewritten' => $datarray,
						'bylanguage' => $lang,
						'result' => $result
					];

					// Set to comment when completed
					// L::cLog($test);  
					
					return ['flag' => 'Ok', 'msg' => $result];

		        } catch(Exception $e) {
					$err = [
						'method' => self::THISCLASS.'->'.$method,
						'errmsg' => $e->getMessage(),
						'request' => $rq,
						'postdata' => $strarray,
						'newdata' => $newarray,
						'arraytobewritten' => $datarray,
						'bylanguage' => $lang,
						'result' => $result
					];
					L::cLog($err);
					return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
		        }  
			}

		/** Codeeditor 
		 * Provides content for admin page Codeeditor
		 * @param - array - required parameters as array
		 * @return - template and array of data for msgs and Vue etc. 
		 * @internal - Sets Script on Readyscript
		 **/
		 public function codeeditor(array $vars) 
		 {
			try {
	            
				global $clq;				
				// Config file if needed
	            $args = array(
	                'filename' => 'admcodeeditor',         // If file, name of file without extension (.cfg)
	                'subdir' => 'admin/config/',    	// If file, name of subdirectory
	                'type' => 'service',            	// If database, value of c_type
	                'reference' => 'admcodeeditor',        // If database, value of c_reference
	                'key' => ''
	            );
	            $dcfg = C::cfgRead($args);
				$tpl = "admcodeeditor.tpl";
				$js = "
					console.log('Code Editor JS Loaded');
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
					
					Cliq.set('displaytype', 'codeeditor');
				";
				$clq->set('js', $js);
				$thisvars = ['title' => Q::cStr('224:Code Editor'), 'xtrascripts' => ""];
				return self::publishTpl($tpl, $thisvars);
	        } catch(Exception $e) {
	            return ['flag' => 'NotOk', 'html' => $e->getMessage()];
	        }
		 }

	/** General administrative functions and utilities including:
	 * 
	 * publishTpl() -
	 * genListUrl() - generate
	 * dbFormat()
	 * gridMsgs()
	 * topButtons()
	 *
	 ********************************************************************************************************/

		/** Publish a Template 
		 * Common Template publishing function
		 * 
		 * @param - string - name of template
		 * @param - array - array of data to be converted to JSON to accompany the template HTML
		 * @param - array - variables for the template that will be mounted on the template before it is converted to an HTML string
		 * @return - Array - Consisting of three elements - an Ok flag, Html as a string to be rendered into the ID Admin Content 
		 * and Data to be consumed by any Vue JS template functions
		 **/
		 protected function publishTpl($tpl, $vars)
		 {
			// Template engine
	    	return Q::publishTpl($tpl, $vars, "admin/components", "admin/cache");
		 }

		/** Generate a formatted URL 
		 * Generate a URL for a Table, Tree, List and cards etc., based on instructions in the model
		 * @param - array - 
		 * @return - string - with new Url or error response
		 **/
		 function genListUrl(array $vars)
		 {

			try {

				// Check Cookie with User ID
				if(!Z::zget('UserName') || Z::zget('UserName') == "") {
					exit(Q::cStr('124:Database action not authorised as no valid User'));
				};

				// Also ACL
				
				// Load the Model - vars['table'] == table, vars['tabletype'] = tabletype
				$mcfg = Q::cModel($vars['table'], $vars['tabletype'], $mcfg['display']);
				$lcd = $vars['idiom'];
				$clq->set('lcd', $lcd);

				if($mcfg['display'] != "") {
					return "/admin/".$lcd."/".$mcfg['display']."/".$vars['table']."/".$vars['tabletype']."/";
				} else {
					return "NotOK: ".$result;
				}
	        } catch (Exception $e) {
	            return "NotOK: ".$e->getMessage();
	        }						
		 }

		/** Record formatting 
		 * Formats a value for inclusion in the database record
		 * @param - string - $value
		 * @param - array - $properties from the model
		 * @return formatted string
		 * @internal - equivalent to routine in Class Db
		 **/
		 protected static function dbFormat($fld, $tbl, $recid, $val, $props)
		 {
            // Standard Document Management fields first
            try {
				
            	global $clq; $db = $clq->resolve('Db');
				$result = "";
				switch($fld) {
					
					case "c_lastmodified":
						$result = Q::lastMod();
					break;
					
					case "c_whomodified":
						$result = Q::whoMod();
					break;

					case "c_version":
					case "c_revision":
						$result = D::getNextNumber($fld, $tbl, $recid);
					break;
					
					case "c_document":
						$result = false;
					break;
					
					// More here as required
					
					default:
						if($val == '') {
							$result = D::dbEntry($props['dbtype'], $props['defval']);
						} else {
							$result = D::dbEntry($props['dbtype'], $val);
						}					
					break;
				}
				
				// Test
				$test = [
					'method' => self::THISCLASS.'->'.__FUNCTION__,
					'fld' => $fld,
					'value' => $val,
					'properties' => $props,
					'result' => $result
				];

				// Set to comment when completed
				// L::cLog($test); 
				
				return $result;
			
			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => self::THISCLASS.'->'.__FUNCTION__,
					'fld' => $fld,
					'value' => $val,
					'properties' => $props,
					'result' => $result			
				];
				L::cLog($err);
				
				return false;
			}
		 }

		/** Language strings for messages in a Gijgo Grid 
		 * delegates an options routine to a protected method
		 * @return - JSON Array as a string
		 **/
		 protected function gridMsgs()
		 {
			$string = "{
			    First: '".Q::cStr('150:First')."',
			    Previous: '".Q::cStr('151:Previous')."',
			    Next: '".Q::cStr('152:Next')."',
			    Last: '".Q::cStr('153:Last')."',
			    Page: '".Q::cStr('143:Page')."',
			    FirstPageTooltip: '".Q::cStr('357:First page')."',
			    PreviousPageTooltip: '".Q::cStr('358:Previous page')."',
			    NextPageTooltip: '".Q::cStr('360:Following page')."',
			    LastPageTooltip: '".Q::cStr('359:Last Page')."',
			    Refresh: '".Q::cStr('361:Refresh')."',
			    Of: '".Q::cStr('362:of')."',
			    DisplayingRecords: '".Q::cStr('363:Results')."',
			    RowsPerPage: '".Q::cStr('509:Lines per page').":',
			    Edit: '".Q::cStr('103:Edit')."',
			    Delete: '".Q::cStr('104:Delete')."',
			    Update: '".Q::cStr('107:Update')."',
			    Cancel: '".Q::cStr('137:Cancel')."',
			    NoRecordsFound: '".Q::cStr('144:No records found').".',
			    Loading: '".Q::cStr('147:Loading ...')."'
			};";
			return $string;
		 }

		/** Top buttons subroutine 
		 * for datagrid, datalist and datatree etc.
		 * @param - array - config array
		 * @param - array - original variables
		 * @param - string - type, eg datagrid
		 * @return - String HTML of buttons
		 **/
		 protected function topButtons($dtcfg, $vars, $type) {return Q::topButtons($dtcfg, $vars, $type);}	

		/** Add more utility methods here 
		 *
		 **/
		 
} // Class Ends
