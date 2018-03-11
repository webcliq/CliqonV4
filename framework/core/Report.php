<?php
/**
 * Report class - extends HTML
 * Ctrl K3 to fold
 * display collection of records
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@webcliq.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */

 /*
 	id = 999999
	c_reference = '' 		; unique reference
	c_common = ''			; title
	c_level = '50:50:50'	; access requirements
	c_options = ''          ; report description
	c_category = ''			; report type - popup, page, window
	c_parent = ''			; table
	c_order = ''			; tabletype
	c_status = 'draft'		; status
	c_notes = ''			; additional notes

	d_columns.1.d_colid = 'id'
	d_columns.1.d_colname = 'Id'
	d_columns.1.d_colstart = 1
	d_columns.1.d_colend = 2
	d_columns.1.d_coltype = 'number'
	d_columns.1.d_colattrs = ''

	d_sql = ''				; if entered, will replace generated SQL
	d_filters = ''			; 
	d_idiom = 'en'          ; language for idiomset
	d_pagelength = 30 		; page length
	d_email = ''			; who to receive report
	d_description = ''		; extra description
	d_stylesheet = ''		; which stylesheet if not bootstrap
	d_groupby = ''			; group report by this column
	d_sortby = 'id'			; sort by this column
	d_runtime = ''			; items to be updated at runtime
 */

class Report extends HTML
{
	const THISCLASS = "View extends HTML";
	const CLIQDOC = "c_document";
	public static $reporthtml = "";
	public static $reportscript = "";
	public static $reportdata = [];
	public static $reporttype = ""; // popup, page or window
	private static $idioms = [];
	private static $lcd = "";
	private static $table = "";
	private static $tabletype = "";
	private static $lw = "col-md-3";
	private static $cw = "col-md-9";
	private static $formtype = "columnview";
	private static $displaytype = "datatable";
	private static $rq = []; // _REQUEST or equivalent

	public function __construct() 
	{
		global $clq;
		global $cfg;
		self::$idioms = $cfg['site']['idioms'];
	}

	/** Display Reports
	 *
	 * reportDesigner()
	 * - collection()	// tab 1
	 * - columns() 		// tab 2
	 * - filters() 		// tab 3
	 * - tools()
	 *
	 * previewReport()
	 * updateReport()
	 * publishReport()
	 *	 
	 ***********************************************************************************************************/

        /** Drag and drop report designer
         * attempt at designing a Vue based report designer
         * @param - array - usual $vars
         * @return - Small amount of HTML content and JSON for the Vue control
         **/
         public static function reportDesigner($vars)
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
					$rcfg = $model->stdModel('reportdesigner', $table, $tabletype);

				// Top Buttons
					$topbuttons = Q::topButtons($rcfg, $vars, 'sitedesign');
		    		unset($rcfg['topbuttons']);	

				// Tabs containing HTML and Form
					$html = ''; $tabs = ''; $content = '';
					foreach($rcfg['tabs'] as $id => $tab) {
						$tabs .= H::li(['class' => 'nav-item', 'id' => 'repdestabs'],
							H::a(['class' => 'nav-link lp10 rp10', 'id' => 'tab_'.$id, 'data-toggle' => 'tab', 'href' => '#'.$id, 'role' => 'tab', 'aria-controls' => $id, 'aria-selected' => $tab['state']], Q::cStr($tab['label']))
						);
					};
					$html = H::ul([' class' => 'nav nav-tabs', 'id' => '', 'role' => 'tablist'], $tabs);

				// Buttons at foot of form
					$btns = '<hr style="" />';
					foreach($rcfg['buttons'] as $b => $btn) {
						$btns .= H::button(['type' => 'button', 'v-on:click' => 'clickbutton($event)', 'class' => 'btn btn-sm mr5 btn-'.$btn['class'], 'id' => $b], Q::cStr($btn['label']));
					};
					$buttons = H::div(['class' => 'form-group ml20 mt-20 mb5', 'id' => 'formbuttons'], $btns);				

				// Tabs content containing the forms
					foreach($rcfg['tabs'] as $id => $tab) {
						$content .= H::div(['class' => 'tab-pane fade '.$tab['class'], 'id' => $id, 'role' => 'tabpanel', 'aria-labelledby' => $id.'-tab'], self::$id());
					};
					$html .= H::div(['class' => 'tab-content', 'id' => 'tabcontent'], $content, $buttons);

					$grid = '<div class="card-block minh40 gridwrapper" id="gridwrapper">
						<div class="" style="grid-column: 1/24; grid-row: 15/16;">{{$data}}</div>
					</div>';

				if(array_key_exists('recid', $rq) and $rq['recid'] != 0) {
					$db = $clq->resolve('Db');
					$sql = "SELECT * FROM dbcollection WHERE id = ?";
					$rawreport = R::getRow($sql, [$rq['recid']]);
					$rpt = D::extractAndMergeRow($rawreport);
					$rcfg['defaultdata']['formdef'] = [
						'recid' => $rq['recid'],
						'c_reference' => $rpt['c_reference'],
						'c_common' => $rpt['c_common'],
						'c_level' => $rpt['c_level'],
						'c_options' => $rpt['c_options'],
						'c_category' => $rpt['c_category'],
						'c_parent' => $rpt['c_parent'],
						'c_order' => $rpt['c_order'],
						'c_status' => $rpt['c_status'],
						'c_notes' => $rpt['c_notes'],
						'd_columns' => $rpt['d_columns'],
						'd_sql' => $rpt['d_sql'],
						'd_filters' => $rpt['d_filters'],
						'd_idiom' => $rpt['d_idiom'],
						'd_pagelength' => $rpt['d_pagelength'],
						'd_email' => $rpt['d_email'],
						'd_description' => $rpt['d_description'],
						'd_stylesheet' => $rpt['d_stylesheet'],
						'd_groupby' => $rpt['d_groupby'],
						'd_sortby' => $rpt['d_sortby'],
						'd_runtime' => $rpt['d_runtime']					
					];
				};

				$thisvars = [
					'table' => $table,
					'designgrid' => $grid,
					'designtabs' => $html,
					'topbuttons' => $topbuttons
				];

				$js = "
					
					Cliq.set('gmapsapi', '".$clq->get('gmapsapi')."');
					Cliq.set('bingkey', '".$clq->get('bingkey')."');
			        Cliq.set('displaytype', 'reportdesign');
			        Cliq.set('formtype', 'columnform');
			        Cliq.set('viewtype', 'popupview');
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");

			        Cliqr.reportDesigner(".object_encode($rcfg).");

				";
			    $clq->set('js', $js);
			

				// Vars = template, data and template variables
				$tpl = "admreportdesigner.tpl";

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 	
				return Q::publishTpl($tpl, $thisvars, "admin/components", "admin/cache");	

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

        /** Report designer - tab1 - collection
         * @return - string of HTML content
         **/
         private static function collection()
         {

			global $clq;
			$dd = C::cfgReadFile('models/datadictionary.cfg');         	
         	$optsb = H::option(['value' => ''], 'Please select');

         	$frm = "";
         	$frm .= H::input(['type' => 'hidden', 'value' => '', 'v-model' => 'formdef.recid']);

         	// Title - c_common
	         	$frm .= H::div(['class' => 'form-group mt0'],
	         		H::label(['for' => 'c_common'], Q::cStr('130:Title')),
	         		H::input(['type' => 'text', 'class' => 'form-control c12', 'id' => 'c_common', 'v-model' => 'formdef.c_common', 'required' => 'true', 'autofocus' => 'true']),
	         		H::span(['class' => 'form-text small text-muted'], Q::cStr('181:Please select title for report ....'))
	         	);

         	// Description - c_options
	         	$frm .= H::div(['class' => 'form-group mt0'],
	         		H::label(['for' => 'c_options'], Q::cStr('125:Description')),
	         		H::input(['type' => '', 'class' => 'form-control c15', 'id' => 'c_options', 'v-model' => 'formdef.c_options'])
	         	);

			// Tables - c_parent
				$tbls = []; $q = 0;
				foreach($dd['tables'] as $tbl => $lblref) {
					$tbls[$q]['tablename'] = $tbl;
					$tbls[$q]['label'] = Q::cStr($lblref);
					$q++;
				}
				$tblist = Q::array_orderby($tbls, 'label', SORT_ASC);
				$tables = H::option(['class' => 'pad3', 'value' => ''], Q::cStr('164:Select an option'));
	        	foreach($tblist as $t => $tb) {
	        		$tables .= H::option(['class' => 'pad3', 'value' => $tb['tablename']], $tb['label']);
	        	};
	         	$frm .= H::div(['class' => 'form-group'],
	         		H::label(['for' => 'c_parent', 'class' => 'c4 text-right mr5'], Q::cStr('126:Table')),
	         		H::select(['class' => 'custom-select c9 watch', 'id' => 'c_parent', 'v-model' => 'formdef.c_parent', 'data-name' => 'table', 'v-on:change' => 'modelChange'], $tables)
	         	);

         	// Table Type - c_order
				$tts = []; $q = 0;
				foreach($dd['tabletypes'] as $p => $param) {
					$tts[$q]['typename'] = $param['tabletype'];
					$tts[$q]['label'] = Q::cStr($param['title']);
					$tts[$q]['table'] = $param['table'];
					$q++;
				};

				$ttlist = Q::array_orderby($tts, 'label', SORT_ASC);
				$tabletypes = H::option(['class' => 'pad3', 'value' => ''], Q::cStr('164:Select an option'));
				foreach($ttlist as $t => $tt) {
					$tabletypes .= H::option(['class' => 'pad3', 'data-label' => $tt['label'], 'data-table' => $tt['table'], 'value' => $tt['typename']], $tt['label']);
				};

	         	$frm .= H::div(['class' => 'form-group mt0'],
	         		H::label(['for' => 'c_order', 'class' => 'c4 text-right mr5'], Q::cStr('226:Table type')),
	         		H::select(['class' => 'custom-select c9 watch', 'id' => 'c_order', 'v-model' => 'formdef.c_order', 'data-name' => 'tabletype'], $tabletypes)
	         	);

         	// Reference c_reference
	         	$frm .= H::div(['class' => 'form-group'],
	         		H::label(['for' => 'c_reference'], Q::cStr('5:Reference')),
	         		H::input(['type' => 'text', 'class' => 'form-control c10 slugified', 'id' => 'c_reference', 'v-model' => 'formdef.c_reference', 'required' => 'true']),
	         		H::span(['class' => 'form-text small text-muted'], Q::cStr('123:Please enter a unique reference'))
	         	);	         	

         	// Level - c_level
	         	$frm .= H::div(['class' => 'form-group mt0'],
	         		H::label(['for' => 'c_level'], Q::cStr('99:Level')),
	         		H::input(['type' => 'text', 'class' => 'form-control c4', 'id' => 'c_level', 'v-model' => 'formdef.c_level', 'required' => 'true']),
	         		H::span(['class' => 'form-text small text-muted'], Q::cStr('450:Select the level ....'))
	         	);

         	// Category - c_category
				$rts = []; $q = 0;
				foreach(Q::cList('reporttypes') as $val => $lbl) {
					$rts[$q]['val'] = $val;
					$rts[$q]['label'] = $lbl;
					$q++;
				};
				$reporttype = Q::array_orderby($rts, 'label', SORT_ASC);
				$optsc = H::option(['class' => 'pad3', 'value' => ''], Q::cStr('164:Select an option'));
				foreach($reporttype as $r => $rp) {
					$optsc .= H::option(['class' => 'pad3', 'value' => $rp['val']], $rp['label']);
				};	 

	         	$frm .= H::div(['class' => 'form-group mt0'],
	         		H::label(['for' => 'c_category', 'class' => 'c4 text-right mr5'], Q::cStr('196:Category')),
	         		H::select(['class' => 'custom-select c9', 'id' => 'c_category', 'v-model' => 'formdef.c_category'], $optsc)
	         	);

         	// Status - c_status
	         	$frm .= H::div(['class' => 'form-group mt0'],
		         	H::div(['class' => 'form-check form-check-inline'],
		         		H::input(['type' => 'radio', 'class' => 'form-check-input ml5 mt8', 'id' => 'c_status_draft', 'value' => 'draft', 'v-model' => 'formdef.c_status', 'name' => 'c_status']),         		
		         		H::label(['for' => 'c_status_draft', 'class' => 'form-check-label ml5'], Q::cStr('9999:Draft'))
		         	),
		         	H::div(['class' => 'form-check form-check-inline'],
		         		H::input(['type' => 'radio', 'class' => 'form-check-input ml5 mt8', 'id' => 'c_status_published', 'value' => 'published', 'v-model' => 'formdef.c_status', 'name' => 'c_status']),         		
		         		H::label(['for' => 'c_status_published', 'class' => 'form-check-label ml5'], Q::cStr('9999:Published'))
		         	),
		         	H::div(['class' => 'form-check form-check-inline'],
		         		H::input(['type' => 'radio', 'class' => 'form-check-input ml5 mt8', 'id' => 'c_status_archived', 'value' => 'archived', 'v-model' => 'formdef.c_status', 'name' => 'c_status']),         		
		         		H::label(['for' => 'c_status_archived', 'class' => 'form-check-label ml5'], Q::cStr('9999:Archived'))
		         	)
		        );

         	// Notes
	         	$frm .= H::div(['class' => 'form-group mt0'],
	         		H::label(['for' => 'c_notes'], Q::cStr('8:Notes')),
	         		H::textarea(['class' => 'form-control', 'id' => 'c_notes', 'v-model' => 'formdef.c_notes']),
	         		H::span(['class' => 'form-text small text-muted'], Q::cStr('505:Additional notes'))
	         	);

         	return $frm;
         }

        /** Report designer - tab2 - columns
         * @return - string of HTML content
         **/
         private static function columns()
         {
			global $clq;
			$types = ""; $tps = []; $q = 0;
			foreach(Q::cList('displaytypes') as $val => $lbl) {
				$tps[$q]['val'] = $val;
				$tps[$q]['label'] = $lbl;
				$q++;
			};
			$typelist = Q::array_orderby($tps, 'label', SORT_ASC);
			$types = H::option(['class' => 'pad3', 'value' => ''], Q::cStr('164:Select an option'));
			foreach($typelist as $t => $tp) {
				$types .= H::option(['class' => 'pad3', 'value' => $tp['val']], $tp['label']);
			};

         	$frm = H::div(['class' => '', 'id' => 'column-form'],
         		// Column number
         			H::input(['type' => 'hidden', 'v-model' => 'coldef.xid']),
         		// Field ID - 
		         	H::div(['class' => 'form-group'],
		         		H::label(['for' => 'coldid'], Q::cStr('9999:Id')),
		         		H::input(['type' => 'text', 'class' => 'form-control c10', 'id' => 'colid', 'v-model' => 'coldef.colid'])
		         	),
         		// Field Title - 
		         	H::div(['class' => 'form-group'],
		         		H::label(['for' => 'colname'], Q::cStr('135:Field name')),
		         		H::input(['type' => 'text', 'class' => 'form-control c10', 'id' => 'colname', 'v-model' => 'coldef.colname'])
		         	),
	         	// Position - 
		         	H::div(['class' => 'form-group'],
		         		H::label(['for' => 'position'], Q::cStr('561:Position')),
		         		H::div(['class' => 'form-group-inline row'],
		         			H::span(['class' => 'ml15 mr5'], Q::cStr('562:Row start')),
		         			H::input(['type' => 'number', 'class' => 'form-control col-3', 'id' => 'colstart', 'v-model' => 'coldef.colstart', 'min' => 1, 'max' => 24]),
		         			H::span(['class' => 'ml5 mr5'], Q::cStr('563:Row end')),
			         		H::input(['type' => 'number', 'class' => 'form-control col-3', 'id' => 'colend', 'v-model' => 'coldef.colend', 'min' => 1, 'max' => 24])
		         		),
		         		H::span(['class' => 'form-text small text-muted'], Q::cStr('566:Grid of 24 rows'))
		         	),
		        // Type
	         	H::div(['class' => 'form-group'],
	         		H::label(['for' => 'coltype'], Q::cStr('128:Type')),
	         		H::select(['class' => 'custom-select c9', 'v-model' => 'coldef.coltype', 'data-name' => 'coltype', 'id' => 'coltype'], $types),
	         		H::span(['class' => 'form-text small text-muted'], Q::cStr('569:Please select which display type for this column'))
	         	),

         		// Attributes - 
		         	H::div(['class' => 'form-group'],
		         		H::label(['for' => 'colattrs'], Q::cStr('564:Attributes')),
		         		H::textarea(['class' => 'form-control h200 toml', 'id' => 'colattrs', 'v-model' => 'coldef.colattrs']),
		         		H::span(['class' => 'form-text small text-muted'], Q::cStr('449:Configuration settings in TOML format'))
		         	),

		        // Buttons set
					// Set and clear
					H::button(['type' => 'button', 'v-on:click' => 'clickupdate($event, coldef.xid)', 'class' => 'btn btn-sm mb20 btn-primary'], Q::cStr('565:Set field')),
					// Delete and clear
					H::button(['type' => 'button', 'v-on:click' => 'clickdelete($event, coldef.xid)', 'class' => 'btn btn-sm mb20 btn-danger'], Q::cStr('104:Delete'))
         	);
         	return $frm;
         }

        /** Report designer - tab3 - queries and filters
         * @return - string of HTML content
         **/
         private static function filters()
         {
			global $clq;      	
         	$frm = '';

         	// Group By - d_groupby
         		$opts = H::option(['value' => ''], Q::cStr('164:Select an option'));
	         	$frm .= H::div(['class' => 'form-group'],
	         		H::label(['for' => 'd_groupby', 'class' => 'c4 text-right mr5'], Q::cStr('96:Group')),
	         		H::select(['class' => 'custom-select c9 watch', 'id' => 'd_groupby', 'v-model' => 'formdef.d_groupby', 'data-name' => 'd_groupby'], $opts),
	         		H::span(['class' => 'form-text small text-muted'], Q::cStr('568:Select an option or leave blank'))

	         	);

	        // Sort By - d_sortby
	         	$frm .= H::div(['class' => 'form-group'],
	         		H::label(['for' => 'd_sortby', 'class' => 'c4 text-right mr5'], Q::cStr('341:Sort order')),
	         		H::select(['class' => 'custom-select c9 watch', 'id' => 'd_sortby', 'v-model' => 'formdef.d_sortby', 'data-name' => 'd_sortby'], $opts),
	         		H::span(['class' => 'form-text small text-muted'], Q::cStr('568:Select an option or leave blank'))

	         	);

	        // Filter by - will need field name and value for that field
	         	$frm .= H::div(['class' => 'form-group'],
	         		H::label(['for' => 'd_filterby', 'class' => 'c4 text-right mr5'], Q::cStr('173:Filter by')),
		         	H::div(['class' => 'form-group-inline', 'style' => 'display:inline'],
		         		H::input(['class' => 'form-control c4 right', 'v-model' => 'formdef.d_filterbyval']),         		
		         		H::select(['class' => 'custom-select c6', 'id' => 'd_filterby', 'v-model' => 'formdef.d_filterbyfld', 'data-name' => 'd_filterby'], $opts)
	         		),
	         		H::div(['class' => 'form-text small text-muted'], Q::cStr('568:Select an option or leave blank'))

	         	);

         	return $frm;
         }

        /** Report designer - tab4 - extra tools
         * @return - string of HTML content
         **/
         private static function tools()
         {

			global $clq;      	
         	$frm = '';

         	$idioms = [];
         	$perpage = [];

			// Select language

				$idioms = H::option(['class' => 'pad3', 'value' => ''], Q::cStr('164:Select an option'));
	        	foreach($clq->get('idioms') as $i => $idm) {
	        		$idioms .= H::option(['class' => 'pad3', 'value' => $i], $idm);
	        	};

				$frm .= H::div(['class' => 'form-group'],
					H::label(['class' => 'col-form-label', 'for' => ''], Q::cStr('187:Language')),
					H::select(['class' => 'custom-select c8', 'v-model' => 'formdef.d_idiom'], $idioms)
				);

			// Records per page
				$nums = [15, 20, 30, 40, 50];
				$perpage = H::option(['class' => 'pad3', 'value' => ''], Q::cStr('164:Select an option'));
	        	foreach($nums as $t) {
	        		$perpage .= H::option(['class' => 'pad3', 'value' => $t], $t);
	        	};

				$frm .= H::div(['class' => 'form-group'],
					H::label(['class' => 'col-form-label', 'for' => ''], Q::cStr('188:Per page')),
					H::select(['class' => 'custom-select c8', 'v-model' => 'formdef.d_pagelength'], $perpage)
				);

			// Email address
				$frm .= H::div(['class' => 'form-group'],
					H::label(['class' => 'col-form-label', 'for' => ''], Q::cStr('95:Email address')),
					H::input(['class' => 'form-control', 'v-model' => 'formdef.d_email', 'placeholder' => Q::cStr('95:Email address')])
				);

			// Instructions
				$frm .= H::div(['class' => 'form-group'],
					H::label(['class' => 'col-form-label', 'for' => ''], Q::cStr('559:Instructions')),
					H::textarea(['class' => 'form-control', 'v-model' => 'formdef.d_description', 'placeholder' => Q::cStr('125:Description')]),
					H::span(['class' => 'form-text small text-muted'], Q::cStr('560:Please enter any additional instructions'))
				);

			// Style sheet
				$frm .= H::div(['class' => 'form-group'],
					H::label(['class' => 'col-form-label', 'for' => ''], Q::cStr('189:Style sheet')),
					H::input(['class' => 'form-control', 'v-model' => 'formdef.d_stylesheet', 'placeholder' => Q::cStr('189:Style sheet')])
				);

			// Update at runtime
				$frm .= H::div(['class' => 'form-group'],
				H::label(['class' => 'col-form-label', 'for' => ''], Q::cStr('191:Update at runtime')),

				// Document labels
				H::label(['class' => 'custom-control custom-checkbox'],
					H::input(['class' => 'custom-control-input', 'type'  => 'checkbox',  'id' => 'labels', 'value' => 'labels', 'v-model' => 'formdef.d_runtime']),
					H::span(['class' => 'custom-control-indicator']),
					H::span(['class' => 'custom-control-description'], Q::cStr('190:Document labels'))
				),

				// Title
				H::label(['class' => 'custom-control custom-checkbox'],
					H::input(['class' => 'custom-control-input', 'type'  => 'checkbox',  'id' => 'title', 'value' => 'title', 'v-model' => 'formdef.d_runtime']),
					H::span(['class' => 'custom-control-indicator']),
					H::span(['class' => 'custom-control-description'], Q::cStr('130:Title'))
				),

				// Date
				H::label(['class' => 'custom-control custom-checkbox'],
					H::input(['class' => 'custom-control-input', 'type'  => 'checkbox',  'id' => 'date', 'value' => 'date', 'v-model' => 'formdef.d_runtime']),
					H::span(['class' => 'custom-control-indicator']),
					H::span(['class' => 'custom-control-description'], Q::cStr('183:Date'))
				),

				// Footer
				H::label(['class' => 'custom-control custom-checkbox'],
					H::input(['class' => 'custom-control-input', 'type'  => 'checkbox',  'id' => 'footer', 'value' => 'footer', 'v-model' => 'formdef.d_runtime']),
					H::span(['class' => 'custom-control-indicator']),
					H::span(['class' => 'custom-control-description'], Q::cStr('42:Footer'))
				),

				// Header
				H::label(['class' => 'custom-control custom-checkbox'],
					H::input(['class' => 'custom-control-input', 'type'  => 'checkbox',  'id' => 'header', 'value' => 'header', 'v-model' => 'formdef.d_runtime']),
					H::span(['class' => 'custom-control-indicator']),
					H::span(['class' => 'custom-control-description'], Q::cStr('182:Header'))
				),

				// Filter by
				H::label(['class' => 'custom-control custom-checkbox'],
					H::input(['class' => 'custom-control-input', 'type'  => 'checkbox',  'id' => 'filterby', 'value' => 'filterby', 'v-model' => 'formdef.d_runtime']),
					H::span(['class' => 'custom-control-indicator']),
					H::span(['class' => 'custom-control-description'], Q::cStr('173:Filter by'))
				)
			);
			return $frm;
         }

		/** Preview a Report  
		 * 
		 * @param - array - Variables
		 * @return - array of data, including Flag (Ok or NotOk) and HTML
		 **/
		 static function previewReport($vars)
		 {
		    $method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	global $clq;
		    	$str = $vars['rq']['formdef'];
		    	self::$rq = json_decode(urldecode($str), true);	    	
				if(!is_array(self::$rq)) {
					throw new Exception("No request array");
				};

				// Validate data here
				if(self::$rq['c_order'] == '') {
					throw new Exception("No tabletype selected");
				}

		    	self::$reporttype = 'popup';
				self::$lcd = $vars['idiom'];

				$fdef = self::$rq;
				self::$table = $fdef['c_parent'];
				self::$tabletype = $fdef['c_order'];

				// Start the Query
				// Fields later when the Recordset is extracted
				$sql = "SELECT * FROM ".self::$table." WHERE c_type = ? LIMIT 0, 10";
				$rawset = R::getAll($sql, [self::$tabletype]);
				if(!is_array($rawset)) {
					throw new Exception("No raw recordset");
				};
				$rs = self::processRecordSet($rawset, $fdef);

				if(is_array($rs)) {
					return ['flag' => 'Ok', 'data' => self::reportToTable($rs, $fdef)];
				} else {
					return ['flag' => 'NotOk', 'msg' => Q::cStr('144:No records available').' - '.$rs];
				}

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage(), 
				]; 
			}	
		 }

		/** Create or Update a Report record  
		 * 
		 * @param - array - Variables
		 * @return - array of data, including Flag (Ok or NotOk) and HTML
		 **/
		 static function updateReport($vars)
		 {
		    $method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	global $clq;
		    	$str = $vars['rq']['formdef'];
		    	self::$rq = json_decode(urldecode($str), true);	    	
				if(!is_array(self::$rq)) {
					throw new Exception("No request array");
				};

				// Validate data here
				if(self::$rq['c_order'] == '') {
					throw new Exception("No tabletype selected");
				}

				// Set values and variables to be used
				$recid = (int)self::$rq['recid'];
				$tbl = "dbcollection";
				$tbltype = "report";
				$rqc = []; $rqd = [];  $result = ''; $ref = '';	

				// Is it an Insert or an Update
				if($recid > 0) {
					$action = "update";
				} else {
					$action = "insert";
				}

				// Insert ACL here - is this User allowed to Create or Edit this record - see level
				if(!A::getAuth($action, $tbl, $tbltype, '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				} 
				
				// Walk through all the values in $rq
				foreach(self::$rq as $key => $value) {	
					$chk = strtolower(substr($key, 0, 2));	
					switch($chk) {
						case "c_": $rqc[$key] = $value; break;
						case "d_": $rqd[$key] = $value; break;	
						default: false; break;	// throws anything else in the REQUEST away
					}
				};	

				if($action == "insert") { // Insert
					
					$db = R::dispense($tbl);
					$msg = Q::cStr('367:New record created with Id').':&nbsp;';
					$text = Q::cStr('234:Insert record');

				} else { // Update

					$db = R::load($tbl, $recid);
					$msg = Q::cStr('368:Existing record updated with Id').':&nbsp;';
					$text = Q::cStr('369:Update Record');
				}

				// Send $vals for formatting
				foreach($rqc as $fldc => $valc) {
					$db->$fldc = $valc;
				}

				// If action equals insert, all we need to is write d_values to c_document
				if($action == 'insert') {

					$doc = [];
					// Send $doc for formatting
					foreach($rqd as $fldd => $vald) {
						$doc[$fldd] = $vald;
					}
					$db->c_document = F::jsonEncode($doc);						
				}

				if($action == 'update') {

					// call up the existing record if it exists
					$sql = "SELECT ".self::CLIQDOC." FROM ".$tbl." WHERE id = ?";
					$doc = json_decode(R::getCell($sql, [$recid]), true);

					if(!is_array($doc)) {
						throw new Exception("The 'existing' array has not been created from c_document!");
					} 

					foreach($rqd as $fldd => $vald) {
						$doc[$fldd] = $vald;
					}				
					$db->c_document = F::jsonEncode($doc);
				}

				$db->c_type = 'report';
				$db->c_lastmodified = Q::lastMod();
				$db->c_whomodified = Q::whoMod();

	            $sqlc = "SELECT c_revision FROM ".$tbl." WHERE id = ?";
	            $existing = R::getcell($sqlc, [$recid]);
	            $lastnum = filter_var($existing, FILTER_SANITIZE_NUMBER_INT);            
	            $nextnum = (int)$lastnum + 1;  
	            $db->c_revision = $nextnum;

	            $result = R::store($db);

				if(is_numeric($result) and $result > 0) {
					$sqld = "SELECT * FROM dbcollection WHERE id = ?";
					$row = R::getRow($sqld, [$result]);
					return ['flag' => 'Ok', 'msg' => Q::cStr('370:Record was successfully updated'), 'row' => $row];
				} else {
					return ['flag' => 'NotOk', 'msg' => Q::cStr('495:Record was not successfully written to database').': '.$result];
				}

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage(), 
				]; 
			}	
		 }

		/** Get stored reports
		 *
		 * @param - array - usual set of variables
		 * @return - HTML list of reports
		 **/
		 static function listReports($vars)
		 {
		    $method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	global $clq;
		    	$tbl = $clq->resolve('Table');		    	

				// Start the Query
				// Fields later when the Recordset is extracted
				$sql = "SELECT id, c_reference, c_common, c_category, c_status FROM dbcollection WHERE c_type = ?";
				$rset = R::getAll($sql, ['report']);
				if(!is_array($rset)) {
					throw new Exception("No raw recordset");
				};

		    	// Table instance
		    	$tbl->addTable('datatable', 'table table-striped table-condensed', []);

				// thead
				$tbl->addTSection('thead');
				$tbl->addRow();
					$tbl->addCell(Q::cStr('9999:Id'), 'bluec bold', 'header', []);
					$tbl->addCell(Q::cStr('5:Reference'), 'bluec bold', 'header', []);
					$tbl->addCell(Q::cStr('125:Description'), 'bluec bold', 'header', []);
					$tbl->addCell(Q::cStr('535:Display'), 'bluec bold', 'header', []);
					$tbl->addCell(Q::cStr('199:Status'), 'bluec bold', 'header', []);
					$tbl->addCell('*', '', 'header', []);

				// tbody
				$tbl->addTSection('tbody');
				for($r = 0; $r < count($rset); $r++) {
					$icons = "";
					$tbl->addRow();
						$tbl->addCell($rset[$r]['id']);
						$tbl->addCell($rset[$r]['c_reference']);
						$tbl->addCell($rset[$r]['c_common']);
						$tbl->addCell( Q::fList($rset[$r]['c_category'],'reporttypes') );
						$tbl->addCell( Q::fList($rset[$r]['c_status'], 'statustypes') );
							$icons .= H::i(['class' => 'fa fa-fw fa-pencil pointer reporticon', 'data-action' => 'editicon', 'data-recid' => $rset[$r]['id'], 'data-reference' => $rset[$r]['c_reference'], 'data-description' => $rset[$r]['c_common']]);
							$icons .= H::i(['class' => 'fa fa-fw fa-eye pointer reporticon', 'data-action' => 'viewicon', 'data-recid' => $rset[$r]['id'], 'data-reference' => $rset[$r]['c_reference'], 'data-description' => $rset[$r]['c_common']]);
							$icons .= H::i(['class' => 'fa fa-fw fa-trash pointer reporticon', 'data-action' => 'deleteicon', 'data-recid' => $rset[$r]['id'], 'data-reference' => $rset[$r]['c_reference'], 'data-description' => $rset[$r]['c_common']]);
						$tbl->addCell($icons); unset($icons);
				}
			    
				if(is_array($rset)) {
					return ['flag' => 'Ok', 'data' => $tbl->display(), 'title' => Q::cStr('344:Store reports')];
				} else {
					return ['flag' => 'NotOk', 'msg' => Q::cStr('144:No records available').' - '.$rs];
				}

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage(), 
				]; 
			}	
		 }

		/** Publish a Report  
		 * 
		 * @param - array - Variables
		 * @return - array of data, including Flag (Ok or NotOk) and HTML
		 **/
		 static function publishReport($vars)
		 {
		    $method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	global $clq;
		    	self::$rq = $vars['rq'];
				self::$lcd = $vars['idiom'];

				$cell = R::findOne('dbcollection', ['id' => $vars['rq']['recid']]);

		    	self::$reporttype = 'popup';
				self::$table = $fdef['c_parent'];
				self::$tabletype = $fdef['c_order'];

				// Start the Query
				// Fields later when the Recordset is extracted
				$sql = "SELECT * FROM ".self::$table." WHERE c_type = ?";
				$rawset = R::getAll($sql, [self::$tabletype]);
				if(!is_array($rawset)) {
					throw new Exception("No raw recordset");
				};
				$rs = self::processRecordSet($rawset, $fdef);

				if(is_array($rs)) {
					return ['flag' => 'Ok', 'data' => self::reportToTable($rs, $fdef)];
				} else {
					return ['flag' => 'NotOk', 'msg' => Q::cStr('144:No records available').' - '.$rs];
				}

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage(), 
				]; 
			}	
		 }

		/** Report snippet utilities 
		 *
		 * processRecordSet(raw recordset, report definition)
		 * reportToTable(processed recordset, report definition)
		 * reportToJSON(processed recordset, report definition)
		 * - 
		 * - 
		 * - 
		 * 
		 **/
		 protected static function processRecordSet($rawset, $fdef)
		 {
		 	$method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {
		    	
		    	global $clq;
		    	$db = $clq->resolve('Db');
		    	$rset = D::extractAndMergeRecordset($rawset);
				if(!is_array($rset)) {
					throw new Exception("No processed recordset");
				};
				// We have a recordset
				$cols = $fdef['d_columns'];
				unset($fdef['d_columns']);

				// Create a working recordset
				$rs = [];
				for($r = 0; $r < count($rset); $r++) {
					// Filter
					if($col['d_colid'] == $fdef['d_filterbyfld'] and stristr($rset[$r][$col['d_colid']], $fdef['d_filterbyval']) == false) {
						exit();
					} else {
						$row = [];						
						foreach($cols as $q => $col) {
							// Format = formatCell(fieldname, row, attributes for field, table, record id)
							$fid = $col['d_colid'];
							$id = $rset[$r][$col['id']];
							$rrow = $rset[$r];
							// self::table
							$attr = $fdef['d_columns'][$fid];
	        				$row[$fid] = Q::formatCell($fid, $rrow, $attr, self::$table, $id);
						};	
						$rs[] = $row; unset($row);							
					}
				}
				
				// Orderby 
				$rs = array_orderby($rs, $fdef['d_orderby']);

				return $rs;
			} catch (Exception $e) {
				return $method.': '.$e->getMessage();
			}		    	
		 }

		/** Recordset to simple table
		 * using Class -> Table
		 * @param - array - recordset
		 * @param - array - report definition
		 * @return - string - Table HTML
		 **/
		 protected static function reportToTable($rset, $fdef)
		 {
		 	$method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	global $clq;
		    	$tbl = $clq->resolve('Table');

		    	// Table instance
		    	$tbl->addTable('datatable', 'table table-striped table-condensed', []);

		    	// caption
		    	$tbl->addCaption($fdef['c_common']);

				// thead
				$tbl->addTSection('thead');
				$tbl->addRow();

				foreach($fdef['d_columns'] as $c => $col) {
					$tbl->addCell($col['d_colname'], 'bluec bold', 'header', C::cfgReadString($col['d_colattrs']));
				}
			    
				// tfoot
				$tbl->addTSection('tfoot');
				$tbl->addRow();
				$numrows = count($fdef['d_columns']);
				$tbl->addCell($fdef['c_options'], 'foot', 'data', ['colspan' => $numrows]);

				// tbody
				$tbl->addTSection('tbody');
				for($r = 0; $r < count($rset); $r++) {
					$tbl->addRow();
					foreach($fdef['d_columns'] as $c => $col) {
						$tbl->addCell($rset[$r][$col['d_colid']]);
					}
				}
			    
				return $tbl->display();	    	

			} catch (Exception $e) {
				return $method.': '.$e->getMessage();
			}	
		 }

		 protected static function reportToJSON($rset, $fdef)
		 {
		 	$method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

			} catch (Exception $e) {
				return $method.': '.$e->getMessage();
			}	
		 }

        /** Gets the HTML content of a Report   
         * 
         * Report parameters are a TOMLMap stored in a dbcollection > report > c_document > d_text
         * @param - array - array of variables
         * @return - String HTML 
         **/
         public static function getReport($vars) 
         {

		    $method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	// Set variables
		    	global $clq;  $sqla = ""; $sqlb = ""; $rcfg = [];
		    	$db = $clq->resolve('Db');
		    	self::$rq = $vars['rq'];
				self::$lcd = $vars['idiom']; 

				// First select gets Report Definition from dbcollection->report
				$sqla = "SELECT * FROM dbcollection WHERE c_type = ? AND c_reference = ?";
				$cell = R::getRow($sqla, ['report', self::$rq['reportref']]);
				$rpt = D::extractAndMergeRow($cell);

				// Report title
				array_key_exists('c_common', $rpt) ? $title = $rpt['c_common'] : $title = ucfirst($rpt['c_order']);

				// Second select gets data from table and flattens
				if($rpt['c_order'] != "") {
					$sqlb = "SELECT * FROM ".$rpt['c_parent']. " WHERE c_type = ?";
					$rawset = R::getAll($sqlb, [$rpt['c_order']]);	
				} else {
					$sqlb = "SELECT * FROM ".$rpt['c_parent'];
					$rawset = R::getAll($sqlb);			
				};
				$rs = D::extractAndMergeRecordset($rawset);

				// Generate HTML for the report
				$html = self::popupReport($rs, $rpt);

				$props = [];
				foreach($rpt['d_columns'] as $colid => $col) {
					$props[] = $col['d_colid'];
				};

				if(count($rs) > 1) {
					$report = [
						'flag' => "Ok",
						'html' => $html,
						'data' => $rs,
						'props' => $props,
						'title' => $title
					];
				} else {
					$report = [
						'flag' => "NotOk",
						'html' => H::div(['class' => 'h2 redc'], Q::cStr('144:No records available')),
						'title' => $title,
					];					
				}

				return $report;

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method,
					'rq' => self::$rq,
					'rcfg' => $rcfg,
					'sqla' => $sqla,
					'sqlb' => $sqlb
				];
				L::cLog($err);
				$report = [
					'flag' => "NotOk",
					'html' => print_f($err),
					'title' => Q::cStr('570:Error with AJAX activity')
				];
				return $report;
			}				      	
         }

		/** Creates the CSS Grid based HTML for a Popup Report 
		 * 
		 * @param - array - The array of report fields
		 * @param - array - Recordset 
		 * @param - string - Tablename
		 * @return - string - Table HTML
		 **/
		 protected static function popupReport($rs, $rpt)
		 {
			global $clq;
		
			// Header
			$hdrcells = "";
			// Step through ordered form fields
			foreach($rpt['d_columns'] as $colid => $col) {
				$hdrcells .= H::div(['class' => 'bold bluec', 'style' => 'grid-column: '.$col['d_colstart'].'/'.$col['d_colend'].'; grid-row: 1/2;'], $col['d_colname']);
			};
			$thead = H::div(['class' => 'reportwrapper'], $hdrcells);

			// Body
			$tbody = "";
			for($r = 0; $r < count($rs); $r++) {
				$row = "";
				// Step through ordered form fields
				foreach($rpt['d_columns'] as $colid => $col) {
					$props = [
						'type' => $col['d_coltype'],
					];
					if($col['d_colattrs'] != '') {
						$props = array_merge($props, C::cfgReadString($col['d_colattrs']));
					}; 
					$row .= H::div(['style' => 'grid-column: '.$col['d_colstart'].'/'.$col['d_colend']], Q::formatCell($col['d_colid'], $rs[$r], $props, $rpt['c_parent'], $rs[$r]['id']));
					unset($props);
				};
				if( ($r != 0) and ($r %26 == 0 ) ) { 
					$tbody .= H::div(['class' => 'reportwrapper pb'], $row);
					$tbody .= H::div(['class' => 'reportwrapper tp10 printhide'], $hdrcells); 
				} else {
					$tbody .= H::div(['class' => 'reportwrapper'], $row);
				}; 
				unset($row);
			};

			// Footer
			$tfoot = "";

			// Generates an HTML Table
			return H::div(['class' => 'gridreport', 'id' => 'gridreport'],
				$thead, $tbody, $tfoot
			);
		 }

	/** Display Reports - Old versions
	 *
	 * displayReport()
	 * - columnReport()
	 * - popupReport()
	 * displayImages()
	 * getReport()
	 * reportGenerator()
	 *
	 *
	 ***********************************************************************************************************/

		/** Generate a Report - Old Version 
		 * 
		 * @param - string - Language code
		 * @param - array - Variables
		 * @return - array of data, including Flag (Ok or NotOk) and HTML
		 **/
		 static function displayReport($vars)
		 {
		    $method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	self::$rq = $vars['rq'];
		    	self::$reporttype = self::$rq['reporttype'];
				self::$lcd = $vars['idiom'];
				self::$table = $vars['table'];
				self::$tabletype = $vars['tabletype'];
  
				global $clq; $model = $clq->resolve('Model'); 
				$rcfg = $model->stdModel('report', self::$table, self::$tabletype);

				// Extend any language fields
				$rcfg['reportheader']['title'] = Q::cStr($rcfg['reportheader']['title']);

				// Get data from table
				$db = $clq->resolve('Db');
				if(self::$tabletype != "") {
					$sqlb = "SELECT * FROM ".self::$table. " WHERE c_type = ?";
					$rs = D::extractAndMergeRecordset(R::getAll($sqlb, [self::$tabletype]));	
				} else {
					$sqlb = "SELECT * FROM ".self::$table;
					$rs = D::extractAndMergeRecordset(R::getAll($sqlb));				
				}
				
				// Order formfields by order
				foreach($rcfg['reportfields'] as $key => $config) {
					if(!array_key_exists('order', $config)) {
						$rcfg['reportfields'][$key]['order'] = 'zz';
					}
				}
				$ordered = Q::array_orderby($rcfg['reportfields'], 'order', SORT_ASC);

				switch(self::$reporttype) {
					
					case "columnreport":
						$html = self::columnReport($ordered, $rs, $vars, $rcfg);	
					break;

					default:
					case "popupreport":
						$html = self::popupReport($ordered, $rs, $vars, $rcfg);
					break;
				}
				
				$report = [
					'flag' => "Ok",
					'html' => $html,
					'options' => $rcfg,
					'data' => $rs
				];

				// Test
				// $clq->get('cfg')['site']['debug'] == 'development' ? self::frm_txt('<span>{{$data}}</span>') : null ;
				$test = [
					'method' => $method,
					'model' => $rcfg,
					'recordset' => $rs,
					'html' => $report,
				];
				// L::cLog($test);

				return $report;

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method,
					'model' => $rcfg,
					'sql' => $sqla
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'html' => $err, 
				]; 
			}	
		 }

		/** Creates the Table HTML for a Column Report - Old Version  
		 * 
		 * @param - array - The array of report fields
		 * @param - array - Recordset 
		 * @param - string - Tablename
		 * @return - string - Table HTML
		 **/
		 protected static function columnReport($ordered, $rs, $vars, $rcfg)
		 {
			
			global $clq; $tbody = "";
			// Step through ordered form fields
			foreach($ordered as $fid => $prop) {
				$fld = $prop['fld'];
				$tr = H::tr(['class' => ''],
					H::td(['class' => 'bluec text-right e20'], Q::cStr($prop['label'])),
					H::td(['class' => 'e80'], Q::formatCell($fld, $row, $prop, $table, $recid) )
				);
				$tbody .= $tr; unset($tr);
			};

			// Generates an HTML Table
			return H::table(['class' => 'table table-bordered table-sm table-condensed pad', 'id' => 'reporttable'],
				H::thead(['class' => ''],
					H::tr(['class' => ''],
						H::td(['class' => 'redc bold e30 text-right'], Q::cStr('135:Field')),
						H::td(['class' => 'redc bold e70'], Q::cStr('138:Value'))
					)
				),
				
				H::tbody(['class' => ''], $tbody),
				H::tfoot(['class' => ''],
					H::tr(['class' => ''],
						H::td(['class' => 'text-right mr20', 'colspan' => 2], 
							H::a(['href' => '#', 'onClick' => 'printReport()', 'class' => 'btn btn-sm btn-success', 'role' => 'button'], Q::cStr('131:Print'))
						)
					)
				)
			);
		 }

        /** Generates a version of Galeria - Old Version   
         * Generates a version of Galeria.Js for use as the report function for a Gallery
         * 
         * @param - array - array of variables
         * @return - String HTML 
         **/
         public static function displayImages($vars)
         {
		    try {

		    	$method = __FUNCTION__."()";
		    	self::$rq = $vars['rq'];
				self::$lcd = $vars['idiom'];
				self::$table = $vars['table'];
				self::$tabletype = $vars['tabletype'];

				global $clq; $model = $clq->resolve('Model'); 
				$rcfg = $model->stdModel('report', self::$table, self::$tabletype);

				// Extend any language fields
				$rcfg['reportheader']['title'] = Q::cStr($rcfg['reportheader']['title']);

				// Get data from table
				$db = $clq->resolve('Db');
				if(self::$tabletype != "") {
					$sql = "SELECT * FROM ".self::$table. " WHERE c_type = ?";
					$rs = D::extractAndMergeRecordset(R::getAll($sql, [self::$tabletype]));	
				} else {
					$sql = "SELECT * FROM ".self::$table;
					$rs = D::extractAndMergeRecordset(R::getAll($sql));				
				}
				
				$images = "";
				for($r = 0; $r < count($rs); $r++) {
					$images .= H::img(['class' => 'h100', 'src' => $rcfg['reportfields']['subdir'].$rs[$r]['d_image'], 'data-title' => $rs[$r]['d_title'][self::$lcd], 'data-description' => $rs[$r]['d_description'][self::$lcd]]);
				};

				// Test
				// $clq->get('cfg')['site']['debug'] == 'development' ? self::frm_txt('<span>{{$data}}</span>') : null ;
				$test = [
					'method' => self::THISCLASS.'->'.$method,
					'model' => $rcfg,
					'recordset' => $rs,
					'html' => H::div(['class' => 'galleria'], $images),
				];
				// L::cLog($test);

				return [
					'flag' => "Ok",
					'html' => H::div(['class' => 'galleria', 'style' => 'max-width: 700px; height: 640px;'], $images),
					'options' => $rcfg,
					'data' => $rs
				];

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => self::THISCLASS.'->'.$method,
					'model' => $rcfg,
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'html' => $err, 
				]; 
			}
         } 

		/** Report Generator  - Old Version 
		 * Provides content for a Report Designer
		 * needs to be completed
		 * @param - array - usual collection of variables as an array
		 * @return - HTML string 
		 **/
		 function reportGenerator($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				global $clq;
			    $rq = $vars['rq'];
			    $table = 'dbcollection';
			    $tabletype = 'report';
				$idiom = $vars['idiom'];

				// Buttons required by this method which will be printed to the right of Breadcrumb row
				$repcfg = C::cfgReadFile('admin/config/reportdesigner.cfg');
 
			    $topbuttons = Q::topButtons($repcfg, $vars, 'reportdesigner');
		    	unset($repcfg['topbuttons']);	

		    	// Tabs and Content
		    	foreach($repcfg['tabs'] as $id => $prop) {
		    		$repcfg['tabs'][$id]['label'] = Q::cStr($prop['label']);
		    		$repcfg['sections'][$id]['title'] = Q::cStr($prop['label']);

		    		switch($id) {

		    			case "collections":

							$model = new Model();
							$dd = $model->get_datadictionary();
							$vardata = [];
							$tbltypes = $dd['tabletypes'];
							ksort($tbltypes); 
							$options = "";
							foreach($tbltypes as $typeid => $row) {
								$options .= H::option(['value' => $row['table'].':'.$row['tabletype']], '('.$row['table'].') '.Q::cStr($row['title']));
							};
		    				$html = H::p(['class' => 'bluec'], Q::cStr('163:Please select one or more collections ....'));
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label right', 'for' => 'collectionselect'], Q::cStr('116:Collections')),
		    					H::div(['class' => 'col-sm-6'],
		    						H::select(['class' => 'form-control h300', 'v-model' => 'collectionselect', 'v-bind:data-id' => 'collectionselect', 'id' => 'collectionselect', 'multiple' => 'multiple'], $options)
		    					)
		    				);
		    				$html .= H::p(['id' => 'collectionchosen']);
		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;
    			
		    			case "fields":
		    				$html = H::p(['class' => 'bluec'], Q::cStr('169:Please select fields ....'));
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label right', 'for' => 'fieldselect'], Q::cStr('168:Fields')),
		    					H::div(['class' => 'col-sm-4'],
		    						H::select(['class' => 'form-control h300', 'id' => 'fieldselect', 'v-bind:data-id' => 'fieldselect', 'v-model' => 'fieldselect', 'multiple' => 'multiple'])
		    					)
		    				);
		    				$html .= H::p(['id' => 'fieldschosen']);
		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;

		    			case "columns":
		    				$html = H::p(['class' => 'bluec'], Q::cStr('170:Put the columns into the desired order'));
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::div(['class' => 'col-sm-6'],
		    						H::span(['class' => 'right col-sm-5'], ''),
		    						H::input(['class' => 'form-control col-sm-1', 'value' => ''])
		    					)
		    				);
		    				$html .= H::p(['id' => 'columnschosen']);
		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;
		    			
		    			case "group":
		    				$options = "";
		    				$html = H::p(['class' => 'bluec'], Q::cStr('172:Select one or two fields by which to group'));
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label right', 'for' => 'group'], Q::cStr('96:Group')),
		    					H::div(['class' => 'col-sm-4'],
		    						H::select(['class' => 'custom-select e100 h60', 'id' => 'group'], $options)
		    					)
		    				);
		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;
		
		    			case "filterby":
		    				
		    				$html = H::p(['class' => 'bluec'], Q::cStr('174:Choose one or more fields by which to filter'));
		    				
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label right'], Q::cStr('173:Filter by')),
		    					H::div(['class' => 'col-sm-4'],
		    						H::select(['class' => 'custom-select e100 h60', 'id' => 'filterby'])
		    					)
		    				);

							$options = Q::cOptions('operands'); // Operand;
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label'], ''),
		    					H::input(['class' => 'form-control col-sm-3 ml15', 'value' => '', 'placeholder' => Q::cStr('135:Field name')]),
		    					H::select(['class' => 'custom-select col-sm-3 ml10', 'id' => 'operand'], $options),
		    					H::input(['class' => 'form-control col-sm-3 ml10 mr10', 'value' => '', 'placeholder' => Q::cStr('138:Value')])
		    				);
							$html .= H::p(['id' => 'filter']);
		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;
		    			
		    			case "join":

		    				$html = H::p(['class' => 'bluec'], Q::cStr('177:Join one or more fields on one table to ....'));

		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label'], Q::cStr('176:Join tables')),
		    					H::select(['class' => 'custom-select col-sm-4 h60', 'id' => 'joinfrom']),
		    					H::button(['type' => 'button', 'class' => 'btn btn-sm btn-default ml5 mr5'], '> >'),
								H::select(['class' => 'custom-select col-sm-4 h60', 'id' => 'jointo'])
		    				);
							$html .= H::p(['id' => 'joinchosen']);
		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;

		    			case "sort":

		    				$html = H::p(['class' => 'bluec'], Q::cStr('178:Select one or more fields to order the result ....'));
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('168:Fields')),
		    					H::div(['class' => 'col-sm-4'],
		    						H::select(['class' => 'custom-select e100 h80', 'id' => 'sortfields', 'multiple' => 'multiple'])
		    					)
		    				);

		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('179:Direction')),
		    					H::span(['class' => 'col-sm-2'], '-'),
		    					H::label(['class' => 'custom-control custom-radio'],
		    						H::input(['class' => 'custom-control-input', 'id' => 'radio1', 'name' => 'radio', 'type' => 'radio']),
		    						H::span(['class' => 'custom-control-indicator']),
		    						H::span(['class' => 'custom-control-description'], 'ASC')
		    					),
		    					H::label(['class' => 'custom-control custom-radio'],
		    						H::input(['class' => 'custom-control-input', 'id' => 'radio2', 'name' => 'radio', 'type' => 'radio']),
		    						H::span(['class' => 'custom-control-indicator']),
		    						H::span(['class' => 'custom-control-description'], 'DESC')
		    					),
		    					H::input(['class' => 'form-control col-sm-1', 'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 1, 'value' => 0])
		    				);

		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;
		    			
		    			case "type":

		    				$html = H::p(['class' => 'bluec'], Q::cStr('180:Select a paged document or report. For a document ....'));

		    				// Radio group for document type
		    				$html .= H::div(['class' => 'form-group row'],
		    					H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('128:Type')),
		    					H::label(['class' => 'custom-control custom-radio'],
		    						H::input(['class' => 'custom-control-input', 'id' => 'radio1', 'name' => 'radio', 'type' => 'radio']),
		    						H::span(['class' => 'custom-control-indicator']),
		    						H::span(['class' => 'custom-control-description'], 'Document')
		    					),
		    					H::label(['class' => 'custom-control custom-radio'],
		    						H::input(['class' => 'custom-control-input', 'id' => 'radio2', 'name' => 'radio', 'type' => 'radio']),
		    						H::span(['class' => 'custom-control-indicator']),
		    						H::span(['class' => 'custom-control-description'], 'Report')
		    					)
		    				);

		    				// Five additional fields
		    				for($q = 1; $q < 6; $q++) {
    							$html .= H::div(['class' => 'form-group row'],
		    						// Field
		    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('135:Field name').' '.$q),
		    						H::input(['class' => 'form-control col-sm-3', 'value' => '']),

		    						// Value
		    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('138:Value').' '.$q),
		    						H::input(['class' => 'form-control col-sm-3', 'value' => ''])
		    					);
		    				}
		
		    				$html .= H::p(['id' => 'columnschosen']);
		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;

		    			case "title":

		    				// Instructions
		    				$html = H::p(['class' => 'bluec'], Q::cStr('181:Select the Title for the report plus optional ....'));

		    				// Title - input text
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('130:Title')),
	    						H::input(['class' => 'form-control col-sm-6', 'value' => '', 'placeholder' => Q::cStr('130:Title')])
	    					);

		    				// Header - textarea
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('182:Header')),
	    						H::textarea(['class' => 'form-control col-sm-6 h80', 'placeholder' => Q::cStr('182:Header')])
	    					);

		    				// Footer - textarea
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('42:Footer')),
	    						H::textarea(['class' => 'form-control col-sm-6 h80', 'placeholder' => Q::cStr('42:Footer')])
	    					);

		    				// Date - datepicker on input
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('183:Date')),
	    						H::input(['class' => 'form-control col-sm-2 datepicker', 'value' => Q::fDate(Q::cNow())])
	    					);

		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;
		    			
		    			case "misc":

		    				// Instructions
		    				$html = H::p(['class' => 'bluec'], Q::cStr('184:To save report, give it a name ..... '));

		    				// Stored file name
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('186:File name')),
	    						H::input(['class' => 'form-control col-sm-6', 'value' => '', 'placeholder' => Q::cStr('186:File name')])
	    					);

	    					// Select language
	    					$idioms = "";
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('187:Language')),
	    						H::select(['class' => 'custom-select col-sm-3'], $idioms)
	    					);

	    					// Records per page
	    					$perpage = "";
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('188:Per page')),
	    						H::select(['class' => 'custom-select col-sm-2'], $perpage)
	    					);

 							// Email address
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('95:Email address')),
	    						H::input(['class' => 'form-control col-sm-6', 'value' => '', 'placeholder' => Q::cStr('95:Email address')])
	    					);

	    					// Description
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('32:Description')),
	    						H::textarea(['class' => 'form-control col-sm-6', 'value' => '', 'placeholder' => Q::cStr('32:Description')])
	    					);

	    					// Style sheet
 							$html .= H::div(['class' => 'form-group row'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('189:Style sheet')),
	    						H::input(['class' => 'form-control col-sm-6', 'value' => '', 'placeholder' => Q::cStr('189:Style sheet')])
	    					);

	    					// Update at runtime
 							$html .= H::div(['class' => 'form-group row mt20'],
	    						H::label(['class' => 'col-sm-2 col-form-label right', 'for' => ''], Q::cStr('191:Update at runtime')),

	    						// Document labels
	    						H::label(['class' => 'custom-control custom-checkbox'],
	    							H::input(['class' => 'custom-control-input', 'type'  => 'checkbox']),
	    							H::span(['class' => 'custom-control-indicator']),
	    							H::span(['class' => 'custom-control-description'], Q::cStr('190:Document labels'))
	    						),

	    						// Title
	    						H::label(['class' => 'custom-control custom-checkbox'],
	    							H::input(['class' => 'custom-control-input', 'type'  => 'checkbox']),
	    							H::span(['class' => 'custom-control-indicator']),
	    							H::span(['class' => 'custom-control-description'], Q::cStr('130:Title'))
	    						),

	    						// Date
	    						H::label(['class' => 'custom-control custom-checkbox'],
	    							H::input(['class' => 'custom-control-input', 'type'  => 'checkbox']),
	    							H::span(['class' => 'custom-control-indicator']),
	    							H::span(['class' => 'custom-control-description'], Q::cStr('183:Date'))
	    						),

	    						// Footer
	    						H::label(['class' => 'custom-control custom-checkbox'],
	    							H::input(['class' => 'custom-control-input', 'type'  => 'checkbox']),
	    							H::span(['class' => 'custom-control-indicator']),
	    							H::span(['class' => 'custom-control-description'], Q::cStr('42:Footer'))
	    						),

	    						// Header
	    						H::label(['class' => 'custom-control custom-checkbox'],
	    							H::input(['class' => 'custom-control-input', 'type'  => 'checkbox']),
	    							H::span(['class' => 'custom-control-indicator']),
	    							H::span(['class' => 'custom-control-description'], Q::cStr('182:Header'))
	    						),

	    						// Filter by
	    						H::label(['class' => 'custom-control custom-checkbox'],
	    							H::input(['class' => 'custom-control-input', 'type'  => 'checkbox']),
	    							H::span(['class' => 'custom-control-indicator']),
	    							H::span(['class' => 'custom-control-description'], Q::cStr('173:Filter by'))
	    						)
	    					);

		    				$repcfg['sections'][$id]['content'] = $html;
		    			break;
		    		} // End Tabs content switch
		    	};

		    	// Buttons
		    	foreach($repcfg['buttons'] as $id => $prop) {
		    		$repcfg['buttons'][$id]['label'] = Q::cStr($prop['label']);
		    	}

				// Name of the Admin Component template which will be loaded from /admin/components/
				$tpl = "admreportdesigner.tpl"; // This component uses Vue

				// Javascript required by this method
				$js = "
			        Cliq.set('langcd', '".$idiom."');
			        Cliq.set('lcd', '".$idiom."');
			        Cliq.set('idioms', ".object_encode(self::$idioms).");
			        Cliq.set('table', '".$table."');
			        Cliq.set('tabletype', '".$tabletype."');
			        Cliq.set('displaytype', 'reportdesigner');
					Cliqr.reportDesigner(".F::jsonEncode($repcfg).");
				";
			    $clq->set('js', $js);

				// Template variables these are used and converted by the template
				$thisvars = [
					'table' => $table,
					'tabletype' => $tabletype,
					'topbuttons' => $topbuttons,
					'repcfg' => $repcfg,
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
				return Q::publishTpl($tpl, $thisvars, "admin/components", "admin/cache");			

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

		 static function getFields($vars)
		 {
			global $clq; $vardata = [];
			$dd = C::cfgReadFile('models/datadictionary.cfg');
			if($vars['tabletype'] != '') {
				$fn = 'models/'.$vars['table'].'.'.$vars['tabletype'].'.cfg';
			} else {
				$fn = 'models/'.$vars['table'].'.cfg';
			};
			$rcfg = C::cfgReadFile($fn);
			$fldstr = $rcfg['common']['fieldsused'];
			$flds = explode(',', $fldstr);
			foreach($flds as $n => $fld) {
				$fld = trim($fld);
				$v = [
					'value' => $fld, 
					'label' => '('.$vars['table'].':'.$fld.') '.Q::cStr($dd['fieldnames'][$fld])
				];
				$vardata[] = $v; unset($v);
			};
			return ['flag' => 'Ok', 'fldoptions' => $vardata];
		 }

} // Report Class Ends

# alias +e+ class
if(!class_exists("G")){ class_alias('Report', 'G'); };