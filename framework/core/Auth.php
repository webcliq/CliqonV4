<?php
/**
 * Auth Class
 *
 * All matters relating to Admin Users
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Auth {

	const THISCLASS = "Auth";
    public $tblname = 'dbuser';
    public $cfg;

	function __construct() 
	{
        global $clq;
        global $cfg; $this->cfg = $cfg;
	}

    /** User Management - CRUD
     * creatUser()
     * updateUserRecord()
     * deleteUserRecord()
     * changeUserPassword()
     * changeUserElement()
     *
     * createUser() - quick user create
     *
     *************************************  Basic User Management  *************************************************/

        /** Create User  
         * Create new user record via registration or direct input - when does this get used??
         * user status is inactive
         * @var array post
         * @return string Ok or notOk plus message
         **/
         function createUser($rq) 
         {

            try {

                // Check various elements of user situation before creating a "new" record
                getUserElement($id, $ele = 'status');

                $hasher = new PasswordHash(8, false);
                $pwd = $hasher->HashPassword($rq['password']);

                // c_document -> $doc fields first
                $doc = [
                    'd_firstname' => self::setDefault('firstname', '', $rq),                
                    'd_midname' => self::setDefault('midname', '', $rq),
                    'd_lastname' => self::setDefault('lastname', '', $rq),
                    'd_langcd' => self::setDefault('langcd', 'en', $rq),
                    'd_avatar' => self::setDefault('avatar', '', $rq),
                    'd_comments' => self::setDefault('comments', 'No comments', $rq),
                    // ididtype
                ];            

                $result = $this->db->insert($this->tblname, [
                    'c_group' => self::setDefault('group', 'admin', $rq),
                    'c_username' => self::setDefault('username', '', $rq),
                    'c_password' => $pwd,
                    'c_level' => self::setDefault('level', '20:20:20', $rq),
                    'c_status' => self::setDefault('status', 'inactive', $rq),
                    'c_document' => json_encode($doc),
                    'c_email' => self::setDefault('email', '', $rq),
                    'c_lastmodified' => Q::lastMod(),
                    'c_whomodified' => Q::whoMod(),
                    'c_notes' => self::setDefault('notes', 'No additional notes', $rq),
                ]);



                $updb = R::dispense('dbuser');
                foreach($userarray as $key => $val) {
                    $updb->$key = $val;
                }
                $result = R::store($updb);

                if(is_numeric($result)) {
                    return "Ok";
                } else {
                    return "NotOk: ".$result;
                }

            } catch(Exception $e) {
                return "NotOk: ".$e->getMessage();
            }   
         }

        /** Update User  
         * @var array post
         * @return string Ok or notOk plus message
         **/
         function updateUserRecord($rq) 
         {
            
            try {

                // c_document -> $doc fields first
                $doc = [
                    'd_firstname' => self::setDefault('firstname', '', $rq),                
                    'd_midname' => self::setDefault('midname', '', $rq),
                    'd_lastname' => self::setDefault('lastname', '', $rq),
                    'd_langcd' => self::setDefault('langcd', 'en', $rq),
                    'd_avatar' => self::setDefault('avatar', '', $rq),
                    'd_comments' => self::setDefault('comments', 'No comments', $rq),
                ];

                $result = $this->db->update($this->tblname, [
                    'c_group' => self::setDefault('group', 'admin', $rq),
                    'c_username' => self::setDefault('username', '', $rq),
                    'c_level' => self::setDefault('level', '60:60:60', $rq),
                    'c_status' => self::setDefault('status', 'active', $rq),
                    'c_document' => json_encode($doc),
                    'c_email' => self::setDefault('email', '', $rq),
                    'c_lastmodified' => Q::lastMod(),
                    'c_whomodified' => Q::whoMod(),
                    'c_notes' => self::setDefault('notes', 'No additional notes', $rq),
                ],[
                    'id' => $rq['id']
                ]);

                if(is_numeric($result)) {
                    return "Ok";
                } else {
                    return "NotOk: ".$result;
                }

            } catch(Exception $e) {
                return "NotOk: ".$e->getMessage();
            }
         }

        /** Delete user  
         * Delete User from system
         * @var array post
         * @return string Ok or notOk plus message
         **/
         function deleteUserRecord($rq) 
         {

            try {

                $result = $this->db->delete($this->tblname, [
                    'id' => $rq['id']
                ]);

                if(is_numeric($result)) {
                    return "Ok";
                } else {
                    return "NotOk: ".$result;
                }

            } catch(Exception $e) {
                return "NotOk: ".$e->getMessage();
            }           
         }

        /** Change User Password  
         * @var array Request
         * @return string Ok or notOk plus message
         **/
         function changeUserPassword($vars) 
         {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $rq = $vars['rq'];

                $hasher = new PasswordHash(8, false);
                $pwd = $hasher->HashPassword($rq['c_password']);
                $updb = R::load($vars['table'], $rq['id']);
                $updb->c_password = $pwd;
                $updb->c_lastmodified = Q::lastMod();
                $updb->c_whomodified = Q::cUserId($rq['id']);

                $result = R::store($updb);

                if(is_numeric($result)) {
                    return [
                        'flag' => "Ok",
                        'msg' => Q::cStr('9999:Password successfully reset')
                    ];
                } else {
                    return [
                        'flag' => "NotOk",
                        'msg' => Q::cStr('495:Record was not successfully written to database').': '.$result
                    ];
                }

            } catch (Exception $e) {
                $err = [
                    'errmsg' => $e->getMessage(),
                    'method' => $method
                ];
                L::cLog($err);
                return [
                    'flag' => "NotOk",
                    'html' => $err
                ]; 
            }  
         }

        /** Change user element  
         * Change Single Element of User Record such as Group or Status
         * @var array $vars
         * @return string Ok or notOk plus message
         **/
         function changeUserElement($vars)
         {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $html = ""; $rq = $vars['rq']; $id = $rq['id'];
                $fld = $rq['fldname'];
                $updb = R::load($vars['table'], $id);
                $updb->$fld = $rq['value'];
                $updb->c_lastmodified = Q::lastMod();
                $updb->c_whomodified = Q::whoMod();
                $updb->c_notes .= Q::cAddNotes($fld, $rq['value']);
                $result = R::store($updb);

                // Return the result to the browser
                if($result > 0) {
                    return [
                        'flag' => "Ok",
                        'msg' => Q::cStr('368:Existing record update with id').' - '.$id  
                    ];
                } else {
                    return [
                        'flag' => "NotOk",
                        'msg' => Q::cStr('495:Record was not successfully written to database').': '.$result
                    ];
                }               

            } catch (Exception $e) {
                $err = [
                    'errmsg' => $e->getMessage(),
                    'method' => $method
                ];
                L::cLog($err);
                return [
                    'flag' => "NotOk",
                    'html' => $err
                ]; 
            }      
         }

         private function setDefault($name, $default, $rq)
         {
            if($default == '') {
                return $rq[$name];
            } else {
                if($rq[$name] != '') {
                    return $rq[$name];
                } else {
                    return $default;
                }
            }
         }

    /** User Access
     * doLogin()
     * doLogout()
     * authAction()
     *
     *************************************  Basic User Access  *************************************************/

        /**
         * Function to Log User into Admin System
         * @var array Post from Login Form
         * @return string OK or Not OK plus Message
         **/
        function login($rq) 
        {
            
            try {

                global $clq;
                $check = false;
                $cfg = $clq->get('cfg');

                //if session isnt active but a cookie exists load the user back up.
                if (((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === false) || !isset($_SESSION['loggedin'])) && isset($_COOKIE['loggedin'])  && $_COOKIE['loggedin'] == 1) {
                    
                    //DONT FORGET - USER CAN CHANGE THE COOKIE :O - check the hash
                    $hash = $_COOKIE['hash'];
                    $id = $_COOKIE['UserID'];

                    if ($hash == md5('rand_3453*'.$id.'_87@hashHASH')) {
                        // Call to Database for Username and Password
                        $sql = "SELECT * FROM dbuser WHERE c_username = ? AND c_status = ?";
                        $row = R::getRow($sql, [$rq['username'], 'active']);
                        $check = true;
                    } else {
                        //destroy the cookie, its been tampered with.
                        setcookie("loggedin", '', time() - 9999);
                        setcookie("UserID", '', time() - 9999);
                        setcookie("hash", '', time() - 9999);
                    }

                } else {

                    // Call to Database for Username and Password
                    $sql = "SELECT * FROM dbuser WHERE c_username = ? AND c_status = ?";
                    $row = R::getRow($sql, [$rq['username'], 'active']);

                    if($row) {
                        $hasher = new PasswordHash(8, false);
                        $check = $hasher->CheckPassword($rq['password'], $row['c_password']); // Input, Database         
                    } else {

                        // Temporary login
                        $users = $cfg['site']['users'];
                        foreach($users as $id => $user) {
                            // Does User exist in Config file ??
                            if($rq['username'] == $user['c_username']) {
                                if($user['c_password'] == $rq['password']) {
                                    $check = true;
                                    $row = $user;
                                    $row['id'] = $id;  

                                    break;
                                } else {
                                    break;
                                }
                                
                            }
                            
                        } // End foreach $users
                    } // End if Row

                    // Remember me
                    if(isset($rq['rememberme']) and $rq['rememberme'] == 'remember-me') {
                        setcookie("UserID", $row['id'], time() + (3600 * 24 * 14));
                        setcookie("hash", md5('rand_3453*'.$row['id'].'_87@hashHASH'), time() + (3600 * 24 * 14));
                    };                    

                }

                if($check) {
                    self::setSession($row);
                    $_SESSION['loggedin'] = true;
                    $clq->set('lcd', $rq['langcd']); 

                    Z::zset('Langcd', $rq['langcd']); // 2 hours
                    return ['flag' => 'Ok', 'msg' => $_SESSION['UserName'], 'data' => ['recid' => $_SESSION['UserID']]];
                } else { 
                    return ['flag' => 'NotOk', 'msg' => Q::cStr('364:Login failed')];
                } 

            } catch(Exception $e) {
                return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            }
        }

        private function setSession($row)
        {
            $_SESSION['FullName'] = self::getUserFullName($row, false);
            $_SESSION['UserName'] = $row['c_username'];
            $_SESSION['Group'] = $row['c_group'];
            $_SESSION['UserID'] = $row['id'];
            $_SESSION['UserEmail'] = $row['c_email'];
            $_SESSION['UserLevel'] = $row['c_level'];
            $_SESSION['UserGroup'] = $row['c_group'];
            $_SESSION['CliqonAdminUser'] = $_SESSION['UserName'];

            return;
        }

        /**
         * Log adminuser out of system
         * @return string OK or Not OK plus Message. Causes JS to Load Login Form 
         **/
        function logout() 
        {

            global $clq;
            // Remove Authority here
            $_SESSION['FullName'] = "";
            $_SESSION['UserName'] = "";
            $_SESSION['Group'] = "";
            $_SESSION['UserID'] = "";
            $_SESSION['UserEmail'] = "";
            $_SESSION['UserLevel'] = "";
            $_SESSION['UserGroup'] = "";
            $_SESSION['UserLanguage'] = "";

            Z::zremove('UserName');
            session_destroy(); 
            Z::zremove('UserID');

            header('Location: '.$clq->get('rootpath'));
            exit;
        }

        /**
         * Main action authorizer
         * @param - string - Action, such as delete, insert, view etc.
         * @param - string - table
         * @param - string - tabletype
         * @return - boolean - true / false
         **/
        public static function getAuth($action, $tbl = "", $tbltype = "", $lvl = "")
        {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                // Level could be set in the call because we are at record level
                if($lvl != "") {
                    $acl = explode(':', $lvl);
                    if(!is_array($acl)) {
                        throw new Exception("No level array created based on Level provided");
                    };
                // Record level is not appropriate, so we are using table and tabletype  
                } else {
                    
                    $mdl = $clq->resolve('Model');
                    $com = $mdl->stdModel('common', $tbl, $tbltype);
                    $acl = explode(':', $com['level']);
                    if(!is_array($acl)) {
                        throw new Exception("No level array returned based on table > tabletype > common");
                    };                    
                };
                
                // Get the User Level from the User Session
                $ucl = explode(':', $_SESSION['UserLevel']);    
                if(!is_array($ucl)) {
                    throw new Exception("No UserLevel array created from User Session");
                };  

                // Set Authorised to False as default, then change it to true (calling process is OK to proceed) by comparison
                $authorised = false;

                switch($action) {

                    // Third bit
                    case "delete":
                        $ucl[2] >= $acl[2] ? $authorised = true : null ;
                    break;

                    // Second bit
                    case "write":
                    case "insert":
                    case "update":
                        $ucl[1] >= $acl[1] ? $authorised = true : null ;
                    break;

                    // First bit
                    case "read":
                    case "view":
                        $ucl[0] >= $acl[0] ? $authorised = true : null ;
                    break;

                    // A default for completeness
                    default: 
                        $ucl[0] >= 90 ? $authorised = true : null ;
                    break;                   

                };

                // Test
                $test = [
                    'method' => $method,
                    'tablename' => $tbl,
                    'tabletype' => $tbltype,
                    'level' => $lvl,
                    'levelrequired' => implode(':', $acl),
                    'leveloffered' => implode(':', $ucl),
                ];

                // Set to comment when completed
                // L::cLog($test);  
                
                // If not returned already 
                return $authorised;          

            } catch (Exception $e) {
                $err = [
                    'errmsg' => $e->getMessage(),
                    'method' => $method,
                    'tablename' => $tbl,
                    'level' => $lvl,
                    'tabletype' => $tbltype,                
                ];

                is_array($acl) ? $err['levelrequired'] = implode(':', $acl) : $err['levelrequired'] = 'Problem' ;
                is_array($ucl) ? $err['leveloffered'] = implode(':', $ucl) : $err['leveloffered'] = 'Problem' ;

                L::cLog($err);
                return false; 
            }
        }

    /** User Display
     * getUsername()
     * getUserFullName()
     * getUserFullAddress()
     * getUserElement()
     * uLevel()
     *
     *************************************  User Display and List  *************************************************/

        /**
         * Generates a meaningful name from the Username
         * There is a nod here of the cultural significance of the difference between two and three part names but this needs further development
         * @param - array - A Reference containing a Username 
         * @param - numeric - a number, either 2 or 3 defining how many elements to be returned
         * @return - string - Name
         **/
        static function getUserName($userref, $ele = 2)
        {
            $sql = "SELECT c_document FROM dbuser WHERE c_username = ?";
            $doc = R::getCell($sql, [$userref]);
            $row = json_decode($doc, true);

            $fullname = "";
            if($ele == 2) {

                array_key_exists('d_firstname', $row) ? $fullname .= $row['d_firstname'] : null ;
                if(array_key_exists('d_langcd', $row) AND $row['d_langcd'] == 'en') {
                    array_key_exists('d_lastname', $row) ? $fullname .= " ".$row['d_lastname'] : null ;
                } else {
                    array_key_exists('d_midname', $row) ? $fullname .= " ".$row['d_midname'] : null ;
                }
                
            } else { // Number of elements to return = 3 or all
                array_key_exists('d_firstname', $row) ? $fullname .= $row['d_firstname'] : null ;
                array_key_exists('d_midname', $row) ? $fullname .= " ".$row['d_midname'] : null ;
                array_key_exists('d_lastname', $row) ? $fullname .= " ".$row['d_lastname'] : null ;
            }

            return $fullname;
        }

        /**
         * Generates a full name from a user Row
         * @param - array - Row from user database
         * @return - string - Name
         **/
        static function getUserFullName($row)
        {
            $fullname = "";
            array_key_exists('d_firstname', $row) ? $fullname .= $row['d_firstname'] : null ;
            array_key_exists('d_midname', $row) ? $fullname .= " ".$row['d_midname'] : null ;
            array_key_exists('d_lastname', $row) ? $fullname .= " ".$row['d_lastname'] : null ;
            return $fullname;
        }

        /**
         * Generates a full address from a user Row
         * @param - array - Row from user database
         * @return - string - Name
         **/
        static function getUserFullAddress($row)
        {
            $fulladdress = "";
            array_key_exists('d_addr1', $row) ? $fulladdress .= $row['d_addr1'] : null ;
            array_key_exists('d_addr2', $row) ? $fulladdress .= ", ".$row['d_addr2'] : null ;
            array_key_exists('d_suburb', $row) ? $fulladdress .= ", ".$row['d_suburb'] : null ;
            array_key_exists('d_postcode', $row) ? $fulladdress .= ", ".$row['d_postcode'] : null ;
            array_key_exists('d_city', $row) ? $fulladdress .= ", ".$row['d_city'] : null ;
            array_key_exists('d_region', $row) ? $fulladdress .= ", ".$row['d_region'] : null ;
            array_key_exists('d_country', $row) ? $fulladdress .= ", ".$row['d_country'] : null ;
            return $fulladdress;
        }

        /**
         * To be done
         * @param - string - User ID
         * @return - 
         **/
        protected function getUserElement($id, $ele = 'status')
        {

        }

        /** Get User Level as a number
         * 
         * @param - string - action read, write, delete
         * @return - string - level as an number
         **/  
        static function uLevel($action = 'read')
        {
            if(array_key_exists('UserLevel', $_SESSION)) {
                $lev = $_SESSION['UserLevel'];
                $l = explode(':', $lev);
                switch($action) {
                    case "read": $u = $l[0]; break;
                    case "write": $u = $l[1]; break;
                    case "delete": $u = $l[2]; break;
                }                
            } else {
                $u = '20';
            };
            return $u;
        } 
}

# alias +h+ class
if(!class_exists("A")){ class_alias('Auth', 'A'); };

/**
 * Password Hashing
 */
class PasswordHash {
    var $itoa64;
    var $iteration_count_log2;
    var $portable_hashes;
    var $random_state;

    function __construct($iteration_count_log2, $portable_hashes) {
        $this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
            $iteration_count_log2 = 8;
        $this->iteration_count_log2 = $iteration_count_log2;

        $this->portable_hashes = $portable_hashes;

        $this->random_state = microtime();
        if (function_exists('getmypid'))
            $this->random_state .= getmypid();
    }

    function get_random_bytes($count) {
        $output = '';
        if (is_readable('/dev/urandom') &&
            ($fh = @fopen('/dev/urandom', 'rb'))) {
            $output = fread($fh, $count);
            fclose($fh);
        }

        if (strlen($output) < $count) {
            $output = '';
            for ($i = 0; $i < $count; $i += 16) {
                $this->random_state =
                    md5(microtime() . $this->random_state);
                $output .=
                    pack('H*', md5($this->random_state));
            }
            $output = substr($output, 0, $count);
        }

        return $output;
    }

    function encode64($input, $count) {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->itoa64[$value & 0x3f];
            if ($i < $count)
                $value |= ord($input[$i]) << 8;
            $output .= $this->itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count)
                break;
            if ($i < $count)
                $value |= ord($input[$i]) << 16;
            $output .= $this->itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count)
                break;
            $output .= $this->itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    function gensalt_private($input) {
        $output = '$P$';
        $output .= $this->itoa64[min($this->iteration_count_log2 +
            ((PHP_VERSION >= '5') ? 5 : 3), 30)];
        $output .= $this->encode64($input, 6);

        return $output;
    }

    function crypt_private($password, $setting) {
        $output = '*0';
        if (substr($setting, 0, 2) == $output)
            $output = '*1';

        $id = substr($setting, 0, 3);
        # We use "$P$", phpBB3 uses "$H$" for the same thing
        if ($id != '$P$' && $id != '$H$')
            return $output;

        $count_log2 = strpos($this->itoa64, $setting[3]);
        if ($count_log2 < 7 || $count_log2 > 30)
            return $output;

        $count = 1 << $count_log2;

        $salt = substr($setting, 4, 8);
        if (strlen($salt) != 8)
            return $output;

        # We're kind of forced to use MD5 here since it's the only
        # cryptographic primitive available in all versions of PHP
        # currently in use.  To implement our own low-level crypto
        # in PHP would result in much worse performance and
        # consequently in lower iteration counts and hashes that are
        # quicker to crack (by non-PHP code).
        if (PHP_VERSION >= '5') {
            $hash = md5($salt . $password, TRUE);
            do {
                $hash = md5($hash . $password, TRUE);
            } while (--$count);
        } else {
            $hash = pack('H*', md5($salt . $password));
            do {
                $hash = pack('H*', md5($hash . $password));
            } while (--$count);
        }

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, 16);

        return $output;
    }

    function gensalt_extended($input) {
        $count_log2 = min($this->iteration_count_log2 + 8, 24);
        # This should be odd to not reveal weak DES keys, and the
        # maximum valid value is (2**24 - 1) which is odd anyway.
        $count = (1 << $count_log2) - 1;

        $output = '_';
        $output .= $this->itoa64[$count & 0x3f];
        $output .= $this->itoa64[($count >> 6) & 0x3f];
        $output .= $this->itoa64[($count >> 12) & 0x3f];
        $output .= $this->itoa64[($count >> 18) & 0x3f];

        $output .= $this->encode64($input, 3);

        return $output;
    }

    function gensalt_blowfish($input) {
        # This one needs to use a different order of characters and a
        # different encoding scheme from the one in encode64() above.
        # We care because the last character in our encoded string will
        # only represent 2 bits.  While two known implementations of
        # bcrypt will happily accept and correct a salt string which
        # has the 4 unused bits set to non-zero, we do not want to take
        # chances and we also do not want to waste an additional byte
        # of entropy.
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $output = '$2a$';
        $output .= chr(ord('0') + $this->iteration_count_log2 / 10);
        $output .= chr(ord('0') + $this->iteration_count_log2 % 10);
        $output .= '$';

        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
    }

    function HashPassword($password) {
        $random = '';

        if (CRYPT_BLOWFISH == 1 && !$this->portable_hashes) {
            $random = $this->get_random_bytes(16);
            $hash =
                crypt($password, $this->gensalt_blowfish($random));
            if (strlen($hash) == 60)
                return $hash;
        }

        if (CRYPT_EXT_DES == 1 && !$this->portable_hashes) {
            if (strlen($random) < 3)
                $random = $this->get_random_bytes(3);
            $hash =
                crypt($password, $this->gensalt_extended($random));
            if (strlen($hash) == 20)
                return $hash;
        }

        if (strlen($random) < 6)
            $random = $this->get_random_bytes(6);
        $hash =
            $this->crypt_private($password,
            $this->gensalt_private($random));
        if (strlen($hash) == 34)
            return $hash;

        # Returning '*' on error is safe here, but would _not_ be safe
        # in a crypt(3)-like function used _both_ for generating new
        # hashes and for validating passwords against existing hashes.
        return '*';
    }

    function CheckPassword($password, $stored_hash) {
        $hash = $this->crypt_private($password, $stored_hash);
        if ($hash[0] == '*')
            $hash = crypt($password, $stored_hash);

        return $hash == $stored_hash;
    }
} // Ends Class
