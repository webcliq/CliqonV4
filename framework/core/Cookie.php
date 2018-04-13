<?php
/**
 * @title           Cookie Class
 *
 * @author    		Webcliq    	   
 * @copyright        
 * @license          
 * @package          
 */

class Cookie
{

    /**
     * Get and Set Session Handling depending on Pierre-Henry Soria Session and Cookie Handler
     * @var $var = variable name
     * @var $val = value for Cookie variable
     */	
	public static function zset($ref, $data)
	{
		@setcookie($ref, $data, time() + (7200), "/");
	}

	public static function zget($ref)
	{
        try {
        	isset($_COOKIE[$ref]) ? $ck = $_COOKIE[$ref] : $ck = false ;
            return $ck;
        } catch(Exception $e) {
            return false;
        }
	}

	public static function zremove($ref)
	{
		setcookie($ref, "", time() - 7200);
	}

	public static function _setCookie($ref, $data) {return self::zset($ref, $data);}
	public static function _getCookie($ref) {return self::zget($ref);}
	public static function _removeCookie($ref) {return self::zremove($ref);}
	public static function _hasCookie($ref) {return self::zget($ref);}

} // Class Ends

# alias +h+ class
if(!class_exists("Z")){ class_alias('Cookie', 'Z'); };
