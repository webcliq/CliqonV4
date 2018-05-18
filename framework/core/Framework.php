<?php
/**
 * Cliqon Framework Core
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Conkas cb
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Framework
{

	const THISCLASS = "Framework";
	const CLIQDOC = "c_document";
	/**
	* The frameworks human readable name
	* @access private
	*/
	private static $frameworkname = "Cliqon";  
	private static $thisclass = "Framework";
	private static $cfg;


    /**
     * Constructor
     * @param String $user_agent_string Custom User Agent String
     */
	public function __construct() {
		global $cfg;
		self::$cfg = $cfg;
		global $clq;
		foreach($cfg['site'] as $key => $val) {
			$clq->set($key, $val);
		}
    }

	/** Language and Client Handling
	 * getDefLanguage() - public, static
	 * - parseDefaultLanguage() - private, static
	 * 
	 * 
	 * 
	 *****************************************************  Language  **********************************************/

    	/** Gets the framework name
		 * 
		 * @return String
		 **/
		 public function getFrameworkName()
		 {
			return self::$frameworkname;
		 }		

		/** Get the Default Language from the Browser
		 * 
		 * @return string of two characters
		 **/
		 public static function getDefLanguage() 
		 {
			$lcd = "en"; // Set a default
			// Rely on browser to give us language
			if(self::$cfg['site']['setdefaultidiom'] == 'dynamic') {
				// Get it dynamically
				if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
					$lcd = self::parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
				} else {
					$lcd = self::parseDefaultLanguage(NULL);
				}
			// We can decide to set the default idiom statically from the config file		
			} else if(self::$cfg['site']['setdefaultidiom'] == 'static') {
				// Use language in Config File - do this if there is possibility that browsers that do not appear in list of supported languages will exist
				$lcd = self::$cfg['site']['defaultidiom'];
			} else {
				// Unless there is a better way
				if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
					$lcd = self::parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
				} else {
					$lcd = self::parseDefaultLanguage(NULL);
				}
			};
			return $lcd;
		 }

		 /** Helper for above 
		 **/ 
		 private static function parseDefaultLanguage($http_accept) 
		 {

			if(isset($http_accept) && strlen($http_accept) > 1)  {
				# Split possible languages into array
				$x = explode(",",$http_accept);
				foreach ($x as $val) {
					#check for q-value and create associative array. No q-value means 1 by rule
					if(preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i",$val,$matches))
						$lang[$matches[1]] = (float)$matches[2];
					else
						$lang[$val] = 1.0;
				}

				#return default language (highest q-value)
				$qval = 0.0;
				foreach ($lang as $key => $value) {
					if ($value > $qval) {
						$qval = (float)$value;
						$deflang = $key;
					}
				}
			};

			$lcd = strtolower(substr($deflang, 0, 2));
			if(array_key_exists($lcd, self::$cfg['site']['idioms'])) {
				return $lcd;
			} else {
				return self::$cfg['site']['defaultidiom'];
			}
		 }   
	
		/** Parse User Client
		 *
		 * @return - array - properties
		 **/
		 public static function parseClient()
		 {
		  	
		  	global $clq;
		  	$ua = $clq->resolve('Useragent');
		  	$client = [
		  		'useragent' => $ua->string,
		  		'browsername' => $ua->browserName,
		  		'browserversion' => $ua->browserVersion,
		  		'systemstring' => $ua->systemString,	
		  		'osplatform' => $ua->osPlatform,
		  		'osversion' => $ua->osVersion,
		  		'osshortv' => $ua->osShortVersion,		
		  		'os' => $ua->osArch,
		  		'ismobile' => $ua->isMobile,
		  		'mobil' => $ua->mobileName			  		
		  	];
		  	return $client;
		 }

	/** JSON utilities
	 * decode()
	 * encode()
	 * sign()
	 * jsonEncode()
	 * jsonDecode()
	 ****************************************************************************************************************/

		/**
		 * Decodes a self string into a PHP object.
		 *
		 * @param string      $jwt    The self
		 * @param string|null $key    The secret key
		 * @param bool        $verify Don't skip verification process 
		 *
		 * @return object      The self's payload as a PHP object
		 * @throws UnexpectedValueException Provided self was invalid
		 * @throws DomainException          Algorithm was not provided
		 * 
		 * @uses jsonDecode
		 * @uses urlsafeB64Decode
		 */
		public static function decode($jwt, $key = null, $verify = true)
		{
			$tks = explode('.', $jwt);
			if (count($tks) != 3) {
				throw new UnexpectedValueException('Wrong number of segments');
			}
			list($headb64, $bodyb64, $cryptob64) = $tks;
			if (null === ($header = self::jsonDecode(self::urlsafeB64Decode($headb64)))) {
				throw new UnexpectedValueException('Invalid segment encoding');
			}
			if (null === $payload = self::jsonDecode(self::urlsafeB64Decode($bodyb64))) {
				throw new UnexpectedValueException('Invalid segment encoding');
			}
			$sig = self::urlsafeB64Decode($cryptob64);
			if ($verify) {
				if (empty($header->alg)) {
					throw new DomainException('Empty algorithm');
				}
				if ($sig != self::sign("$headb64.$bodyb64", $key, $header->alg)) {
					throw new UnexpectedValueException('Signature verification failed');
				}
			}
			return $payload;
		}

		/**
		 * Converts and signs a PHP object or array into a self string.
		 *
		 * @param object|array $payload PHP object or array
		 * @param string       $key     The secret key
		 * @param string       $algo    The signing algorithm. Supported
		 *                              algorithms are 'HS256', 'HS384' and 'HS512'
		 *
		 * @return string      A signed self
		 * @uses jsonEncode
		 * @uses urlsafeB64Encode
		 */
		public static function encode($payload, $key, $algo = 'HS256')
		{
			$header = array('typ' => 'self', 'alg' => $algo);

			$segments = array();
			$segments[] = self::urlsafeB64Encode(self::jsonEncode($header));
			$segments[] = self::urlsafeB64Encode(self::jsonEncode($payload));
			$signing_input = implode('.', $segments);

			$signature = self::sign($signing_input, $key, $algo);
			$segments[] = self::urlsafeB64Encode($signature);

			return implode('.', $segments);
		}

		/**
		 * Sign a string with a given key and algorithm.
		 *
		 * @param string $msg    The message to sign
		 * @param string $key    The secret key
		 * @param string $method The signing algorithm. Supported
		 *                       algorithms are 'HS256', 'HS384' and 'HS512'
		 *
		 * @return string          An encrypted message
		 * @throws DomainException Unsupported algorithm was specified
		 */
		public static function sign($msg, $key, $method = 'HS256')
		{
			$methods = array(
				'HS256' => 'sha256',
				'HS384' => 'sha384',
				'HS512' => 'sha512',
			);
			if (empty($methods[$method])) {
				throw new DomainException('Algorithm not supported');
			}
			return hash_hmac($methods[$method], $msg, $key, true);
		}

		/**
		 * Decode a JSON string into a PHP object.
		 *
		 * @param string $input JSON string
		 *
		 * @return object          Object representation of JSON string
		 * @throws DomainException Provided string was invalid JSON
		 */
		public static function jsonDecode($input)
		{
			$obj = json_decode($input, true);
			if (function_exists('json_last_error') && $errno = json_last_error()) {
				self::_handleJsonError($errno);
			} else if ($obj === null && $input !== 'null') {
				throw new DomainException('F::jsonDecode() produced an error where there was input but output was NULL');
			}
			return $obj;
		}

		/**
		 * Encode a PHP object into a JSON string.
		 *
		 * @param object|array $input A PHP object or array
		 *
		 * @return string          JSON representation of the PHP object or array
		 * @throws DomainException Provided object could not be encoded to valid JSON
		 */
		public static function jsonEncode($input)
		{
			global $cfg; global $clq;
			$array = array_map_deep("strtobool", $input);
			$json = json_encode($array);

			if (function_exists('json_last_error') && $errno = json_last_error()) {
				self::_handleJsonError($errno);
			} else if ($json === 'null' && $input !== null) {
				throw new DomainException('Null result with non-null input');
			};

            $qrepl = array('"@', '@"', '{url}', '{lcd}', '\\r\\n');
            $qwith = array('', '', $cfg['site']['website'], $clq->get('idiom'), '');
            $json = str_replace($qrepl, $qwith, $json);          

			return $json;
		}

		/**
		 * Decode a string with URL-safe Base64.
		 *
		 * @param string $input A Base64 encoded string
		 *
		 * @return string A decoded string
		 */
		public static function urlsafeB64Decode($input)
		{
			$remainder = strlen($input) % 4;
			if ($remainder) {
				$padlen = 4 - $remainder;
				$input .= str_repeat('=', $padlen);
			}
			return base64_decode(strtr($input, '-_', '+/'));
		}

		/**
		 * Encode a string with URL-safe Base64.
		 *
		 * @param string $input The string you want encoded
		 *
		 * @return string The base64 encode of what you passed in
		 */
		public static function urlsafeB64Encode($input)
		{
			return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
		}

		/**
		 * Helper method to create a JSON error.
		 *
		 * @param int $errno An error number from json_last_error()
		 *
		 * @return void
		 */
		private static function _handleJsonError($errno)
		{
			$messages = array(
				JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
				JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
				JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
			);
			throw new DomainException(
				isset($messages[$errno])
				? $messages[$errno]
				: 'Unknown JSON error: ' . $errno
			);
		}	

		/** Simple valid JSON checker
       	 * @param - string to check
       	 * @return - error or nothing
       	 **/
		public static function isJson($string) {
 			json_decode($string);
 			return (json_last_error() == JSON_ERROR_NONE);
		}

	/** JSON Handling
	 * 
	 *****************************************************  JSON  **********************************************/		

        /**
        * Utility to output data in JSON format
        **/
        static function echoJson($data) {

            $json = self::jsonEncode($data);
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-Type: application/json; charset=utf-8');
            echo $json;
        }

        /**
        * Utility to output data in JSON format - cross domain
        **/
        static function echoJsonp($data, $useCallback) {

            $json = self::jsonEncode($data);
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-Type: application/json; charset=utf-8');
			echo $useCallback."(".$json.")";
        }

        /**
        * Utility to output data in HTML format
        **/
        static function echoText($data) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-Type: application/html; charset=utf-8');
            echo $data;
        }

        /**
         ** convert input array to a csv file and force downlaod the same
         **
         ** should be called before any output is send to the browser
         ** input array should be an associative array
         ** the key of the associative array will be first row of the csv file
         ** 
         ** @param array $rs
         ** @param $filename
         ** @return null
         **/
        function echoDownload($file) {
            header('Content-Description: File Transfer');
            header('Content-Transfer-Encoding: binary');
            header('Content-Type: application/octet-stream');
            header("Content-Type: application/force-download");
            header('Content-Disposition: attachment; filename="'.$file.'"');
            header('Expires: 0');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.filesize($file));
            readfile($file);
            exit;
        }	

} // Ends Class

# alias +f+ class
if(!class_exists("F")){ class_alias('Framework', 'F'); };

