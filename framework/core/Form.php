<?php
/** 
 * Form Generation class - extends HTML
 * Fold Ctrl K3
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@webcliq.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class Form extends HTML
{
	const THISCLASS = "Form extends HTML";
	public static $formhtml = "";
	public static $formscript = "";
	public static $formdata = [];
	public static $vuewatch = "";
	private static $action = 'insert';
	private static $vue = [];
	private static $idioms = [];
	private static $lcd = "";
	private static $recid = 0;
	private static $table = "";
	private static $tabletype = "";
	private static $lw = "col-2";
	private static $cw = "col-10";
	private static $formtype = "columnform";
	private static $displaytype = "datatable";
	private static $rq = []; // _REQUEST or equivalent

	public function __construct() 
	{
		global $clq;
		global $cfg;
		self::$idioms = $cfg['site']['idioms'];
	}

	/** Process content of Form configuration file 
	 * publishForm()
	 * - setFormData()
	 *
	 ********************************************************************************************************/

		/** Publish form  
		 * This a pure PHP implementation of the Cliqon form where the HTML is written to the browser component
		 * for use by the Template or as content for a popup Window.
		 * For the version that processes the Config file to be used as a config for the JS, see FormJSON()
		 * Assembles the form from:
		 * 	fieldset - tbd
		 * 	formheader
		 * 	formfields
		 * 	buttons
		 * the HTML for each field is added o $formhtml and the javascript is added to $formscript
		 **/
		 static function publishForm($vars)
		 {
		
		    try {

		    	$method = self::THISCLASS.'->'.__FUNCTION__."()";
		    	global $clq;
		    	self::$rq = $vars['rq'];
				self::$lcd = $vars['idiom'];
				self::$table = $vars['table'];
				self::$tabletype = $vars['tabletype'];
				self::$displaytype = self::$rq['displaytype'];
				self::$formtype = self::$rq['formtype'];
				self::$recid = self::$rq['recid'];
				switch(self::$rq['action']) {
					case "update": $action = 'u'; break;
					// Add others here as needed
					case "addchild":
					case "insert":
					default: $action = 'c'; break;
				};
				$formdata = []; $row = [];

				$model = $clq->resolve('Model'); 
				$frmcfg = $model->stdModel('form', self::$table, self::$tabletype);

				if(array_key_exists('labelwidth', $frmcfg)) {
					self::$lw = $frmcfg['labelwidth'];
					self::$cw = $frmcfg['formwidth'];					
				};

				if(!array_key_exists('width', $frmcfg)) {
					$frmcfg['width'] = 580;
					$frmcfg['height'] = 640;					
				};

				// Existing record
				if(self::$recid != 0) {
					// Get data from record
					$db = $clq->resolve('Db');
					$sql = "SELECT * FROM ".self::$table. " WHERE id = ?";
					$row = D::extractAndMergeRow(R::getRow($sql, [self::$recid]));			
				};		

				// Order formfields by order
				foreach($frmcfg['formfields'] as $key => $config) {
					if(!array_key_exists('order', $config)) {
						$frmcfg['formfields'][$key]['order'] = 'zz';
					}
				};
				$ordered = Q::array_orderby($frmcfg['formfields'], 'order', SORT_ASC);

				// Step through ordered form fields
				foreach($ordered as $fid => $fld) {

					if($fld['type'] == "rowtext") {
						self::frm_txt(H::span(['class' => $fld['class']], Q::cStr($fld['text'])));
					} else if($fld['type'] == "rowstring") {
						self::frm_string($fld);
					} else if($fld['type'] == "vueonly") {
						self::setFormData($action, $fid, $fld, $row); 	
					} else {

						// Introduce concept of (i)initialise, (c)reate and (u)pdate here

						// (i)initialise - some fields must have value on starting like (c)reate but hidden field
						if(in_array('i', str_split($fld['display'])) && $action == 'c') {
							
							if(self::$rq['action'] == 'addchild' && $fid == 'c_parent') {
								$fld['defval'] = $row['c_reference'];
							};	

							// Get defaults and populate JS Data
							self::setFormData($action, $fid, $fld); 
							// Generate field
							self::frm_hidden($fld);													

						} else if(in_array($action, str_split($fld['display'])) && $action == 'c') {

							if(self::$rq['action'] == 'addchild' && $fid == 'c_parent') {
								$fld['defval'] = $row['c_reference'];
							};	
			
							// Get defaults and populate JS Data
							self::setFormData($action, $fid, $fld); 
							// Generate field
							$method = "frm_".$fld['type'];
							self::$method($fld);


						} else if (in_array($action, str_split($fld['display'])) && $action == 'u') {
							
							// Read existing record and populate JS Data
							self::setFormData($action, $fid, $fld, $row); 
							// Generate field
							$method = "frm_".$fld['type'];
							self::$method($fld);
	
						}						
					}
				}

				self::frm_buttons($frmcfg['buttons']);

				// Test
				// $clq->get('cfg')['site']['debug'] == 'development' ? self::frm_txt('<span>{{$data}}</span>') : null ;
				$test = [
					'method' => $method,
					'model' => $frmcfg,
					'row' => $row,
				];
				// L::cLog($test);

				self::$vue['el'] = "#dataform";
				self::$vue['data'] = self::$formdata;
				self::$vue['mounted'] = self::getFormScript();
				self::$vue['watch'] = self::getVueWatch();
				return [
					'flag' => "Ok",
					'html' => H::form($frmcfg['formheader'], self::getForm()),
					'script' => object_encode(self::$vue),
					'model' => $frmcfg,
					'action' => $action
				];
		
			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method,
					'model' => $frmcfg,
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'html' => $err, 
					'scripts' => ''
				]; 
			}			
		 }

		/** Set form data  
		 * This function allows for the reduction and normalisation of the set formdata process
		 * @param - string - action, currently 'c' or 'u'
		 * @param - string - field ID
		 * @param - array - Field parameters
		 * @param - array (optional) - the record data
		 * @internal - sets formdata for the field(s)
		 **/
		 protected static function setFormData($action, $fid, $fld, $row = null)
		 {

			$idioms = self::$idioms;
			if($action == 'c') { // Insert

				if(array_key_exists('realflds', $fld)) {

					// Complex Field - also cannot be multi-lingual (true??)
					foreach(self::barSplit($fld['realflds']) as $q => $subfldname) {
						is_set('defval', $fld) == true ? self::$formdata[$subfldname] = self::barSplit($fld['defval'])[$q] : self::$formdata[$subfldname] = '';
					}

				} else {

					// Handles idiomtext fields
					if($fld['type'] == 'idiomtext') {
						foreach($idioms as $lcdcode => $lcdname) {
							is_set('defval', $fld) == true ? self::$formdata[$fid.'_'.$lcdcode] = $fld['defval'][$lcdcode] : self::$formdata[$fid.'_'.$lcdcode] = '';
						}
					} else {

						// Simple single Field - do we need to handle Array separately ??
						if(array_key_exists('defval', $fld)) {
							
							if(is_array($fld['defval'])) {
								$def = "";
								foreach($fld['defval'] as $n => $val) {
									$def .= $val.',';
								}
								$def = trim($def, ',');
								self::$formdata[$fid] = $def;
							} else {
								self::$formdata[$fid] = $fld['defval'];
							}
							
						} else {
							self::$formdata[$fid] = '';
						}
					}	
	 	
				}; 

			} else { // Update

				if(array_key_exists('realflds', $fld)) {

					// Handles idiomtext fields
					if($fld['type'] == 'idiomtext') {

						// Complex Field
						foreach(self::barSplit($fld['realflds']) as $q => $subfldname) {
							
							foreach($idioms as $lcdcode => $lcdname) {		
								self::$formdata[$subfldname.'_'.$lcdcode] = $row[$subfldname][$lcdcode];
							}							

						}

					} else {

						// Complex Field
						foreach(self::barSplit($fld['realflds']) as $q => $subfldname) {
							is_set($subfldname, $row) == true ? self::$formdata[$subfldname] = $row[$subfldname] : self::$formdata[$subfldname] = "" ;
						}
					}		

				} else {

					// Handles idiomtext fields
					if($fld['type'] == 'idiomtext') {
						foreach($idioms as $lcdcode => $lcdname) {	
							is_set($fid, $row) == true ? $val = $row[$fid][$lcdcode] : $val = '';
							self::$formdata[$fid.'_'.$lcdcode] = $val;
						}

					} else {
						// Simple single Field
						is_set($fid, $row) == true ? $val = $row[$fid] : $val = '';
						self::$formdata[$fid] = $val;
					}
				}; 

			} 

			return;
		 }

	/** Form Field Types made up of Tags and conforming to Bootstrap4 Form structure protocol
	 * frm_hidden()
	 * frm_text()
	 * frm_tag()
	 * frm_textarea()
	 * frm_select()
	 * frm_radio()
	 * frm_checkbox()
	 * frm_slider()
	 * frm_json()
	 * frm_file()
	 * frm_image()
	 * frm_boolean()
	 * frm_ ()
	 ********************************************************************************************************/

		/**
		* Hidden field
		* @param - array -
		* @return - adds Form HTML to self::form
		**/
		static function frm_hidden($fld)
		{
			$array = self::setFldProps($fld);
			self::setForm(H::input($array));
			return true;
		}

		/**
		* Text field
		* @param - array -
		* @return - adds Form HTML to self::form
		**/
		static function frm_text($fld)
		{
           	array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control' ;
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(['class' => 'form-inline'],
           				self::addIcon($fld, true),
           				H::input(self::setFldProps($fld)),
	           			self::addIcon($fld)
           			),
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);    
           	$js = "";  self::setFormScript($js);  
			return true;       
		}

		/**
		* Tags field
		* @param - array -
		* @return - adds Form HTML to self::form
		**/
		static function frm_tag($fld)
		{
           	array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control';

           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(['class' => 'form-inline border1 h38'],
           				H::input(self::setFldProps($fld)),
	           			self::addIcon($fld)
           			),
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);    
           	$js = "";  self::setFormScript($js);  
			return true;       
		}

		/**
		* Textarea
		* @param - array -
		* @return - adds Form HTML to self::form
		**/
		static function frm_textarea($fld)
		{
			array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control' ;           
           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::textarea(self::setFldProps($fld)),
           			self::addIcon($fld),
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true;  
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_select($fld)
		{
			array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control custom-select' ;  
			array_key_exists('optionclass', $fld) ? $fld['optionclass'] = 'custom-control '.$fld['optionclass'] : $fld['optionclass'] = 'custom-control' ; 	
           	$options = '';

			foreach(self::setOptions($fld) as $val => $label) {
				$options .= H::option(['class' => $fld['optionclass'], 'value' => $val], $label);
			}	

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(['class' => 'form-inline'],
           				H::select(self::setFldProps($fld), 
	           				$options
	           			),
	           			self::addIcon($fld)
           			),	
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);     
			return true; 
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_radio($fld)
		{
           	
           	is_true('inline', $fld) == true ? $divclass = ' form-check-inline' : $divclass = '' ;

           	$options = '';
			foreach(self::setOptions($fld) as $val => $label) {
				$options .= H::div(['class' => 'form-check'.$divclass], // 
					H::label(['class' => 'form-check-label'],
						H::input(['class' => 'form-check-input cliqradio', 'type' => 'radio', 'v-model' => $fld['v-model'], 'value' => $val]), $label
					)
				);
			}	

           	$icn = self::addIcon($fld);
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), H::div(['class' => self::$cw], $options)
           	); self::setForm($frmfld);     
			return true; 
		}	

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_checkbox($fld)
		{
           	is_true('inline', $fld) == true ? $divclass = ' form-check-inline checkbox' : $divclass = '' ;

           	$options = '';
           	$q = 0;
			foreach(self::setOptions($fld) as $val => $label) {
				$options .= H::div(['class' => 'form-check'.$divclass], // 
					H::label(['class' => 'form-check-label'],
						H::input(['class' => 'form-check-input cliqcheckbox checkbox'.$q, 'type' => 'checkbox', 'name' => $fld['v-model'], 'value' => $val, 'data-hook' => 'checkboxtoggle']), $label
					)
				);
				$q++;
			}	
			$options .= H::input(['type' => 'hidden', 'v-model'=> $fld['v-model'], 'id' => $fld['v-model']]);
           	$icn = self::addIcon($fld);
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), H::div(['class' => self::$cw], $options)
           	); self::setForm($frmfld);     
			return true; 
		}

		/**
		*
		* @param - array - subtype = 'range'
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_slider($fld)
		{

           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(['class' => 'form-inline'],
           				H::input(self::setFldProps($fld)),
	           			self::addIcon($fld)
           			),
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);    
           	$js = "";  self::setFormScript($js);  
			return true;  
		}		

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_json($fld)
		{        	
           	array_key_exists('class', $fld) ? $class = $fld['class'] : $class = 'h200' ;
           	array_key_exists('style', $fld) ? $style = $fld['style'] : $style = '' ;  
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw],
           			H::div(['data-type' => 'jsoneditor', 'id' => $fld['v-model'], 'class' => $class, 'style' => $style]),
           			H::input(['type' => 'hidden', 'v-model' => $fld['v-model'], 'name' => $fld['v-model']])
           		)
           	); self::setForm($frmfld);     
			return true; 
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_file($fld)
		{   
			array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control' ;           	
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::input(self::setFldProps($fld),
           			self::addIcon($fld)), 
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);
           	$js = "";  
           	self::setFormScript($js);    
			return true;    
		}	

		/** Form image
		 * this stores the image content in the database record itself
		 * @param - array - field properties
		 * @return - adds Form HTML to self::form
		 **/
		 static function frm_image($fld)
		 {
           	array_key_exists('imgclass', $fld) ? $imgclass = $fld['imgclass'] : $imgclass = 'h80 pad5 border1 mr20' ;
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(self::setFldProps($fld),
           				H::div(['v-if' => '!'.$fld['id']],
           					H::input(['type' => 'file', 'class' => 'form-control', 'data-fldid' => $fld['id'], 'v-on:change' => 'onFileChange'])
           				),
           				H::div(['v-else' => ''],
           					H::img([':src' => $fld['id'], 'class' => $imgclass]),
           					H::button(['type' => 'button', 'class' => 'btn btn-sm btn-danger text-right', 'v-on:click' => 'removeImage', 'data-fldid' => $fld['id']], Q::cStr('515:Remove image'))
           				),
	           			self::addHelp($fld)
           			)
           		)
           	); 
           	self::setForm($frmfld);  
           	$js = "";  
           	self::setFormScript($js);           	   
			return true;  	
		 }

		/** Bootstrap 4 On/Off slider
		 * 
		 * @param - array -
		 * @return - adds Form HTML to self::form
		 * @todo - 
		 **/
		 static function frm_boolean($fld)
		 {

           	$frmfld = H::div(
           		['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],
           			H::div(['class' => 'form-inline'],
	           			H::label(['class' => 'switch'],
		           			H::input(self::setFldProps($fld)), 
		           			H::span(['class' => 'boolean round'])
		           			
		           		),
		           		H::span(['class' => 'ml20'], Q::cStr($fld['helptext']))
           			) 
	
	           	)      	
           	); 
           	self::setForm($frmfld);    
           	$js = "";  
           	self::setFormScript($js);  
			return true;  
		 }	

	/** Complex Multi-field Form Field Types
	 * frm_level()
	 * frm_password()
	 * frm_date()
	 * frm_autocomplete()
	 * frm_idiomtext()
	 * frm_phones()
	 * frm_fullname()
	 * frm_address()
	 * frm_maplocn()
	 * frm_ccard()
	 * frm_identity()
	 * frm_model()
	 * frm_ ()
	 *
	 ********************************************************************************************************/		

		/** Read, Write and Delete Level
		 *
		 * @param - array -
		 * @return - adds Form HTML to self::form
		 * @todo - 
		 **/
		 static function frm_level($fld)
		 {
           	
			$id = $fld['id'];

			$spinbox = "width:55px; padding: 8px 5px;";

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],     			
           			H::div(['class' => 'form-inline accesslevel', 'id' => $id],
							
           				// Read
           				H::span(['class' => 'c1', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Q::cStr('132:Read')], substr(Q::cStr('132:Read'),0,1)),
           				H::input(['type' => 'number', 'id' => $id.'_r', 'class' => 'spinboxes form-control mr5', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Q::cStr('132:Read'), 'min' => '10', 'max' => '100', 'step' => '10', 'style' => $spinbox]),

           				// Write
           				H::span(['class' => 'c1', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Q::cStr('133:Write')], substr(Q::cStr('133:Write'),0,1)),
           				H::input(['type' => 'number', 'id' => $id.'_w', 'class' => 'spinboxes form-control mr5', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Q::cStr('133:Write'), 'min' => '10', 'max' => '100', 'step' => '10', 'style' => $spinbox]),

           				// Delete
           				H::span(['class' => 'c1', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Q::cStr('104:Delete')], substr(Q::cStr('104:Delete'),0,1)),
           				H::input(['type' => 'number', 'id' => $id.'_d', 'class' => 'spinboxes form-control', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Q::cStr('104:Delete'), 'min' => '10', 'max' => '100', 'step' => '10', 'style' => $spinbox]),

						H::input(['type' => 'text', 'readonly' => 'true', 'id' => $id.'_val', 'v-model' => $fld['v-model'], 'class' => 'form-control ml5', 'style' => 'width: 120px'])

	           		),
	           		self::addHelp($fld)	  		
           		)
           	); self::setForm($frmfld);     
           	$js = "

           	";  
           	self::setFormScript($js); 
			return true; 
		 }

		/** Suggestion box
		 * Display a readonly text field where the value is created by selecting an option from a linked select.
		 * Extra values can be added to the linked select
		 * @param - array - field
		 * @return - adds Form HTML to self::form
		 * @todo - 
		 **/
		 static function frm_suggestion($fld)
		 {
           	/*
				listtype = 'dynamic'
				inline = 'true'
				options = 'routes'
				defval = 'admindesktop'
           	*/

			array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control custom-select' ;  
			array_key_exists('optionclass', $fld) ? $fld['optionclass'] = 'custom-control '.$fld['optionclass'] : $fld['optionclass'] = 'custom-control' ; 	
           	$options = '';

			foreach(self::setOptions($fld) as $val => $label) {
				$options .= H::option(['class' => $fld['optionclass'], 'value' => $val], $label);
			}	

           	is_set('class', $fld) ? $fld['class'] = 'form-control suggestion '.$fld['class'] : $fld['class'] = 'form-control suggestion' ;
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(['class' => 'form-inline'],
           				
           				// The field - readonly
           				H::input(self::setFldProps($fld)),

           				// Transfer value
           				H::i(['class' => 'fa fa-fw pointer bluec fa-border vpad10 lightgray fa-lg fa-arrow-left', 'v-on:click' => 'clickicon', 'data-action' => 'transferval', 'data-id' => $fld['id']]),

           				// Options for the field
           				H::select(['class' => 'custom-select ml5', 'data-id' => $fld['id']], $options),

           				// Maintain the options
           				H::i(['class' => 'fa fa-fw pointer bluec fa-border vpad10 lightgray fa-lg fa-external-link-square', 'v-on:click' => 'clickicon', 'data-action' => 'maintainval', 'data-listname' => $fld['options'], 'data-id' => $fld['id']])
           			),
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);    
           	$js = "";  self::setFormScript($js);  
			return true;  
		 }

		/** Password with confirmation
		 *
		 * @param - array -
		 * @return - adds Form HTML to self::form
		 * @todo - 
		 **/
		 static function frm_password($fld)
		 {
			array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control' ;

			$first = [
				'class' => $fld['class'].' mr10',
				'maxlength' => $fld['maxlength'],
				'minlength' => $fld['minlength'],
				'type' => 'password',
				'required' => 'true',
				'style' => $fld['style'],
				'id' => $fld['id'].'_confirm'
			];	
			$first = array_merge($first, self::formatPlaceholder($fld['placeholder']));
			
			$second = self::setFldProps($fld);

           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(['class' => 'form-inline'],
	           			H::input($first),
	           			H::input($second),
	           			// Password Strength ??
	           			self::addIcon($fld)	           			
	           		),
	           		self::addHelp($fld)
           		)
           	); 
           	self::setForm($frmfld);    
           	$js = "";  self::setFormScript($js);  
			return true;  
		 }

		/** Day, month and year pcker using spin box dropdowns
		 *
		 * @param - array -
		 * @return - adds Form HTML to self::form
		 * @todo - 
		 **/
		 static function frm_date($fld)
		 {
           	
			$dopt = "";
			for($d = 1; $d <= 31; $d++) {
				
				$dopt .= '<option class="" value="'.$d.'">'.$d.'</option>';
			}
			
			$mopt = "";
			for($m = 1; $m <= 12; $m++) {
				$mopt .= '<option value="'.$m.'">'.$m.'</option>';
			}
			
			$yopt = "";
			for($y = 2017; $y <= 2027; $y++) {
				$yopt .= '<option value="'.$y.'">'.$y.'</option>';
			}

			$params = array_merge(self::setFldProps($fld), ['v-on:click' =>'plunk('.$fld['id'].')']);

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],     			
           			H::div(['class' => 'form-inline'],
						H::select(['class' => 'dateselect form-control col-md-2 pad3 mr5', 'id' => 'day_'.$fld['id']], $dopt),
						H::select(['class' => 'dateselect form-control col-md-2 pad3 mr5', 'id' => 'month_'.$fld['id']], $mopt),
						H::select(['class' => 'dateselect form-control col-md-2 pad3 mr5', 'id' => 'year_'.$fld['id']], $yopt),
						H::input($params)
	           		)
           		)
           	); self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true;
		 }

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_daterange($fld)
		{

			// Row 1
			$d_datefrom = [
				'span' => [
					'class' => 'c2', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('377:From')
				],
				'input' => [
					'type' => 'date',
					'v-model' => 'd_datefrom',
					'id' => 'd_datefrom',
					'class' => 'form-control datepicker mr10',
					'required' => 'true',
					'style' => 'width: 35%;'
				]
			];

			$d_dateto = [
				'span' => [
					'class' => 'c2', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('378:To')
				],
				'input' => [
					'type' => 'date',
					'v-model' => 'd_dateto',
					'id' => 'd_dateto',
					'class' => 'form-control datepicker',
					'required' => 'true',
					'style' => 'width: 35%;',
				]
			];

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],     			
           			H::div(['class' => 'form-inline'],				

           				// From
           				H::span($d_datefrom['span'], Q::cStr('377:From')),
           				H::input($d_datefrom['input']),

           				// To
           				H::span($d_dateto['span'], Q::cStr('To:To')),
           				H::input($d_dateto['input'])

	           		),
	           		self::addHelp($fld)	  		
           		)
           	); self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true; 
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_autocomplete($fld)
		{
           	global $clq;
           	array_key_exists('class', $fld) ? $fld['class'] = 'form-control bootcomplete '.$fld['class'] : $fld['class'] = 'form-control' ;
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
           			H::div(['class' => 'form-inline'],
           				H::input(self::setFldProps($fld)),
	           			self::addIcon($fld)
           			),
           			self::addHelp($fld)
           		)
           	); self::setForm($frmfld);    
           	$js = "
			    $('#".$fld['id']."').bootcomplete({
			        url: '/ajax/".$clq->get('idiom')."/bootcomplete/".$fld['data-table']."/".$fld['data-tabletype']."/',
			        minLength : 3,
			        dataParams: {operator : 'LIKE', options: ".object_encode($fld['options'])."},
			        idField: false
			    });          		
           	";  
           	self::setFormScript($js);  
			return true;   
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
	 	static function frm_idiomtext($fld)
		{
			$did = $fld['v-model'];
			global $clq;
           	$idioms = self::$idioms;
           	array_key_exists('class', $fld) ? $fld['class'] = 'form-control '.$fld['class'] : $fld['class'] = 'form-control' ;

           	// Language tabs
           	// href must point to outer div of text input or textarea with language added
           	$tabs = ""; $idmcode = [];
			foreach($idioms as $lcdcode => $lcdname) {
				$tabs .= H::li(['class' => 'nav-item'],
					H::a(['class' => 'nav-link', 'data-target' => '#'.$fld['id'].'_div_'.$lcdcode, 'data-toggle' => 'tab', 'role' => 'tab', 'href' => '#'.$fld['id'].'_div_'.$lcdcode], $lcdname)
				);
				$idmcode[] = $lcdcode;
			};
			// Add translate icon
           	if($fld['class'] !== 'form-control tiny') {
           		$tabs .= H::i(['class' => 'fa fa-lg fa-language pointer translatebutton ml10 mt12', 'id' => $did, 'data-idioms' => implode('|', $idmcode)]);
           	};

           	$content = ""; $w = '100%';           	

			foreach($idioms as $lcdcode => $lcdname) {
				
				$props = self::setFldProps($fld, $lcdcode, true);
				$props['style'] = 'width:'.$w;
				$props['id'] = $did.'_'.$lcdcode;
				if($fld['subtype'] == 'textarea') {
					$pane = H::textarea($props);
				} else { // Text
					$pane = H::input($props);
				};	

				// Content div
				$content .= H::div(
					['class' => 'tab-pane form-inline', 'id' => $fld['id'].'_div_'.$lcdcode, 'role' => 'tabpanel', 'style' => 'border:0; padding:0; background: #E0E0E0;'], $pane
				);

			};      	

           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
					// Tab header - language
					H::ul(['class' => 'nav nav-tabs tabssm', 'id' => $fld['id'], 'role' => 'tablist'], $tabs),
					// Tab content - language
					H::div(['class' => 'tab-content', 'id' => 'tabbedcontent'], $content)
           		)
           	); self::setForm($frmfld); 

			$js = " 
				$('#".$fld['id']." li a:first').tab('show');
				
				$('#".$fld['id']." li a').click(function (e) {
					e.preventDefault();
					$(this).tab('show');
				});	
			";

			foreach($idioms as $lcdcode => $lcdname) {
				$tid = $fld['id'].'_'.$lcdcode.'_te';
				$js .= "";
			};

			// 'font', 'calibri', 'georgia',
			self::setFormScript($js);
			return true; 
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_fullname($fld)
		{

			// realflds = 'd_title|d_firstname|d_midname|d_lastname'
			// ;defval = ''		

			$d_title = [
				'v-model' => 'd_title',			
				'class' => 'form-control mr10',
				'style' => 'width: 20%;',
				'placeholder' => Q::cStr('130:Title')

			];	

			$d_firstname = [
				'v-model' => 'd_firstname',
				'class' => 'form-control',
				'required' => 'true',
				'style' => 'width: 70%;',
				'placeholder' => Q::cStr('211:First Name')
			];

			$d_midname = [
				'v-model' => 'd_midname',
				'class' => 'form-control mr10',
				'style' => 'width: 40%;',
				'placeholder' => Q::cStr('212:Middle Name')
			];

			$d_lastname = [
				'v-model' => 'd_lastname',
				'class' => 'form-control',
				'required' => 'true',
				'style' => 'width: 50%;',
				'placeholder' => Q::cStr('213:Last Name')
			];
			
           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
       				H::div(['class' => 'form-inline mb10'],
	           			H::input($d_title),
	           			H::input($d_firstname)				
	           		),
       				H::div(['class' => 'form-inline'],
	           			H::input($d_midname),
	           			H::input($d_lastname)	           							
	           		),
	           		self::addHelp($fld)	
           		)
           	); 
           	self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true;
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_address($fld)
		{
			// realflds = 'd_addr1|d_addr2|d_suburb|d_postcode|d_city|d_region|d_country'
			// defval = '|||||Illes Balears|España'

			// Row 1
			$d_addr1 = [
				'v-model' => 'd_addr1',
				'class' => 'form-control',
				'required' => 'true',
				'style' => 'width: 100%;',
				'placeholder' => Q::cStr('203:Address Line 1')
			];

			// Row 2
			$d_addr2 = [
				'v-model' => 'd_addr2',
				'class' => 'form-control',
				'style' => 'width: 100%;',
				'placeholder' => Q::cStr('204:Address Line 2')
			];

			// Row 3
			$d_suburb = [
				'v-model' => 'd_suburb',
				'class' => 'form-control',
				'style' => 'width: 100%;',
				'placeholder' => Q::cStr('223:Suburb')
			];

			// Row 4

			$d_postcode = [
				'v-model' => 'd_postcode',
				'class' => 'form-control mr10',
				'style' => 'width: 30%;',
				'required' => 'true',
				'placeholder' => Q::cStr('219:Post Code')
			];

			$d_city = [
				'v-model' => 'd_city',
				'class' => 'form-control',
				'required' => 'true',
				'style' => 'width: 60%;',
				'placeholder' => Q::cStr('379:Town or City')
			];

			// Row 5
			$d_region = [
				'v-model' => 'd_region',
				'class' => 'form-control mr10',
				'required' => 'true',
				'style' => 'width: 50%;',
				'placeholder' => Q::cStr('220:Region')
			];

			$d_country = [
				'v-model' => 'd_country',
				'class' => 'form-control',
				'required' => 'true',
				'style' => 'width: 40%;',
				'placeholder' => Q::cStr('210:Country')
			];

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],     			
       				H::div(['class' => 'form-inline mb10'],
	           			H::input($d_addr1)		
	           		),
       				H::div(['class' => 'form-inline mb10'],
	           			H::input($d_addr2)		
	           		),
       				H::div(['class' => 'form-inline mb10'],
	           			H::input($d_suburb)		
	           		),
       				H::div(['class' => 'form-inline mb10'],
	           			H::input($d_postcode),
	           			H::input($d_city)					
	           		),
       				H::div(['class' => 'form-inline'],
	           			H::input($d_region),
	           			H::input($d_country)	           								
	           		),
	           		self::addHelp($fld)
           		)
           	); self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true;
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/
		static function frm_maplocn($fld)
		{
			// realflds = 'd_maplocnx|d_maplocny'
			// ;defval = '2.5|30.5'		

			$d_maplocnx = [
				'v-model' => 'd_maplocnx',			
				'class' => 'form-control mr10',
				'style' => 'width: 40%;',
			];	

			$d_maplocny = [
				'v-model' => 'd_maplocny',
				'class' => 'form-control mr10',
				'style' => 'width: 40%;'
			];
			
           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw], 
       				H::div(['class' => 'form-inline'],
	           			H::input($d_maplocnx),
	           			H::input($d_maplocny),
	           			H::i(['class' => 'fa fa-fw fa-map-marker fa-lg pointer orangec', 'v-on:click' => 'clickicon', 'data-action' => 'geolocate']),
	           			H::i(['class' => 'fa fa-fw fa-map-pin fa-lg pointer orangec', 'v-on:click' => 'clickicon', 'data-action' => 'getcoords'])					
	           		),
	           		self::addHelp($fld)	           		 
           		)
           	); 
           	self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true;
		}

		/**
		* Credit Card details form
		* @param - array - field parameters
		* @return - adds Form HTML to self::form
		* @todo - 
		**/	
		static function frm_creditcard($fld)
		{
			// 'cc_name|cc_cardnumber|cc_issue|cc_validfrom|cc_expirydate|cc_cvccode'

			// Row 1 - Card holder name
			// to add ?? <span class="help-block field-validation-valid" data-valmsg-for="cc-name" data-valmsg-replace="true"></span>
			$cc_name = [
				'span' => [
					'class' => 'c4 txt-right', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('380:Please enter your name as printed on the Credit Card'),
					'data-text' => Q::cStr('94:Name')
				],
				'input' => [
					'type' => 'text',
					'v-model' => 'cc_name',
					'id' => 'cc_name',
					'class' => 'form-control ml10 cc-name valid',
					'data-val' => 'true',
					'autocomplete' => 'cc_name',
					'required' => 'true',
					'aria-required' => 'true',
					'aria-invalid' => 'false',
					'aria-describedby' => 'cc-name-error',
					'style' => 'width: 75%;'
				]
			];

			// Row 2 - Card number    
            // to add ?? <span class="help-block" data-valmsg-for="cc-number" data-valmsg-replace="true"></span>
			$cc_cardnumber = [
				'span' => [
					'class' => 'c4 txt-right', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('381:Please enter Card Number as shown on the Credit Card'),
					'data-text' => Q::cStr('382:Number')
				],
				'input' => [
					'type' => 'tel',
					'v-model' => 'cc_cardnumber',
					'id' => 'cc_cardnumber',
					'class' => 'form-control ml10 cc-number identified visa',
					'required' => 'true',
					'pattern' => '\d{16}',
					'data-val' => 'true',
					'autocomplete' => 'cc_cardnumber',
					'aria-required' => 'true',
					'aria-invalid' => 'false',
					'aria-describedby' => 'cc-number-error',
					'style' => 'width: 75%;'
				]
			];

			// cc_issue|cc_validfrom
			// Row 3 - Issue Number and Valid From
			$cc_issue = [
				'span' => [
					'class' => 'c4 txt-right', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('383:Please enter the Card Issue number as printed on the Dedit Card'),
					'data-text' => Q::cStr('384:Issue')
				],
				'input' => [
					'type' => 'text',
					'v-model' => 'cc_issue',
					'id' => 'cc_issue',
					'pattern' => '\d{1,2}',
					'class' => 'form-control ml10',
					'style' => 'width: 25%;'
				]
			];
			$cc_validfrom = [
				'span' => [
					'class' => 'c4 txt-right', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('385:Please enter the Month the Card became valid as printed on the Dedit Card'),
					'data-text' => Q::cStr('386:Valid From')
				],
				'input' => [
					'type' => 'text',
					'v-model' => 'cc_validfrom',
					'id' => 'cc_validfrom',
					'class' => 'form-control ml10 cc-exp',
					'pattern' => '\d{2}/\d{2}',
					'placeholder' => 'MM / YY',
					'style' => 'width: 25%;'
				]
			];

			// cc_expirydate|cc_cvccode
			// Row 4 - Expiry Date and CVC Code
			$cc_expirydate = [
				'span' => [
					'class' => 'c4 txt-right', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('387:Please enter the Expirydate as printed on the Credit Card'),
					'data-text' => Q::cStr('388:Expiry')
				],
				'input' => [
					'type' => 'tel',
					'v-model' => 'cc_expirydate',
					'id' => 'cc_expirydate',
					'class' => 'form-control ml10 cc-exp',
					'required' => 'true',
					'pattern' => '\d{2}/\d{2}',
					'data-val' => 'true',
					'autocomplete' => 'cc_expirydate',
					'aria-required' => 'true',
					'aria-invalid' => 'false',
					'aria-describedby' => 'cc-number-error',
					'placeholder' => 'MM / YY',
					'style' => 'width: 25%;'
				]
			];

			$cc_cvccode = [
				'span' => [
					'class' => 'c4 txt-right', 
					'data-toggle' => 'tooltip', 
					'data-placement' => 'top', 
					'title' => Q::cStr('389:Please enter the 3 Digit CVC Code as printed on the reverse of the Credit Card'),
					'data-text' => Q::cStr('390:CVC Code')
				],
				'input' => [
					'type' => 'text',
					'v-model' => 'cc_cvccode',
					'id' => 'cc_cvccode',
					'class' => 'form-control ml10',
					'required' => 'true',
					'pattern' => '\d{3}',
					'data-val' => 'true',
					'autocomplete' => 'cc_cvccode',
					'aria-required' => 'true',
					'aria-invalid' => 'false',
					'aria-describedby' => 'cc-number-error',
					'placeholder' => '999',
					'style' => 'width: 25%;'
				]
			];

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],     			
           			
           			// Row 1
       				H::div(['class' => 'form-inline mb10'],
           				H::span($cc_name['span'], $cc_name['span']['data-text']),
           				H::input($cc_name['input'])
	           		),

	           		// Row 2
       				H::div(['class' => 'form-inline mb10'],
           				H::span($cc_cardnumber['span'], $cc_cardnumber['span']['data-text']),
           				H::input($cc_cardnumber['input'])
	           		),

	           		// Row 3
       				H::div(['class' => 'form-inline mb10'],
           				H::span($cc_issue['span'], $cc_issue['span']['data-text']),
           				H::input($cc_issue['input']),
           				H::span($cc_validfrom['span'], $cc_validfrom['span']['data-text']),
           				H::input($cc_validfrom['input'])					
	           		),

	           		// Row 4
       				H::div(['class' => 'form-inline'],
           				H::span($cc_expirydate['span'], $cc_expirydate['span']['data-text']),
           				H::input($cc_expirydate['input']),
           				H::span($cc_cvccode['span'], $cc_cvccode['span']['data-text']),
           				H::input($cc_cvccode['input'])	           								
	           		),
	           		self::addHelp($fld)		
           		)
           	); self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true;
		}

		/**
		*
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/	
		static function frm_identity($fld)
		{

			/*
			realflds = 'd_identity|d_identitytype'
			defval = '|nif'
			type = 'identity'
			listtype = 'static'
			class = 'form-control col-md-6'
			selectclass = 'form-control col-md-5 ml10'
			options = 'nif|NIF,nie|NIE,passport|Passport'
			label = '{fields.d_identity.title}'
			helptext = '9999:Enter your Identity Number and Type of Document'
			required = 'required'
			*/  

			$d_identity = [
				'class' => ' mr10 form-control',
				'required' => 'true',
				'v-model' => 'd_identity'
			];	
			
			$options = '';
			foreach(self::setOptions($fld) as $val => $label) {
				$options .= H::option(['class' => $fld['optionclass'], 'value' => $val], $label);
			}	
			$d_identitytype = [
				'class' => 'form-control',
				'v-model' => 'd_identitytype'
			];	

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],     			
           			H::div(['class' => 'form-inline'],
	           			H::input($d_identity),
	           			H::select($d_identitytype, $options)	           								
	           		),
	           		self::addHelp($fld)	
           		)
           	); self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true; 
		}


		/**
		* 
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/	
		static function frm_repeater($fld)
		{
           	array_key_exists('class', $fld) ? $fld['class'] = 'form-control'.$fld['class'] : $fld['class'] = 'form-control' ;
           	array_key_exists('style', $fld) ? $fld['style'] = 'width: 25%; '.$fld['style'] : $fld['style'] = 'width: 25%;' ;
    
           	$options = '';
			foreach(self::setOptions($fld) as $val => $label) {
				$options .= H::option(['class' => $fld['optionclass'], 'value' => $val], $label);
			};

           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel($fld), 
           		H::div(['class' => self::$cw, 'data-repeater-list' => $fld['groupid']], 
           			H::div(['class' => 'input-group', 'data-repeater-item' => 'data-repeater-item'],
           				H::select(['class' => 'form-control mr5', 'style' => 'width: 60%;'], 
	           				$options
	           			),
           				H::input(self::setFldProps($fld)),
						H::i(['class' => 'fa fa-fw pointer bluec fa-border vpad10 lightgray fa-lg fa-minus ml5', 'data-repeater-delete' => 'data-repeater-delete'])
           			),
           			H::div(['class' => 'form-text'],
           				H::i(['class' => 'fa fa-plus mr10 fa-lg pointer fa-border', 'data-repeater-create' => 'true']),
           				H::small(['class' => ' text-muted'], Q::cStr($fld['helptext']))
           			)
           		)
           	); self::setForm($frmfld);    
           	$js = "";  self::setFormScript($js);  
			return true;   			
		}

		/**
		* 
		* @param - array -
		* @return - adds Form HTML to self::form
		* @todo - 
		**/	
		static function frm_model($fld)
		{		
           	global $clq;
           	$dd = C::cfgReadFile('models/datadictionary.cfg');

           	// Tables
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
        	}

        	// Tabletypes
           	$tts = []; $q = 0;
           	foreach($dd['tabletypes'] as $p => $param) {
           		$tts[$q]['typename'] = $param['tabletype'];
           		$tts[$q]['label'] = Q::cStr($param['title']);
           		$tts[$q]['table'] = $param['table'];
           		$q++;
           	}
           	$ttlist = Q::array_orderby($tts, 'label', SORT_ASC);
			$tabletypes = H::option(['class' => 'pad3', 'value' => ''], Q::cStr('164:Select an option'));
        	foreach($ttlist as $t => $tt) {
        		$tabletypes .= H::option(['class' => 'pad3', 'data-label' => $tt['label'], 'data-table' => $tt['table'], 'value' => $tt['typename']], $tt['label']);
        	}

           	$frmfld = H::div(['class' => 'form-group row'], 
           		self::setLabel($fld), 
           		H::div(['class' => self::$cw],     			
           			H::div(['class' => 'form-inline watch', 'id' => 'model'],
           				H::select(['class' => 'form-control col-md-5 mr5', 'v-model' => 'c_parent', 'data-id' => 'c_parent', 'data-name' => 'table', 'v-on:change' => 'modelChange'], $tables),
           				H::select(['class' => 'form-control col-md-6', 'v-model' => 'c_category', 'data-id' => 'c_category', 'data-name' => 'tabletype'], $tabletypes)
	           		),
	           		self::addHelp($fld),
	           		H::input(['type' => 'hidden', 'v-model' => $fld['v-model'], 'data-id' => $fld['id']])  		
           		)
           	); 

           	self::setForm($frmfld);     
           	$js = "";  
           	self::setFormScript($js); 
			return true;  			
		}

	/** Other Form components
	 * frm_buttons()	// form button group
	 * frm_txt()		// miscellaneous text and instructions
	 * frm_fieldset() 	// open or close
	 ********************************************************************************************************/

		static function frm_buttons($args)
		{		
			$buttons = "";
			foreach($args as $b => $btn) {
				switch($btn['type']) {
					case "submit":
						$btnparams = [
							'type' => 'submit', 
							'class' => 'pointer btn btn-sm btn-primary', 
						];
					break;

					case "reset":
						$btnparams = [
							'type' => 'reset', 
							'class' => 'pointer btn btn-sm btn-warning',
						];
					break;

					default:
					case"button":
						$btnparams = [
							'type' => 'button', 
							'class' => 'pointer btn btn-sm '.$btn['class'], 
							'@click' => $btn['action']
						];
					break;
				}
				$buttons .= H::button($btnparams, Q::cStr($btn['title']));
			}; 
           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel([]), H::div(['class' => self::$cw], 
           		$buttons
           	)); self::setForm($frmfld);     
			return true; 
		}		

		static function frm_txt($txt)
		{		

           	$frmfld = H::div(['class' => 'form-group row'], self::setLabel([]), H::div(['class' => self::$cw], $txt));
           	self::setForm($frmfld);     
			return true; 
		}	


		static function frm_string($fld)
		{		
           	self::setForm($fld['html']);     
			return true; 
		}	

		static function frm_fieldset($fset)
		{		

			$frmfld = H::fieldset('fieldset', ['class' => 'form-group row '.$fset['fieldsetclass']],
				H::legend(['class' => $fset['legendclass']], Q::cStr($fset['legendtext'])),
				self::getForm()
			);
			self::clearForm();
           	self::setForm($frmfld);     
			return true; 
		}

	/** Form Utility functions
	 * setForm();
	 * getForm()
	 * setFormScript()
	 * getFormScripts()
	 * setLabel()
	 * setFldProps()
	 * keyVal()
	 * addIcon()
	 * addHelp()
	 * setOptions()
	 * formatPlaceholder()
	 *
	 *
	 ********************************************************************************************************/

		private static function setForm($html)
		{
			if($html != "") {
				self::$formhtml .= $html.PHP_EOL;
			};
			return true;
		}

		private static function clearForm()
		{
			self::$formhtml = '';
		}

		private static function getForm()
		{
			return self::$formhtml;
		}

		private static function setFormScript($js)
		{
			if($js != "") {
				self::$formscript .= $js;
			}; 
			return true;
		}

		private static function getFormScript()
		{
			return self::$formscript;
		}

		private static function setVueWatch($js)
		{
			if($js != "") {self::$vuewatch .= $js;}; 
			return true;
		}

		private static function getVueWatch()
		{
			return self::$vuewatch;
		}

		/**
		* Create the label
		* tbd - maybe set whole label
		* @param - array - Field array
		* @return - string text string
		**/
		private static function setLabel($fld)
		{
			global $clq;
           	array_key_exists('labelclass', $fld) ? $lc = $fld['labelclass']: $lc = "";

           	// Only display something if the label key has been used
			if(array_key_exists('label', $fld)) {if($fld['label'] != '') {

				// Add a red bold star at the front of the text if an entry in this field is required 
				// and it is possible to not enter a value - not true of radio and select etc.
				if(array_key_exists('required', $fld)) {
					$req = H::span(['class' => 'bold verylarge redc'], '*'); $wrap = 13;
				} else {
					$req = ""; $wrap = 16;
				};	

				// Routine for reducing the font-size of the label text if exceeds 15 characters
				$sm = ""; $txt = Q::cStr($fld['label']);
				if(strlen($txt) > $wrap) {
					$sm = ' smaller';
				}; 
				$labeltxt = H::span(['class' => 'right pad0 mr-15 '.$sm], $req.$txt);

			}};

			$label = H::label(['class' => self::$lw.' form-control-label'.$lc, 'for' => @$fld['v-model']], @$labeltxt);
			return $label;
		}

		/**
		* Set properties for simple field
		* needs to read list of attributes and act upon each one
		* @param - array - Field array
		* @return - array of properties
		**/	
		private static function setFldProps($props, $lcdcode = false)
		{
			
			$fldarray = [];
			array_key_exists('subtype', $props) ? $type = $props['subtype'] : $type = $props['type'] ;
			$fldarray['type'] = $type;
			
			foreach($props as $key => $val) {

				// Send props to keyVal to create more array pairs where necessary
				$attr = self::keyVal($props, $key, $val, $lcdcode);
				if(is_array($attr)) {
					$fldarray = array_merge($fldarray, $attr);
				}
			};

			return $fldarray;
		}

		private static function keyVal($fld, $key, $val, $lcdcode)
		{
			
			switch($key) {
	
				// Keys we have to ignore, because they are already set - will need enhancing
				case "rowstyle":
				case "label":
				case "pricon":
				case "sficon":
				case "listtype":
				case "help":
				case "labelclass":
				case "imgclass":
				case "value":
				case "defval":
				case "order":
				case "type":
				case "subtype":
				case "display":
				case "dbtype":
				case "options":
				case "inline":
				case "click":
				case "name":
				case "realflds":
					return false;
				break;

				case "placeholder": return self::formatPlaceholder($val, $lcdcode); break;

				// Keys that have no value in a tag
				case "required":
				case "autofocus":
				case "readonly":
				case "disabled":
				case "novalidate":
				case "v-validate";
					return [$key => $key];
				break;

				case "style":
					// Style needs to be array, introduced V4.1.1
					if(is_array($val)) {
						$stylestring = "";
						foreach($val as $st => $sv) {
							$stylestring .= $st.':'.$sv.';';
						}
						return [$key => $stylestring];
					} else {
						return false;
					}
				break;
				case "v-modellazy":
				case "v-model":
					if($lcdcode) {
						return [$key => $val.'_'.$lcdcode];
					} else {
						return [$key => $val];
					};
				break;

				default:
					if(!stristr($val, '|v') == false) {
						$val = rtrim($val, '|v');
						return ['v-bind:'.$key => $val]; 
					} else {
						return [$key => $val];
					}
				break;
			}
		}

		private static function addIcon($fld, $pre = false)
		{
			if($pre == false) {if(array_key_exists('sficon', $fld)) {
				$icn = str_replace('fa-', '', $fld['sficon']);
				return H::i(['class' => 'fa fa-fw pointer bluec fa-border vpad10 lightgray fa-lg fa-'.$icn, 'v-on:click' => 'clickicon', 'data-action' => $fld['action']]);	
			}} else {if(array_key_exists('pricon', $fld)) {
				$icn = str_replace('fa-', '', $fld['pricon']);
				return H::i(['class' => 'fa fa-fw pointer bluec fa-border vpad10 lightgray fa-lg fa-'.$icn, 'v-on:click' => 'clickicon', 'data-action' => $fld['praction']]);			
			}};
			return false;
		}

		private static function addHelp($fld)
		{	
			if(array_key_exists('helptext', $fld)) {
				return H::small(['class' => 'form-text text-muted'], Q::cStr($fld['helptext']));
			} else {
				return false;
			}
		}	

		/**
		 * Create Array for use by Select, Radio and Checkbox
		 * @param - array - Params for field
		 * @return - array - HTML
		 **/
		private static function setOptions($params) 
		{
			
			$opts = []; global $clq;
			switch($params['listtype']) {

				case "dynamic": $opts = Q::cList($params['options']); break;

				case "staticidm":
					// 'cr|9999:(I)ncome,dr|9999:(E)xpense'			
					$a1 = explode(',', $params['options']);
					foreach($a1 as $q => $val) {
						$a2 = explode('|', $val);
						$opts[$a2[0]] = Q::cStr($a2[1]);
					}	
				break;

				case "static":
					// 'markr|Mark Richards,dianam|Diana Mason'			
					$a1 = explode(',', $params['options']);
					foreach($a1 as $q => $val) {
						$a2 = explode('|', $val);
						$opts[$a2[0]] = $a2[1];
					}	
				break;

				case "collection": 
					global $clq;
					$model = $clq->resolve('Model');
					$opts = $model->get_tables();
				break;
				
				case "service":
					global $clq;
					$model = $clq->resolve('Model');
					$opts = $model->get_services();
				break;

				case "query":
					// 'table|field1|field2|orderby|ASC|x=y|x=y'
					$f = explode('|', $params['query']);

					// Table
					$sql = "SELECT * FROM ".$f[0]; $q = [];

					// Where
					$where = function ($s) {
						$t = explode('=', $s);
						if ($t[0] != "") {
							return [
								'val' => $t[1],
								'lbl' => $t[0].' = ? AND '
							];
						} else {
							return false;
						}
					};
					if(count($f) > 4) {
						$w = $where($f[5]); if($w) {$q[] = $w['val'];
						$sql .= " WHERE ".$w['lbl'];};
					};
					if(count($f) > 5) {
						$w = $where($f[6]); if($w) {$q[] = $w['val'];
						$sql .= " ".$w['lbl'];};
					};
					if(count($f) > 6) {
						$w = $where($f[7]); if($w) {$q[] = $w['val'];
						$sql .= " ".$w['lbl'];};
					};
					$sql = trim($sql, " AND ");

					// Order by
					if(count($f) > 2) {
						$sql .= " ORDER BY ".$f[3]." ".$f[4];
					};

					if(count($q) > 0) {
						$rawrs = R::getAll($sql,$q);
					} else {
						$rawrs = R::getAll($sql);
					}

					$db = $clq->resolve('Db');
					$rs = D::extractAndMergeRecordset($rawrs);
					
					for($r = 0; $r < count($rs); $r++) {
						$row = $rs[$r];
						$opts[$row[$f[1]]] = $row[$f[2]];
					}
				break;

				default:
					// 'markr|Mark Richards,dianam|Diana Mason'			
					$a1 = explode(',', $params['options']);
					foreach($a1 as $q => $val) {
						$a2 = explode('|', $val);
						$opts[$a2[0]] = $a2[1];
					}	
				break;			
			};
		
			return $opts;
		}

		private static function formatPlaceholder($val, $lcdcode = false)
		{
			if( (stristr($val, '|x') == false) || ($lcdcode) ) {
				return ['placeholder' => Q::cStr($val)];
			} else {
				$val = rtrim($val, '|x');
				return ['placeholder' => $val];
			}
		}

		protected static function getPdate($l, $t)
		{
			date_default_timezone_set(F::get('timezone'));
			$dt = new DateTime();
			if($dt->format($t) == $l) {
				return 'selected="selected"';
			} else {
				return "";
			}					
		}

	/** Other forms
	 *
	 * miscellaneous and plugin forms go here
	 * publishCreatorForm()
	 *
	 ********************************************************************************************************/

		static function publishCreatorForm($vars)
		{
		    try {

		    	$method = self::THISCLASS.'->'.__FUNCTION__."()";
		    	global $clq;
		    	self::$rq = $vars['rq'];
				self::$lcd = $vars['idiom'];
				self::$rq['table'] != "" ? self::$table = self::$rq['table'] : self::$table = "dbcollection" ;
				self::$recid = self::$rq['recid'];
				self::$rq['action'] == "update" ? $action = 'u' : $action = 'c' ;
				$formdata = []; $row = [];
				$frmcfg = [
					'formheader' => [
						'name' => 'dataform',
						'id' => 'dataform',
						'method' => 'POST',
						'action' => '/ajax/'.self::$lcd.'/postcreatorform/',
					],
					'labelwidth' => 'col-2',
					'formwidth' => 'col-10',
					'type' => 'columnform',
					'formfields' => [
						'recid' => [
							'type' => 'hidden',
							'v-model' => 'recid',							
						],
						'table' => [
							'type' => 'hidden',
							'v-model' => 'table',						
						],
						'text' => [
							'type' => 'textarea',
							'v-model' => 'text',
							'class' => 'h300 toml',
							'label' => '510:Record',	
							'id' => 'text',
							'required' => 'true',											
						],										
					],
					'buttons' => [
						'submit' => [
							'type' => 'button',
							'class' => 'btn-danger',
							'title' => '105:Submit',
							'action' => 'submitbutton'
						]
					]
				];
		    	
		    	// Form here
				self::$lw = $frmcfg['labelwidth'];
				self::$cw = $frmcfg['formwidth'];

				// Instructions
				self::frm_txt(H::span(['class' => ''], Q::cStr('511:Insert or edit the contents of the record and press submit')));

				// Existing record
				if(self::$recid != 0) {
					// Get data from record
					$db = $clq->resolve('Db');
					$sql = "SELECT * FROM ".self::$table." WHERE id = ?";
					$row = D::extractAndMergeRow(R::getRow($sql, [self::$recid]));
					$formdata = C::cfgWriteString($row);
				};	

				foreach($frmcfg['formfields'] as $fid => $fld) { 
					// Generate field
					$method = "frm_".$fld['type']; self::$method($fld);					
				};

				self::frm_buttons($frmcfg['buttons']);

				// Test
				// $clq->get('cfg')['site']['debug'] == 'development' ? self::frm_txt('<span>{{$data}}</span>') : null ;
				$test = [
					'method' => $method,
					'model' => $frmcfg,
					'row' => $row,
				];
				// L::cLog($test);

				self::$vue['el'] = "#dataform";
				self::$vue['data'] = [
					'recid' => self::$recid,
					'table' => self::$table,
					'text' => $formdata
				];
				return [
					'flag' => "Ok",
					'html' => H::form($frmcfg['formheader'], self::getForm()),
					'opts' => object_encode(self::$vue),
					'action' => self::$rq['action'] 
				];
		
			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method,
					'query' => $sql,
					'model' => $frmcfg,
				];
				L::cLog($err);
				return [
					'flag' => "NotOk",
					'html' => $err
				]; 
			}			
		}

} # alias +e+ class
if(!class_exists("E")){ class_alias('Form', 'E'); };
