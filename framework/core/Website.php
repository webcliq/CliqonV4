<?php
/**
 * Website adds website specific methods to the Cliq Class
 * in addition its is most likely that the Website Class will be the endpoint for the CMS Controller
 * Website.Php will not be included in the general Site update but must exist, even if not used
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <support@cliqon.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Website extends Cliq
{

	const THISCLASS = "Website extends Cliq";
    const CLIQDOC = "c_document";
	public $tblname = 'dbitem';

    function __construct() 
    {
        global $clq;
    }

    /** WebSite public Functions
     *
     *
     *
     *****************************************************  Website  **************************************************/

        /**
         * Shortcut to Section
         * @param - string - Reference
         * @param - string - (optional) Fieldname within c_document
         * @return - HTML - Component
         **/
        public static function cSecn($ref, $fld = 'd_text')
        {
            return self::cValbyRef('dbitem', 'section', $fld, $ref, true, false);
        }

        /**
         * Shortcut to Component
         * @param - string - Reference
         * @param - string - (optional) Fieldname within c_document
         * @return - HTML - Component
         **/
        public static function cComp($ref, $fld = 'd_text')
        {
            return self::cValbyRef('dbitem', 'component', $fld, $ref, false, false);
        }      

        /**
         * Shortcut to a News article
         * @param - string - Reference
         * @param - string - (d_title) Title or (d_text) Content etc. or false for whole row
         * @return - String or Array which will be consumed by the Template
         **/
        public static function cNews($ref, $fld = false)
        {
            return self::cRowbyRef('dbitem', 'news', $ref, $fld, true);
        } 

        /**
         * Shortcut to collection of news articles
         * @param - array - Params such as number or date
         * @return - Array to be consumed by Template
         **/
        public static function wNews($params = [])
        {
            return self::cAllByType('news', $params);
        } 

        /**
         * Shortcut to a Document
         * @param - string - Reference
         * @param - string - (d_title) Title or (d_text) Content etc. or false for whole row
         * @return - String or Array which will be consumed by the Template
         **/
        public static function cDoc($ref, $fld = false)
        {
            return self::cRowByRef('dbitem', 'document', $ref, $fld, true);
        } 

        /**
         * Shortcut to collection of documents
         * @param - array - Params such as number or date
         * @return - Array to be consumed by Template
         **/
        public static function wDocs($params = [])
        {
            return self::cAllByType('document', $params);
        } 

        /**
         * Shortcut to a weblink
         * @param - string - Reference
         * @param - string - (d_title) Title or (d_text) Content etc. or false for whole row
         * @return - String or Array which will be consumed by the Template
         **/
        public static function cWeblink($ref, $fld = false)
        {
            return self::cRowByRef('dbitem', 'weblink', $ref, $fld, true);
        }  

        /**
         * Shortcut to collection of weblinks
         * @param - array - Params such as number or date
         * @return - Array to be consumed by Template
         **/
        public static function wWeblinks($params = [])
        {
            return self::cAllByType('weblink', $params);
        } 

        /**
         * Shortcut to an Image
         * @param - string - Reference
         * @param - string - (d_title) Title or (d_text) Content etc. or false for whole row
         * @return - String or Array which will be consumed by the Template
         **/
        public static function cImg($ref, $fld = false)
        {
            return self::cRowByRef('dbitem', 'image', $ref, $fld, true);
        } 

        /**
         * Shortcut to collection of documents
         * @param - array - Params such as number or date
         * @return - Array to be consumed by Template
         **/
        public static function wImages($params = [])
        {
            return self::cAllByType('image', $params);
        } 

        /**
         * Current last index value to use for generating next references
         * @param - string - Index reference required, Category is not required or used for 3rd party indices
         * @return - string - Value
         **/
        public static function cIdx($ref)
        {
            return Q::cVal('dbindex', '', 'c_value', $ref, false);
        }

} // Class ends

# alias +e+ class
if(!class_exists("W")){ class_alias('Website', 'W'); };

