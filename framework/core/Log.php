<?php
/**
 * Cliqon Log Class and Methods
 * extends Tracy Logging with Helpers
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Conkas cb
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class Log
{
    public $tblname = 'dblog';
    const THISCLASS = "Log";

	function __construct() 
	{
        // Make table if not exists
        $tablename = "dblog";
	}

	/**
	 * Write record to Log File
	 *
	 * @param - String - The text of the Log record
	 * @param - String - category from list of categories
	 * @return - boolean - true/false
	 */
	public static function wLog(array $text, $cat = 'other', $notes = 'No extra notes', $type = '')
	{
		$db = R::dispense('dblog');
		$db->c_reference = uniqid('log_');
		$db->c_type = $type;		
		$db->c_category = $cat;
		$db->c_revision = 0;
		$db->c_document = json_encode($text, JSON_PRETTY_PRINT);
		$db->c_lastmodified = Q::lastMod();
		$db->c_whomodified = Q::whoMod();
		$db->c_notes = $notes;
		$result = R::store($db);
	}

	/**
	 * Read record from Log File
	 *
	 * @param - String - Category
	 * @param - String - Reference
	 * @return - Object - Row
	 */
	public static function rLog($cat, $ref)
	{
		$sql = "SELECT * FROM dblog WHERE c_category = ? AND c_reference = ?";
		return R::getRow($sql, [$cat, $ref]);
	}

	/**
	 * Read records from Log File
	 *
	 * @param - String -
	 * @param - String -
	 * @return - boolean - true/false
	 */
	public function readLogData($cat = null, $who = null, $between = null)
	{
		$sql = "SELECT * FROM dblog";

		$wh = ""; $array = [];

		// Category
		if($cat != null) {
			$wh .= " c_category = ? AND ";
			$array[] = $cat;
		};

		// Who
		if($who != null) {
			$wh .= " c_whomodified = ? AND ";
			$array[] = $who;
		};

		// Between
		if($between != null) {
			$b = explode('|', $between);
			$wh .= " (c_lastmodified between :start AND :end) AND";
			$array['start'] = $b[0];
			$array['end'] = $b[1];
		};

		if($wh != '') {
			$wh = trim($wh, " AND ");	
			$wh = " WHERE ".$wh;
			$sql .= $wh;
			return R::getAll($sql, $array);									
		} else {
			return R::getAll($sql);
		}
	}

	public static function cLog($msg)
	{
		global $clq;
		if($clq->get('cfg')['site']['debug'] == 'development') {
			T::fireLog($msg);
			T::log($msg);		
		}
		return true;
	}

	public static function routeLog($route, $rq)
	{
		return self::wLog($route, 'routing', $rq);
	}

} // Class Ends

# alias +l+ class
if(!class_exists("L")){ class_alias('Log', 'L'); };
