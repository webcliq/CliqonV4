<?php
/**
 * Apiservices Class
 *
 * handles all functions and activities related to generic API Services
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Conkas cb
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

/** JSON Web Token implementation, based on this spec:
 * 
 * https://tools.ietf.org/html/rfc7519
 *
 * PHP version 5
 *
 * @category Authentication
 * @package  Authentication_JWT
 * @author   Neuman Vong <neuman@twilio.com>
 * @author   Anant Narayanan <anant@php.net>
 * @license  http://opensource.org/licenses/BSD-3-Clause 3-clause BSD
 * @link     https://github.com/firebase/php-jwt
 */

class Apiservices
{
	public $thisclass = "Apiservices";
    /**
     * When checking nbf, iat or expiration times,
     * we want to provide some extra leeway time to
     * account for clock skew.
     */
     public static $leeway = 0;

    /**
     * Allow the current timestamp to be specified.
     * Useful for fixing a value within unit testing.
     *
     * Will default to PHP time() value if null.
     */
     public static $timestamp = null;

     public static $supported_algs = array(
        'HS256' => array('hash_hmac', 'SHA256'),
        'HS512' => array('hash_hmac', 'SHA512'),
        'HS384' => array('hash_hmac', 'SHA384'),
        'RS256' => array('openssl', 'SHA256'),
        'RS384' => array('openssl', 'SHA384'),
        'RS512' => array('openssl', 'SHA512'),
     );

	public function __construct() 
	{
      	global $cfg;
      	global $clq;
	}

    /** API Service functions
     *
     * apiLogin()
     * apiLogout()
     *
     *
     *
     *
     *************************************************************************************************************/

    	/** API User Login and get Token
    	 *
    	 * @param - array - Arguments
    	 * @return - array to be encoded by controller with callback
    	 **/
    	 public static function apiLogin($rq)
    	 {
            try {
            	global $clq;
            	$data = [];


              
              	return ['flag' => 'Ok', 'data' => $data]; 
            } catch(Exception $e) {
            	return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            }  	 	
    	 }

    	/** API User Logout and remove Token
    	 *
    	 * @param - array - Arguments
    	 * @return - array to be encoded by controller with callback - will be Ok
    	 **/
    	 public static function apiLogout($args)
    	 {
    	 	
    	 	return ['flag' => 'Ok'];
    	 }









    	 

    /** User Session Management and Tokens
     * 
     * __setTokenHdr()
     * __getTokenHdr()
     *
     * Copied from JWT.Php
     *
     *************************************  User Session Management  *******************************************/       

    	
        public static function __setTokenHdr($key)
        {
            session_start();
            $token_value = md5(sha1(time() + 5).base64_encode($key).sha1(time() - 5));
            header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');
            header('X-CSRF-Token: '.$token_value);
            header('Authorization: Bearer '.$token_value);
            $_SESSION['CSRF_TOKEN'] = $token_value;
            if(!isset($token_value)){
                header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                return new \Exception('401 Unauthorized');
            }else{
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                return print json_encode(array('token' => $_SESSION['CSRF_TOKEN']), JSON_PRETTY_PRINT).'\n';
            }
        }
       
        public static function __getTokenHdr()
        {
            session_start();
            header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');
            header('X-CSRF-Token: '.$_SESSION['CSRF_TOKEN']);
            header('Authorization: Bearer '.$token_value);
            if(!isset($_SESSION['CSRF_TOKEN'])){
                header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                return new \Exception('401 Unauthorized');
            }else{
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                return print json_encode(array('token' => $_SESSION['CSRF_TOKEN']), JSON_PRETTY_PRINT).'\n';
            }
        }

        public static function __start($key)
        {
            return self::__init($key);
        }
       
        public static function __compare($ui_token)
        {
            return self::__validate($ui_token);
        }
       
        public static function __getToken()
        {
            return self::__bringTokenToUi();
        }
       
        protected static function __init($key)
        {
            
            return self::__setTokenHdr($key);
        }
       
        protected static function __bringTokenToUi()
        {
            return self::__getTokenHdr();
        }
       
        protected static function __validate($ui_token)
        {
            header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');
            return ((string)self::__getTokenHdr() === (string)$ui_token) ? TRUE : FALSE;
        }

	    /** Decodes a JWT string into a PHP object  
	     *
	     * @param string        $jwt            The JWT
	     * @param string|array  $key            The key, or map of keys.
	     *                                      If the algorithm used is asymmetric, this is the public key
	     * @param array         $allowed_algs   List of supported verification algorithms
	     *                                      Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
	     *
	     * @return object The JWT's payload as a PHP object
	     *
	     * @throws UnexpectedValueException     Provided JWT was invalid
	     * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
	     * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
	     * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
	     * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
	     *
	     * @uses jsonDecode
	     * @uses urlsafeB64Decode
	     */
	     public static function decode($jwt, $key, array $allowed_algs = array())
	     {
	        $timestamp = is_null(static::$timestamp) ? time() : static::$timestamp;

	        if (empty($key)) {
	            throw new InvalidArgumentException('Key may not be empty');
	        }
	        $tks = explode('.', $jwt);
	        if (count($tks) != 3) {
	            throw new UnexpectedValueException('Wrong number of segments');
	        }
	        list($headb64, $bodyb64, $cryptob64) = $tks;
	        if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
	            throw new UnexpectedValueException('Invalid header encoding');
	        }
	        if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
	            throw new UnexpectedValueException('Invalid claims encoding');
	        }
	        if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
	            throw new UnexpectedValueException('Invalid signature encoding');
	        }
	        if (empty($header->alg)) {
	            throw new UnexpectedValueException('Empty algorithm');
	        }
	        if (empty(static::$supported_algs[$header->alg])) {
	            throw new UnexpectedValueException('Algorithm not supported');
	        }
	        if (!in_array($header->alg, $allowed_algs)) {
	            throw new UnexpectedValueException('Algorithm not allowed');
	        }
	        if (is_array($key) || $key instanceof \ArrayAccess) {
	            if (isset($header->kid)) {
	                if (!isset($key[$header->kid])) {
	                    throw new UnexpectedValueException('"kid" invalid, unable to lookup correct key');
	                }
	                $key = $key[$header->kid];
	            } else {
	                throw new UnexpectedValueException('"kid" empty, unable to lookup correct key');
	            }
	        }

	        // Check the signature
	        if (!static::verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
	            throw new SignatureInvalidException('Signature verification failed');
	        }

	        // Check if the nbf if it is defined. This is the time that the
	        // token can actually be used. If it's not yet that time, abort.
	        if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
	            throw new BeforeValidException(
	                'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->nbf)
	            );
	        }

	        // Check that this token has been created before 'now'. This prevents
	        // using tokens that have been created for later use (and haven't
	        // correctly used the nbf claim).
	        if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
	            throw new BeforeValidException(
	                'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->iat)
	            );
	        }

	        // Check if this token has expired.
	        if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
	            throw new ExpiredException('Expired token');
	        }

	        return $payload;
	     }

	    /** Converts and signs a PHP object or array into a JWT string  
	     * 
	     *
	     * @param object|array  $payload    PHP object or array
	     * @param string        $key        The secret key.
	     *                                  If the algorithm used is asymmetric, this is the private key
	     * @param string        $alg        The signing algorithm.
	     *                                  Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
	     * @param mixed         $keyId
	     * @param array         $head       An array with header elements to attach
	     *
	     * @return string A signed JWT
	     *
	     * @uses jsonEncode
	     * @uses urlsafeB64Encode
	     */
	     public static function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null)
	     {
	        $header = array('typ' => 'JWT', 'alg' => $alg);
	        if ($keyId !== null) {
	            $header['kid'] = $keyId;
	        }
	        if ( isset($head) && is_array($head) ) {
	            $header = array_merge($head, $header);
	        }
	        $segments = array();
	        $segments[] = static::urlsafeB64Encode(static::jsonEncode($header));
	        $segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));
	        $signing_input = implode('.', $segments);

	        $signature = static::sign($signing_input, $key, $alg);
	        $segments[] = static::urlsafeB64Encode($signature);

	        return implode('.', $segments);
	     }

	    /** Sign a string with a given key and algorithm  
	     *
	     * @param string            $msg    The message to sign
	     * @param string|resource   $key    The secret key
	     * @param string            $alg    The signing algorithm.
	     *                                  Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
	     * @return string An encrypted message
	     * @throws DomainException Unsupported algorithm was specified
	     */
	     public static function sign($msg, $key, $alg = 'HS256')
	     {
	        if (empty(static::$supported_algs[$alg])) {
	            throw new DomainException('Algorithm not supported');
	        }
	        list($function, $algorithm) = static::$supported_algs[$alg];
	        switch($function) {
	            case 'hash_hmac':
	                return hash_hmac($algorithm, $msg, $key, true);
	            case 'openssl':
	                $signature = '';
	                $success = openssl_sign($msg, $signature, $key, $algorithm);
	                if (!$success) {
	                    throw new DomainException("OpenSSL unable to sign data");
	                } else {
	                    return $signature;
	                }
	        }
	     }

	    /** Verify a signature 
	     * Verify a signature with the message, key and method. Not all methods
	     * are symmetric, so we must have a separate verify and sign method.
	     *
	     * @param string            $msg        The original message (header and body)
	     * @param string            $signature  The original signature
	     * @param string|resource   $key        For HS*, a string key works. for RS*, must be a resource of an openssl public key
	     * @param string            $alg        The algorithm
	     *
	     * @return bool
	     *
	     * @throws DomainException Invalid Algorithm or OpenSSL failure
	     */
	     private static function verify($msg, $signature, $key, $alg)
	     {
	        if (empty(static::$supported_algs[$alg])) {
	            throw new DomainException('Algorithm not supported');
	        }

	        list($function, $algorithm) = static::$supported_algs[$alg];
	        switch($function) {
	            case 'openssl':
	                $success = openssl_verify($msg, $signature, $key, $algorithm);
	                if ($success === 1) {
	                    return true;
	                } elseif ($success === 0) {
	                    return false;
	                }
	                // returns 1 on success, 0 on failure, -1 on error.
	                throw new DomainException(
	                    'OpenSSL error: ' . openssl_error_string()
	                );
	            case 'hash_hmac':
	            default:
	                $hash = hash_hmac($algorithm, $msg, $key, true);
	                if (function_exists('hash_equals')) {
	                    return hash_equals($signature, $hash);
	                }
	                $len = min(static::safeStrlen($signature), static::safeStrlen($hash));

	                $status = 0;
	                for ($i = 0; $i < $len; $i++) {
	                    $status |= (ord($signature[$i]) ^ ord($hash[$i]));
	                }
	                $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));

	                return ($status === 0);
	        }
	     }

	    /** Version of jsonDecode 
	     * Decode a JSON string into a PHP object.
	     *
	     * @param string $input JSON string
	     *
	     * @return object Object representation of JSON string
	     *
	     * @throws DomainException Provided string was invalid JSON
	     */
	     public static function jsonDecode($input)
	     {
	        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
	            /** In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
	             * to specify that large ints (like Steam Transaction IDs) should be treated as
	             * strings, rather than the PHP default behaviour of converting them to floats.
	             */
	            $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
	        } else {
	            /** Not all servers will support that, however, so for older versions we must
	             * manually detect large ints in the JSON string and quote them (thus converting
	             *them to strings) before decoding, hence the preg_replace() call.
	             */
	            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
	            $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
	            $obj = json_decode($json_without_bigints);
	        }

	        if (function_exists('json_last_error') && $errno = json_last_error()) {
	            static::handleJsonError($errno);
	        } elseif ($obj === null && $input !== 'null') {
	            throw new DomainException('Null result with non-null input');
	        }
	        return $obj;
	     }

	    /** Version of jsonEncode  
	     * Encode a PHP object into a JSON string.
	     *
	     * @param object|array $input A PHP object or array
	     *
	     * @return string JSON representation of the PHP object or array
	     *
	     * @throws DomainException Provided object could not be encoded to valid JSON
	     */
	     public static function jsonEncode($input)
	     {
	        $json = json_encode($input);
	        if (function_exists('json_last_error') && $errno = json_last_error()) {
	            static::handleJsonError($errno);
	        } elseif ($json === 'null' && $input !== null) {
	            throw new DomainException('Null result with non-null input');
	        }
	        return $json;
	     }

	    /** Decode a string with URL-safe Base64  
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

	    /** Encode a string with URL-safe Base64.
	     * 
	     * @param string $input The string you want encoded
	     * @return string The base64 encode of what you passed in
	     */
	     public static function urlsafeB64Encode($input)
	     {
	        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	     }

	    /** Helper method to create a JSON error.
	     *
	     * @param int $errno An error number from json_last_error()
	     * @return void
	     */
	     private static function handleJsonError($errno)
	     {
	        $messages = array(
	            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
	            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
	            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
	            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
	            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters' //PHP >= 5.3.3
	        );
	        throw new DomainException(
	            isset($messages[$errno])
	            ? $messages[$errno]
	            : 'Unknown JSON error: ' . $errno
	        );
	     }

	    /** Get the number of bytes in cryptographic strings. 
	     * 
	     * @param string
	     * @return int
	     */
	     private static function safeStrlen($str)
	     {
	        if (function_exists('mb_strlen')) {
	            return mb_strlen($str, '8bit');
	        }
	        return strlen($str);
	     }

} // Class ends

# alias +API+ class
if(!class_exists("API")){ class_alias('Apiservices', 'API'); };

class SignatureInvalidException extends UnexpectedValueException {}
class ExpiredException extends UnexpectedValueException {}
class BeforeValidException extends UnexpectedValueException {}
