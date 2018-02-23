<?php
/**
 * Redbean Database Helper Class
 * Fold = Ctrl K3
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Conkas cb
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Db 
{
    const THISCLASS = "Db (implements ORM/Redbean)";
    const CLIQDOC = "c_document";
    protected $test; // if test mode dumps SQL to screen.
    public $statements = []; //if no db connection saves statements to array
    public $statement_count;
     
	function __construct() 
	{
        // Make table if not exists
        $tablename = "dbindex";
	}

    /** Cliqon ORM Helpers to write records to the database
     *
     * postForm()
     * deleteRecord()
     * removeRecord()
     * createTable()
     * setFormat()
     * dbEntry() - needs review
     * treeNodeDrop() - supports Gijgo Tree
     * saveContent()
     * saveCode()
     * saveCreatorRecord()
     *
     * postValue()
     *
     **************************************************************************************************************/

		/** Main insert or update record
		 * The primary record creator or updater
		 * Contains a few edge cases but if special updates are required then Db contains other method
		 * Called by methods in Api or Ajax classes
		 * @param - array - all the data
		 * @return - string 
		 **/
		 static function postForm($vars)
		 {
			$method = self::THISCLASS.'->'."postForm()";
			try {
				
				global $clq;
				$rq = $vars['rq'];
				if(!is_array($rq)) {
					throw new Exception("No request array");
				} 
				
				// Set values and variables to be used
				$recid = (int)$rq['id'];
				$tbl = $vars['table']; 
				array_key_exists('tabletype', $vars) ? $tbltype = $vars['tabletype'] : $tbltype = '';
				$rqc = []; $rqd = []; $submit = []; $test = []; $result = ''; $ref = '';	

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
				foreach($rq as $key => $value) {	
					$chk = strtolower(substr($key, 0, 2));	
					switch($chk) {
						case "c_": $rqc[$key] = $value; break;
						case "d_": $rqd[$key] = $value; break;	
						default: false; break;	// throws anything else in the REQUEST away
					}
				};		
				
				// Get the model for this Table and Type
				$mdl = $clq->resolve('Model');
				$model = $mdl->stdModel('fields', $tbl, $tbltype);
				if(!is_array($model)) {
					throw new Exception("No model array returned based on table > tabletype > fields");
				} 

				if($action == "insert") { // Insert
					
					$db = R::dispense($tbl);
					$msg = Q::cStr('367:New record created with Id').':&nbsp;';
					$text = Q::cStr('234:Insert record');

				} else { // Update

					// Implement Revision system
					if($tbl == 'dbitem') {
						// Copy current Record to Archive
						$sqlc = "SELECT * FROM dbitem WHERE id = ?";
						$copy = R::getRow($sqlc, [$recid]);
						unset($copy['id']);
						$adb = R::dispense('dbarchive');
						foreach($copy as $akey => $aval) {
							$adb->$akey = $aval;
						}
						$archiverec = R::store($adb);
					};

					$db = R::load($tbl, $recid);
					$msg = Q::cStr('368:Existing record updated with Id').':&nbsp;';
					$text = Q::cStr('369:Update Record');
				}				
				
				// Send $vals for formatting
				foreach($rqc as $fldc => $valc) {
					if(array_key_exists($fldc, $model)) {
						$props = $model[$fldc];
						$submit[$fldc] = self::setFormat($action, $fldc, $props, $rqc, $tbl, $recid);
					}
				}
				
				// Do we have a valid d_ array to save into c_document
				if(count($rqd) > 0) {
					
					// If action equals insert, all we need to is write d_values to c_document
					if($action == 'insert') {

						$doc = [];
						// Send $doc for formatting
						foreach($rqd as $fldd => $vald) {
							$isidm = explode('_', $fldd);
							if(count($isidm) == 3) {
								$fldname = $isidm[0].'_'.$isidm[1];
								$thislcd = $isidm[2];
								$props = $model[$fldname];
								$doc[$fldname][$thislcd] = self::dbEntry($props['dbtype'], $vald);
							} else {
								$props = $model[$fldd];
								$doc[$fldd] = self::setFormat($action, $fldd, $props, $rqd, $tbl, $recid);
							}
						}
						$submit[self::CLIQDOC] = F::jsonEncode($doc);						
					}

					if($action == 'update') {

						// call up the existing record if it exists
						$sql = "SELECT ".self::CLIQDOC." FROM ".$tbl." WHERE id = ?";
						$doc = json_decode(R::getCell($sql, [$recid]), true);

						if(!is_array($doc)) {
							throw new Exception("The 'existing' array has not been created from c_document!");
						} 

						foreach($rqd as $fldd => $vald) {
							$isidm = explode('_', $fldd);
							if(count($isidm) == 3) {
								$fldname = $isidm[0].'_'.$isidm[1]; // d_text
								$thislcd = $isidm[2]; // _en
								$props = $model[$fldname];
								$doc[$fldname][$thislcd] = self::dbEntry($props['dbtype'], $vald);
							} else {
								$props = $model[$fldd];
								$doc[$fldd] = self::setFormat($action, $fldd, $props, $rqd, $tbl, $recid);
							}
						}				
						$submit[self::CLIQDOC] = F::jsonEncode($doc);	
					}

				}
				
				// If it failed to produce a usable array of keys, exit here
				if(!is_array($submit)) {
					throw new Exception("The values array has not been created!");
				} 
								
				foreach($submit as $fld => $val) {
					$test[$fld] = $val;
					$db->$fld = $val;
				}

				$result = R::store($db);					

				if(!is_numeric($result)) {
					throw new Exception("No result has been created!");
				} else {
					$sql = "SELECT * FROM ".$tbl." WHERE id = ?";
					$row = R::getRow($sql, [$result]);
					switch ($tbl) {
						
						case "dbuser":	
							$ref = "c_username";
						break;	

						default:
							$ref = "c_reference";
						break;
					}
					$ref = $row[$ref];				
				}	

				$text.": ";
				$notes = "Tablename: ".$tbl.", Id: ".$ref."<br />Request - ".json_encode($submit, JSON_PRETTY_PRINT);
				// L::cLog($text, 'dbwrite', $notes);

				// Production log = array, type, notes and table
				L::wLog($submit, 'write', 'Result: '.$result, $tbl);

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
					'tablename' => $tbl,
					'id' => $recid,
					'submittedvalues' => $submit
					// 'result' => $result
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 
				return ['flag' => 'Ok', 'data' => ['msg' => $msg.'&nbsp;'.$ref, 'row' => $row]];              

			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method
				];

				// is_array($vars) ? $err['vars'] = $vars: $err['vars'] = 'Not set' ;
				// isset($tbl) ? $err['tablename'] = $tbl: $err['tablename'] = 'Not set' ;
				// isset($recid) ? $err['recid'] = $recid: $err['recid'] = 'Not set' ;
				// isset($action) ? $err['action'] = $action: $err['action'] = 'Not set' ;
				// is_array($rq) ? $err['request'] = $rq: $err['request'] = 'Not set' ;
				// is_array($model) ? $err['model'] = $model: $err['model'] = 'Not set' ;
				// is_array($rqc) ? $err['values'] = $rqc: $err['values'] = 'Not set' ;
				// is_array($rqd) ? $err['doc'] = $rqd: $err['doc'] = 'Not set' ;
				// is_array($submit) ? $err['submittedvalues'] = $submit: $err['submittedvalues'] = 'Not set' ;
				is_array($test) ? $err['generatedarray'] = $test: $err['generatedarray'] = 'Not set' ;

				L::cLog($err);
				return ['flag' => 'NotOk', 'data' => ['msg' => $err, 'row' => []]]; 
			}				
    	 }

		/** Delete record / row
		 * Delete a Record
		 * @param - string - Recid
		 * @return - string - OK or Not Ok
		 **/
		 function deleteRecord($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				// Insert ACL here - is this User allowed to delete this record - see level
				$tbl = $vars['table']; $tbltype = $vars['tabletype'];

				if(!A::getAuth("delete", $tbl, $tbltype, '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				} 

				// Set values and variables to be used
				$rq = $vars['rq'];
				if(!is_array($rq)) {
					throw new Exception("No request array");
				} 			
				$recid = $rq['recid']; 		
				
				$sql = "DELETE FROM ".$tbl." WHERE id = ?";
				$result = R::exec($sql, [$recid]);
				
				// Test
				$test = [
					'method' => $method,
					'recordid' => $recid,
					'result' => $result
				];

				// Set to comment when completed
				// L::cLog($test);  

				// Production log = array, type, notes and table
				L::wLog($test, 'delete', '', $tbl);
				
				if($result == '1') {
					$msg = [
						'msg' => $result,
						'flag' => 'Ok'
					];
					return $msg;
				} else {
					$msg = [
						'msg' => $result,
						'flag' => 'NotOk'
					];
					return $msg;
				}   

			} catch (Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'id' => $recid
				];
				L::cLog($err);
				$msg = [
					'msg' => $err,
					'flag' => 'NotOk'
				];
				return $msg;
			}
		 }

		/** Restore a record from dbarchive to dbitem 
		 * Needs a bit more thought to deal with edge cases
		 * @param - array - usual variables
		 * @return - string - OK or Not Ok
		 **/
		 function restoreRecord($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION.'()';
			try {

				global $clq;
				$rq = $vars['rq'];
				if(!is_array($rq)) {
					throw new Exception("No request array");
				} 	

				if(!A::getAuth("write", $vars['table'], $vars['tabletype'], '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				};

				// Implement Revision system
				$sqla = "SELECT * FROM dbarchive WHERE id = ?";
				$row = R::getRow($sqla, [$rq['recid']]);
				// We need to identify the latest record in dbitem with respect to type and reference
				$sqlb = "SELECT id FROM dbitem WHERE c_reference = ? and c_type = ?";
				$recid = R::getCell($sqlb, [$row['c_reference'], $row['c_type']]);

				$updb = R::load('dbitem', $recid);
				foreach($row as $key => $val) {
					$updb->$key = $val;
				};
				$restore = R::store($updb);

				if(+$restore > 0) {
					return ['flag' => 'Ok', 'msg' => $restore];
				} else {
					return ['flag' => 'NotOk', 'msg' => $restore];
				}

	        } catch(Exception $e) {
				$err = [
					'msg' => $e->getMessage(),
					'flag' => 'NotOk'
				];
				L::cLog($err);
				return $err;
	        }
		 }

		/** Delete records from dbarchive before data 
		 * Delete records from the archive before a certain date
		 * @param - array - Parameters
		 * @return - string - OK or Not Ok
		 * * */
		 function deleteBefore($vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {

				// Insert ACL here - is this User allowed to delete this record - see level
				$tbl = $vars['table']; $tbltype = $vars['tabletype'];

				if(!A::getAuth("delete", $tbl, $tbltype, '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				} 

				// Set values and variables to be used
				$rq = $vars['rq'];
				if(!is_array($rq)) {
					throw new Exception("No request array");
				};

				$dtb = Q::dbDatePlus(Q::cNow(), $rq['before']);	// -60
						
				$sql = "DELETE FROM ".$tbl." WHERE c_lastmodified < ?";
				$result = R::exec($sql, [$dtb]);

				// Test
				$test = [
					'method' => $method,
					'date' => $dtb,
					'result' => $result
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				if($result == '1') {
					$msg = [
						'msg' => $result,
						'flag' => 'Ok'
					];
					return $msg;
				} else {
					$msg = [
						'msg' => $result,
						'flag' => 'NotOk'
					];
					return $msg;
				}   

			} catch (Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'id' => $id
				];
				L::cLog($err);
				$msg = [
					'msg' => $err,
					'flag' => 'NotOk'
				];
				return $msg;
			}
		 }

        /** Create a table, if not exist 
         * Routine used by some Classes in the Construct to Create tables if they do not exist
         * also used by Install Routing
         * @param - String -
         * @return - 
         */
    	 static function createTable($tablename) 
    	 {

            try {
                $fields = R::inspect($tablename);
                return true;
            } catch (Exception $e) {
                $fields = Q::cModel($tablename, '', 'fields');
                $flds = array_keys($fields);
                $create = R::dispense($tablename);
                foreach($flds as $fldname) {
                    $create->$fldname = 'z';
                }
                $created = R::store($create);
                return true;
            }	
    	 }

        /** Data saver formatting and helper function - first - by field name 
         * - when a REQUEST is being cycled through, the method is invoked to format the value, suitable for the database
         * @param - String - Action equals (i)nsert, (u)pdate
         * @param - String - Field Name
         * @param - Array - Model properties for this field
         * @param - Array - All the Request Vals - we need all, if this is a multi field
         * @return - String
         **/
         static function setFormat($action, $fld, $props, $rq, $tbl, $recid)
         {
            // Standard Document Management fields first
            $method = self::THISCLASS.'->'.'setFormat()';
            try {
				
				switch($fld) {
					
					case "c_dateentered":
					case "c_lastmodified":
						$result = Q::lastMod();
					break;
					
					case "c_whoentered":
					case "c_whomodified":
						$result = Q::whoMod();
					break;

					case "c_version":
					case "c_revision":
						if($action == 'insert') { // action = (i)nsert	
							$result = 0;
						} else {
							$result = self::getNextNumber($fld, $tbl, $recid);
						}
					break;
					
					case "c_document":
						$result = false;
					break;
					
					// More
					
					default:
						if($action == 'insert') { // action = (i)nsert	
							if($rq[$fld] == '') {
								$result = self::dbEntry($props['dbtype'], $props['defval']);
							} else {
								$result = self::dbEntry($props['dbtype'], $rq[$fld]);
							}					
						} else { // action = update
							$result = self::dbEntry($props['dbtype'], $rq[$fld]);
						}
					break;
				}
				
				// Test
				$test = [
					'method' => $method,
					'fld' => $fld,
					'value' => $rq[$fld],
					'properties' => $props,
					'result' => $result
				];

				// Set to comment when completed
				// L::cLog($test); 
				
				return $result;
			
			} catch (Exception $e) {
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method,
					'fld' => $fld,
					'value' => $rq[$fld],
					'properties' => $props,
					'result' => $result			
				];
				L::cLog($err);
				
				return false;
			}
         }
        
        /** Data saver formatting and helper function - second - by cell type 
         * @param - string - element type
         * @param - string - element val
         * @return - string formatted value
         **/
         static function dbEntry($type, $val)
         {
			$method = self::THISCLASS.'->'."dbEntry()";
			try {
				
				switch($type) {
					
					case "password":
						$hasher = new PasswordHash(8, false);
            			$result = $hasher->HashPassword($val);
					break;

					case "boolean":
						if($val == false || $val == 'false' || $val == '' || $val = 0 || $val = '0') {
							$result = '0';
						} else {
							$result = '1';
						}
					break;

					case "number":
						$result = Q::dbNum($val);
					break;
					
					case "date":
						$result = Q::dbDate($val);
					break;

					case "datetime":
						$result = Q::dbDateTime($val);
					break;
					
					case "noupdate":
						$result = false;
					break;

					case "slugify":
						$result = Q::slugify($rawref, 0);
					break;

					// $val must become an array
					case "json":
						$type = gettype($val);
						switch($type) {

							case "array": $result = $val; break;
							case "object": $result = Q::object_to_array($val); break;
							case "string":
								// If it is a badly formatted string
								$qrepl = ['\\', '"{', '}"'];
								$qwith = ['', '{', '}'];
								$val = str_replace($qrepl, $qwith, $val);
								// Now convert the JSON string to an array
								$result = json_decode($val, true);
							break;
							case "NULL": $result = ['false']; break;
							default: $result = [$val]; break;

						}
					break;

					case "toml":
					case "encoded":
						$result = rawurldecode($val);
					break;

					default:
					case "string":
						// possible Q::tidyVal ??
						$result = $val;
					break;
				}
				
				return $result;
			
			} catch (Exception $e) {
				
				$err = [
					'errmsg' => $e->getMessage(),
					'method' => $method,
					'type' => $type,
					'value' => $val,
					'result' => $result			
				];
				L::cLog($err);
				
				return false;
			}				
		 }

		/** Save tree node data 
		 * Routine to change the order of items in a tree
		 * @param - array - usual $vars contain a Request with Current ID and a string of Ids in which 
		 * the Current ID has been changed
		 * @return - array - message set
		 **/
		 static function treeNodeDrop($vars)
		 {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {
				
				$tbl = 'dbitem'; $type = 'manual';
                // $this->rq['id'] // c_reference of Record to altered
                // $this->rq['parentid'] // c_parent for this record
                // $this->rq['ordernumber'] // Numeric position of Record within children, which might shift everything down

                // So we reorder where the Records from the list / children where the record was, then reorder the records in the new children list where the record now resides

                // Need record ID and existing parent of tree item to be altered
                $sqla = "SELECT id, c_parent FROM $tbl WHERE c_reference = ? AND c_type = ?";
                $row = R::getRow($sqla, [$this->rq['id'], $type]);

                // Renumber from group, excluding moved item
                $sqlb = "SELECT id FROM $tbl WHERE c_type = ? AND c_parent = ? AND id <> ? ORDER BY c_order ASC";
                $rs = R::getAll($sqlb, [$type, $row['c_parent'], $row['id']]);
                for($f =0; $f < count($rs); $f++) {
                    $updb = R::load($tbl, $rs[$f]['id']);
                    $updb->c_order = $this->toOrderStr($f);
                    $result = R::store($updb);
                }

                // What will be c_order letters of value ordernumber
                $neworder = $this->toOrderStr($this->rq['ordernumber']);

                // In the to group where c_parent = parent_id, add in record at position 'ordernumber'
                $sqlc = "SELECT id FROM $tbl WHERE c_type = ? AND c_parent = ? AND c_order >= ? ORDER BY c_order";
                $rsb = R::getAll($sqlc, [$type, $this->rq['parentid'], $neworder]);
                for($r = 0; $r < count($rsb); $r++) {
                    $updb = R::load($tbl, $rs[$r]['id']);
                    $updb->c_order = $this->toOrderStr($r+$this->rq['ordernumber']+1);
                    $r = R::store($updb);                   
                }

                // Finally set the moved item record into the new situation
                $updb = R::load($tbl, $row['id']);
                $updb->c_order = $neworder;
                $updb->c_parent = $this->rq['parentid'];
                $result = R::store($updb);                

                
                return ['flag' => 'Ok', 'msg' => 'Recid: '.$row['id'].', with c_reference: '.$this->rq['id'].' goes to: Parent Group: '.$this->rq['parentid'].', at position: '.$neworder];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'dberror' => $result
                ];
                return ['flag' => 'NotOk', 'msg' => $err];
            }		
		 }
		
        /** Record stored at Tree Root level
         * Save the new Tree OrderPart of routine to save data in a Tree
         * @param - array - Usual $args
         * @return - array - OK Flag and data or NotOk and error message 
         **/
         function recordtoroot($vars)
         {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {
				
				$tbl = 'dbitem'; $type = 'manual';
                // $this->rq['reference'] // c_reference of Record to altered
                $sql = "SELECT id FROM $tbl WHERE c_reference = ? AND c_type = ?";
                $id = R::getCell($sql, [$rq['reference'], $type]);
                $updb = R::load($tbl, $id);
                $updb->c_order = 'zz';
                $updb->c_parent = '';
                $result = R::store($updb);             
                return ['flag' => 'Ok', 'msg' => 'Recid: '.$id.', with c_reference: '.$rq['reference'].' goes to: Root, at position: zz'];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'dberror' => $result
                ];
                return ['flag' => 'NotOk', 'msg' => $err];
            }
         } 		

        /** Save Content
         * Save content from a multi-lingual popup rich text content editor (TinyMCE) to a JSON field in c_document, probably d_text in c_document
         * @param - array - Usual $arguments
         * @return - array - Ok flag and data or error
         **/
		 public static function saveContent($vars)
		 {

	        try {

				if(!A::getAuth("write", $vars['table'], $vars['tabletype'], '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				};

	        	global $clq;
			    $rq = $vars['rq'];
			    $recid = $rq['recid'];
			    $fld = $rq['fldname'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];

	        	// Multiple language records
	        	$idioms = $clq->get('idioms');
	        	$updb = R::load($table, $recid);
	        	$val = [];
	        	foreach($idioms as $lcdcode => $lcdname) {
	        		$val[$lcdcode] = rawurldecode($rq[$fld.'_'.$lcdcode]);
	        	};

	        	// Deal with existing stuff in c_document
				$sql = "SELECT c_document FROM ".$table." WHERE id = ?";
				$doc = json_decode(R::getCell($sql, [$recid]), true);
				$doc[$fld] = $val; 
	        	$updb->c_document = F::jsonEncode($doc);
	        	$updb->c_whomodified = Q::whoMod();
	        	$updb->c_lastmodified = Q::lastMod();

	        	// Deal with revision or version
	        	$v = Q::versionControl($vars);
	        	$versionfield = $v['fld'];
	        	$versionnumber = $v['newval'];
	        	$updb->$versionfield = $versionnumber;

	        	$result = R::store($updb);
	        	if(is_int($result) && (int)$result > 0) {
	        		$msg = Q::cStr('370:Record updated successfully');
	        	} else {
	        		$msg = Q::cStr('371:Error saving record').' - '.$result;
	        	}
				return [
					'flag' => "Ok",
					'msg' => $msg
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage() 
				]; 
			}						
		 }

		/** Save the code in the record
		 *
		 * @param - array - usual args
		 * @return - array - Ok flag and message
		 **/
		 public static function saveCode($vars)
		 {

	        try {

	        	global $clq;
			    $rq = $vars['rq'];
			    $recid = $rq['recid'];
			    $fld = $rq['fldname'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];

				if(!A::getAuth("write", $vars['table'], $vars['tabletype'], '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				};

	        	// Multiple language records
	        	$idioms = $clq->get('idioms');
	        	$updb = R::load($table, $recid);
	        	$val = preg_replace("/\r\n/", "\n", $rq[$fld]);
	        	$doc = [$fld => $val];

	        	// Deal with existing stuff in c_document
				$sql = "SELECT c_document FROM ".$table." WHERE id = ?";
				$cell = R::getCell($sql, [$recid]);
				if(count($cell) > 0) {
					$cdoc = json_decode($cell, true);
					$doc = array_merge($cdoc, $doc);
				}

	        	$updb->c_document = F::jsonEncode($doc);
	        	$updb->c_whomodified = Q::whoMod();
	        	$updb->c_lastmodified = Q::lastMod();

	        	// Deal with revision or version
	        	$v = Q::versionControl($vars);
	        	$updb->$v['fld'] = $v['newval'];

	        	$result = R::store($updb);
	        	if(is_int($result) && (int)$result > 0) {
	        		$msg = Q::cStr('370:Record updated successfully');
	        	} else {
	        		$msg = Q::cStr('371:Error saving record').' - '.$result;
	        	}
				return [
					'flag' => "Ok",
					'msg' => $msg
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage() 
				]; 
			}						
		 }

		/** Save post of Creator Form which is text from Code div
		 *
		 * @param - array - usual array of variables
		 * @return - string - message in JSON format
		 **/
		 public static function saveCreatorRecord($vars)
		 {
	        try {

	        	global $clq;
			    $rq = $vars['rq'];
			    $recid = $rq['recid'];
				$idiom = $vars['idiom'];
				$table = $rq['table'];
				$recid != 0 ? $updb = R::load($table, $recid) : $updb = R::dispense($table) ;
				$txt = urldecode($rq['text']);
				// $filtered = preg_replace("/\r\n/", "\n", $txt);

				$toml = $clq->resolve('Toml');
				$request = Toml::parse($txt); 
				$doc = [];

	        	// Walk through all the fields and values in the request
	        	foreach($request as $fld => $val) {	        		
	        		$chk = strtolower(substr($fld, 0, 2));	
					switch($chk) {
						case "c_": 
							if(isset($val)) {
								$updb->$fld = $val;
							} else {
								$updb->$fld = "";
							}
						break;
						case "d_": 
							if(isset($val)) {
								$pts = explode('.', $fld);
								switch(count($pts)) {
									case 1:
										$doc[$pts[0]] = $val;
									break;

									case 2:
										$doc[$pts[0]][$pts[1]] = $val;
									break;

									case 3:
										$doc[$pts[0]][$pts[1]][[2]] = $val;
									break;
								}
							} else {
								$doc[$fld] = "";
							}
						break;	
						default: false; 
					}	
	        	};
	        	$jdoc = F::jsonEncode($doc);
	        	$updb->c_document = $jdoc;
	        	$updb->c_whomodified = Q::whoMod();
	        	$updb->c_lastmodified = Q::lastMod();

	        	$result = (int)$recid;
	        	$result = R::store($updb);
	        	if(is_int($result) && (int)$result > 0) {
					$sql = "SELECT * FROM ".$table." WHERE id = ?";
					$row = R::getRow($sql, [$result]);
					return [
						'flag' => "Ok",
						'data' => ['row' => $row]
					];
	        	} else { 
					return [
						'flag' => "NotOk",
						'data' => ['msg' => [$row, $jdoc]]  // $result
					];	        		
	        	}
	        		
			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage() 
				]; 
			}				
		 }

		/** Post a value to a table in the database
		 * if a c_document field, it does an update
		 * @param - array - usual array of variables
		 * @return - string - message in JSON format
		 **/		 
		 public static function postValue($vars)
		 {
	        try {

	        	global $clq;
			    $rq = $vars['rq'];
			    $recid = $rq['recid'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$fld = $rq['fldname'];
				$val = $rq['newvalue'];
				$updb = R::load($table, $recid);

        		$chk = strtolower(substr($fld, 0, 1));	
				switch($chk) {
					case "c": 
						$updb->$fld = $val;
					break;
					case "d": 
						$sql = "SELECT c_document FROM ".$table." WHERE id = ?";
						$doc = json_decode(R::getCell($sql, [$recid]), true);
						$doc[$fld] = $val; 
						$updb->c_document = F::jsonEncode($doc);
					break;	
					default: throw new Exception("Request key had no usable starting letter! - ".$chk." - ".$fld);
				};		

	        	$updb->c_whomodified = Q::whoMod();
	        	$updb->c_lastmodified = Q::lastMod();
	        	$result = R::store($updb);
	        	if(is_int($result) && (int)$result > 0) {
					$sql = "SELECT * FROM ".$table." WHERE id = ?";
					$row = R::getRow($sql, [$result]);
					return [
						'flag' => "Ok",
						'data' => ['row' => $row]
					];
	        	} else { 
					return [
						'flag' => "NotOk",
						'data' => ['msg' => $result]
					];	        		
	        	}
	        		
			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage() 
				]; 
			}
		 }

		/** Add a new list item 
		 *
		 * @param- array - usual arguments
		 * @return - flagg
		 **/
		 static function addNewOption($vars)
		 {
	        try {

	        	global $clq;
			    $rq = $vars['rq'];
				$sql = "SELECT c_document FROM dbcollection WHERE c_type = ? AND c_reference = ?";
				$cell = R::getCell($sql, ['list', $rq['listname']]);
				$olddoc = json_decode($cell, true);			    
				$updb = R::findOne('dbcollection', 'WHERE c_type = ? AND c_reference = ?', ['list', $rq['listname']]);
				$doc = []; $idms = $clq->get('idioms');
				foreach($idms as $lcdcode => $lcdname) {
					$doc[$rq['x_value']][$lcdcode] = $rq['x_label_'.$lcdcode];
				};
				$newdoc['d_text'] = array_merge($olddoc['d_text'], $doc);
				$updb->c_document = json_encode($newdoc);
	        	$updb->c_whomodified = Q::whoMod();
	        	$updb->c_lastmodified = Q::lastMod();
	        	$result = R::store($updb);
	        	if(is_int($result) && (int)$result > 0) {
					return [
						'flag' => "Ok",
						'newval' => $rq['x_value']
					];
	        	} else { 
					return [
						'flag' => "NotOk",
						'msg' => $result
					];	        		
	        	}
	        		
			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'msg' => $e->getMessage() 
				]; 
			}	    		 	
		 }

    /** Data Retrieval
     *
     * getGridData()
     * getListData()  
     * getTableData()        
     * getTreeData()
     * getCardData()
     * getCalendarData()
     * getGalleryData()
     * getRowData()
     * getRecordData()  
     *
     *************************************************************************************************************/
         
        /** Get Recordset for a Gijgo Grid layout 
         * 
         * @param - array - variables
         * @return - array - Recordset
         **/
         static function getGridData(array $vars)
         {   
			$vars['service'] = 'datagrid';
			$r = self::getPagedData($vars);
			$result = [
				'total' => $r['total'], 
				'records' => $r['rows'],
				'query' => $r['sql'],
			];						
			return $result; 
         }

        /** Get Recordset, suitable for a datalist layout 
         * 
         * @param - array - variables
         * @return - array - Recordset
         **/
         static function getListData(array $vars)
         {
			$vars['service'] = 'datalist';
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

        /** Get Recordset, suitable for a datatable 
         * 
         * $dgcfg = Q::cModel('datatable', $vars['table'], $vars['tabletype']);
         * @param - array - variables
         * @return - array - Recordset
         **/ 
         static function getTableData(array $vars) 
         {
        	$vars['service'] = 'datatable';
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

		/** Get paged data
		 *
		 * @param - array - usual $vars
		 * @return - array - recordset and info required by caller
		 **/
		 static function getPagedData(array $vars)
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__."()";
			try {

				global $clq;
				// Set process variables
				$sql = ""; $total = 0; $offset = 0; $limit = 0; $rawrs = []; $rows = []; $dcfg = []; $search = []; $fid = "field"; $rq = $vars['rq']; 

				// Authorise
				if(!A::getAuth("read", $vars['table'], $vars['tabletype'], '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				};
				
				// Define datagrid config
				$model = $clq->resolve('Model'); 
				$dcfg = $model->stdModel($vars['service'], $vars['table'], $vars['tabletype']);

				// Which fields do we need for the table?
				foreach($dcfg['columns'] as $q => $prop) {
					if(array_key_exists('visible', $prop) and $prop['visible'] == 'false') {
						unset($dcfg['columns'][$q]);
						if(!array_key_exists('order', $prop)) {
							$dcfg['columns'][$q]['order'] = 'z';
					}};
				};
				// Fields ordered here
				if($vars['service'] != 'datalist') {
					$ordered = Q::array_orderby($dcfg['columns'], 'order', SORT_ASC);
				} else {
					$ordered = $dcfg['columns'];
				}
								
				
				$flds = [];
				foreach($ordered as $q => $prop) {
					
					// Identify field name
					if($vars['service'] == 'datagrid') {
						$fld = $prop['field'];
					} else {
						$fld = $q;
					}

					// Does column have search attached to it ??
					if(array_key_exists($fld, $rq)) {
						// Column could exist but be empty, only use if not empty
						if($rq[$fld] != '') {
							$search[$fld] = $rq[$fld];
						}
					};						
					
					// Add fieldname to array of fields
					$flds[] = $fld;
				}; 

				// Which fields do we need for the table?				
				// But must convert any d_ to one c_document
				foreach($flds as $n => $fldname) {
					$chk = strtolower(substr($fldname, 0, 1));
					if($chk == 'd') {
						$flds[$n] = 'c_document';
					}
				};

				// Add id, just in case this config does not include it
				$flds[] = 'id';
				$flds = array_unique($flds);
				$fldnames = "";
				foreach($flds as $n => $fldname) {
					$fldnames .= $fldname.',';
				};			
				
				$fldnames = trim($fldnames, ',');

				// Build the Query
				$sql = ""; $params = []; $where = "";
				$sql .= "SELECT $fldnames FROM ".$vars['table'];	

				// Add Tabletype as a where
				if($vars['tabletype'] != '') {
					$where .= "c_type = ?";
					$params[0] = $vars['tabletype'];
				};

				// Is Search set ?
				if(array_key_exists('search', $rq)) {if($rq['search'] != '') {
					$searches = explode(',', $rq['search']);
					foreach($searches as $t => $item) {
						$e = explode('|', $item);
						$search[$e[0]] = $e[1];
					}
				}};

				// More searches if search array has elements
				if(count($search) > 0) {
					foreach($search as $s => $w) {
						$params[] = '%'.$w.'%';
						if($where != '') {
							$where .= ' AND ';
						};
						$where .= $s." COLLATE latin1_swedish_ci LIKE ? ";
					}
				};

				if($where != '') {$sql .= " WHERE ".$where;};

				// Run query at this point to get total number of records
				$recs = R::getAll($sql, $params); 
				$total = count($recs); 

				// Now add Order By
				if(array_key_exists('sortBy', $rq)) {if($rq['sortBy'] != '') {
					$sql .= ' ORDER BY '.$rq['sortBy'].' '.$rq['direction'];
				}} else {
					$sql .= ' ORDER BY '.$dcfg['orderby'];
				}

				// And add Limit by
				if($vars['service'] == 'datagrid') {
					$offset = (int)$rq['page'] - 1; // Page 1 minus 1 becomes 0
					$limit = (int)$rq['limit']; // 15
					$start = $offset * $limit; 	// 0 x 15 = 0, 				
				} else { // datalist and datatable
					$offset = (int)$rq['offset']; // Reads 10
					$limit = (int)$rq['limit'];	// also reads 10
					$start = $offset;
				}
				
				// If total number of records 
				if($total < $limit) {
					$start = 0;
					$limit = $total;
				}

				// Start creating all the edge cases here
				if(+$total > 0) {		

					$sql .= ' LIMIT '.$start.', '.$limit;
					
					// Finally Run the Query
					$rawrs = R::getAll($sql, $params); 
					$rs = self::extractAndMergeRecordset($rawrs);

					// Now format the records
					for($r = 0; $r < count($rs); $r++) {		
						foreach($dcfg['columns'] as $n => $prop) {
							// Identify field name
							if($vars['service'] == 'datagrid') {
								$f = $prop['field'];
							} else {
								$f = $n;
							}
							$rows[$r][$f] = Q::formatCell(
								$f, // fieldname
								$rs[$r], // row
								$prop, // properties of the field
								$vars['table'], // table from which record is derived
								$rs[$r]['id'] // row id
							);
						} 
					};	

				}; 	

				$result = [
					'total' => $total, 
					'rows' => $rows,
					'offset' => $offset,
					'limit' => $limit,	
					'search' => $params,			
					'sql' => $sql
				];
				return $result;

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
					'sql' => $sql,	
					'dbrowset' => $rawrs,				
					'rows' => $rows,
					// 'tabledefinition' => $dtcfg
				];
				L::cLog($err);
				return [
					'flag' => 'NotOk',
					'data' => $err
				];
	        }    
		 }

        /** Previous three methods share this method as common 
         * Get Recordset, suitable for a Gijgo Tree layout
         * $dtcfg = Q::cModel($vars['table'], $vars['tabletype'], 'datatree');
         * @param - array - variables
         * @return - string in the form of an HTML list
         **/
         static function getGjTreeData(array $vars)
         {
        	global $clq;
        	$clq->set('table', $vars['table']);
        	$clq->set('tabletype', $vars['tabletype']);
        	$clq->set('rq', $vars['rq']);

			if(!A::getAuth("read", $vars['table'], $vars['tabletype'], '')) {
				throw new Exception("Not authorised based on table > tabletype > fields");
			};

            $sql = "SELECT * FROM ".$vars['table']." WHERE c_type = ? ORDER BY c_parent ASC, c_order ASC";
            $rs = R::getAll($sql, [$vars['tabletype']]); 

            if(!is_array($rs) || count($rs) < 1) {
            	$fn = $vars['table'].'.'.$vars['tabletype'].'.cfg';
                $rs = C::cfgReadFile('admin/data/'.$fn);
            };

            $dbresult = self::extractAndMergeRecordset($rs);

            if(count($dbresult) < 1) {
            	$lvl = ""; // Top level
				$list = self::formatTree($dbresult, $lvl, $vars, 'gjtree'); 
            } else {
 				
 				// Dummy
 				$list = [
 					['id' => '1', 'text' => '1 dummy', 'children' => [
					 	['id' => '2', 'text' => '1-1 dummy'],
					 	['id' => '3', 'text' => '1-2 dummy'],
					 	['id' => '4', 'text' => '1-3 dummy']
					]],
  					['id' => '5', 'text' => '2 dummy', 'children' => [
  					 	['id' => '6', 'text' => '2-1 dummy']
  					]]	
 				];
	        };			

			return $list;
         }

        /** Get Recordset, suitable for a jqTree layout 
         * 
         * $dtcfg = Q::cModel($vars['table'], $vars['tabletype'], 'datatree');
         * @param - array - variables
         * @return - string in the form of an HTML list
         **/
         static function getTreeData(array $vars)
         {

        	$method = self::THISCLASS.'->'.__FUNCTION__."()";
        	try {

	        	global $clq;
        		$clq->set('table', $vars['table']);
        		$clq->set('tabletype', $vars['tabletype']);
        		$clq->set('rq', $vars['rq']);

				if(!A::getAuth("read", $vars['table'], $vars['tabletype'], '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				};

	            $sql = "SELECT * FROM ".$vars['table']." WHERE c_type = ? ORDER BY c_parent ASC, c_order ASC";
	            $rawrs = R::getAll($sql, [$vars['tabletype']]); 

	            if(!is_array($rawrs) || count($rawrs) < 1) {
	            	$fn = $vars['table'].'.'.$vars['tabletype'].'.cfg';
	                $rawrs = C::cfgReadFile('admin/data/'.$fn);
	            };

	            $dbresult = self::extractAndMergeRecordset($rawrs);

	            $lvl = "0"; // Top level
	            if( (is_array($dbresult)) && (count($dbresult) > 0) ) {
	            	$thisone = "valid";
					$list = self::formatTree($dbresult, $lvl, $vars, 'jqtree'); 
	            } else {
	 				
	 				// Not correct now
	 				$dummy = [

	 				];
	 				$list = self::formatTree($dummy, $lvl, $vars, 'jqtree');
		        };			

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
					// 'query' => $sql,
					// 'dbrowset' => $rawrs,
					// 'rowset' => $dbresult,
					'thisone' => $thisone,
					'result' => $list
				];

				// Set to comment when completed
				L::cLog($test);  
				
				// If not returned already 				
				return $list;

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
					'query' => $sql,	
					'dbrowset' => $rawrs,
					'rowset' => $dbresult
				];
				L::cLog($err);
				return false;
	        } 
         }

        /** Get Recordset, suitable for a datacard layout 
         *
         * Decision made, Carddata can only be store in TOML formatted files
         * @param - array - variables
         * @return - array - Recordset
         **/
         static function getCardData(array $vars)
         {
            
			$method = self::THISCLASS.'->'.__FUNCTION__."()";
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				if(!A::getAuth("read", $table, $tabletype, '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				};

				// Set process variables
				$sql = ""; $rawrs = []; $rs = []; $rset = []; $dccfg = [];		
							
				// Define datalist config
				$model = $clq->resolve('Model'); 
				$dccfg = $model->stdModel('datacard', $table, $tabletype);

				$flds = $dccfg['columns'];
				$fields = "id,";
				foreach($flds as $fld => $label) {
					$fields .= $fld.",";
				}
				$fields = trim($fields, ',');

				// Obtains existing records from the database - either Table plus Tabletype, or just Table
				if($tabletype != '') {
					$sql = "SELECT ".$fields." FROM ".$table." WHERE c_type = ? ORDER BY ".$dccfg['orderby'];
					$rawrs = R::getAll($sql, [$tabletype]); 
				} else {
					$sql = "SELECT ".$fields." FROM ".$table." ORDER BY ".$dccfg['orderby'];
					$rawrs = R::getAll($sql); 
				}	

				// Recordset is always an array but may be empty. 
				if(count($rawrs) < 1) {
					// Creates a Dummy Test record
					$rset = [];
					$row['id'] = 0;
					foreach($flds as $fld => $props) {
						$row[$fld] = 'x';
					}
					$rset[] = $row; 
				} else {
					$rset = self::extractAndMergeRecordset($rawrs);
				};	

				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 	
				if(array_key_exists('type', $vars) && $vars['type'] == 'reload') {
					return ['flag' => 'Ok', 'data' => $rset];
				} else {
					return $rset;
				}		
				

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
				];
				L::cLog($err);
				return false;
	        } 
         }

        /** Get Recordset, suitable for a calendar layout 
         *
         * $dtcfg = Q::cModel($vars['table'], $vars['tabletype'], 'datatree');
         * @param - array - variables
         * @return - array - Recordset
         **/
         static function getCalendarData($vars)
         {

			$method = self::THISCLASS.'->'.__FUNCTION__."()";
			try {
				
				// Set process variables
				global $clq;
				$sql = ""; $rawrs = []; $rs = []; $calcfg = [];	$evset = [];	
							
				// Define datable config
				$model = $clq->resolve('Model'); 
				$calcfg = $model->stdModel('calendar', $vars['table'], $vars['tabletype']);			
	 
				$sql = "SELECT * FROM ".$vars['table']." WHERE c_type = ? ORDER BY c_reference ASC";
				$rawrs = R::getAll($sql, [$vars['tabletype']]); 
	            $rs = self::extractAndMergeRecordset($rawrs);

	            $evset = [];
	            for($r = 0; $r < count($rs); $r++) {
	            	$evt = [];
	            	$evt['id'] = $rs[$r]['id'];
					$evt['start_date'] = $rs[$r]['d_datefrom'];
					$evt['end_date'] = $rs[$r]['d_dateto'];
					$evt['text'] = $rs[$r]['d_title'];
					$evt['url'] = $rs[$r]['d_url'];
					$evt['details'] = $rs[$r]['d_description'];
					$evt['type'] = $rs[$r]['c_category'];
					$evset[] = $evt; unset($evt);
	            };
		        // If no records at all, send dummy message
		            if(!count($evset) > 0) {
		            	$dummy = [
		            		'id' => 1,
							'start_date' => Q::cNow(),
							'end_date' => Q::cNow(),
							'text' => Q::cStr('144:No records available'),	
							'details' => Q::cStr('372:To start adding records, dbl-click on any day'),				
							'type' => 'task',
							'url' => 'http://'
		            	];
		            	$evset[] = $dummy;
		            }  
  
				// Test
				$test = [
					'method' => $method,
					'vars' => $vars,
					'query' => $sql,
					'dbrowset' => $rawrs,
					'rowset' => $rs,
					'events' => $evset,
					'calendardefinition' => $calcfg
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return $evset;

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					// 'vars' => $vars,
					'query' => $sql,	
					'dbrowset' => $rawrs,				
					'rowset' => $rs,
					'eventset' => $evset,
					'calendardefinition' => $calcfg
				];
				L::cLog($err);
				return false;
	        }    
         }

        /** Get Recordset, suitable for a gallery layout 
         *
         * $dtcfg = Q::cModel($vars['table'], $vars['tabletype'], 'datatree');
         * @param - array - variables
         * @return - array - Recordset
         **/
         static function getGalleryData($vars)
         {
			$method = self::THISCLASS.'->'.__FUNCTION__."()";
			try {

				global $clq;
			    $rq = $vars['rq'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				// Set process variables
				$sql = ""; $rawrs = []; $rs = []; $rset = []; $dlcfg = [];		
							
				// Define datable config
				$model = $clq->resolve('Model'); 
				$gcfg = $model->stdModel('gallery', $vars['table'], $vars['tabletype']);

				// Obtains existing records from the database - either Table plus Tabletype, or just Table
				if($vars['tabletype'] != '') {
					$sql = "SELECT * FROM ".$vars['table']." WHERE c_type = ? ORDER BY ".$gcfg['orderby'];
					$rawrs = R::getAll($sql, [$vars['tabletype']]); 
				} else {
					$sql = "SELECT * FROM ".$vars['table']." ORDER BY ".$gcfg['orderby'];
					$rawrs = R::getAll($sql); 
				}

				// Recordset is always an array but may be empty. 
				if(count($rawrs) < 1) {
					// Creates a Dummy Test record
					$row = [
						'id' => '1',
						'c_reference' => 'img(0)',
						'c_common' => 'Test Image',
						'd_title' => 'Test Image',
						'd_image' =>$gcfg['subdir'].'php-cup.png',
						'd_description' => 'The main PHP cup',
						'c_category' => 'other',
						// '' => '',
						// '' => ''
					];
					$rset[] = $row;
				} else {

					$rs = self::extractAndMergeRecordset($rawrs);

					// Format the recordset so it is suitable for use by the gallery
					// Don't forget idiomtext fields are already array and Vue templating would happily show them as Objects
					for($r = 0; $r < count($rs); $r++) {
						
						$row = [
							'id' => $rs[$r]['id'],
							'c_reference' => $rs[$r]['c_reference'],
							'c_common' => $rs[$r]['c_common'],
							'd_title' => $rs[$r]['d_title'][$idiom], // maybe language
							'd_image' => $gcfg['subdir'].$rs[$r]['d_image'],
							'd_description' => $rs[$r]['d_description'][$idiom], // maybe language
							'c_category' => Q::fList($rs[$r]['c_category'], $gcfg['categories'])
							// '' => '',
							// '' => ''
						];
						$rset[] = $row;
					}
				};	

				// Test
				$test = [
					'method' => self::THISCLASS.'->'.$method,
					'vars' => $vars,
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 				
				return $rset;

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.$method,
					'errmsg' => $e->getMessage(),
				];
				L::cLog($err);
				return false;
	        }  
            
			return $dbresult;
         }

        /** Get Generic Recordset 
         *
         * @param - array - variables
         * @return - array - Recordset
         **/
         static function getRowData(array $vars)
         {

			$method = "getRecordData()";
			try {

				// Table, Tabletype, Display Type and Model
					$table = $vars['table'];
					global $clq;
					if(array_key_exists('displaytype', $vars['rq'])) {
						$displaytype = $vars['rq']['displaytype'];
					} else {
						$displaytype = "datatable";
					}		            
					if(array_key_exists('tabletype', $vars)) {
						$tabletype = $vars['tabletype'];
					} else {
						$tabletype = "";
					}

					$mdl = $clq->resolve('Model'); 
					$model = $mdl->stdModel($displaytype, $table, $tabletype);
	            		
				// Obtain generic Recordset from Table
					if($tabletype != '') {
		           		$sql = "SELECT * FROM ".$table." WHERE c_type = ?";
		            	$rs = R::getAll($sql, [$tabletype]); 
					} else {
		           		$sql = "SELECT * FROM ".$table;
		            	$rs = R::getAll($sql); 
					}
		            $dbrs = self::extractAndMergeRecordset($rs);
				
					$filter = $clq->resolve('Filter');
	            	$fi = $filter->filter(false);

		        	/*
					$fi->value('name')->callback(function($value, $filterData) {
					    return '<strong>' . $value . '</strong>';
					});
					$result = $fi->filter(['name' => 'John']);

					$fi->value('names')->each(function (Filter $filter) {
					    $filter->value('name')->upperFirst();
					});
					$result = $fi->filter([
					    'names' => [
					        ['name' => 'john'],
					        ['name' => 'rick'],
					    ],
					]);

					*/

		        // Apply fields
		           	$flds = explode(',', $model['fieldsused']);
		           	$fi->values($flds);
		           	$result = $fi->filter($dbrs);

	            // Apply Filters

		           	$ed = strtotime($vars['rq']['end']);
					$fi->value('d_dateto')->callback(function($ed, $rsd) {
						$dt = strtotime($val);
					    return $dt >= $ed;
					});
					$rs = $fi->filter($rsd);

	            // Apply limit
					$start = (int)$vars['rq']['page'] * (int)$vars['rq']['limit'];
					array_slice($rs, $start, (int)$vars['rq']['limit'], true);

	            // Apply Order
					$rset = $fi->filter($dbrs);

				// Test
				$test = [
					'method' => self::THISCLASS.'->'.$method,
					'vars' => $vars
				];

				// Set to comment when completed
				L::cLog($test);  
				
				// If not returned already 				
				return $rset;

	        } catch(Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.$method,
					'errmsg' => $e->getMessage()
				];
				L::cLog($err);
				return false;
	        }            
		 }

        /** Get Recordset for a Gijgo Grid layout containing all data from a table 
         *
         * @param - array - variables
         * @return - array - Recordset
         **/
         static function getRecordData(array $vars)
         {   
			
			$method = self::THISCLASS.'->'.__FUNCTION__."()";
			try {

				global $clq;
				// Set process variables
				$sql = ""; $total = 0; $offset = 0; $limit = 0; $rawrs = []; $rows = []; $dcfg = []; $search = []; 
				$fid = "field"; $rq = $vars['rq']; 
				array_key_exists('table', $rq) ? $table = $rq['table'] : $table = "dbcollection" ;
				array_key_exists('page', $rq) ? $page = $rq['page'] : $page = 1 ;
				array_key_exists('limit', $rq) ? $lmt = $rq['limit'] : $lmt = 15 ;

				// Which fields do we need for the table?
				$dcfg = [
					'columns' => [
						['field' => 'id', 'order' => 'a'],
						['field' => 'c_type', 'order' => 'b'],						
						['field' => 'c_reference', 'order' => 'c'],
						['field' => 'c_common', 'order' => 'z']
					],
					'orderby' => 'c_reference ASC'
				];

				// Fields ordered here
				$ordered = Q::array_orderby($dcfg['columns'], 'order', SORT_ASC);			

				$flds = [];
				foreach($ordered as $q => $prop) {
					
					$fld = $prop['field'];
					// Does column have search attached to it ??
					if(array_key_exists($fld, $rq)) {
						// Column could exist but be empty, only use if not empty
						if($rq[$fld] != '') {
							$search[$fld] = $rq[$fld];
						}
					};						
					
					// Add fieldname to array of fields
					$flds[] = $fld;
				};

				// Which fields do we need for the table?				
				// But must convert any d_ to one c_document
				foreach($flds as $n => $fldname) {
					$chk = strtolower(substr($fldname, 0, 1));
					if($chk == 'd') {
						$flds[$n] = 'c_document';
					}
				};
				
				// Add id, just in case this config does not include it
				$fldnames = "";
				foreach($flds as $n => $fldname) {
					$fldnames .= $fldname.',';
				}; $fldnames = trim($fldnames, ',');

				// Build the Query
				$sql = ""; $params = []; $where = "";
				$sql .= "SELECT $fldnames FROM ".$table;	

				// Is Search set ?
				if(array_key_exists('search', $rq)) {if($rq['search'] != '') {
					$searches = explode(',', $rq['search']);
					foreach($searches as $t => $item) {
						$e = explode('|', $item);
						$search[$e[0]] = $e[1];
					}
				}};

				// More searches if search array has elements
				if(count($search) > 0) {
					foreach($search as $s => $w) {
						$params[] = '%'.$w.'%';
						if($where != '') {
							$where .= ' AND ';
						};
						$where .= $s." COLLATE latin1_swedish_ci LIKE ? ";
					}
				};

				if($where != '') {$sql .= " WHERE ".$where;};

				// Run query at this point to get total number of records
				$recs = R::getAll($sql, $params); 
				$total = count($recs); 

				// Now add Order By
				if(array_key_exists('sortBy', $rq)) {if($rq['sortBy'] != '') {
					$sql .= ' ORDER BY '.$rq['sortBy'].' '.$rq['direction'];
				}} else {
					$sql .= ' ORDER BY '.$dcfg['orderby'];
				}

				// And add Limit by
				$offset = (int)$page - 1; // Page 1 minus 1 becomes 0
				$limit = (int)$lmt; // 15
				$start = $offset * $limit; 	// 0 x 15 = 0, 				

				// If total number of records 
				if($total < $limit) {
					$start = 0;
					$limit = $total;
				}

				// Start creating all the edge cases here
				if(+$total > 0) {		

					$sql .= ' LIMIT '.$start.', '.$limit;
					
					// Finally Run the Query
					$rawrs = R::getAll($sql, $params); 
					$rs = self::extractAndMergeRecordset($rawrs);

					// Now format the records
					for($r = 0; $r < count($rs); $r++) {		
						foreach($dcfg['columns'] as $n => $prop) {
							// Identify field name
							$f = $prop['field'];
							$rows[$r][$f] = Q::formatCell($f, $rs[$r], $prop, $vars['table'], $rs[$r]['id']);
						} 
					};	
				}; 			

				$result = [
					'total' => $total, 
					'records' => $rows,
					'offset' => $offset,
					'limit' => $limit,	
					'search' => $params,			
					'query' => $sql
				];
				return $result;

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $vars,
					'query' => $sql,	
					'dbrowset' => $rawrs,				
					'rows' => $rows
				];
				L::cLog($err);
				return [
					'flag' => 'NotOk',
					'data' => $err
				];
	        } 
         }

        /** Get data from a record, formatted for use on a Formlet 
         * return data as JSON to populate select options, checkbox boxes and labels and a radiogroup
         * also suitable for an autocomplete
         * updates go to postValue($vars)
         * @param - array - usual set of arguments
         * @return - array - object_encoded
         **/
         static function formletData(array $vars)
         {
         	
			$method = self::THISCLASS.'->'.__FUNCTION__."()";
			try {

				global $clq;
			    $rq = $vars['rq'];
			    /*
				    'displaytype': cfg.displaytype,
                	'action': dta.action, // example - changestatus
                	'ajabuster': 'anything',
                	'formlettype': 'select',
                	'params': dta.params,
                	'recid': cfg.recid			    
			    */
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				// Check permissions to read at this stage
				if(!A::getAuth("read", $table, $tabletype, '')) {
					throw new Exception("Not authorised based on table > tabletype > fields");
				};

				// Set defaults for variables
				$usedoc = false; $label = ""; $fld = $rq['fldname'];
				$help = Q::cStr('164:Select an option from above and press submit');
				switch($rq['action']) {

					// Select
					case "changestatus": // draft, published, archived etc.
						$label = Q::cStr('501:Change status');
					break;

					// Radio or slider
					case "changedisplay": // active or inactive
						$label = Q::cStr('521:Change display status');
					break;

					// Check boxes
					case "changeoptions":
						$label = Q::cStr('522:Change options');
					break;

					// Select
					case "changecategory":
						$label = Q::cStr('522:Change category');
					break;

					// Level selector - plunk
					case "changelevel":
						$label = Q::cStr('524:Change level');
						$help = Q::cStr('450:Select the access level for read, write and delete records');
					break;

					// dbuser only - select
					case "changegroup":
						$label = Q::cStr('525:Change group');
					break;
				};

				// Get current value from original record such as newsitem or user
				// language not supported ??
				if($usedoc) {
					$sql = "SELECT c_document FROM ".$table." WHERE id = ?";
					$cell = R::getCell($sql, [$rq['recid']]);
					$doc = json_decode($cell, true);
					$currval = $doc[$fld];
				} else {
					$sql = "SELECT ".$fld." FROM ".$table." WHERE id = ?";
					$currval = R::getCell($sql, [$rq['recid']]);			
				}

				switch($rq['formlettype']) {

					// Create formlet array for a select
					case "select":

						$p = explode('|', $rq['params']); // $p[0] == 'list', $p[1] == 'statustypes'
						$list = Q::cList($p[1]);
						$options = [];
						foreach($list as $val => $lbl) {
							$opt = [];
							$val == $currval ? $opt = ['value' => $val, 'html' => $lbl, 'selected' => 'selected'] :$opt = ['value' => $val, 'html' => $lbl] ;
							$options[] = $opt; unset($opt);
						};
						
						$jtml = [
							'type' => 'container',
							'class' => 'form-group',
							'html' => [
								// Label
								[
									'type' => 'label',
									'class' => 'bold',
									'for' => $fld,
									'html' => $label
								],
								// Input such as select
								[
									'type' => 'select',
									'name' => $fld,
									'id' => $fld,
									'class' => 'form-control custom-select',
									'options' => $options
								],
								// Helptext
								[
									'type' => 'span',
									'class' => 'form-text text-muted',
									'html' => $help
								]
							]
						];
					break;

					case "radiogroup":

					break;

					case "checkboxgroup":

					break;

					case "level":

					break;
				}


				if($jtml) {
					return ['flag' => 'Ok', 'data' => $jtml];
				} else {
					return ['flag' => 'NotOk', 'msg' => $msg];
				}	

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'variables' => $vars,

                ];
                return ['flag' => 'NotOk', 'msg' => $err];
            }
         }

    /** Data Retrieval Support Utilities
     *
     * formatTree()
     * - treeElement()
     * getList()
     * getEvent()
     * getFilteredArray()
     * - displayElement()
     * - formatElement() - old
     * - formatCell() - new generic cell formatter for Grids and Table
     * 
     *************************************************************************************************************/

    	/** Format records for a tree  
    	 * This method deals with creating the structure of a tree and has no bearing on presentation
    	 * @param - array - the recordset of all nodes
    	 * @param - string - the level, starting with top
    	 * @param - array - passing on the $vars
    	 * @return - HTML containing a structured and formatted Tree
    	 **/
         static function formatTree($rs, $lvl, $vars, $tree = "jqtree")
         {

        	$filteredarray = self::getFilteredArray($rs, $lvl);

        	if($tree == "jqtree" ) {
				$t = "name"; $method = "treeJsonElement"; $fld = 'c_reference';
        	} else {
				$t = "text"; $method = "treeElement"; $fld = 'd_title';
        	};

        	if($filteredarray) {
	           	$list = [];
	           	foreach($filteredarray as $i => $row) {
	           		$itm = [];
	                if(self::formatTree($rs, $row[$fld], $vars, $tree)) {
	                    $itm[$t] = $row['id'];
	                    $itm['id'] = self::$method($row, $vars);
	                    $itm['children'] = self::formatTree($rs, $row[$fld], $vars, $tree);

	                } else {
	                    $itm[$t] = $row['id'];
	                    $itm['id'] = self::$method($row, $vars);
	                }
	                $list[] = $itm; unset($itm);
	            }

	            return $list;
        	} else {
        		return false;
        	}
         }

        /** Returns a JSON Tree 
         * 
         * @param - array - row of data from the recordset
         * @param - array - the $vars - useful but in practice not being used!
         * @return - string - a Tree row
         **/
         protected static function treeJsonElement($row, $vars)
         {

        	global $clq;
        	$model = $clq->resolve('Model'); 
			$dtcfg = $model->stdModel('datatree', $vars['table'], $vars['tabletype']);

        	// Generate row
        	$itms = "";
        	foreach($dtcfg['columns'] as $action => $col) {if($action != 'id') {
        		$itms .= self::formatCell(['type' => $col['type']], $row, $action).'|';
        	}};
        	$itms = trim($itms, '|');
        	return $itms;
         }

        /** Tree element 
         * As displayElement(), but for a Tree
         * @param - array - row of data from the recordset
         * @param - array - the $vars - useful but in practice not being used!
         * @return - string - a Tree row
         **/
         protected static function treeElement($row, $vars)
         {
        	
        	global $clq;
        	$model = $clq->resolve('Model'); 
			$dtcfg = $model->stdModel('datatree', $clq->get('table'), $clq->get('tabletype'));

        	// Generate row
        	$itms = "";
        	foreach($dtcfg['columns'] as $action => $col) {
        		$itms .= H::span(['class' => 'table-cell '.$col['class']], self::formatCell(['type' => $col['type']], $row, $action));
        	}

        	// Generate block of icons
        	$icons = "";
        	foreach($dtcfg['icons'] as $action => $icn) {
        		$icons .= H::i(['class' => 'treeicon fa fa-fw fa-'.$icn['icon'], 'data-toggle' => 'tooltip', 'title' => Q::cStr($icn['tooltip']), 'data-id' => $row['id'], 'data-action' => $action]);
        	}

        	// Style of the overall Button or Div
            $btn = [
            	'class' => 'btn e100',
            	'data-ref' => $row['c_reference'],
            	'data-parent' => $row['c_parent'],
            	'data-order' => $row['c_order'],
            	'id' => "tree_".$row['id']
            ];     
            // Format and contents of the text of the button
            $ele = H::div(['class' => ''],
            	$ele = H::div(['class' => 'left'], $itms),
            	$ele = H::div(['class' => 'right'], $icons)
            );
        	return H::li($btn, $ele);	
         }  

    	/** Tree formatted list - method 1 
    	 * First of four Class methods that create a plain but tree formatted list to be displayed by Clqtree
    	 * @param - array - recordset obtained from either the database or a development config file
    	 * @param - string - the level in the list. Starts with an empty string and then contains a Reference 
    	 * (c_reference) which maps to a parent (c_parent) in a child element
    	 * @param - array - pass the original $vars as we need table and tabletype to obtain the model later on
    	 * @return - string - returns an HTML list which is sent to the template
		    <li>Example 1</li>
		    <li>Example 2</li>
		    <li>Example 3
		        <ul>
		            <li>Example 1</li>
		            <li>Example 2
		                <ul>
		                    <li>Example 1</li>
		                    <li>Example 2</li>
		                </ul>
		            </li>
		            <li>Example 3</li>
		            <li>Example 4</li>
		        </ul>
		    </li>    	 
    	 **/
         protected static function getList($rs, $lvl, $vars) {
           	
        	$filteredarray = self::getFilteredArray($rs, $lvl);

        	if($filteredarray) {
	           	$list = "";
	           	foreach($filteredarray as $i => $row) {
	                if(self::getList($rs, $row['c_reference'], $vars)) {
	                    $list .= self::displayElement($row, $vars);
	                    $list .= '<ul>'.self::getList($rs, $row['c_reference'], $vars).'</ul>';
	                    $list .= '</li>';
	                } else {
	                    $list .= self::displayElement($row, $vars);
	                    $list .= '</li>';
	                }
	            }
	            return $list;
        	} else {
        		return false;
        	}
         }

    	/** Tree method 2
    	 * Second of the four methods 
    	 * As we are working with an an array (which is obtained once) we need to filter the array
    	 * by level. We look for any records in the array which have a c_parent equal to the c_reference
    	 * that has been passed. If an array is generated (and the c_order is used for the key) then
    	 * sort the array by key (now in order by c_order) and return. If no array generated, return false.
    	 * @param - array - original recordset
    	 * @param - string - reference to searched for
    	 * @return - mixed - array of records or false
    	 **/
         protected static function getFilteredArray($array, $parentref)
         {
        	$filteredarray = [];
        	foreach($array as $i => $row) {      		
        		if($row['c_parent'] === $parentref) {
        			$filteredarray[$row['c_order']] = $row; 
        		}
        	}
        	if(count($filteredarray) > 0) {
        		ksort($filteredarray);
	        	return $filteredarray;
        	} else {
        		return false;
        	}
         }

    	/** Tree method 3
    	 * Third of four methods
    	 * This method generates a row of table cell formatted entries within the unordered list <li>
    	 * The configuration for this datatree is recovered and the columns are iterated
    	 * each value in the row is sent for formatting
    	 * @param - array - row array
    	 * @param - array - original $vars, including table and tabletype
    	 * @return - string - a formatted element
    	 **/
         protected static function displayElement($row, $vars)
         {
        	$ele = '<li id="'.$row['id'].'"><span class="ml30">';

        	// Get model for this datatree
        	$dtfg = Q::cModel($vars['table'], $vars['tabletype'], 'plaintree');	

        	$ele .= '<span class="td-cell">'.$row['id'].'</span>';
        	foreach($dtfg['columns'] as $fld => $col) {
        		$ele .= '<span class="td-cell mr5 '.$col['class'].'">'.self::formatElement($row[$fld], $col, $vars).'</span>';
        	}
        	$ele .= '</span>';
        	return $ele;
         }

    	/** Tree method 4
    	 * Fourth of four methods
    	 * This method formats the cell content according to the column type
    	 * @param - string - raw element value
    	 * @param - array - column or field definition from configuration
    	 * @param - array - $vars, just in case we need to look something up
    	 * @return - string - formatted cell content
    	 **/
         protected static function formatElement($ele, $col, $vars)
         {   

        	switch($col['type']) {

        		case "list":
        			$fele = Q::cList($ele, $col['listtype']);
        		break;

        		case "idiomtext":
        			$fele = Q::cStr($ele);
        		break;

        		case "idiom":
        			$lcd = F::get('idiom');
        			$fele = $ele[$lcd];
        		break;

        		case "text":
        		default:
        			$fele = $ele;
        		break;
        	}
        	return $fele;
         } 

        /** Cell formatter
         * Formats cell contents for most display functions such as grid and table
         * @param - array - Attributes for Table or Grid column
         * @param - array - Recordset row
         * @param - string - Field identifier or field name
         * @return - string - HTML
         **/
         protected static function formatCell($prop, $row, $f) {

        	global $clq;
        	$table = $clq->get('table');
        	array_key_exists('recid', $clq->get('rq')) ? $recid = $clq->get('rq')['recid'] : $recid = 0;
        	return Q::formatCell($f, $row, $prop, $table, $recid);
         }

    /** Content Display and Editing Routines - 
     * mostly involving the display or editing of rich text data and TinyMCE
     *
     * displayContent()
     * editContent()
     * editCode()
     * getSiteMap()
     * setSiteMap() 
     *
     ****************************************************************************************************/

        /** Display Content for Popup 
         * View Content in a larger Popup
         * language texts will be displayed in separate Tabs
         * @param - array - array of variables
         * @return - String HTML 
         **/
         public static function displayContent($vars)
         {
	        try {

	        	global $clq;
			    $rq = $vars['rq'];
			    $recid = $rq['recid'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				$tabletype = $vars['tabletype'];

				$sql = ""; $rawrow = []; $row = []; $dcfg = [];		
							
				// Define displaytype config
				$comcfg = Q::cModel('common', $table, $tabletype);
				$tblcfg = Q::cModel($rq['displaytype'], $table, $tabletype);		
				$dcfg = array_replace_recursive($comcfg, $tblcfg);

	        	$sql = "SELECT * FROM ".$table." WHERE id = ?";
	        	$rawrow = R::getRow($sql, [$recid]);
	        	$row = self::extractAndMergeRow($rawrow);

	        	// Multiple language records
	        	$idioms = $clq->get('idioms');
	        	$tablist = ""; $tabcontent = "";
	        	foreach($idioms as $lcdcode => $lcdname) {
	        		// $lcdcode == $idiom ? $exp = ['expanded' => 'true'] : $exp = [] ;
	        		$tablist .= H::li(['class' => 'nav-item'],
	        			H::a(['class' => 'nav-link', 'id' => $lcdcode.'-tab', 'data-toggle' => 'tab', 'href' => '#'.$lcdcode.'-content', 'role' => 'tab', 'aria-controls' => $lcdname], $lcdname)
	        		);
	        		$tabcontent .= H::div(['class' => 'tab-pane fade minh29', 'id' => $lcdcode.'-content', 'role' => 'tabpanel', 'aria-labelledby' => $lcdname], $row['d_text'][$lcdcode]);
	        	};

	        	// Header Record
        		$tablist .= H::li(['class' => 'nav-item'],
        			H::a(['class' => 'nav-link active', 'id' => 'header-tab', 'data-toggle' => 'tab', 'href' => '#header-content', 'role' => 'tab', 'aria-controls' => 'header'], Q::cStr('182:Header'))
        		);
        		// Change the Header layout later on to accomdate other types of Headers as specified in the Model
        		$tabcontent .= H::div(['class' => 'tab-pane fade minh29', 'id' => 'header-content', 'role' => 'tabpanel', 'aria-labelledby' => 'header'], 
        			H::h5([], 'Id: '.$row['id'].' - '.Q::cStr('5:Reference').': '.$row['c_reference']),
        			H::p([], Q::cStr('6:Common').': '.$row['c_common']),
        			H::p([], Q::cStr('8:Notes').': '.$row['c_notes'])
        		);


	        	$html = H::div(['class' => 'pad col mr10'],
					H::ul(['class' => 'nav nav-tabs', 'id' => 'contenttabs', 'role' => 'tablist'], $tablist), 
	        		H::div(['class' => 'tab-content', 'id' => 'tabbedcontent'], $tabcontent)
	        	);

	        	/* Dropdown to be implement for Header
				  <li class="nav-item dropdown">
				    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
				      Dropdown
				    </a>
				    <div class="dropdown-menu">
				      <a class="dropdown-item" id="dropdown1-tab" href="#dropdown1" role="tab" data-toggle="tab" aria-controls="dropdown1">@fat</a>
				      <a class="dropdown-item" id="dropdown2-tab" href="#dropdown2" role="tab" data-toggle="tab" aria-controls="dropdown2">@mdo</a>
				    </div>
				  </li>
				 */
				return [
					'flag' => "Ok",
					'html' => $html
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'html' => $e->getMessage() 
				]; 
			}	
         }

        /** Content editor in popup 
         * Edit Content in a larger Popup
         * language texts will be displayed in separate Tabs
         * @param - array - array of variables
         * @return - String HTML 
         **/
         public static function editContent($vars)
         {
	        try {

	        	global $clq;
			    $rq = $vars['rq'];
			    $recid = $rq['recid'];
			    $fld = $rq['fldname'];
				$idiom = $vars['idiom'];
				$table = $vars['table'];
				// $tabletype = $vars['tabletype'];

				$sql = ""; $rawrow = []; $row = []; $dcfg = [];	$tmp = [];
							
				// Define displaytype config
				// $comcfg = Q::cModel('common', $table, $tabletype);
				// $tblcfg = Q::cModel($rq['displaytype'], $table, $tabletype);		
				// $dcfg = array_replace_recursive($comcfg, $tblcfg);

	        	$sql = "SELECT * FROM ".$table." WHERE id = ?";
	        	$rawrow = R::getRow($sql, [$recid]);
	        	$row = self::extractAndMergeRow($rawrow);

	        	// Multiple language records
	        	$idioms = $clq->get('idioms');
	        	$tablist = ""; $tabcontent = "";
	        	foreach($idioms as $lcdcode => $lcdname) {
	        		// $lcdcode == $idiom ? $exp = ['expanded' => 'true'] : $exp = [] ;
	        		$tablist .= H::li(['class' => 'nav-item'],
	        			H::a(['class' => 'nav-link', 'id' => $lcdcode.'-tab', 'data-toggle' => 'tab', 'href' => '#'.$lcdcode.'-content', 'role' => 'tab', 'aria-controls' => $lcdname], $lcdname)
	        		);
	        		// A ==> b ==> c OK
	        		if( array_key_exists($fld, $row) and is_array($row[$fld]) and isset($row[$fld][$lcdcode]) ) {
						$c = $row[$fld][$lcdcode];
	        		} else {
						$tmp[$lcdcode] = Q::cStr('526:Default');
	        			$c = $tmp[$lcdcode];
	        		};
	        		$tabcontent .= H::div(['class' => 'tab-pane fade tiny', 'id' => $lcdcode.'-content', 'role' => 'tabpanel', 'aria-labelledby' => $lcdname], H::textarea(['class' => 'rte', 'id' => $fld.'_'.$lcdcode, 'data-id' => $fld.'_'.$lcdcode, 'name' => $fld.'_'.$lcdcode], $c)        			
	        		);
	        	};

	        	$html = H::div(['class' => 'pad col mr10'],
					H::ul(['class' => 'nav nav-tabs', 'id' => 'contenttabs', 'role' => 'tablist'], $tablist), 
	        		H::form(['action' => '#', 'method' => 'POST', 'name' => 'dataform', 'id' => 'dataform'],
	        			H::div(['class' => 'tab-content pad0 m0', 'id' => 'tabbedcontent'], $tabcontent)
	        		)
	        	);

        		$js = "
        			Cliq.set('fldname', 'd_text');
        		";

	        	$clq->set('js', $js);
	        	$opts = [
					'fldname' => 'd_text',
					'bingkey' => $clq->get('cfg')['site']['bingkey'],
					'idioms' => $clq->get('cfg')['site']['idioms'],
	        	];
				return [
					'flag' => "Ok",
					'html' => $html,
					'data' => $opts
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'html' => $e->getMessage() 
				]; 
			}	
         }

        /** Code editor in popup 
         * Edit Code in a larger Popup
         * @param - array - array of variables
         * @return - String HTML 
         **/
         public static function editCode($vars)
         {
	        try {

	        	global $clq;
			    $rq = $vars['rq'];
			    $recid = $rq['recid'];
				$table = $vars['table'];
				$fld = $rq['fldname'];
				$sql = ""; $rawrow = []; $row = [];	

	        	$sql = "SELECT * FROM ".$table." WHERE id = ?";
	        	$rawrow = R::getRow($sql, [$recid]);
	        	$row = self::extractAndMergeRow($rawrow);
				$val = $row[$fld];
                // $val = preg_replace("/\t/", " ", $val); // tabs with spaces
                // $val = preg_replace("/\s+/", " ", $val); // Multiple spaces with single space
                $val = preg_replace("/\r\n/", "\n", $val); // Carriage return and newline (not respected by CodeEditor display) with just Newline
	        	$content = H::textarea(['class' => 'codeeditor', 'id' => $fld, 'data-id' => $fld, 'name' => $fld], $val);        	

	        	$js = "
        			Cliq.set('fldname', '".$fld."');
        		";
        		$clq->set('js', $js);
				return [
					'flag' => "Ok",
					'html' => $content,
					'data' => []
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'html' => $e->getMessage() 
				]; 
			}	
         }    

        /** Get Site Map 
         * Creates a visual and heirachical sitemap using the CSS stylesheet slickmap.css
         * @param - array - usual set of arguments
         * @return - array - data for the display routine
		 **/
         public function getSiteMap($vars)
         {

 	        try {

	        	global $clq;
	        	$cfg = $clq->get('cfg');
	        	$ref = "sitemap";
	        	$sql = "SELECT * FROM dbitem WHERE c_type = ? AND c_reference = ?";
	        	$row = R::getRow($sql, ['config', $ref]);
	        	$result = self::extractAndMergeRow($row);

	        	if(count($result) > 0) {
	        		// Read the record which is stored in TOML format
	        		$tomlmap = $result['d_text'];
	        		// Convert to an array
	        		$array = C::cfgReadString($tomlmap);
	        		// Check if array
	        		if(!is_array($array)) {
	        			throw new Exception('Convert $map from TOML to array did not produce an Array : '.$map);
	        		};
	        		// Convert Array to Unordered List - needs PlainTree
	        		$mnu = $clq->resolve('Menu');
	        		$map = $mnu->publishList($array);

	        	} else {
	        		$map = H::ul(['class' => 'sitemapnav col4'],
	        			H::li(['id' => 'home'], 
	        				H::a(['href' => $cfg['site']['website']], Q::cStr('374:Home'))
	        			)
	        		);
	        		$tomlmap = "
						[home] 
							label = '11:Dashboard'
						    title = '115:Principal page of Administration System'	
							page = 'admindesktop'
						    type = 'main'
						    table = ''
						    tabletype = ''
						    params = ''    
						    icon = 'dashboard'
						    css = ''
						    submenu = ''
	        		";
	        	};

	        	// $tomlmap = preg_replace("/\t/", " ", $tomlmap); // tabs with spaces
	        	// $tomlmap = preg_replace("/\s+/", " ", $tomlmap); // Multiple spaces with single space
				$tomlmap = preg_replace("/\r\n/", "\n", $tomlmap); // Carriage return and newline (not respected by CodeEditor display) with just Newline

				return [
					'flag' => "Ok",
					'data' => $tomlmap,
					'html' => $map
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'html' => $e->getMessage() 
				]; 
			}	
         }

        /** Set Site Map 
         * Generates a new Sitemap Record
         * @param - array - usual set of arguments
         * @return - array - data for the display routine
		 **/       
         public function setSiteMap($vars)
         {
  	        try {

	        	global $clq;
	        	$sqla = "SELECT id, c_document FROM ".$vars['table']." WHERE c_type = ? AND c_reference = ?";
	        	$row = R::getRow($sqla, [$vars['tabletype'], 'sitemap']);
	        	$recid = $row['id'];
	        	if($recid > 0) {
	        		
	        		$doc = $row['c_document'];
		        	$docarray = json_decode($doc, true);
		        	$updb = R::load($vars['table'], $recid);
		        	$docarray['d_text'] = $vars['rq']['d_text'];
		        	$updb->c_document = json_encode($docarray);
		        	$updb->c_lastmodified = Q::lastMod();
		        	$updb->c_whomodified = Q::whoMod();
		        	$result = R::store($updb);

	        	} else {

	        		// New record
	        		$indb = R::dispense($vars['table']);
	        		$indb->c_reference = "sitemap";
	        		$indb->c_type = "config";
	        		$indb->c_notes = "Generated by SiteMap routine";
	        		$indb->c_version = 0;
	        		$indb->c_level = "60:70:90";
	        		$indb->c_category = "frontend";
	        		$indb->c_parent = 0;
	        		$indb->c_status = "active";
	        		$indb->c_common = "Website Sitemap in TOML format";
	        		$indb->c_order = "zz";
	        		$indb->c_lastmodified = Q::lastMod();
	        		$indb->c_whomodified = Q::whoMod();
	        		$docarray = [];
	        		$docarray['d_text'] = $vars['rq']['d_text'];
	        		$indb->c_document = json_encode($docarray);
	        		$result = R::store($indb);
	        	}

        		if(!is_numeric($result)) {
        			throw new Exception('Database update procedure did not produce stored result : '.$result);
        		};	

				return [
					'flag' => "Ok",
					'html' => Q::cStr('370:Record updated successfully: '.$result)
				];

			} catch (Exception $e) {
				return [
					'flag' => "NotOk",
					'html' => $e->getMessage() 
				]; 
			}		        	       	
         }

	/** Utilities
	 * extractAndMergeRow()
	 * extractAndMergeRecordset()
	 * findRecords() - review
	 * findStrings() - review
	 * getNextRef()
	 * getNextEntry() - review
	 * getNextId() - index table
	 * getNextNumber() - review
	 * isUnique()
	 * getAutoCompleteData()
	 * getValueFromLabel()
	 *
	 ****************************************************************************************************************/

        /** Extract and merge a Row 
         * Extracts the document component from a table row and creates a new row 
         * @param - array - Table row as an array
         * @param - string - the field that contains the document content. Defaults to '_document'
         * @return - array - the merged and modified row
         **/
         public static function extractAndMergeRow($row, $fld = 'c_document')
         {
            $newrow = [];
            
            foreach($row as $key => $val) {
                if($key != $fld) {
                    $newrow[$key] = $val;
                } else {
                    // maybe use basic function - is_json($val)
                    $content = F::jsonDecode($val);
                    if(is_array($content)) {
                        foreach($content as $ckey => $cval) {
                            $newrow[$ckey] = $cval;
                        }                        
                    } else {
                        $newrow[$fld] = [$content];
                    }
                }
            }
            return $newrow;
         }

        /** Extract and merge a Recordset (uses row routine)
         * Extracts the document component from the rows of a recordset and creates a new recordset 
         * @param - array - Recordset
         * @param - string - the field that contains the document content. Defaults to '_document'
         * @return - array - the merged and modified recordset
         **/
         public static function extractAndMergeRecordset($rs, $fld = 'c_document')
         {
            $newrs = [];
            for($q = 0; $q < count($rs); $q++) {
                $newrs[] = self::extractAndMergeRow($rs[$q], $fld);
            }
            return $newrs;
         }

        /** Generic FindRecords function 
         * to be completed
         *
         * @param - array - $Vars contains all variables
         * @return - array - recordset
         **/
         public static function findRecords($vars)
         {

         	$method = self::THISCLASS.'->'.__FUNCTION__."()";
            try {
                
                // Set values for clarity and comprehension
                global $clq;
                $table = $vars['table'];
                $tabletype = $vars['tabletype'];
                $querystr = $vars['rq']['query'];
                $operator = $vars['rq']['operator'];
                $lcd = $clq->get('idiom');
                $method = 'findRecords()';

                // Create database query to get database records if they exist            
                $sql =  "SELECT * FROM ".$table." WHERE c_type = ?";
                $rawset = R::getAll($sql, [$tabletype]);

                // If database query does not return a result
                if(is_array($rawset) && !empty($rawset)) {
                    $allset = self::extractAndMergeRecordset($rawset, 'c_document'); 
                    $rs = [];
                    for($q = 0; $q < count($allset); $q++) {                     
                        $rs[$key] = $allset[$q]['text'][$lcd];
                    }                   
                } else {
                    
                    // Define from Model

                    $fn = $table.'-'.$tabletype.'-'.$lcd.'.lcd';
                    $rs = C::cfgReadFile('includes/i18n/'.$fn); 
              
                } 

                if(!is_array($rs)) {
                    throw new Exception("Result is not an array as required!");
                }
      
                // Test
                $test = [
                    'method' => $method,
                    'variables' => $vars
                ];

                // Set to comment when completed
                // L::cLog($test);  
                
                // If not returned already 
                return $rs;                

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'variables' => $vars,
                    'lcd' => $lcd
                ];
                L::cLog($err);
                return false;
            }
         }

        /** Generic find strings function
         * Find strings
         * @param - array - $Vars contains all variables
         * @return - array - recordset
         **/
         public static function findStrings($vars)
         {

        	$method = self::THISCLASS.'->'.__FUNCTION__."()";
            try {
                
                // Set values for clarity and comprehension
                $table = $vars['table'];
                $tabletype = $vars['tabletype'];
                $querystr = $vars['rq']['query'];
                $operator = $vars['rq']['operator'];
                $lcd = F::get('idiom');
                $method = 'findRecords()';

                // Create database query to get database records if they exist            
                $sql =  "SELECT * FROM ".$table." WHERE c_type = ?";
                $rawset = R::getAll($sql, [$tabletype]);

                // If database query does not return a result
                if(is_array($rawset) && !empty($rawset)) {

                    $allset = self::extractAndMergeRecordset($rawset, 'c_document'); 

                    $rs = [];
                    for($q = 0; $q < count($allset); $q++) {

                        if(stristr($allset[$q]['_reference'], '(') !== false) {
                            // We need to strip out just the number
                            $key = filter_var($ref, FILTER_SANITIZE_NUMBER_INT);
                        }                        

                        $rs[$key] = $allset[$q]['text'][$lcd];
                    }                   

                } else {
                    
                    $fn = $table.'-'.$tabletype.'-'.$lcd.'.lcd';
                    $rs = C::cfgReadFile('includes/i18n/'.$fn); 
              
                } 

                if(!is_array($rs)) {
                    throw new Exception("Result is not an array as required!");
                }

                // $rs[99] = 'string' 

                $result = [];

                foreach($rs as $num => $val) {

                    switch($operator) {

                        case "equals":
                            $val == $querystr ? $result[] = ['id' => $num.':'.$val, 'label' => $num.':'.$val]: null ;
                        break;

                        case "contains":
                        default:
                            stristr($val, $querystr) !== false ? $result[] = ['id' => $num.':'.$val, 'label' => $num.':'.$val]: null ;
                        break;
                    }                    
                }
         
                // Test
                $test = [
                    'method' => self::THISCLASS.'->'.$method,
                    'variables' => $vars
                ];

                // Set to comment when completed
                // L::cLog($test);  
                
                // If not returned already 
                return $result;                

            } catch (Exception $e) {
                $err = [
                    'method' => self::THISCLASS.'->'.$method,
                    'errmsg' => $e->getMessage(),
                    'variables' => $vars,
                    'lcd' => $lcd
                ];
                L::cLog($err);
                return false;
            }
         }

        /** Get next reference 
         * In Form configueration, PHP to respond to CSS class of next ref. Needs table and tabletype
         * @param - array - 
         * @return - array - Ok flag and data or error
         **/
         public static function getNextRef($vars)
         {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {	

				global $clq;		
				$sqla = "SELECT id, c_value FROM dbindex WHERE c_reference = ? AND c_category = ?";
				$row = R::getRow($sqla, [$vars['tabletype'], $vars['table']]);
				$lastref = $row['c_value'];
				$recid = $row['id'];

				// If this was the first time this Reference had ever been needed, then a dbindex record would not exist
				if(!$lastref) {
					$nextref = $vars['rq']['currval'];
					$updb = R::dispense('dbindex');
					$updb->c_reference = $vars['tabletype'];
					$updb->c_category = $vars['table'];
					$updb->c_value = $nextref;
					$updb->c_lastmodified = Q::lastMod();
					$updb->c_whomodified = Q::whoMod();
					$result = R::store($updb);					
				} else {
					// If a Lastref record exists, we need to know if it actually is in use.
					// If it is in use, then increment, otherwise use Lastref as Nextref

					$sqlb = "SELECT c_reference FROM ".$vars['table']." WHERE c_type = ? and c_reference LIKE ? LIMIT 1";
					$ref = R::getCell($sqlb, [$vars['tabletype'], '%'.$lastref.'%']);
					if($ref != '') {
						$a = explode("(", $lastref);
						$lastnum = filter_var($lastref, FILTER_SANITIZE_NUMBER_INT);            
						$nextnum = (int)$lastnum + 1;
						$nextref = $a[0].'('.$nextnum.')'; 
						$updb = R::load('dbindex', $recid);
						$updb->c_reference = $vars['tabletype'];
						$updb->c_category = $vars['table'];
						$updb->c_value = $nextref;
						$updb->c_lastmodified = Q::lastMod();
						$updb->c_whomodified = Q::whoMod();
						$result = R::store($updb);							
					} else {
						$nextref = $lastref;
					}
	
				}
				
				$result = (trim(str_replace(",", "", $nextref)));  				
				
				// Test
				$test = [
					'method' => $method,
					'result' => $result
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 
				return ['flag' => 'Ok', 'data' => $result];                

			} catch (Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage()
				];
				L::cLog($err);
				return ['flag' => 'NotOk', '' => $e->getMessage()];
			}
         }

		/** Get next entry 
		 * Get the next transaction reference - same as getNextRef but formats the response with parentheses etc.
		 * @param - array - usual $vars - table, tabletype, prefix
		 * @return - string
		 * */
		 static function getNextEntry($vars) 
		 {
			$method = self::THISCLASS.'->'.__FUNCTION__.'()';
			try {	

				global $clq;		
				$sqla = "SELECT id, c_value FROM dbindex WHERE c_reference = ? AND c_category = ?";
				$row = R::getRow($sqla, [$vars['ref'], $vars['cat']]);
				$lastref = $row['c_value']; $recid = $row['id'];

				// If this was the first time this Reference had ever been needed, then a dbindex record would not exist
				if(!$lastref) {
					$nextref = $vars['default'];
					$updb = R::dispense('dbindex');				
				} else {
					$lastnum = filter_var($lastref, FILTER_SANITIZE_NUMBER_INT);            
					$nextnum = (int)$lastnum + 1;
					$nextref = $nextnum; 
					$updb = R::load('dbindex', $recid);
				}		
				
				$updb->c_reference = $vars['ref'];
				$updb->c_category = $vars['cat'];
				$updb->c_value = $nextref;
				$updb->c_lastmodified = Q::lastMod();
				$updb->c_whomodified = Q::whoMod();
				$result = R::store($updb);	
				
				// If not returned already 
				return $nextref;                

			} catch (Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage()
				];
				L::cLog($err);
				return ['flag' => 'NotOk', '' => $e->getMessage()];
			}
		 }      

        /** Get Next ID 
         * Get Next ID using Index Table used by Ajax - This not an "id" but something like "coid" or "cid"
         * @param - array - array of necessary variables
         * @return - array - JSON format
         **/
         public static function getNextId($vars)
         {
			$method = "getNextId()";
			try {					
				
				$id = Q::getNextIndex($vars['rq']['fld']);      
				if(!$id) {             
					$id = $vars['rq']['currval'];         
				};  
			
				// Test
				$test = [
					'method' => self::THISCLASS.'->'.$method,
					'result' => $id
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 
				return ['flag' => 'Ok', 'data' => $id];                

			} catch (Exception $e) {
				$err = [
					'method' => self::THISCLASS.'->'.$method,
					'errmsg' => $e->getMessage()
				];
				L::cLog($err);
				return ['flag' => 'NotOk', '' => $e->getMessage()];
			}      
         }

        /** Get next number 
         * Field contains a number and this increments it by one
         * @param - array - 
         * @return - array - Ok flag and data or error
         **/
         public static function getNextNumber($fld, $tbl, $recid)
         {
			try {			
				
				$method = self::THISCLASS.'->'."getNextNumber()";	
				$sql = "SELECT ".$fld." FROM ".$tbl." WHERE id = ?";
				$existing = R::getcell($sql, [$recid]);
				$lastnum = filter_var($existing, FILTER_SANITIZE_NUMBER_INT);            
				$nextnum = (int)$lastnum + 1;			
             	return $nextnum;

			} catch (Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage()
				];
				L::cLog($err);
				return false;
			}
         }

        /** Is unique 
         * Method responds to Form configuration Css Class of "isunique". Is proposesd entry unique? False = nothing, True = a result plus flag
         * @param - array - usual args
         * @return - array - Ok flag and data or error
         **/
         public static function isUnique($vars)
         {
			$method = self::THISCLASS.'->'."isUnique()";
			try {	
				
				$sql = "SELECT ".$vars['rq']['fld']." FROM ".$vars['table']." WHERE c_type = ? AND ".$vars['rq']['fld']." LIKE ?";
				$result = R::getcell($sql, [$vars['tabletype'], $vars['rq']['currval']]);
				if($result != "") {
					$flag = "NotOK";
				} else {
					$flag = "Ok";
				}
				
				// Test
				$test = [
					'method' => $method,
					'result' => $result,
					'flag' => $flag
				];

				// Set to comment when completed
				// L::cLog($test);  
				
				// If not returned already 
				return ['flag' => $flag, 'data' => $result];                

			} catch (Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage()
				];
				L::cLog($err);
				return ['flag' => 'NotOk', '' => $e->getMessage()];
			} 				
		 }

        /** Auto complete data 
         * Get data for an autocomplete function
         *
         * @param - array - array of variables
         * @return - array - in format [['id' => 'x1', 'label' => 'y1'],['id' => 'x2', 'label' => 'y2']]
         **/
         public static function getAutoCompleteData($vars)
         {
			
        	$opts = $vars['rq']['options'];
        	$query = $vars['rq']['query'];
        	$operator = $vars['rq']['operator'];

        	// Local or Remote d others
        	$type = $opts['type'];
        	
        	switch($type) {

        		case "remote":
					$url = $opts['url'];
        		break;

        		case "local":
        		default:
        			
        			$opts['where'] = str_replace('[operator]', $operator, $opts['where']);
	        		$sql = "SELECT ".$opts['flds']." FROM ".$opts['table']." WHERE ".$opts['where'];
	        		$params = explode('|', str_replace('[query]', $query, $opts['params']));
	        		$rset = R::getAll($sql, $params);
	        		// L::log($rset);
	        		return $rset;

        		break;
        	}
        	return false;
         }

        /** Get Collection 
         * Get all records for a given tabletype or collection
         * @param - string - name of table
         * @param - string - (optional) name of tabletype
         * @return - array - Ok flag and data or error
         **/
    	 public static function getCollection($tablename, $type = '')
    	 {

    		$method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {
                // Set values for clarity and comprehension
                global $clq;
                $idiom = $clq->get('idiom');
    		    
    		    // Create database query to get database records if they exist 
    		    if($type != '') {
	                $sql =  "SELECT * FROM ".$tablename." WHERE c_type = ?";
	                $rawset = R::getAll($sql, [$type]);    		    	
    		    } else {
	                $sql =  "SELECT * FROM ".$tablename;
	                $rawset = R::getAll($sql);   		    	
    		    }       

 				// If database query does not return a result
                if(is_array($rawset) && !empty($rawset)) {
                    $allset = self::extractAndMergeRecordset($rawset, 'c_document'); 
                } else {
                	throw new Exception("Result is not an array as required!");
                }

                // But allset contains language arrays
                $rs = [];
                for($r = 0; $r < count($allset); $r++) {
	                $row = [];
	                foreach($allset[$r] as $fld => $val) {
	                	if(is_array($val)) {
	                		$row[$fld] = $val[$idiom];
	                	} else {
	                		$row[$fld] = $val;
	                	}
	                }
	                $rs[] = $row; unset($row);
                }
                return $rs;                

            } catch (Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                ];
                L::cLog($err);
                return false;
            }        
    	 }

    	/** Get value from label
    	 * Some of the datatypes are lists and radios, we need the base selector, not the translation
    	 * @param - string - name of the list
    	 * @param - string - label to reference
    	 * @param - string(optional) - table name in which to search
    	 * @return - string - base value
    	 **/
    	 public static function getValueFromLabel($listname, $searchstr, $table = 'dbcollection')
    	 {
    		global $clq; $idm = $clq->get('idiom');
    		$sql = "SELECT c_document FROM ".$table." WHERE c_type = ? AND c_reference = ? LIMIT 1";
    		$doc = R::getCell($sql, ['list', $listname]);
    		$ar = json_decode($doc, true);
    		foreach($ar['d_text'] as $base => $lbls) {
    			if($lbls[$idm] == $searchstr) {
    				return $base;
    			}
    		};
    		return "NotFound";
    	 }



   	/** SQL Import
   	 *
   	 * pdoConn()
   	 * sqlImport()
   	 * clearSql
   	 * isQuoted()
   	 * query
   	 *
   	 ***************************************************************************************************/

   		/** Generate an ordinary PDO Connection
   		 * reads database configuration values from Configuration
   		 *
   		 * @return - Object - PDO Connection
   		 **/
	    public static function pdoConn($dbcfg) 
	    {
	        try {
		        global $clq;
		        if($dbcfg['type'] != 'sqlite') {
					$dbconn = new PDO($dbcfg['type'].':host='.$dbcfg['server'].'; dbname='.$dbcfg['dbname'], $dbcfg['username'],$dbcfg['password']);
		        } else {
					$dbconn = new PDO('sqlite:'.$clq->get('basedir').'data/'.$dbcfg['dbname'].'.sqlite');			
		        }
				$dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		        return $dbconn;
		    } catch (PDOException $e) {
		        die("DB ERROR: ". $e->getMessage());
		    }		    
	    }

	    /** Import SQL from file
	     *
	     * @param string path to sql file
	     */
	     public static function sqlImport($filename, $dbcfg)
	     {
	        
	        global $clq;
	        $f = $clq->resolve('Files');
	        $delimiter = ';';
	        $file = Y::openFile($filename, 'r');
	        if(!$file){
	            throw new Exception("Error: Cannot open file {$filename}\n");
	        }
	        $isFirstRow = true;
	        $isMultiLineComment = false;
	        $sql = '';
	 
	        while (!feof($file)) {
	 
	            $row = fgets($file);
	 
	            // remove BOM for utf-8 encoded file
	            if ($isFirstRow) {
	                $row = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $row);
	                $isFirstRow = false;
	            }
	 
	            // 1. ignore empty string and comment row
	            if (trim($row) == '' || preg_match('/^\s*(#|--\s)/sUi', $row)) {
	                continue;
	            }
	 
	            // 2. clear comments
	            $row = trim(self::clearSQL($row, $isMultiLineComment));
	 
	            // 3. parse delimiter row
	            if (preg_match('/^DELIMITER\s+[^ ]+/sUi', $row)) {
	                $delimiter = preg_replace('/^DELIMITER\s+([^ ]+)$/sUi', '$1', $row);
	                continue;
	            }
	 
	            // 4. separate sql queries by delimiter
	            $offset = 0;
	            while (strpos($row, $delimiter, $offset) !== false) {
	                $delimiterOffset = strpos($row, $delimiter, $offset);
	                if (self::isQuoted($delimiterOffset, $row)) {
	                    $offset = $delimiterOffset + strlen($delimiter);
	                } else {
	                    $sql = trim($sql . ' ' . trim(substr($row, 0, $delimiterOffset)));
	                    self::sqlQuery($sql, $dbcfg);
	 
	                    $row = substr($row, $delimiterOffset + strlen($delimiter));
	                    $offset = 0;
	                    $sql = '';
	                }
	            }
	            $sql = trim($sql . ' ' . $row);
	        }
	         
	        if (strlen($sql) > 0) {
	            self::sqlQuery($row, $dbcfg);
	        }
	 
	        fclose($file);
	     }

	    /** Remove comments from sql
	     *
	     * @param string sql
	     * @param boolean is multicomment line
	     * @return string
	     */
	     protected static function clearSQL($sql, &$isMultiComment) 
	     {
	         
	        if ($isMultiComment) {
	            if (preg_match('#\*/#sUi', $sql)) {
	                $sql = preg_replace('#^.*\*/\s*#sUi', '', $sql);
	                $isMultiComment = false;
	            } else {
	                $sql = '';
	            }
	            if(trim($sql) == ''){
	                return $sql;
	            }
	        }
	 
	        $offset = 0;
	        while (preg_match('{--\s|#|/\*[^!]}sUi', $sql, $matched, PREG_OFFSET_CAPTURE, $offset)) {
	            list($comment, $foundOn) = $matched[0];
	            if (self::isQuoted($foundOn, $sql)) {
	                $offset = $foundOn + strlen($comment);
	            } else {
	                if (substr($comment, 0, 2) == '/*') {
	                    $closedOn = strpos($sql, '*/', $foundOn);
	                    if ($closedOn !== false) {
	                        $sql = substr($sql, 0, $foundOn) . substr($sql, $closedOn + 2);
	                    } else {
	                        $sql = substr($sql, 0, $foundOn);
	                        $isMultiComment = true;
	                    }
	                } else {
	                    $sql = substr($sql, 0, $foundOn);
	                    break;
	                }
	            }
	        }
	        return $sql;
	     }
	     
	    /** Check if "offset" position is quoted
	     *
	     * @param int $offset
	     * @param string $text
	     * @return boolean
	     */
	     protected static function isQuoted($offset, $text) 
	     {
	         
	        if ($offset > strlen($text)){
	            $offset = strlen($text);
	        }
	         
	        $isQuoted = false;
	        for ($i = 0; $i < $offset; $i++) {
	            if ($text[$i] == "'"){
	                $isQuoted = !$isQuoted;
	            }
	            if ($text[$i] == "\\" && $isQuoted){
	                $i++;
	            }
	        }
	        return $isQuoted;
	     }
	    
	    /** Does the actual Database work
	     *
	     * @param - string - the SQL query
	     * @return - string - 
	     **/
	     protected static function sqlQuery($sql, $dbcfg) 
	     {
	        $dbconn = self::pdoConn($dbcfg);
            $dbconn->exec($sql); 
	     }

} // Ends Class

# alias +d+ class
if(!class_exists("D")){ class_alias('Db', 'D'); };
