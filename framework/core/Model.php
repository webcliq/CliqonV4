<?php
/**
 * Model Definition Class
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@webcliq.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class Model 
{
	const THISCLASS = "Model";
    public function __construct() {
    	global $clq;
    }

    /** Standard Model
     *
     **************************************************************************************************************/

        /**
         * Standard Model
         * The Data Dictionary for a particular "tabletype" such as string, list or cashbook will be stored in this array 
         * @param - string - $service = the type of service for which the model is to be populated such as fields, datatable, form, datatree, report etc. Cannot be false.
         * @param - string - the Tabletype such as string or news etc.
         * @return - array - consisting of a set of field definitions derived from Standard but overwritten by this type where necessary
         */
        function stdModel($service, $table, $tabletype = '') 
        {
    	    $method = self::THISCLASS.'->'.__FUNCTION__."()";
    	    try {
    			
                // Setup variables etc.
    			$model = []; global $clq; $common = [];

                // Do we have a Cached version of this model?
                if($tabletype != '') {
                    $fn = $table.'_'.$tabletype.'_'.$service.'.txt';
                } else {
                    $fn = $table.'_'.$service.'.txt';
                };
                $cleanordirty = $clq->get('model');
                if( is_array(Q::cacheRead($fn) || $cleanordirty == 'clean') ) {
                    $model = Q::cacheRead($fn); 
                } else {
                    
                    // Cached version of model does not yet exist so build Model, write this to Cache and return the model
                    
                    // Always get service 
                    $model = self::getService($service);                

                    // Get Common - in all cases - will take into account whether $tabletype exists
                    $common = self::getCommon($table, $tabletype);

                    // Make Common add and overwite service
                    // cascade - Overwrite if the key exists but add if the key does not exist
                    // also check thatCommon exists
                    is_array($common) ? $model = array_replace_recursive($model, $common) : null ;

                    // Table for this service
                    $tbl = self::getTable($service, $table);

                    // cascade - Overwrite if the key exists but add if the key does not exist
                    // Check that Service exists
                    is_array($tbl) ? $model = array_replace_recursive($model, $tbl) : null;

                    // Read in the Table eg dbcollection.cfg
                    if($tabletype != '') {
                        $tbltype = self::getTabletype($service, $table, $tabletype);

                        // cascade - Overwrite if the key exists but add if the key does not exist
                        // If a key from array1 exists in array2, values from array1 will be replaced by the values from array2. If the key only exists in array1, it will be left as it is. If a key exist in array2 and not in array1, it will be created in array1. If multiple arrays are used, values from later arrays will overwrite the previous ones.

                        // Make Tabletype overwrite Table
                    // cascade - Overwrite if the key exists but add if the key does not exist
                    // Check that Service exists
                    is_array($tbltype) ? $model = array_replace_recursive($model, $tbltype) : null;
                    };
                                  
                    // Write resulting model to cache
                    $result = Q::cacheWrite($fn, $model); 
                    $clq->set('model', 'clean');
                }

                // Test
                $test = [
                    'method' => $method,
                    'service' => $service,
                    'common' => $common, 
                    'table' => $table, 
                    'tabletype' => $tabletype,
                    'model' => $model
                ];   

                // L::cLog($test);
                // T::dump($test);  
    	        return $model;

    		} catch (Exception $e) {
    			$err = [
    				'errmsg' => $e->getMessage(),
    				'method' => $method,
    				'service' => $service,
                    'common' => $common, 
    				'table' => $table,
    				'tabletype' => $tabletype,
                    'model' => $model
    			];
    			L::cLog($err);
    			return []; 
    		}
        }

        /** Get Common for this table and/or tabletype
         * @param - string - Table
         * @param - string - Tabletype
         * @return - array - Contents of request in array format
         **/
        private static function getCommon($table, $tabletype) 
        {  

            $vars = array(
                'filename' => $table,
                'subdir' => 'models/',
                'type' => 'collection',
                'reference' => $table,
                'key' => 'common'
            );
            $common = self::fileOrDb($vars);

            if($tabletype != '') {
                $vars = array(
                    'filename' => $table.'.'.$tabletype,
                    'subdir' => 'models/',
                    'type' => 'model',
                    'reference' => $table.'_'.$tabletype,
                    'key' => 'common'
                );
                $ttype = self::fileOrDb($vars);        
                $common = array_replace_recursive($common, $ttype);
            };

            return $common;
        }

        /** Get service, such as datagrid or 
         * @param - string - Name of service
         * @return - array - Contents of request in array format
         **/
        private static function getService($service) 
        {
            $vars = array(
                'filename' => $service,         // If file, name of file without extension (.cfg)
                'subdir' => 'admin/config/',    // If file, name of subdirectory
                'type' => 'service',            // If database, value of c_type
                'reference' => $service,        // If database, value of c_reference
                'key' => ''
            );
            return self::fileOrDb($vars);
        }

        /**
         * @param - string - Service or Key
         * @param - string - Tablename
         * @return - array - Contents of request in array format
         **/
        private static function getTable($service, $table) 
        {
            $vars = array(
                'filename' => $table,
                'subdir' => 'models/',
                'type' => 'collection',
                'reference' => $table,
                'key' => $service
            );
            return self::fileOrDb($vars);        
        }

        /**
         * @param - string - Service or Key name
         * @param - string - Table
         * @param - string - Table type
         * @return - array - Contents of request in array format
         **/
        private static function getTabletype($service, $table, $tabletype) 
        { 
            $vars = array(
                'filename' => $table.'.'.$tabletype,
                'subdir' => 'models/',
                'type' => 'model',
                'reference' => $table.'_'.$tabletype,
                'key' => $service
            );
            return self::fileOrDb($vars);        
        }

        /** Get array from file
         * @param - array - variables
         * @return - array - Contents of request in array format
         **/
        private static function fileOrDb($vars)
        {
            $array = self::getFromDb($vars);
            if(!count($array) < 1) {
                $result = $array;
            } else {
                $result = self::getFromFile($vars);
            }; 
            return $result;
        }

        /** Get array from database
         * @param - array - variables
         * @return - array - Contents of request in array format
         **/
        private static function getFromDb($vars)
        {
            global $clq;   
            // Temporary    
            $sql = "SELECT c_options FROM dbcollection WHERE c_type = ? AND c_reference = ?";
            $value = R::getCell($sql, [$vars['type'], $vars['reference']]);
            if($value == "") {
                $sql = "SELECT c_common FROM dbcollection WHERE c_type = ? AND c_reference = ?";
                $value = R::getCell($sql, [$vars['type'], $vars['reference']]);   

                // Then transfer
                $sqla = "UPDATE dbcollection SET c_options = '".$value."' WHERE c_type = ? AND c_reference = ?";
                $result = R::exec($sqla, [$vars['type'], $vars['reference']]);           
            }
           
            $config = $clq->resolve('Config');
            $farray = $config->cfgReadString($value);
            if(count($farray) > 1) {
                if($vars['key'] != '') {
                    return $farray[$vars['key']];
                } else {
                   return $farray; 
                }
            } else {
                return [];
            }
        }

        /** Get array from a TOML file
         * @param - array - variables, consisting of subdirectory and filename
         * @return - array - Contents of request in array format
         **/
        private static function getFromFile($vars)
        {
            global $clq;
            $config = $clq->resolve('Config');
            $farray = $config->cfgReadFile($vars['subdir'].$vars['filename'].'.cfg');
            if(count($farray) > 1) {
                if($vars['key'] != '') {
                    return $farray[$vars['key']];
                } else {
                   return $farray; 
                }
            } else {
                return [];
            }
        }

        private static function getAllFromFileorDb($vars)
        {
            
            // From database, is a list
            $array = Q::cList($vars['listname']);
            if(count($array) > 1) {
                $result = $array;
            } else {
                $farray = C::cfgReadFile($vars['subdir'].$vars['filename'].'.cfg');
                $result = [];
                foreach($farray as $key => $str) {
                    $result[$key] = Q::cStr($str);
                }          
            }; 
            return $result;
        }

        /**
         * List of Tables
         * @return array - Tables
         */
        function get_tables()
        {    
        	$dd = $this->get_datadictionary();
        	$tables = [];
        	foreach($dd['tables'] as $tbl => $str) {
        		$tables[$tbl] = Q::cStr($str);
        	}
    		return $tables;
        }
          
        /**
         * List of Table Types
         * @return array - Tabletypes
         */
        function get_tabletypes($tbl = null)
        {
           	$dd = $this->get_datadictionary();
        	$types = [];
        	foreach($dd['tabletypes'] as $type => $array) {
        		
                if($tbl) {
                    if($array['table'] == $tbl) {
                        $types[$type] = Q::cStr($array['title']);
                    }
                } else {
                    $types[$type] = Q::cStr($array['title']);
                }

        	}
            if(count($types) > 0) {
                return $types;
            } else {
                return false;
            }
        }

         /**
         * List of all Services
         * @return array - Services
         */
        function get_services(){
            
            $vars = array(
                'listname' => 'service',
                'subdir' => 'admin/config/',
                'filename' => 'services'
            );
            return self::getAllFromFileorDb($vars);
        }

        /**
         * Data Dictionary in total
         *
         * @return - array - Data Dictionary of Tables and Tabletypes
         **/
        function get_datadictionary()
        {
        	return C::cfgReadFile('models/datadictionary.cfg');
        }


} // Class Ends

# alias +h+ class
if(!class_exists("M")){ class_alias('Model', 'M'); };
