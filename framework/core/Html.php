<?php
/**
 * HTML and Tag Generation class
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@webcliq.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class Html 
{
	public $thisclass = "Html";

	public static $stag = [
		'link', 'meta', 'input', 'img', 'br', 'hr', 'wbr', 'embed'
	];

	public static $tag = [
		'html', 'head', 'script', 'title', 'body', 'style', 'a', 'p', 'iframe', 'map', 'noscript',
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'div', 'small', 'sub', 'sup', 'var',
		'ul', 'ol', 'li', 'dd', 'dl', 'dt', 'span', 'strong', 'i', 'q', 's', 'u',
		'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'colgroup', 'col',
		// HTML5
		'nav', 'main', 'section', 'footer', 'header', 'aside', 'address', 'abbr', 'article', 'aside', 'audio',
		'bdi', 'blockquote', 'canvas', 'cite', 'datalist', 'details', 'dfn', 'figcaption', 'mark',
		'menu', 'menuitem', 'meter', 'object', 'param', 'output', 'picture', 'rp', 'rt', 'samp', 'source',
		'summary', 'time', 'track', 'video', 'dialog',
		// Form Element
		'form', 'button', 'fieldset', 'legend', 'textarea', 'select', 'optgroup', 'option', 'label', 'small'
		// To do checkbox, file, radio, select etc.	
	];

	/** Tag Assembler
	 *
	 *
	 *
	 ********************************************************************************************************/

		public static function __callStatic($name, $arguments) 
		{

			$ftag = array_flip(self::$tag);
			$fstag = array_flip(self::$stag);

			if($name == 'str') {
				return self::notag($tag, $args);
			} else if(array_key_exists($name, $fstag)) {
				return self::html_single_tag_wrapper($name, $arguments, true); // func_get_args()
			} else if(array_key_exists($name, $ftag)) {
				return self::html_tag_wrapper($name, $arguments);
			} else {
				return $name." Not defined";
			}
	    }

	/** Tag types and attributes
	 * attr()
	 * html_tag()
	 * html_single_tag()
	 * html_tag_wrapper()
	 * html_single_tag_wrapper()
	 *
	 ********************************************************************************************************/

	    // Simple Tag creation
		static function attr($key, $val) 
		{ 
			return "$key=\"$val\""; 
		}

		static function html_tag($tag, $attributes, $content)
		{
			$str = "<$tag ";
			
			foreach ($attributes as $key => $value)
				$str .= self::attr($key, $value) . ' ';
			$str .= '>'; $str .= "\n";
			foreach ($content as $elt) $str .= $elt;
			$str .= "</$tag>"; $str .= "\n";
			return $str;
		}

		static function html_single_tag($tag, $attributes, $noslash)
		{
			$str = "<$tag ";
			foreach ($attributes as $key => $value) {
				$str .= self::attr($key, $value) . ' ';
			}
			if($noslash == false) {
				$str .= '/>';
			} else {
				$str .= '>';
			}
			 
			$str .= "\n";
			return $str;
		}

		static function html_tag_wrapper($tag, $args)
		{
			$attributes = is_array($args[0]) ? array_shift($args) : array();
			$content = $args;
			return self::html_tag($tag, $attributes, $content);
		}

		static function html_single_tag_wrapper($tag, $args, $noslash = false)
		{
			$attributes = is_array($args[0]) ? array_shift($args) : array();
			return self::html_single_tag($tag, $attributes, $noslash);
		}

		static function notag($tag, $args)
		{
			$attributes = is_array($args[0]) ? array_shift($args) : array();

			$str = "<$tag ";
			foreach($attributes as $key => $value) {
				$str .= self::attr($key, $value).' ';
			}; $str .= '>\n';

			return $str;			
		}

		/**
		 * Splits a bar separated string into an array
		 * @tobedone - enhance to include split by coma and then bar
		 * @param - string to be split
		 * @return - array
		 **/
		protected static function barSplit($str)
		{
			return explode('|', $str);
		}

} // Ends Class

# alias +h+ class
if(!class_exists("H")){ class_alias('Html', 'H'); };
