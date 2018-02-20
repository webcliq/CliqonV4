<?php
/**
 * View class - extends HTML
 * display of any single static record
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@webcliq.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class View extends HTML
{
	const THISCLASS = "View extends HTML";
	public static $viewhtml = "";
	public static $viewscript = "";
	public static $viewdata = [];
	public static $viewtype = "";
	private static $idioms = [];
	private static $lcd = "";
	private static $recid = 0;
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

	/** All the View Functions
	 *
	 * viewContent()
	 * - columnView()
	 * - popupView()
	 * helpContent()
	 * displayHelp()
	 *
	 **************************************  View Record  *********************************************/

		/** Generate a Record view for the ID
		 * @param - string - Language code
		 * @param - string - Table Name
		 * @param - string - Table Type
		 * @param - array - Request Variables
		 * @return - array of data, including Flag (Ok or NotOk) and HTML
		 **/
		 static function viewContent($vars)
		 {
		    $method = self::THISCLASS.'->'.__FUNCTION__.'()';
		    try {

		    	
		    	self::$rq = $vars['rq'];
		    	self::$viewtype = self::$rq['viewtype'];
				self::$lcd = $vars['idiom'];
				self::$table = $vars['table'];
				self::$tabletype = $vars['tabletype'];
				self::$displaytype = self::$rq['displaytype'];
				self::$recid = self::$rq['recid'];

				global $clq; $model = $clq->resolve('Model'); 
				$vcfg = $model->stdModel('view', self::$table, self::$tabletype);

				// Get data from record
				$db = $clq->resolve('Db');
				$sql = "SELECT * FROM ".self::$table. " WHERE id = ?";
				$set = R::getRow($sql, [self::$recid]);
				$row = D::extractAndMergeRow($set);				

				// Order formfields by order
				foreach($vcfg['viewfields'] as $key => $config) {
					if(!array_key_exists('order', $config)) {
						$vcfg['viewfields'][$key]['order'] = 'zz';
					}
				}
				$ordered = Q::array_orderby($vcfg['viewfields'], 'order', SORT_ASC);

				switch(self::$viewtype) {
					
					case "columnview":
						$html = self::columnView($ordered, $row, self::$table, self::$recid);
					break;

					case "popupview":
						$html = self::popupView($ordered, $row, self::$table, self::$recid);
					break;

					default:
						$html = self::columnView($ordered, $row, self::$table, self::$recid);
					break;
				}
				
				$test = [
					'method' => $method,
					'model' => $vcfg,
					'row' => $row,
					'html' => $html,
				];
				// L::cLog($test);

				return [
					'flag' => "Ok",
					'model' => $vcfg,
					'html' => $html,
				];

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method,
					'model' => $vcfg,
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'html' => $err, 
				]; 
			}	
		 }

		/** Creates the Table HTML for a Column View
		 * @param - array - The array of view fields
		 * @param - array - Recordset Row
		 * @param - string - Tablename
		 * @param - string(int) - Record number
		 * @return - string - Table HTML
		 **/
		 protected static function columnView($ordered, $row, $table, $recid)
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
			return H::table(['class' => 'table table-bordered table-sm table-condensed pad', 'id' => 'viewtable'],
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
							H::a(['href' => '#', 'onClick' => 'printView()', 'class' => 'btn btn-sm btn-success', 'role' => 'button'], Q::cStr('131:Print'))
						)
					)
				)
			);
		 }

		/** Creates the Table HTML for a Popup View   
		 *
		 * @param - array - The array of view fields
		 * @param - array - Recordset Row
		 * @param - string - Tablename
		 * @param - string(int) - Record number
		 * @return - string - Table HTML
		 **/
		 protected static function popupView($ordered, $row, $table, $recid)
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
			return H::table(['class' => 'table table-bordered table-sm table-condensed pad', 'id' => 'viewtable'],
				H::thead(['class' => ''],
					H::tr(['class' => ''],
						H::td(['class' => 'redc bold e30 text-right'], Q::cStr('135:Field')),
						H::td(['class' => 'redc bold e70'], Q::cStr('138:Value'))
					)
				),
				H::tbody(['class' => ''], $tbody)
			);
		 }

        /** Get Help content for a Collection Type and Reference  
         * 
		 * @param - string - Language code
		 * @param - string - Table Name
		 * @param - string - Table Type
		 * @param - array - Request Variables
         * @return - string - Text
         **/
         static function helpContent($idiom, $table, $tabletype, $rq)
         {
            try {
            	$method = "helpContent()";
                $sql = "SELECT c_document FROM dbcollection WHERE c_type = ? AND c_reference = ?";
                $txt = R::getCell($sql, ['help', $table.':'.$tabletype]);
                // Result should be JSON
                $hlp = json_decode($txt, true);

                if($hlp[$idiom] != '') {
                    $html = $hlp[$idiom];
                } else {
                    $html = H::h5(['class' => 'pad'], Q::cStr('394:Help for this subject and in this language does not yet exist').' - '.$table.':'.$tabletype);
                };

				$test = [
					'method' => self::THISCLASS.'->'.$method,
					'html' => $html,
				];
				// L::cLog($test);

				return [
					'flag' => "Ok",
					'html' => $html,
				];

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => self::THISCLASS.'->'.$method,
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'html' => $err, 
				]; 
			}
         }

        /** Textual content for the Help system
         *
         * @param - array - array of variables
         * @return - String HTML 
         **/
         public static function displayHelp($vars)
         {
	        try {

	        	global $clq;
	        	$rq = $vars['rq'];
	        	if($vars['tabletype'] != "") {
	        		$ref = $vars['table'].':'.$vars['tabletype'];
	        	} else {
	        		$ref = $vars['table'];
	        	}
	        	
	        	$sql = "SELECT c_document FROM ".$rq['table']." WHERE c_type = ? AND c_reference LIKE ?";
	        	$cell = R::getCell($sql, [$rq['type'], '%'.$ref.'%']);
	        	$doc = json_decode($cell, true);

	        	if(count($doc) > 0) {
	        		$title = $doc['d_title'][$vars['idiom']];
	        		$text = $doc['d_text'][$vars['idiom']];
	        	} else {
	        		$title = Q::cStr('85:Help');
	        		$text = Q::cStr('394:No help available for this topic').' : '.$ref;
	        	}

	        	$hlp = H::div(['class' => 'col pad mr20'],
	        		H::h6(['class' => 'redc c40'], $title),
	        		h::div(['class' => ''], $text)
	        	);
				return [
					'flag' => "Ok",
					'html' => $hlp
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'html' => $e->getMessage() 
				]; 
			}	
         }

} // View Class Ends

# alias +e+ class
if(!class_exists("V")){ class_alias('View', 'V'); };