<?php
/**
 * Subset of Cliqon functions and methods
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@cliqon.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Cliq
{

	const THISCLASS = "Cliq";
	public $tblname = 'dbcollection';
    const CLIQDOC = "c_document";

    function __construct() 
    {
        global $clq;
    }
    
    /** Framework public Functions
     *
     * publishTpl() Publish component Template
     * cModel() - gets Model array from database or filesystem
     * cStr() - gets a multi-lingual string from dbcollection
     * cMsg() - Does cStr() on a ref, then replaces a static variable in the string with a dynamic variable
     * uStr() as above but from dbitem
     * cCfg() get config value from database or config file
     * cVal() - gets any value from the database by table, tabletype and reference
     * cList() - gets list of options from database or filesystem and returns this as an array 
     * cValById() - gets any value from the database by table and id
     * cValByRef() - gets any value from the database by table and reference
     * cRowByRef() - gets any row from the database by table, type and reference
     * cAllByRef() - gets all values from the database for a table and tabletype
     * cUname() - checks an API Token ID against logged in user or record in dbuser and confirms if exists
     *
     *****************************************************  Framework  ************************************************/

        /**
         * Common Template publishing function
         * 
         * @param - string - name of template
         * @param - array - variables for the template that will be mounted on the template before it is converted to an HTML string
         * @return - Array - Consisting of three elements - an Ok flag, Html as a string to be rendered into the ID Admin Content 
         * and Data to be consumed by any Vue JS template functions
         **/
        public static function publishTpl($tpl, $vars, $tpldir = "views", $cachedir = "cache")
        {
            // Template engine
            global $clq;
            $razr = new Engine(new FilesystemLoader($clq->get('basedir').$tpldir), $clq->get('basedir').$cachedir);
            return $razr->render($tpl, $vars);
        }

        /**
         * Encapsulates the Model functions
         * @param - string - name of the service index within the model such as datatable, form, common. If the entire model is required, use 'all'
         * @param - string - Table name, such as dbcollection, dbusers 
         * @param - string (optional) - Table Type name such as string, config or cashbook 
         * @return - array - table definistion with overrides for the type
         **/
        static function cModel($cat, $table, $tabletype = '') 
        {
            
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {
                
                global $clq;
                $model = $clq->resolve('Model'); 
                $result = $model->stdModel($cat, $table, $tabletype); 

                if(!is_array($result)) {
                    throw new Exception("Result is not an array as required!");
                }
                // If not returned already 
                return $result;                

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'cat' => $cat,
                    'table' => $table,
                    'tabletype' => $tabletype,
                    'mcfg' => $mcfg,
                    'model' => $result,
                    'reference' => $ref
                ];
                L::cLog($err);
                return false;
            }
        }

        /**
         * Get String variable
         * Firstly, the function looks up the String in the Cache, if exists return value or attribute of value
         * attributes are things such as singular version, plural version and so on
         * see documentation for list of possible attributes
         * if not in Cache, then try lookup in database. If exists, write Cahe file and return
         * if not in Cache or in Database, then probably in Install or Development mode and value can be created
         * in /includes/i18n/idiom_nn.lcd
         *
         * @param   string  $str The configuration variable for which to access and return a value. $str will consist
         *                  of Number:Default:Attribute such as singular/plural or formal/informal or masculine/
         *                  feminine
         *                  Number as in string reference str(nn)
         *                  Default is string
         *                  Attribute is character(s) which must match entries in the JSON Text
         *                  [en]
         *                    1 = 'text'
         *                    [en:2]
         *                      s = 'singular'
         *                      p = 'plural'
         *                  Current attribute codes: (p)lural, (s)ingular, (f)ormal, (i)nformal, (m)asculine, 
         *                  (f)eminine
         * @param   string  $lcd Get a value for this language. If no language code specified, use current language
         * @return  string
         * @access public
         * @static
         **/
		static function cStr($str)
		{
	        try {
	            
	            global $clq;
	            $lcd = Z::zget('Langcd');
	            $method = "cStr()";

	            if(1 !== preg_match('~[0-9]~', $str)){
	                return 'e: '.$str;
	            }; 

	            // Explode string into parts
	            $p = explode(':', $str);
	            $num = $p[0]; 
	            $default = $p[1];
	            if(count($p) > 2) {
	                $attr = $p[2];
	                $useattr = true;
	            } else {
	                $useattr = false;
	            };

	            $ref = "str(".$num.")";
	            
	            // If in development and no value for String yet set, then use check for $num = 9999 and return default
	            if($num === '9999') {
	                $result = $default;
	            } else {
	                $record = self::qStr("string", $method, $ref, true);
	                if($useattr == true) {
	                    // Result will be JSON encoded string, 
	                    $strarray = json_decode($record, true);
	                    $result = $strarray[$attr];
	                } else {
	                    $result = $record;
	                }
	            }  

	            // Test
	            $test = [
	                'method' => self::THISCLASS.'->'.$method,
	                'reference' => $ref,
	                'default' => $default
	            ];

	            // Set to comment when completed
	            // L::cLog($test);  
	            
	            // If not returned already 
	            return $result;                

	        } catch (Exception $e) {
	            $err = [
	                'method' => self::THISCLASS.'->'.$method,
	                'errmsg' => $e->getMessage(),
	                'reference' => $str,
	                'lcd' => $lcd
	            ];
	            L::cLog($err);
	            return false;
	        }
		}

        /**
         * Simple function that works with cStr() to replace a nominated value "~qrepl~" with a replacement string
         * @param - string - usual string for a cStr()
         * @param - string - Replacement value
         * @return - string - modified string
         **/
        static function cMsg($str, $qwith)
        {
            $msg = self::cStr($str);
            return str_replace('~qrepl~', $qwith, $msg);
        }

        /**
         * As cStr but User side from dbitem
         * @param - string - reference consisting of number plus default
         * @return - string - replacement
         **/
        public static function uStr($str)
        {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {
                
                global $clq;
                $lcd = Z::zget('Langcd');

                if(1 !== preg_match('~[0-9]~', $str)){
                    return 'e: '.$str;
                }; 

                // Explode string into parts
                $p = explode(':', $str);
                $num = $p[0]; 
                $default = $p[1];
                if(count($p) > 2) {
                    $attr = $p[2];
                    $useattr = true;
                } else {
                    $useattr = false;
                };

                $ref = "ustr(".$num.")";
                
                // If in development and no value for String yet set, then use check for $num = 9999 and return default
                if($num === '9999') {
                    $result = $default;
                } else {
                    $record = self::qStr("string", $method, $ref, true, 'dbitem');
                    if($useattr == true) {
                        // Result will be JSON encoded string, 
                        $strarray = json_decode($record, true);
                        $result = $strarray[$attr];
                    } else {
                        $result = $record;
                    }
                }  
                return $result;                

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'reference' => $str,
                    'lcd' => $lcd
                ];
                L::cLog($err);
                return false;
            }
        }

        /**
         * Get any translatable configuration value or variable
         * Firstly, the function looks up the value in the Cache, if exists return value as string
         * if not in Cache, then try lookup in database. If exists, write Cache file and return
         * if not in Cache or in Database, then probably in Install or Development mode and list should exist in
         * in /includes/i18n/idiom_nn.lcd
         *
         * @param   string  $str
         * @return  string
         * @access public
         * @static
         **/ 
        public static function cCfg($ref) // site
        { 
            global $clq;
            $result = self::qStr("config", "cCfg()", $ref, true, 'dbitem');
            if($result) {
                return $result;
            } else {
                $c = explode('.', $ref);
                $array = $clq->get('cfg');
                $firstarray = $array[$c[0]];
                $secondkey = $c[1];
                if(array_key_exists($secondkey, $firstarray)) {
                   return $firstarray[$secondkey];
                } else {
                    return "e: ".$ref;
                }
            }
        } 

        /**
         * Get any translatable component value
         * @param - string - reference
         * @return - string - HTML
         **/
        public static function uTxt($ref) // site
        { 
            global $clq;
            $result = self::qStr("text", "uTxt()", $ref, true, 'dbitem');
            if($result) {
                return $result;
            } else {
                return "e: ".$ref;
            }
        }    

        /**
         * Get any translatable section
         * @param - string - reference
         * @return - string - HTML
         **/
        public static function uSecn($ref) // site
        { 
            global $clq;
            $result = self::qStr("section", "uSecn()", $ref, true, 'dbitem');
            if($result) {
                return $result;
            } else {
                return "e: ".$ref;
            }
        }    

        /**
         * Get List as an Array
         * Lookup in database. If exists, return list as an array
         * if not in Database, then probably in Install or Development mode and list should exist in
         * in /admin/data/dbcollection.list.$ref.cfg
         *
         * @param   string  $listname
         * @return  array
         * @access public
         * @static
         **/  
        public static function cList($ref) // list
        {
            
            try {

                global $clq;
                $lcd = Z::zget('Langcd');
                $method = "cList()";

                $qa = [
                    'table' => 'dbcollection',          // dbcollection, dbitem
                    'tabletype' => 'list',       // string, section etc.
                    'docfield' => 'c_document',
                    'field' => 'd_text',            // d_text
                    'reference' => $ref,
                    'idiom' => false,            // true, false
                    'subfield' => false
                ];

                // Look for value in database
                if(is_array(self::qVals($qa)) || !empty(self::qVals($qa))) {

                    $strarray = self::qVals($qa);
                    if(!is_string($strarray)) {
                        throw new Exception("Result returned from database is not a JSON string to be converted to an array as required!");
                    }
                    /*
                    [
                        {"key1":{"en":"lbl_en", "en":"lbl_es"}},
                        {"key2":{"en":"lbl_en", "en":"lbl_es"}}
                    ]
                    */  

                    $listarray = F::jsonDecode($strarray);
                    if(!is_array($listarray)) {
                        throw new Exception("Result returned from database is not an array as required!");
                    }
                    /*
                        [
                            'key1' => ['en' => 'lbl_en', 'es' => 'lbl_es'],
                            'key2' => ['en' => 'lbl_en', 'es' => 'lbl_es'],
                        ]
                    */ 
                    
                    
                // Look for a value in the development cfg
                } else {
                    $fn = 'dbcollection.list.'.$ref.'.cfg';
                    $listarray = C::cfgReadFile('admin/data/'.$fn);
                    
                    if(!is_array($listarray)) {
                        throw new Exception("Result returned from file is not an array as required!");
                    }
                    
                    /*
                    [key]
                        en = 'label'
                        es = 'label'
                    */
                  
                }
                
                $result = [];
                foreach($listarray as $key => $lbls) {
                    $result[$key] = @$lbls[$lcd];
                }                  

                if(!is_array($result)) {
                    throw new Exception("Result is not an array as required!");
                }

                // Test
                $test = [
                    'method' => self::THISCLASS.'->'.$method,
                    'reference' => $ref,
                    'lcd' => $lcd,
                    'result' => $result
                ];

                // Set to comment when completed
                // L::cLog($test);  
                
                // If not returned already 
                return $result;                

            } catch (Exception $e) {
                $err = [
                    'method' => self::THISCLASS.'->'.$method,
                    'errmsg' => $e->getMessage(),
                    'reference' => $ref,
                    'lcd' => $lcd,
                    'listarray' => $listarray
                ];
                L::cLog($err);
                return false;
            }
        } 

        static function cOptions($ref, $selected = '') 
        {
            $opts = self::cList($ref); $options = H::option(['value' => ''], Q::cStr('164:Select an option'));
            foreach($opts as $val => $lbl) {
                $selected == $val ? $s = ['selected' => 'selected'] : $s  = null ;
                $options .= H::option(['value' => $val, $s], $lbl);
            }
            return $options;
        }

        /**
         * Common string handler
         * - from cache
         * - from database
         * - from development ini
         * works only with string value
         * 
         * @param - string - name of the table within the Database
         * @param - string - name of calling method for logging purposes - cCfg(), cStr()
         * @param - string - reference to be looked up
         * @param - boolean - pass onto database method [ qVal() ] whether record is multilingual
         * @return - string - the value to be displayed
         */
		private static function qStr(
	            $type,          // string, text, option, config, plus news, blog
	            $method,        // Name of the calling method
	            $ref,           // Reference to be looked up
	            $idm = false,   // Extract language result from multi-lingual field - true/false
	            $table = "dbcollection", // Table to be looked in
	            $doc = "c_document",
	            $fld = "d_text",   // Field within Document to look up  
	            $subfield = false
			)
		{
            try {
                global $clq;
                $qa = [
                    'table' => $table,          // dbcollection, dbitem
                    'tabletype' => $type,       // string, section etc.
                    'docfield' => $doc,
                    'field' => $fld,            // d_text
                    'reference' => $ref,
                    'idm' => $idm,            // true, false
                    'subfield' => false
                ];

                if($idm == false) {
                    $fn = $table.'-'.$type.'-'.$ref.'.txt';
                } else { // True
                    $lcd = Z::zget('Langcd');
                    $fn = $table.'-'.$type.'-'.$ref.'_'.$lcd.'.txt';
                }

                // Three possibilities

                // First, check for cache value - does file exists for this string??
                // Cache read and write now uses just a string, but string might be Cfg style
                // Returns array or false

                if(is_array(self::cacheRead($fn))) {
                    
                    // Result is array
                    $result = self::cacheRead($fn); 
                    $string = $result[0];          
                    $used = "cache";

                // If no cache Value, try in database
                // Now always returns an array or false

                } elseif( !empty(self::qVal($qa)) ) {

                    $string = self::qVal($qa);

                    // Write string value to cache as array
                    self::cacheWrite($fn, [$string]);

                    $used = "database";

                // Finally look for a value in the development ini
                } else {
                    
                    $fn = $table.'-'.$type.'-'.$lcd.'.lcd';
                    $strarray = C::cfgReadFile('includes/i18n/'.$fn);
                    $string = $strarray[$ref];
                    $used = "language file";
                }

                // Test
                $result = [
                    'method' => self::THISCLASS.'->'.$method,
                    'reference' => $ref,
                    'lcd' => $lcd,
                    'result' => $string,
                    'used' => $used
                ];

                // Set to comment when completed
                // L::cLog($result);  

                // Return result                          
                return $string;

            } catch (Exception $e) {

                $err = [
                    'method' => self::THISCLASS.'->'.$method,
                    'errmsg' => $e->getMessage(),
                ];
                L::cLog($err);
                return false;
            } 
		}

        /**
         * Get Any value from Database
         *
         * @param   string  $table - Will decide which table to be accessed - required
         * @param   string  $type - Tabletype
         * @param   string  $fld - name of the column, defaults to text 
         * @param   string  $ref - The reference to find - required
         * @param   boolean $idm -  if true, routine will find value as per current language; 
         *                          if false, routine returns value from database
         * @return  string
         * @throws  exceptionclass [description]
         *
         * @access public
         * @static true
         **/
        public static function cVal(
            $table,     // Name of Table in Database, eg dbcollection
            $type,      // tabletype, eg string, 
            $fld,       // Always Document as JSON, then name of field within the Document
            $ref,       // Reference to be searched for
            $idm,        // True or False, if true, will expect to find results such as text_en, text_es, if not, just text
            $subfield = false
            )
        {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                // returns boolean false or array of values with language as key or array with numeric zero
                $qa = [
                    'table' => $table,          // dbcollection, dbitem
                    'tabletype' => $type,       // string, section etc.
                    'docfield' => self::CLIQDOC,
                    'field' => $fld,            // d_text
                    'reference' => $ref,
                    'idiom' => $idm,            // true, false
                    'subfield' => false
                ];
                $str = self::qVal($qa);
                if($str != '') {
                    $string = $str;
                } else {
                    $string = "e: ".$ref;
                }

                // Test
                $test = [
                    'method' => $method,
                    'table' => $table,
                    'type' => $type,
                    'reference' => $ref,
                    'field' => $fld,
                    'useidiom' => $idm,
                    'lcd' => $lcd
                ];

                // L::cLog($test);   

                // Return result                          
                return $string;

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'reference' => $ref
                ];
                L::cLog($err);
                return false;
            } 
        }

        /**
         * Get Any single value from database
         *
         * @param   array  $qa - Will contain:
         *  - table = 
         *  - tabletype -
         *  - reference to be searched for
         *  - document field, defaults to c_document
         *  - field within c_document, defaults to d_text
         *  - idiom - true/false
         *  - subfield - used for things like string
         * @return  string with datbase value
         * @throws  exceptionclass [description]
         *
         * @access private
         * @static true
         **/
        private static function qVal($qa)
        {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $lcd = Z::zget('Langcd');

                $sql = "SELECT ".$qa['docfield']." FROM ".$qa['table']." WHERE c_type = ? AND c_reference = ?";
                $record = R::getCell($sql, [$qa['tabletype'], $qa['reference']]);

                $doc = json_decode($record, true);
                if(!is_array($doc)) {
                    $error = $qa['docfield']." did not produce Array: ".$doc;
                    throw new Exception($error);
                }

                $fldval = $doc[$qa['field']];

                if($qa['idm'] == true) {

                    // Stops initial blank page
                    if($lcd == 0) {
                        $lcd = F::getDefLanguage();
                    };
                    $vals = $fldval[$lcd];

                    // Possible that $val is still an array
                    if(is_array($vals)) {
                        $val = $vals[$qa['subfield']];
                    } else {
                        $val = $vals;
                    }

                } else {
                    $val = $fldval;
                }

                if(!is_string($val)) {
                    $error = "Val did not end as String: ".$val;
                    throw new Exception($error);  
                }

                // Test
                $test = [
                    'method' => $method,
                    'qa' => $qa,
                    'result' => $val
                ];

                // Set to comment when completed
                // L::cLog($test);  
                
                // If not returned already 
                return $val;

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'qa' => $qa
                ];
                L::cLog($err);
                return false;
            }
        }

        /**
         * Get an JSON formatted array string 
         *
         * @param   array  $qa - Will contain:
         *  - table = 
         *  - tabletype -
         *  - reference to be searched for
         *  - document field, defaults to c_document
         *  - field within c_document, defaults to d_text
         *  - idiom - true/false - not used
         *  - subfield - used for things like string
         * @return  string with datbase value
         * @throws  exceptionclass [description]
         *
         * @access private
         * @static true
         **/
        private static function qVals($qa)
        {

            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $lcd = Z::zget('Langcd');

                $sql = "SELECT ".$qa['docfield']." FROM ".$qa['table']." WHERE c_type = ? AND c_reference = ?";
                $record = R::getCell($sql, [$qa['tabletype'], $qa['reference']]);

                $doc = json_decode($record, true);
                if(!is_array($doc)) {
                    $error = "c_document did not produce Array: ".$doc;
                    throw new Exception($error);
                }

                $fldval = $doc[$qa['field']];

                /*
                    {"key1":{"en":"lbl_en", "en":"lbl_es"}},
                    {"key2":{"en":"lbl_en", "en":"lbl_es"}}                
                */

                $val = json_encode($fldval);

                if(!is_string($val)) {
                    $error = "Val did not end as String: ".$val;
                    throw new Exception($error);  
                }

                // Test
                $test = [
                    'method' => $method,
                    'qa' => $qa,
                    'result' => $val
                ];

                // Set to comment when completed
                // L::cLog($test);  
                
                // If not returned already 
                return $val;

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'qa' => $qa
                ];
                L::cLog($err);
                return false;
            }
        }

        /**
         * valById presumes that record will exist
         **/
        public static function cValbyId(
            $table,                 // 
            $fld,                   //
            $recid,                 //
            $idm = false,           //
            $docfld = "c_document"  //
            )
        {

            // If first character of $fld == "d", it must be a fld from $docfld (ie c_document)
            if(substr($fld, 0, 1) == "c") {
                $sql = "SELECT ".$fld." FROM ".$table." WHERE id = ?";
                return R::getCell($sql, [$recid]);
            } else {
                $sql = "SELECT ".$docfld." FROM ".$table." WHERE id = ?";
                $json = R::getCell($sql, [$recid]);
                $array = json_decode($json, true);

                if($idm == false) {
                    return $array[$fld];
                } else {
                    $idms = $array[$fld];
                    return $idms[$idm];
                }
            }
        }

        /**
         * Alias for cVal()
         **/
        public static function cValbyRef(
            $table,     // Name of Table in Database, eg dbcollection
            $type,      // tabletype, eg string, 
            $fld,       // Always Document as JSON, then name of field within the Document
            $ref,       // Reference to be searched for
            $idm,        // True or False, if true, will expect to find results such as text_en, text_es, if not, just text
            $subfield = false
            )
        {
            return self::cVal($table, $type, $fld, $ref, $idm, $subfield);
        }

        /**
         * Cliqon core website call
         * Returns either a Row if field is false or a Field from that row
         * if Row contains multi-lingual fields, then the appropriate single language record will be extracted
         * in addition Cache is read and / or written
         **/
        public static function cRowbyRef(
            $table,     // Name of Table in Database, eg dbcollection
            $type,      // tabletype, eg string, 
            $fld,       // Name of fld or false, to return whole row
            $ref,       // Reference to be searched for
            $idm = true // True or False, if true, will expect to find results such as text_en, text_es, if not, just text
            )
        {

            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq; $lcd = $clq->get('idiom'); $nocache = false;

                if($type != '') {

                    $fp = $table.'.'.$type.'.'.$ref.'.'.$fld.'.txt';
                    $rec = self::cacheRead($fp);
                    if(!$rec) {
                        $nocache = true;
                        $sql = "SELECT * FROM ".$table." WHERE c_type = ? AND c_reference = ? LIMIT 1";
                        $rec = R::getRow($sql, [$type, $ref]);
                    };

                } else {

                    $fp = $table.'.'.$ref.'.'.$fld.'.txt';
                    $rec = self::cacheRead($fp);
                    if(!$rec) {
                        $nocache = true;
                        $sql = "SELECT * FROM ".$table." WHERE c_reference = ? LIMIT 1";
                        $rec = R::getRow($sql, [$ref]);
                    };
                };

                // If no cache, write cache
                if($nocache == true) {
                    self::cacheWrite($fp, $rec);
                };

                $db = $clq->resolve('Db');
                // Returns all fields, both single language strings and multi-language arrays
                $row = D::extractAndMergeRow($rec);

                // Get the field value from the Row or return the whole row
                // Best way to do this is create a new Row array with language values flattened
                $result = [];
                foreach($row as $fldname => $value) {
                    $chk = strtolower(substr($fldname, 0, 1));  
                    switch($chk) {

                        // c_ fields are always simple value strings
                        case "c": case "i": $result[$fldname] = $value; break;

                        // d_fields can be simple value strings or json_encoded arrays
                        case "d":  

                            // Can we decode the value and Is $value an array or string
                            $value = is_json($value);
                            if(is_array($value)) {
                                if($idm != true) {
                                    $result[$fldname] = $value[$lcd];
                                } else {
                                    // We will have to return the Field as an array, which was probably intended
                                    $result[$fldname] = $value;
                                };
                            } else {
                                $result[$fldname] = $value;
                            }

                        break;
                        default: throw new Exception("Field name had no usable starting letter! - ".$chk." - ".$fld);
                    }; // End switch                  
                }; // End foreach

                // If a field name was specified
                if($fld !== false) {
                    $val = $result[$fld];
                } else {
                    $val = $result;
                };

                // Test
                $test = [
                    'method' => $method,
                    'dbrecord' => $rec,
                    'row' => $row,
                    'value' => $val
                ];

                // Set to comment when completed
                // L::cLog($test);  
                
                // If not returned already 
                return $val;

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'dbrecord' => $rec,
                    'row' => $row
                ];
                L::cLog($err);
                return false;
            }
        }

        /**
         * Cliqon core website call
         * Returns Collection
         * @param - string - Table type - required
         * @param - array - Parameters to filetr the array
         * @param - string - (optional) Table name defaults to dbitem
         * @param - string - (optional) Typename, just in case the type name is not c_type
         * @param - string - (optional) Referencename, just in case Reference name is not c_reference
         * @return - Recordset Array with Reference as Key to be consumed by Template
         **/
        public static function cAllByType($tabletype, $params = [], $table = 'dbitem', $type = 'c_type', $ref = 'c_reference')
        {
            $sql = "SELECT ".$ref." FROM ".$table." WHERE ".$type." = ?";
            $refs = R::getAll($sql, [$tabletype]);
            $result = [];
            for($r = 0; $r < count($rs); $r++) {
                $result[$rs[$r][$ref]] = self::cRowbyRef($table, $tabletype, $rs[$r][$ref], false, true);
            };

            // Add filtering here

            // Filter by Date - after using array filter

            // Filter by number - slice 


            return $result;
        }

        /**
         * @param - string - username
         * @return - string return username if exists or false
         **/
        public static function cUname($name)
        {
            if($_SESSION['UserName'] == $name) {
                return $name;
            } else {
                $bean = R::findOne('dbuser', 'c_username = ?', [$name]);
                $uname = $bean->c_username;
                if($uname != "") {
                    return $uname;
                } else {
                    return false;
                }                
            }
        }

        /**
         * @param - string - username
         * @return - string return username if exists or false
         **/
        public static function cUserId($id)
        {
            $bean = R::findOne('dbuser', 'id = ?', [$id]);
            $uname = $bean->c_username;
            return $uname;
        }

        /** Static function to add a note to c_notes
         * @param - string - field to be updated
         * @param - string value
         * @return - string for the notes
         **/
        public static function cAddNotes($fld, $val)
        {
            return 'Field: '.$fld.' set to: '.$val.', by '.self::whoMod().' on '.self::lastMod().'\n'.PHP_EOL;
        }

        /** Static function to support contenteditabler user strings
         * @param - string - string reference
         * @return - string - if Operator/Admin has logged in, appropriate content will become editable
         **/
        public static function eStr($str)
        {
            $txt = self::uStr($str);
            if(array_key_exists('UserName', $_SESSION) and $_SESSION['UserName'] != "") {

                global $clq;
                $lcd = Z::zget('Langcd');
                $key = explode(':', $str);

                return '<span 
                    class="contenteditable" 
                    id="id_'.$key[0].'" 
                    data-url="/ajax/'.$lcd.'/updateuserstring/dbitem/string/" 
                    data-type="textarea" 
                    data-ok-button="&#10004;" 
                    data-cancel-button="&#10008;" 
                    data-object="'.$key[0].'" 
                >'.$txt.'</span>';

            } else {
                return $txt;
            }
        }

        /** Static function to support contenteditabler user sections
         * @param - string - section reference
         * @return - string - if Operator/Admin has logged in, appropriate content will become editable
         **/
        public static function eSecn($str)
        {

            $txt = self::uSecn($str);
            if($_SESSION['UserName']) {

                global $clq;
                $lcd = Z::zget('Langcd');

                return '<span 
                    class="contenteditable" 
                    id="'.$str.'" 
                    data-url="/ajax/'.$lcd.'/updateusertext/dbitem/text/" 
                    data-type="textarea" 
                    data-ok-button="&#10004;" 
                    data-cancel-button="&#10008;" 
                    data-object="id_'.$str.'" 
                >'.$txt.'</span>';

            } else {
                return $txt;
            }
        }

    /** Caching
     * cacheRead()
     * cacheWrite()
     * cacheDelete()
     *****************************************************  Caching  ************************************************/

        /**
         * @desc Function read retrieves value from cache
         * @param $fileName - name of the cache file
         * Usage: Cache::read('fileName.extension')
         * @return - array - read single value by $result[0]
         */
        static function cacheRead($fileName) 
        {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {
                $string = ""; $cp = "";
                $cp = self::getPath('cache');
                $fileName = $cp.$fileName;
                if (file_exists($fileName)) {
                    $handle = fopen($fileName, 'rb');
                    $string = fread($handle, filesize($fileName));
                    fclose($handle);
                    return unserialize($string);
                } else {
                    return false;
                }

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'filename' => $fileName,
                    'path' => $cp,
                    'variable' => $variable,     
                ];
                L::cLog($err);
                return false;
            }
        }

        /**
        * @desc Function for writing key => value to cache
        * @param $fileName - name of the cache file (key)
        * @param $variable - value
        * @return - boolean - true or false
        * Usage: Cache::write('fileName.extension', value)
        */
        static function cacheWrite($fileName, $array) 
        {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                $string = ""; $cp = "";
                $cp = self::getPath('cache');
                $fileName = $cp.$fileName;
                $handle = fopen($fileName, 'a');
                $string = serialize($array);
                fwrite($handle, $string);
                fclose($handle);
                return true;

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'filename' => $fileName,
                    'path' => $cp,
                    'string' => $string,     
                ];
                L::cLog($err);
                return false;
            }
        }

        /**
         * @desc Function for deleteing cache file
         * @param $fileName - name of the cache file (key)
         * Usage: Cache::delete('fileName.extension')
         **/
        static function cacheDelete($fileName) 
        {
            
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {
                $cp = self::getPath('cache');
                $filepath = $cp.$fileName;
                @unlink($filepath);
                return "Ok";
            } catch(Exception $e) {
                return "NotOk : ".$e->getMessage();
            }      
        }

        /**
         * Get a path
         * @param - string - the name of the path to find - for example cache
         * @return - string - a path
         */ 
        public static function getPath($str, $abs = false)
        {
            global $clq;
            if($abs === false) {
                return str_replace('//', '/', $clq->get('basedir').$str.'/');
            } else {
                return str_replace('//', '/', $clq->get('rootpath').$str.'/');
            }
            
        }

    /** Database format prepare
     *
     * dbDate()
     * dbDatePlus()
     * dbDateTime()
     * dbNum()   
     *
     * **************************************************  Database Functions  ****************************************/

        /**
         * Format a date for the database
         * @param - string - date
         * @return - string - database date
         * */
        public static function dbDate($str)
        {
            global $clq;
            date_default_timezone_set($clq->get('timezone'));
            $date = new DateTime($str);
            $dbate = $date->format('Y-m-d '); 
            return $dbate;              
        }   

        public static function dbDatePlus($cell = "", $plus = "") {

            global $clq;
            date_default_timezone_set($clq->get('timezone'));     
            $date = new DateTime($cell);
            $diff = $plus."D";
            $date->add(new DateInterval('P'.$diff));
            $dbdate = $date->format('Y-m-d'); 
            return $dbdate; 
        } 

        public static function dbDateMinus($cell = "", $plus = "") {
            
            global $clq;
            date_default_timezone_set($clq->get('timezone'));     
            $date = new DateTime($cell);
            $diff = $plus."D";
            $date->sub(new DateInterval('P'.$diff));
            $dbdate = $date->format('Y-m-d'); 
            return $dbdate; 
        }

        /**
         * Format a date with time for the database
         * @param - string - date
         * @return - string - database date
         * */
        public static function dbDateTime($str)
        {
            global $clq;
            date_default_timezone_set($clq->get('timezone'));
            $date = new DateTime($str);
            $dbatetime = $date->format('Y-m-d H:i:s'); 
            return $dbatetime;              
        }   

        /**
         * Format a number for the database
         * @param - string - value
         * @return - string - database value
         * */
        public static function dbNum($d)
        {
            // $d = str_replace(".", "", $d);
            $d = str_replace(",", ".", $d);
            $d = str_replace("€", "", $d);
            $d = str_replace("£", "", $d);
            $d = str_replace("&euro;", "", $d);
            $d = str_replace("&pound;", "", $d);
            $d = str_replace(" ", "", $d);  
            $d = trim($d);
            return $d;  
        }         

    /** Format
     * Format functions, used in datatables and the like to format a column
     *
     * formatCell()
     * fData()
     * fList()
     * fNum()
     * fIdm()
     * strToArray()
     * tDateSort()
     * displayYesNo()
     * paidUnpaid()
     * fAvatar()
     * fImage()
     * fLogo()
     * fDoc()
     * fMakeLink()
     * fMapLocn()
     * fTags()
     * fIdiomFlag()
     * fSlider()
     * fIdiomText()
     * fFormatJSON()
     *
     * **************************************************  Format Functions  *****************************************/
        
        /**
         * Formats cell contents for most display functions such as grid and table
         * @param - string - fieldname
         * @param - array - Recordset row
         * @param - array - Attributes for Table or Grid column         
         * @param - string - Table from which record is derived
         * @param - number - record id 
         * @return - string - HTML
         **/
        static function formatCell($f, $row, $prop, $table = 'dbcollection', $recid = 0) {

            global $clq; $str = "";
            array_key_exists('type', $prop) ? $type = $prop['type'] : $type = 'text' ;
            switch($type) {
                
                case "currency":
                    $str =self::fNum($row[$f], true);
                break;
                
                case "number":
                    $str = self::fNum($row[$f]);
                break;
                            
                case "document":
                    $str = self::fDoc($row, $f, $prop);
                break;

                case "username":
                    $usr = $clq->resolve('Auth');
                    $str = A::getUserName($row[$f], 2);
                break;

                case "fulladdress":
                    $usr = $clq->resolve('Auth');
                    $str = A::getUserFullAddress($row);
                break;                      
                
                case "fullname":
                    $usr = $clq->resolve('Auth');
                    $str = A::getUserFullName($row);
                break;                          
                
                // Boolean  
                case "yesno":
                    $str = self::displayYesNo($row[$f], $prop);
                break;

                // Paid or Unpaid 
                case "paidunpaid":
                    $str = self::paidUnpaid($f, $row, $prop);
                break;

                // radio, select
                case "list":
                    $str = self::fList($row[$f], $prop['list']);
                break;
                
                case "date":
                    array_key_exists($f, $row) ? $date = $row[$f] : $date = "" ;
                    $str = self::fDate(Q::dbDate($date));
                break;

                case "yearno": case "monthno": case "dayno":
                    $date = Q::dbDate($row[$prop['relates']]);
                    $split = explode('-', $date);
                    $type == "yearno" ? $str = $split[0] : null ; // Year
                    $type == "monthno" ? $str = $split[1] : null ; // Month
                    $type == "dayno" ? $str = $split[2] : null ; // Day
                break;

                case "avatar":
                    $str = self::fAvatar($row[$f], $prop['height']);
                break;

                case "image":
                    $str = self::fImage($row, $f, $prop);
                break;  

                case "logo":
                    $str = self::fLogo($row, $f, $prop);
                break;  

                case "cstr":
                    $str = self::cStr($row[$f]);
                break;
                
                case "nodata":
                    return false;
                break;  

                case "modifiedby":
                    $usr = $clq->resolve('Auth');
                    $sql = "SELECT * FROM dbuser WHERE c_username = ?";
                    $doc = self::jDecode(R::getRow($sql, [$row['c_whomodified']]));
                    $result = A::getUserFullName($doc);
                break;

                // Tags, Checkboxes
                case "tags":
                    $str = self::fTags($row[$f], $prop);
                break;

                case "idiomflag":
                    $str = self::fIdiomFlag($row[$f], $prop);
                break;

                // Slider
                case "slider":
                    $str = self::fSlider($row[$f], $prop);
                break;

                case "idiomtext":
                    $str = self::fIdiomText($row[$f], $prop);
                break;

                case "json":
                    if(array_key_exists($f, $row)) {
                        $str = self::fFormatJSON($row[$f], $prop);
                    } else {
                        $str = '{}';
                    }
                break;

                case "credit":
                    if($row['c_category'] == 'income') {
                        $str = self::fNum( Q::dbNum($row['c_value']), true );
                    } else { // Debit
                        $str = "";
                    }
                break;

                case "debit":
                    if($row['c_category'] == 'expense') {
                        $str = self::fNum( Q::dbNum($row['c_value']), true );
                    } else { // Credit
                        $str = "";
                    }
                break;

                case "email":
                case "file":
                case "url":
                    $str = self::fMakeLink($row, $f, $prop, $type);
                break;

                case "maplocn":
                    $str = self::fMapLocn($row, $f, $prop);
                break;

                // Instructions required
                case "multitext":
                case "combined":
                    // Plugin
                    $plugin = $clq->resolve($prop['plugin']);
                    $method = $prop['method'];
                    $str = $plugin->$method($f, $row, $prop, $table, $recid);
                break;

                case "titlesummary":
                    $str = self::fTitleSummary($row, $f, $prop);
                break;

                case "imageurl":
                    $str = self::fImageUrl($row, $f, $prop);
                break;

                case "toml":
                    $toml = $clq->resolve('Toml');
                    $farray = C::cfgReadString($row[$f]);
                    $str = '<pre class="pre-scrollable" style="width:400px;"><code>'.htmlspecialchars(print_r($farray, true)).'</code></pre>';                   
                break;

                case "string": case "text":
                    // Stops errors with empty and undefined c_doc strings
                    if(array_key_exists($f, $row)) {
                        if(is_array($row[$f])) {
                            $idm = $clq->get('idiom');
                            $str = self::fHypenate($row[$f][$idm]);
                        } else {
                            $str = self::fHypenate($row[$f]);
                        };
                    };
                break;
        
            }

            if(array_key_exists('viewclass', $prop)) {
                return '<span class="'.$prop['viewclass'].'">'.$str.'</span>';
            } else {
                return $str;
            }              
        } 

        /** Hyphenation
         *
         * @param - string - input value
         * @return - string - reformatted
         **/
        public static function fHypenate($str)
        {
            $qrepl = [
                '_', ':'
            ];
            $qwith = [
                ' _ ', ' : '
            ];
            return str_replace($qrepl, $qwith, $str);
        }    

        /**
         * Format a date in local format according to Config setting format
         * @param string - date in Db format or empty string
         * @return string - formatted date
         * */
        public static function fDate($date = '') 
        {
            global $clq;
            if($date == '') {
                $date = self::cNow();
            }
            date_default_timezone_set($clq->get('timezone'));
            $dt = new DateTime($date);
            return $dt->format($clq->get('dateformat'));   
        }

        function fDatePlus($cell = "", $diff = "") {

            global $clq;
            date_default_timezone_set($clq->get('timezone'));
            $date = new DateTime($cell);
            $date->add(new DateInterval('P'.$diff));
            $ddate = $date->format($this->cfg['site.dateformat']);
            return $ddate;
        }

        function fDateMinus($cell = "", $diff = "") {
            global $clq;
            date_default_timezone_set($clq->get('timezone'));
            $date = new DateTime($cell);
            $date->sub(new DateInterval('P'.$diff));
            $ddate = $date->format($this->cfg['site.dateformat']);
            return $ddate;
        }    

        /**
         * Convert list value to list label
         * @param string - value to e looked up
         * @param - string - list in which to look it up
         * @return string - label
         * */
        public static function fList($val, $listname) 
        {
            $list = self::cList($listname);
            if(array_key_exists($val, $list)) {
                return $list[$val];
            } else {
                return 'e: '.$val;
            }     
        }    

        /**
         * Format a number for display on screen etc.
         * @param - string - value
         * @param - boolean - add Currency symbol
         * @return - string - formatted value
         * */
        public static function fNum($val, $currency = false)
        {
            $val = self::dbNum($val);
            global $clq;
            if($currency) {
                
                // currencyformat = '2|,|.|| €'
                $fn = explode('|', $clq->get('currencyformat'));
                if(!isset($dp)) {$dp = $fn[0];};
                if(!isset($ds)) {$ds = $fn[1];};
                if(!isset($ts)) {$ts = $fn[2];};
                if(!isset($ps)) {$ps = $fn[3];};
                if(!isset($as)) {$as = $fn[4];};
                $val = $ps.number_format(+$val, $dp, $ds, $ts).$as;
                return $val;
                
            } else {
                
                // 'numberformat' => '2|,|.', 
                // Used by numbers - dec places, dec sep, thousands sep '2|,|.'
                $fn = explode('|', $clq->get('numberformat'));
                if(!isset($dp)) {$dp = $fn[0];};
                if(!isset($ds)) {$ds = $fn[1];};
                if(!isset($ts)) {$ts = $fn[2];};
                $val = number_format(+$val, $dp, $ds, $ts);
                return $val;
                
            }   
        }   

        /**
         * Convert a language code to a language
         * @param - string - value
         * @return - string - formatted value
         * */
        public static function fIdm($idmcode)
        {
            global $clq;
            $basedir = $clq->get('basedir');
            $idms = C::cfgReadFile($basedir."data/language_codes.cfg");
            return $idms[$idmcode];
        }    
        
        /*
         * Converts a string in the format 'en|Text,es|Texto'
         * to an array
         **/
        public static function strToArray($str)
        {
            $a1 = explode(',', $str);
            $array = [];
            foreach($a1 as $n => $b) {
                $a2 = explode('|', $b);
                $array[$a2[0]] = $a2[1];
            }
            return $array;
        }

        public static function tDateSort($x, $y, $q = 'd_transactiondate', $desc = true) 
        {   
            if($desc == true) {
                return strcmp(self::dbDate($y[$q]), self::dbDate($x[$q]));
            } else {
                return strcmp(self::dbDate($x[$q]), self::dbDate($y[$q]));
            }
        }

        /**
         * Display Yes / No as icon
         */
        public static function displayYesNo($val, $prop) 
        {
            switch($val) {
                case "y": case "1": $bool = "y"; break;
                default: $bool = "n"; break;
            };

            if($bool == "y" ) {
                $img = H::i(['class' => 'fa fa-check-square-o', 'title' => self::cStr('365:Yes or '.$val), 'style' => 'vertical-align: bottom; font-size: 1.3em; margin-top: 3px;', 'data-action' => 'tono']);
            } else {
                $img = H::i(['class' => 'fa fa-square-o', 'title' => self::cStr('366:No or '.$val), 'style' => 'vertical-align: bottom; font-size: 1.3em; margin-top: 3px;', 'data-action' => 'toyes']);
            }
            return $img;
        }

        /**
         * Display Yes / No as icon
         */
        public static function paidUnpaid($f, $row, $prop) 
        {
            switch($row[$f]) {
                case "paid":
                    $img = H::i(['class' => 'fa fa-check-square-o', 'title' => self::cStr('543:Paid'), 'style' => 'vertical-align: bottom; font-size: 1.3em; margin-top: 3px;', 'data-action' => 'tono', 'data-recid' => $row['id'], 'id' => 'paidunpaid_'.$row['id']]); 
                break;

                case "unpaid":
                    $img = H::i(['class' => 'fa fa-square-o', 'title' => self::cStr('557:Unpaid'), 'style' => 'vertical-align: bottom; font-size: 1.3em; margin-top: 3px;', 'data-action' => 'toyes', 'data-recid' => $row['id'], 'id' => 'paidunpaid_'.$row['id']]);
                break;

                case "other":
                    $img = H::i(['class' => 'fa fa-stop-circle-o', 'style' => 'vertical-align: bottom; font-size: 1.3em; margin-top: 3px;']);
                break;

                default:
                    $img = "";
                break;
            }
            return $img;
        }

        /**
         * Convert logo path to image
         * @param string - source string
         * @param - array - including Rollover title, url, optional array of attributes
         * @return string - HTML to display
         * */
        public static function fLogo($row, $f = 'd_logo', $prop = []) 
        {
            
            global $clq;              
            if(array_key_exists('subdir', $prop)) {
                if($src = 'blank.gif') {
                    $src = Q::makeAvatar($row['d_coname'], 40);
                    $subdir = 'public/images/';
                };
                $prop['src'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$subdir.$src;
            } else {
                $prop['title'] = $row['d_title'][$idm];
                $prop['alt'] = $row['d_title'][$idm];
                $prop['aria-label'] = $row['d_title'][$idm];
                $prop['class'] = $prop['class'].' hint--top';
                $opts = array_merge(['src' => $row[$f]], $prop);
            };
            
            $str =  H::img($opts);
            return $str;     
        }  

        /**
         * Get Avatar or Gravatar for URL stored in Cell
         */
        public static function fAvatar($url, $height) 
        {
            $urlstr = '//gravatar.com/avatar/'.md5($url);
            $urlstr .= "s=".$height."&d=mm";
            $str =  H::img([
                'src' => urlencode($urlstr).'.jpg',
                'class' => 'avatar img',
                'style' => 'height:'.$height
            ]);
            return $str;
        }

        /**
         * Convert image path to image
         * @param string - source string
         * @param - array - including Rollover title, url, optional array of attributes
         * @return string - HTML to display
         * */
        public static function fImage($row, $f = 'd_image', $prop = []) 
        {

            global $clq;
            $idm = $clq->get('idiom');  
            if(array_key_exists('subdir', $prop)) {
                $subdir = $prop['subdir'];
                $opts['src'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$subdir.$row[$f];
            } else {
                $prop['title'] = $row['d_title'][$idm];
                $prop['alt'] = $row['d_title'][$idm];
                $prop['class'] = 'img-thumbnail img-fluid float-right '.$prop['class'];
                $opts = array_merge(['src' => $row[$f]], $prop);
            };
            
            $str =  H::img($opts);
            return $str;           
        }   

        /**
         * Display the contents of a Rich Text Document
         * tbd: Needs testing
         * The idea being that one cannot display large sections of text in a traditional View. 
         * We need to provide a Strip Tags summary with a popup facility to see the contents of the document
         * @param - array - Row values
         * @param - string - field ID or name
         * @param - array - Properties for Field
         * @return string - HTML to display
         * */
        public static function fDoc($row, $f, $prop) 
        {
            global $clq;
            $val = $row[$f];
            $sum = $clq->resolve('Summarizer');
            $str = $sum->get_summary($val);
            L::cLog('Summarize: '.$sum->how_we_did());

            return H::div(['class' => ''],
                H::span(['class' => 'right e10'], 
                    H::i(
                        [
                            'class' => 'fa fa-lg bluec fa-eye border1 pad3'
                            // More here when we can test
                        ]
                    )),
                H::span(['class' => 'left e90'], $str)
            );
        }  
        
        /**
         * Display a clickable link with value as text of link
         * tbd Needs testing
         * @param - array - Row values
         * @param - string - field ID or name
         * @param - array - Properties for Field
         * @param - string - Type eg: url, email, filename (with link to doc)
         * @return string - HTML to display
         * */
        public static function fMakeLink($row, $f, $prop, $type)
        {
            $txt = $row[$f];

            $type == 'email' ? $prop['href'] = 'mailto:'.$txt: null ;
            $type == 'url' ? $prop['href'] = $txt: null ;
            $type == 'file' ? $prop['href'] = $txt: null ;

            return H::a($prop, $txt);
        }

        /**
         * Display a clickable map with marker corresponding to d_maplocnx and d_maplocny
         * tbd - needs appropriate testing
         * @param - array - Row values
         * @param - string - field ID or name
         * @param - array - Properties for Field
         * @return string - HTML to display
         * */
        public static function fMapLocn($row, $f = '', $prop = [])
        {
            if($f == "") {
                $id = "maplocn";
                $x = "d_maplocnx";
                $y = "d_maplocny";
            } else {
                $id = $f;
                $x = "d_".$f."x";
                $y = "d_".$f."y";
            };

            array_key_exists('mapclass', $prop) ? $class = 'staticmap '.$prop['mapclass'] : $class = 'staticmap' ;

            return H::img([
                'src' => 'https://maps.googleapis.com/maps/api/staticmap?center='.$row[$x].','.$row[$y].'&markers=color:red%7Clabel:C%7C'.$row[$x].','.$row[$y].'&zoom='.$prop['data-zoom'].'&size='.$prop['data-width'].'x'.$prop['data-height'], 'class' => $class, 'id' => $f,
                'data-zoom' => $prop['data-zoom'],
                'data-width' => $prop['data-width'],
                'data-height' => $prop['data-height'],
                'data-mapx' => $row[$x],
                'data-mapy' => $row[$y]
            ]);
        }

        /**
         * Tags should be a Comma Separated list
         * @param - string - String of characters
         * @param - array - Properties
         * @return - string - Formatted as per Method
         **/
        public static function fTags($val, $prop = [])
        {
            array_key_exists('tagclass', $prop) ? $class = $prop['tagclass'] : $class = 'btn btn-outline-primary btn-sm mr5 round4' ;
            $tags = explode(',', $val);
            $str = "";
            foreach($tags as $t => $tag) {
                $str.= '<button type="button" class="'.$class.'">'.trim($tag).'</button>';
            };
            return $str;
        }

        /**
         *
         * @param - string - 
         * @return - string - Formatted as per Method
         **/
        public static function fIdiomFlag($val, $prop = [])
        {
            global $clq;
            $src = $clq->get('idiomflags')[$val];
            array_key_exists('subdir', $opts) ? $subdir = $opts['subdir'] : $subdir = "/public/flags/" ;
            $opts['src'] = $subdir.$src;
            $str =  H::img($opts);
            return $str;          
        }

        /**
         *
         * @param - string - 
         * @return - string - Formatted as per Method
         **/
        public static function fSlider($val, $prop = [])
        {
            array_key_exists('style', $prop) ? $style = $prop['style'] : $style = "height: 20px;" ; 
            array_key_exists('valuemax', $prop) ? $max = $prop['valuemax'] : $max = "100" ; 
            array_key_exists('valuemin', $prop) ? $min = $prop['valuemin'] : $min = "0" ; 
            array_key_exists('class', $prop) ? $class = $prop['barclass'] : $class = "primary" ; 
            return H::div(['class' => 'progress'],
                H::div([
                    'class' => 'progress-bar '.$class,
                    'role' => 'progressbar',
                    'aria-valuenow' => $val,
                    'aria-valuemin' => $min,
                    'aria-valuemax' => $max,
                    'style' => $style,
                ])
            );
        }

        /** Not correct
         * Idiomtext 
         * @param - array - Value is already converted to an array, only needs language code to display
         * @param - array - Properties
         * @return - string - Formatted as per Method
         **/
        public static function fIdiomText($val, $prop)
        {
            global $clq;
            $idms = $clq->get('idiomflags');
            array_key_exists('subdir', $prop) ? $subdir = $prop['subdir'] : $subdir = "/public/flags/" ;
            
            $rows = "";
            foreach($idms as $idmcode => $flag) {
                $rows .= H::div(['class' => '', 'style' => 'width: 100%; min-height: 28px;'],
                    H::img([
                        'src' => $subdir.$flag,
                        'class' => 'flag img right',
                        'style' => 'width: 24px;',
                        'title' => self::fIdm($idmcode)
                    ]),
                    H::div(['class' => ''], $val[$idmcode])
                );
            }

            return H::div(['class' => ''], $rows);
        }

        /**
         * Format the Array as JSON
         * @param - array - Value is already converted to an array
         * @param - array - Properties
         * @return - string - Formatted as per Method
         **/
        public static function fFormatJSON($val, $prop = [])
        {
            
            array_key_exists('from', $prop) ? $from_array = $prop['from'] : $from_array = false ; 
            array_key_exists('indent', $prop) ? $indent = $prop['indent'] : $indent = 0 ; 
            $fn = __FUNCTION__;
            $esc = function($str) {
                return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str);
            };

            $json = '';
            if(count($val) > 1) {
                foreach ($val as $key => $value) {
                    $json .= str_repeat("\t", $indent + 1);
                    $json .= "\"".$esc((string)$key)."\": ";

                    if (is_object($value) || is_array($value)) {
                        $json .= "\n";
                        $json .= self::$fn($value, $indent + 1);
                    } elseif (is_bool($value)) {
                        $json .= $value ? 'true' : 'false';
                    } elseif (is_null($value)) {
                        $json .= 'null';
                    } elseif (is_string($value)) {
                        $json .= "\"" . $esc($value) ."\"";
                    } else {
                        $json .= $value;
                    }

                    $json .= ",\n";
                }
            }
    

            if (!empty($json)) {
                $json = substr($json, 0, -2);
            }

            $json = str_repeat("\t", $indent) . "{\n" . $json;
            $json .= "\n" . str_repeat("\t", $indent) . "}";

            return $json;
        }

        /**
         * Concatenates Title and Summary for use in a table
         * tbd - needs appropriate testing
         * @param - array - Row values
         * @param - string - field ID or name
         * @param - array - Properties for Field
         * @return string - HTML to display
         * */
        public static function fTitleSummary($row, $f = 'd_title', $prop = [])
        {
            global $clq;
            $lcd = Z::zget('Langcd');
            array_key_exists('summary', $prop) ? $s = $prop['summary']: $s = 'd_description';
            return H::span($prop, 
                // Title
                H::span(['class' => 'bold'], $row[$f][$lcd].': '),
                // Summary
                H::span(['class' => ''], $row[$s][$lcd])
            );
        }

        /**
         * Formats an Image with a HREF
         * tbd - needs appropriate testing
         * @param - array - Row values
         * @param - string - field ID or name
         * @param - array - Properties for Field
         * @return string - HTML to display
         * */
        public static function fImageUrl($row, $f = 'd_image', $prop = [])
        {
            global $clq;
            $idm = Z::zget('Langcd');        
            array_key_exists('url', $prop) ? $u = $prop['url']: $u = 'd_url';
            $prop['title'] = $row['d_title'][$idm];
            $prop['alt'] = $row['d_title'][$idm];
            $prop['class'] = 'maxh6 maxc6 img-thumbnail img-fluid float-right '.$prop['class'];
            $img = array_merge(['src' => $row[$f]], $prop);
            return H::a(['href' => $row[$u], 'target' => '_blank', 'class' => 'imgurl'],
                H::img($img)
            );
        }

        /**
        * Creates a portion of a date from a supplied full date
        * used to work out month and day numbers etc.
        **/
        public static function fDatePart($date, $which = "m", $verbose = false)
        {
            try {
                global $clq;
                date_default_timezone_set($clq->get('timezone'));
                $dt = new DateTime($date);
                switch($which) {
                    case "d": $verbose == false ? $result = $dt->format('d') : $result = $dt->format('D') ; break;
                    case "w": $result = $dt->format('W'); break; // Add week number
                    case "m": $verbose == false ? $result = $dt->format('m') : $result = $dt->format('M') ; break;
                    case "y": $result = $dt->format('Y'); break;
                }
                return $result;
            } catch(Exception $e) {
                return $e->getMessage();
            }
        }

        public static function fDaysofWeek($dayn)
        {
            $days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
            return $days[$dayn];
        }  

        public static function fMthofYear($mthn)
        {
            $mths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            return $mths[$mthn];           
        }  

    /** Utilities
	 *
	 * toOrderStr()
     * lastMod()
     * whoMod()
     * versionControl()
     * cNow()
     * rand_uniqid()
     * proper_parse_str()
     * cnUrl()
     * getTranslation()
     * - getToken()
     * - curlRequest()
     * convertDstoRq()
     * getPath()
     * print_p()
     * readCfg()
     * makeAvatar()
     * topButtons()
     *
     *****************************************************  Utilities  ********************************************/

	         /**
         * Converts a Number to 2 character Order
         * @param - number
         * @return - string 2 character string eg "aa" or "az"
         **/
		static function toOrderStr($number) {
            
            /*
            $r = (+$q/26);
            $letters = range('a','z');
            return $letters[$r].$letters[$q];
            */

            $alphabet = range('a', 'z');
            $number--;
            $count = count($alphabet);
            if($number <= $count)
                return 'a'.$alphabet[$number+1];
            while($number > 1){
                $modulo     = ($number + 1) % $count;
                $alpha      = $alphabet[$modulo].$alpha;
                $number     = floor((($number - $modulo) / $count));
            };
            return $alpha;
        } 
		
        static function lastMod()
        {
            global $clq;
            date_default_timezone_set($clq->get('cfg')['site']['timezone']);
            $dt = new DateTime('now'); 
            return $dt->format('Y-m-d H:i:s');
        }

        /**
         * Convert current logged in admin user to username for database activities
         * @return string admin username
         **/
        static function whoMod() 
        {
            return Z::zget('UserName');
        }

        static function versionControl($vars)
        {
            global $clq;
            $rq = $vars['rq'];
            $recid = $rq['recid'];
            $comcfg = self::cModel('common', $vars['table'], $vars['tabletype']);
            $fld = $comcfg['versioncontrol'];

            if($fld == 'c_version') { // Copy record to dbarchive
                // Read record 
                $sqla = "SELECT * FROM ".$vars['table']." WHERE id = ?";
                $oldrow = R::getRow($sqla, [$recid]);
                $indb = R::dispense('dbarchive');
                foreach($oldrow as $key => $val) {
                    if($key != 'c_parent') {
                        $indb->$key = $val;
                    }
                }
                $indb->c_parent = $recid;
                $result = R::store($indb); 
            } 
            
            $sqlc = "SELECT ".$fld." FROM ".$vars['table']." WHERE id = ?";
            $existing = R::getcell($sqlc, [$recid]);
            $lastnum = filter_var($existing, FILTER_SANITIZE_NUMBER_INT);            
            $nextnum = (int)$lastnum + 1;           

            return ['fld' => $fld, 'newval' => $nextnum];
        }

        /**
         * Converts current time for given timezone (considering DST)
         *  to 14-digit UTC timestamp (YYYYMMDDHHMMSS)
         *
         * DateTime requires PHP >= 5.2
         *
         * @param $str_user_timezone
         * @param string $str_server_timezone
         * @param string $str_server_dateformat
         * @return string
         */
        static function cNow(
            $str_user_timezone = 'Europe/Madrid',
            $str_server_timezone = 'Europe/Madrid',
            $str_server_dateformat = 'Y-m-d H:i:s') 
        {
         
            // set timezone to user timezone
            date_default_timezone_set($str_user_timezone);

            $date = new DateTime('now');
            $date->setTimezone(new DateTimeZone($str_server_timezone));
            $str_server_now = $date->format($str_server_dateformat);

            // return timezone to server default
            date_default_timezone_set($str_server_timezone);

            return $str_server_now;
        }

        static function rand_uniqid($val, $to_num = false, $pad_up = 8, $passKey = null)
        {
            $valdex = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            if ($passKey !== null) {
                // Although this function's purpose is to just make the
                // ID short - and not so much secure,
                // you can optionally supply a password to make it harder
                // to calculate the corresponding numeric ID

                for ($n = 0; $n<strlen($valdex); $n++) {
                    $i[] = substr( $valdex,$n ,1);
                }

                $passhash = hash('sha256',$passKey);
                $passhash = (strlen($passhash) < strlen($valdex))
                    ? hash('sha512',$passKey)
                    : $passhash;

                for ($n=0; $n < strlen($valdex); $n++) {
                    $p[] =  substr($passhash, $n ,1);
                }

                array_multisort($p,  SORT_DESC, $i);
                $valdex = implode($i);
            }

            $base  = strlen($valdex);

            if ($to_num) {
                // Digital number  <<--  alphabet letter code
                $val  = strrev($val);
                $json = 0;
                $len = strlen($val) - 1;
                for ($t = 0; $t <= $len; $t++) {
                    $bcpow = bcpow($base, $len - $t);
                    $json   = $json + strpos($valdex, substr($val, $t, 1)) * $bcpow;
                }

                if (is_numeric($pad_up)) {
                    $pad_up--;
                    if ($pad_up > 0) {
                        $json -= pow($base, $pad_up);
                    }
                }
                $json = sprintf('%F', $json);
                $json = substr($json, 0, strpos($json, '.'));
            } else {
                // Digital number  -->>  alphabet letter code
                if (is_numeric($pad_up)) {
                    $pad_up--;
                    if ($pad_up > 0) {
                        $val += pow($base, $pad_up);
                    }
                }

                $json = "";
                for ($t = floor(log($val, $base)); $t >= 0; $t--) {
                    $bcp = bcpow($base, $t);
                    $a   = floor($val / $bcp) % $base;
                    $json = $json . substr($valdex, $a, 1);
                    $val  = $val - ($a * $bcp);
                }
                $json = strrev($json); // reverse
            }

            return $json;
        }
        
        static function proper_parse_str($str) 
        {

              # result array
              $arr = array();

              # split on outer delimiter
              $pairs = explode('&', $str);

              # loop through each pair
              foreach ($pairs as $i) {
                # split into name and value
                list($name,$value) = explode('=', $i, 2);
               
                # if name already exists
                if( isset($arr[$name]) ) {
                  # stick multiple values into an array
                  if( is_array($arr[$name]) ) {
                    $arr[$name][] = $value;
                  }
                  else {
                    $arr[$name] = array($arr[$name], $value);
                  }
                }
                # otherwise, simply stick it in a scalar
                else {
                  $arr[$name] = $value;
                }
              }

              # return result array
              return $arr;
        }    

        static function cnUrl($url) 
        {

            global $clq;
            if($clq->get('cfg')['site']['route'] == 'userq') {
                $url = ltrim("/", rtrim("/", $url)); 
                $q = explode("/", $url);
                $t = count($q);
                switch($t) {

                    case 4:
                        $newurl = "?p=".$q[0]."&lcd=".$q[1]."&a=".$q[2]."&t=".$q[3];
                    break;

                    case 5:
                        $newurl = "?p=".$q[0]."&lcd=".$q[1]."&a=".$q[2]."&t=".$q[3]."&s=".$q[4];
                    break;

                    default:
                        $newurl = "?p=".$q[0]."&lcd=".$q[1]."&a=".$q[2];
                    break;
                }

                return $newurl;

            } else {
                return $url;
            }   
        }
   
        /**
         * Bing Transaaltion in PHP
         *
         * @param - string - 2 character language code from eg "en"
         * @param - string - 2 character language code to es "es"
         * @param - string - text to be translated
         * @return - string translated text
         * subrouties =
         *  - getToken()
         *  - curlRequest() 
         *
         **/
        public static function getTranslation($vars) {

            $rq = $vars['rq'];
            $fromlcd = $rq['fromlcd'];
            $tolcd = $rq['tolcd'];
            $text = $rq['original'];

            $accessToken = self::getToken($this->azure_key);
            $params = "text=" . urlencode($text) . "&to=" . $tolcd . "&from=" . $fromlcd . "&appId=Bearer+" . $accessToken;
            $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
            $curlResponse = self::curlRequest($translateUrl);
            $translatedStr = simplexml_load_string($curlResponse);
            return $translatedStr;
        }

            // Get the AZURE token
            protected static function getToken()
            {
                global $clq;
                $url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
                $ch = curl_init();
                $data_string = json_encode('{body}');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string),
                        'Ocp-Apim-Subscription-Key: ' . $clq->get('cfg')['site']['bingkey']
                    )
                );
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $strResponse = curl_exec($ch);
                curl_close($ch);
                return $strResponse;
            }
             
            // Request the translation
            public static function curlRequest($url)
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, "Content-Type: text/xml");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False);
                $curlResponse = curl_exec($ch);
                curl_close($ch);
                return $curlResponse;
            }
      
        /**
         * Convert a Request string into an array. Happens when Request string is sent by AJA
         * @param - string - Request string
         * @return - array - just like a Request or Post etc.
         **/
        static function convertDstoRq($ds)
        {
            $rqs = explode("&", $ds);
            $rq = array();
            foreach($rqs as $e => $el) {
                $t = explode("=", $el);
                $rq[$t[0]] = $t[1];
            };
            return $rq;
        }

        static function print_p($a) {
            return '<pre>'.print_r($a, 1).'</pre>';
        }

        /**
         * This function takes the first letter of a name and generates an Avatar from it
         * the function checks to see if the PNG already exists
         * so gradually the function will cease to create new images
         *
         * @var String such as Company name
         * @var Subdirectory of /public, default is /cologos = Company Logos
         * @var Size of avatar, default is 60
         *
         * @return Image filename such as "avatar_C60.png"
         **/
        public static function makeAvatar($str, $size = 70, $fontFile = "consola.ttf") 
        {
            global $clq;
            // $fontFile = $clq->get('fontfile');
            $l = substr($str, 0, 1).$size;
            $filename = "avatar_".$l.".png";
            if(!is_file($clq->get('basedir')."public/images/".$filename)) {
                $la = new LetterAvatar();
                $la->setFontFile($clq->get('basedir').'includes/'.$fontFile);
                $la->generate($l, $size)->saveAsPng($clq->get('basedir')."public/images/".$filename);
            }
            return $filename;
        }

        /** Top buttons subroutine
         * for datagrid, datalist and datatree
         * @param - array - config array
         * @param - array - original variables
         * @param - string - type, eg datagrid
         * @return - String HTML of buttons
         **/
        static function topButtons($dtcfg, $vars, $type)
        {
            
            global $clq;
            array_key_exists('table', $vars) ? $table = $vars['table'] : $table = "";
            array_key_exists('tabletype', $vars) ? $tabletype = $vars['tabletype'] : $tabletype = "";   

            // Order columns by order
            foreach($dtcfg['topbuttons'] as $key => $config) {
                if(!array_key_exists('order', $config)) {
                    $dtcfg['topbuttons'][$key]['order'] = 'z';
                }
            }; $dtcfg['topbuttons'] = Q::array_orderby($dtcfg['topbuttons'], 'order', SORT_ASC);          

            $topbuttons = '<div class="btn-group">';
            foreach($dtcfg['topbuttons'] as $t => $btn) {

                if(!array_key_exists('dropdown', $btn)) {

                    array_key_exists('formid', $btn) ? $frmid = $btn['formid'] : $frmid = "";
                    array_key_exists('formtype', $btn) ? $frmtp = $btn['formtype'] : $frmtp = "";
                    array_key_exists('icon', $btn) ? $icn = H::i(['class' => 'fa fw fa-'.$btn['icon']]) : $icn = null ;

                    $topbuttons .= H::button([
                        'type' =>  'button',
                        'data-action' =>  $t,
                        'class' => 'topbutton btn btn-sm btn-'.$btn['class'].' mr5 pointer',
                        'style' => 'padding: 5px 8px',
                        'data-table' =>  $table,
                        'data-tabletype' => $tabletype,
                        'data-type' => $type,
                        'data-recid' => 0,
                        'data-idiom' => $vars['idiom'], 
                        'data-formid' => $frmid,
                        'data-formtype' => $frmtp
                    ],
                        $icn,
                        Q::cStr($btn['title'])
                    );                    
                } else { // Button has dropdown

                    // For each submenu
                    $mnu = "";
                    foreach ($btn['submenu'] as $d => $dpbtn) {
                        $mnu .= H::a([
                            'class' => 'dropdown-item topbutton', 
                            'href' => '#', 
                            'data-action' => $t,
                            'data-type' => $dpbtn['type'],
                            'data-idiom' => $vars['idiom'],
                            ], 
                            Q::cStr($dpbtn['title'])
                        );
                    };

                    array_key_exists('icon', $btn) ? $icn = H::i(['class' => 'fa fw fa-'.$btn['icon']]) : $icn = null ;
                    $topbuttons .= H::div(['class' => 'dropdown'],
                        H::button([
                            'type' =>  'button',
                            'id' =>  $t.'_dropdown',
                            'class' => 'dropdown-toggle btn btn-sm btn-'.$btn['class'].' mr5 pointer',
                            'style' => 'padding: 6px 8px',
                            'data-toggle' => 'dropdown', 'aria-haspopup' => true, 'aria-expanded' => true
                            ], Q::cStr($btn['title'])
                        ),
                        H::div(['class' => 'dropdown-menu', 'data-offset' => '100px', 'data-flip' => true, 'aria-labelledby' => $t.'_dropdown'], 
                            H::span(['class' => 'h6 dropdown-header'], $icn, Q::cStr($btn['title'])),
                            $mnu
                        )
                    );

                }; // End if button has dropdown
    
            }; // End foreach button
            $topbuttons .= '</div>';
            return $topbuttons; 
        }

    /** Array Handling
     * object_to_array()
     * 
     * 
     *****************************************************  Arrays  ********************************************/

        /**
         * Sort an array by the value stored in a key
         * eg. Sort a menu array or a set of form fields by order
         * @return the reordered array
         **/
        public static function array_order_by($array, $orderby = '', $order = 'SORT_ASC', $children = false) {
            
            if($orderby == null) {
                return $array;
            }
            
            $key_value = $new_array = [];
            foreach($array as $k => $v) {
                $key_value[$k] = $v[$orderby];
            }

            if($order == 'SORT_DESC') {
                arsort($key_value);
            } else {
                asort($key_value);
            }

            reset($key_value);
            foreach($key_value as $k => $v) {
                $new_array[$k] = $array[$k];
                // children
                if($children && isset($new_array[$k][$children])) {
                    $new_array[$k][$children] = self::array_orderby($new_array[$k][$children], $orderby, $order, $children);
                }
            }

            $new_array = array_values($new_array); 
            $array = $new_array;
            return $new_array;
        }

        public static function array_orderby() {
            $args = func_get_args();
            $data = array_shift( $args );
            if ( ! is_array( $data ) ) {
                return array();
            }
            $multisort_params = array();
            foreach ( $args as $n => $field ) {
                if ( is_string( $field ) ) {
                    $tmp = array();
                    foreach ( $data as $row ) {
                        $tmp[] = $row[ $field ];
                    }
                    $args[ $n ] = $tmp;
                }
                $multisort_params[] = &$args[ $n ];
            }
            $multisort_params[] = &$data;
            call_user_func_array( 'array_multisort', $multisort_params );
            return end( $multisort_params );
        }     

        /**
         * Convert an object into an associative array
         *
         * This function converts an object into an associative array by iterating
         * over its public properties. Because this function uses the foreach
         * construct, Iterators are respected. It also works on arrays of objects.
         *
         * @return array
         */
        public static function object_to_array($var) 
        {
            $result = array();
            $references = array();
         
            // loop over elements/properties
            foreach ($var as $key => $value) {
                // recursively convert objects
                if (is_object($value) || is_array($value)) {
                    // but prevent cycles
                    if (!in_array($value, $references)) {
                        $result[$key] = self::object_to_array($value);
                        $references[] = $value;
                    }
                } else {
                    // simple values are untouched
                    $result[$key] = $value;
                }
            }
            return $result;
        }

        /**
         * Returns the first element in an array.
         *
         * @param  array $array
         * @return mixed
         */
        public static function array_first(array $array)
        {
            return reset($array);
        }

        /**
         * Returns the last element in an array.
         *
         * @param  array $array
         * @return mixed
         */
        public static function array_last(array $array)
        {
            return end($array);
        }

        /**
         * Returns the first key in an array.
         *
         * @param  array $array
         * @return int|string
         */
        public static function array_first_key(array $array)
        {
            reset($array);
            return key($array);
        }

        /**
         * Returns the last key in an array.
         *
         * @param  array $array
         * @return int|string
         */
        public static function array_last_key(array $array)
        {
            end($array);
            return key($array);
        }

        /**
         * Flatten a multi-dimensional array into a one dimensional array.
         *
         * Contributed by Theodore R. Smith of PHP Experts, Inc. <http://www.phpexperts.pro/>
         *
         * @param  array   $array         The array to flatten
         * @param  boolean $preserve_keys Whether or not to preserve array keys.
         *                                Keys from deeply nested arrays will
         *                                overwrite keys from shallowy nested arrays
         * @return array
         */
        public static function array_flatten(array $array, $preserve_keys = true)
        {
            $flattened = array();

            array_walk_recursive($array, function ($value, $key) use (&$flattened, $preserve_keys) {
                if ($preserve_keys && !is_int($key)) {
                    $flattened[$key] = $value;
                } else {
                    $flattened[] = $value;
                }
            });

            return $flattened;
        }

        /**
         * Accepts an array, and returns an array of values from that array as
         * specified by $field. For example, if the array is full of objects
         * and you call util::array_pluck($array, 'name'), the function will
         * return an array of values from $array[]->name.
         *
         * @param  array   $array            An array
         * @param  string  $field            The field to get values from
         * @param  boolean $preserve_keys    Whether or not to preserve the
         *                                   array keys
         * @param  boolean $remove_nomatches If the field doesn't appear to be set,
         *                                   remove it from the array
         * @return array
         */
        public static function array_pluck(array $array, $field, $preserve_keys = true, $remove_nomatches = true)
        {
            $new_list = array();

            foreach ($array as $key => $value) {
                if (is_object($value)) {
                    if (isset($value->{$field})) {
                        if ($preserve_keys) {
                            $new_list[$key] = $value->{$field};
                        } else {
                            $new_list[] = $value->{$field};
                        }
                    } elseif (!$remove_nomatches) {
                        $new_list[$key] = $value;
                    }
                } else {
                    if (isset($value[$field])) {
                        if ($preserve_keys) {
                            $new_list[$key] = $value[$field];
                        } else {
                            $new_list[] = $value[$field];
                        }
                    } elseif (!$remove_nomatches) {
                        $new_list[$key] = $value;
                    }
                }
            }

            return $new_list;
        }

        /**
         * Searches for a given value in an array of arrays, objects and scalar
         * values. You can optionally specify a field of the nested arrays and
         * objects to search in.
         *
         * @param  array   $array  The array to search
         * @param  scalar  $search The value to search for
         * @param  string  $field  The field to search in, if not specified
         *                         all fields will be searched
         * @return boolean|scalar  False on failure or the array key on success
         */
        public static function array_search_deep(array $array, $search, $field = false)
        {
            // *grumbles* stupid PHP type system
            $search = (string) $search;

            foreach ($array as $key => $elem) {
                // *grumbles* stupid PHP type system
                $key = (string) $key;

                if ($field) {
                    if (is_object($elem) && $elem->{$field} === $search) {
                        return $key;
                    } elseif (is_array($elem) && $elem[$field] === $search) {
                        return $key;
                    } elseif (is_scalar($elem) && $elem === $search) {
                        return $key;
                    }
                } else {
                    if (is_object($elem)) {
                        $elem = (array) $elem;

                        if (in_array($search, $elem)) {
                            return $key;
                        }
                    } elseif (is_array($elem) && in_array($search, $elem)) {
                        return $key;
                    } elseif (is_scalar($elem) && $elem === $search) {
                        return $key;
                    }
                }
            }

            return false;
        }

        /**
         * Returns an array containing all the elements of arr1 after applying
         * the callback function to each one.
         *
         * @param  string  $callback     Callback function to run for each
         *                               element in each array
         * @param  array   $array        An array to run through the callback
         *                               function
         * @param  boolean $on_nonscalar Whether or not to call the callback
         *                               function on nonscalar values
         *                               (Objects, resources, etc)
         * @return array
         */
        public static function array_map_deep(array $array, $callback, $on_nonscalar = false)
        {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $args = array($value, $callback, $on_nonscalar);
                    $array[$key] = call_user_func_array(array(__CLASS__, __FUNCTION__), $args);
                } elseif (is_scalar($value) || $on_nonscalar) {
                    $array[$key] = call_user_func($callback, $value);
                }
            }

            return $array;
        }

        /**
         * Merges two arrays recursively and returns the result.
         *
         * @param   array   $dest               Destination array
         * @param   array   $src                Source array
         * @param   boolean $appendIntegerKeys  Whether to append elements of $src
         *                                      to $dest if the key is an integer.
         *                                      This is the default behavior.
         *                                      Otherwise elements from $src will
         *                                      overwrite the ones in $dest.
         * @return  array
         */
        public static function array_merge_deep(array $dest, array $src, $appendIntegerKeys = true)
        {
            foreach ($src as $key => $value) {
                if (is_int($key) and $appendIntegerKeys) {
                    $dest[] = $value;
                } elseif (isset($dest[$key]) and is_array($dest[$key]) and is_array($value)) {
                    $dest[$key] = static::array_merge_deep($dest[$key], $value, $appendIntegerKeys);
                } else {
                    $dest[$key] = $value;
                }
            }
            return $dest;
        }

        /** Clean / Sanitise an array 
         * synonym for array_filter
         * @param - array - the input array
         * @return - array - the cleaned array
         **/
         public static function array_clean(array $array)
         {
            return array_filter($array);
         }
        
        /** Recursively search an array by key 
         * Recursively searches a multidimensional array for a key and optional value and returns the path as a string representation or subset of the array or a value.
         *
         * @author  Akin Williams <aowilliams@arstropica.com>
         *
         * @param   int|string $needle Key
         * @param   array $haystack Array to be searched
         * @param   bool $strict Optional, limit to keys of the same type. Default false.
         * @param   string $output Optional, output key path as a string representation or array subset, ('array'|'string'|'value'). Default array.
         * @param   bool $count Optional, append number of matching elements to result. Default false.
         * @param   int|string $value Optional, limit results to keys matching this value. Default null.
         * 
         * @return  array Array containing matching keys and number of matches
         **/
         public static function multi_array_key_search($needle, $haystack, $strict = false, $output = 'array', $count = false, $value = null) 
         {
            // Sanity Check
            if(!is_array($haystack))
                return false;

            $resIdx='matchedIdx';
            $prevKey = "";
            $keys = array();
            $num_matches = 0;

            $numargs = func_num_args();
            if ($numargs > 6){
                $arg_list = func_get_args();
                $keys = $arg_list[6];
                $prevKey = $arg_list[7];
            }

            $keys[$resIdx] = isset($keys[$resIdx]) ? $keys[$resIdx] : 0;

            foreach($haystack as $key => $val) {
                if(is_array($val)) {
                    if ((($key === $needle) && is_null($value)) || (($key === $needle) && ($val[$key] == $value) && $strict === false) || (($key === $needle) && ($val[$key] === $value) && $strict === true)){
                        if ($output == 'value'){
                            $keys[$keys[$resIdx]] = $val;
                        } else {
                            $keys[$keys[$resIdx]] = $prevKey . (isset($keys[$keys[$resIdx]]) ? $keys[$keys[$resIdx]] : "") . "[$key]";
                        }
                        $keys[$resIdx] ++;
                    }
                    $passedKey = $prevKey . "[$key]";;
                    $keys = self::multi_array_key_search($needle, $val, $strict, $output, true, $value, $keys, $passedKey);
                } else {
                    if ((($key === $needle) && is_null($value)) || (($key === $needle) && ($val == $value) && $strict === false) || (($key === $needle) && ($val === $value) && $strict === true)){
                        if ($output == 'value'){
                            $keys[$keys[$resIdx]] = $val;
                        } else {
                            $keys[$keys[$resIdx]] = $prevKey . (isset($keys[$keys[$resIdx]]) ? $keys[$keys[$resIdx]] : "") . "[$key]";
                        }
                        $keys[$resIdx] ++;
                    }
                }
            }
            if ($numargs < 7){
                $num_matches = (count($keys) == 1) ? 0 : $keys[$resIdx];
                if ($count) $keys['num_matches'] = $num_matches;
                unset($keys[$resIdx]);
                if (($output == 'array') && $num_matches > 0){
                    if (is_null($value)) {
                        $replacements = self::multi_array_key_search($needle, $haystack, $strict, 'value', false);
                    }
                    $arrKeys = ($count) ? array('num_matches' => $num_matches) : array();
                    for ($i=0; $i < $num_matches; $i ++){
                        $keysArr = explode(',', str_replace(array('][', '[', ']'), array(',', '', ''), $keys[$i]));
                        $json = "";
                        foreach($keysArr as $nestedkey){
                            $json .= "{" . $nestedkey . ":";
                        }
                        if (is_null($value)){
                            $placeholder = time();
                            $json .= "$placeholder";
                        } else {
                            $json .= "$value";
                        }
                        foreach($keysArr as $nestedkey){
                            $json .= "}";
                        }
                        $arrKeys[$i] = json_decode($json, true);
                        if (is_null($value)) {
                            array_walk_recursive($arrKeys[$i], function (&$item, $key, &$userdata) {
                                if($item == $userdata['placeholder'])
                                    $item = $userdata['replacement'];
                            }, array('placeholder' => $placeholder, 'replacement' => $replacements[$i]));
                        }
                    }
                    $keys = $arrKeys;
                }
            }
            return $keys;
         }

}

# alias +q+ class
if(!class_exists("Q")){ class_alias('Cliq', 'Q'); };