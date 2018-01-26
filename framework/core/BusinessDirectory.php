<?php
/**
 * Cliqon Directory Class
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@webcliq.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class BusinessDirectory 
{
	const THISCLASS = "BusinessDirectory";
    public function __construct() {
    	global $clq;
    }

    function getCompanies($vars)
    {

	    $method = self::THISCLASS.'->'.__FUNCTION__."()";
	    try {

	    	$sql = "SELECT c_common, c_reference FROM dbdirectory ORDER BY c_common ASC";
	    	$rs = R::getAll($sql); $opts = "";
	    	for($r = 0; $r < count($rs); $r++) {
	    		$opts .= H::option(['value' => $rs[$r]['c_reference']], $rs[$r]['c_common']);
	    	};
	    	$html = H::form(
	    		H::input(['class' => 'form-control', 'name' => 'lookup']),
	    		H::select(['class' => 'form-control', 'name' => 'companies'], $opts)
	    	);
	    	return ['flag'=> 'Ok', 'data' => $html];

		} catch (Exception $e) {
			$err = [
				'method' => $method,
				'errmsg' => $e->getMessage(),
			];
			L::cLog($err);
			return ['flag'=> 'NotOk', 'data' => $e->getMessage()];
		}	    	

    }

    function listCompanies($vars)
    {

	    $method = self::THISCLASS.'->'.__FUNCTION__."()";
	    try {

	    	$sql = "SELECT c_common, c_reference FROM dbdirectory WHERE c_common LIKE ? ORDER BY c_common ASC";
	    	$rs = R::getAll($sql, ['%'.$vars['rq']['query'].'%']); 
	    	$opts = [];
	    	for($r = 0; $r < count($rs); $r++) {
	    		$opts[] = ['id' => $rs[$r]['c_reference'], 'label' => $rs[$r]['c_common']];
	    	};
	    	return $opts;

		} catch (Exception $e) {
			$err = [
				'method' => $method,
				'errmsg' => $e->getMessage(),
			];
			L::cLog($err);
			return [$e->getMessage()];
		}	    	

    }

} // Directory Class ends