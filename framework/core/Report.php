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
class Report extends HTML
{
	const THISCLASS = "View extends HTML";
	public static $reporthtml = "";
	public static $reportscript = "";
	public static $reportdata = [];
	public static $reporttype = "";
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
	 * reportContent()
	 * displayReport()
	 * - columnReport()
	 * - popupReport()
	 * displayImages()
	 * getReport()
	 * reportGenerator()
	 *
	 ***********************************************************************************************************/

		/**
		 * Generate a Report
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

		/**
		 * Creates the Table HTML for a Column Report
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

		/**
		 * Creates the Table HTML for a Popup Report
		 * @param - array - The array of report fields
		 * @param - array - Recordset 
		 * @param - string - Tablename
		 * @return - string - Table HTML
		 **/
		protected static function popupReport($ordered, $rs, $vars, $rcfg)
		{
			global $clq;
			
			// Header

			$hdrcells = "";
			// Step through ordered form fields
			foreach($ordered as $fid => $prop) {
				$hdrcells .= H::td(['class' => $rcfg['reportheader']['hdrcellclass']], Q::cStr($prop['label']));
			};
			$thead = H::tr(['class' => $rcfg['reportheader']['headerclass']], $hdrcells);

			// Body
			$tbody = "";
			for($r = 0; $r < count($rs); $r++) {
				$row = "";
				// Step through ordered form fields
				foreach($ordered as $fid => $prop) {
					$row .= H::td(['class' => $rcfg['reportheader']['cellclass']], Q::formatCell($fid, $rs[$r], $prop, $vars['table']));
				};
				$tbody .= H::tr(['class' => $rcfg['reportheader']['rowclass']], $row); unset($row);
			};

			// Footer
			$tfoot = "";

			// Generates an HTML Table
			return H::table(['class' => $rcfg['reportheader']['class'], 'id' => $rcfg['id']],
				$thead, $tbody, $tfoot
			);
		}

        /**
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

        /**
         * Gets the HTML content of a Report
         * Report parameters are a TOMLMap stored in a dbcollection > report > c_document > d_text
         * @param - array - array of variables
         * @return - String HTML 
         **/
        public static function getReport($vars) {

		    $method = self::THISCLASS.'->'.__FUNCTION__."()";
		    try {

		    	global $clq;  $sqla = ""; $sqlb = ""; $rcfg = [];
		    	self::$rq = $vars['rq'];
				self::$lcd = $vars['idiom']; 

				global $clq;  
				$sqla = "SELECT c_document FROM dbcollection WHERE c_type = ? AND c_reference = ?";
				$json = R::getCell($sqla, ['report', self::$rq['reportref']]);
				$doc = json_decode($json, true);
				$rcfg = $doc['d_text'];

				array_key_exists('d_title', $doc) ? $title = $doc['d_title'][self::$lcd] : $title = ucfirst(self::$tabletype);

				// Order formfields by order
				foreach($rcfg['reportfields'] as $key => $config) {
					if(!array_key_exists('order', $config)) {
						$rcfg['reportfields'][$key]['order'] = 'zz';
					}
				}; $ordered = Q::array_orderby($rcfg['reportfields'], 'order', SORT_ASC);

				// Get data from table
				$db = $clq->resolve('Db');
				if($rcfg['reportheader']['reporttable'] != "") {
					$sql = "SELECT * FROM ".$rcfg['reportheader']['reporttable']. " WHERE c_type = ?";
					$rs = D::extractAndMergeRecordset(R::getAll($sql, [$rcfg['reportheader']['reporttabletype']]));	
				} else {
					$sql = "SELECT * FROM ".$rcfg['reportheader']['reporttable'];
					$rs = D::extractAndMergeRecordset(R::getAll($sql));				
				}

				$html = self::popupReport($ordered, $rs, $vars, $rcfg);
				
				// Test
				$test = [
					'method' => $method,
					'html' => $html,
					'rq' => self::$rq,
					'rcfg' => $rcfg,
					'sqla' => $sqla,
					'sqlb' => $sqlb
				];
				// L::cLog($test);

				$report = [
					'flag' => "Ok",
					'html' => $html,
					'title' => $title,
				];
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
					'html' => $err
				];
				return $report;
			}				      	
        }

		/**
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