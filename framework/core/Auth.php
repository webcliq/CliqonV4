<?php
/**
 * Auth Class
 *
 * All matters relating to Admin Users
 *
 * @category   Web application framework
 * @package    Flight
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

    /** User Management - Forms
     *
     * publishNewUserForm()
     * valueExists()
     * userRegister()
     * userActivate()
     * resendActivation()
     *
     * forgotPassword()
     * identifyUser()
     *
     ***************************************************************************************************/

        /** New User Form
         * This is a reduced version of a User Form. User is expected to modify their Profikle at a later stage.
         * @param - array - usual variables
         * @return - string - HTML for a form
         **/
        function publishNewUserForm(array $vars)
        {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $vue = []; $html = ""; $rq = $vars['rq'];

                $html .= H::fieldset(
                    H::legend([], Q::cStr('496:Partner Name')),
                    // Hidden
                    H::input(['type' => 'hidden', 'v-model' => 'c_type']),
                    H::input(['type' => 'hidden', 'v-model' => 'c_group']),
                    H::input(['type' => 'hidden', 'v-model' => 'c_level']),
                    H::input(['type' => 'hidden', 'v-model' => 'c_status']),
                    // Visible

                    // User email - c_email, first field and check available
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'c_email'], Q::uStr('39:Email address')),
                        H::input(['type' => 'email', 'v-model' => 'c_email', 'class' => 'form-control', 'aria-describedby' => 'c_email_help', 'placeholder' => Q::uStr('72:yourname@yourdomain.com'), 'required' => 'true', 'autofocus' => 'true', 'id' => 'c_email', 'data-action' => 'emailexists']),
                        H::span(['class' => 'small orangec', 'id' => 'c_email_help'], Q::uStr('68:Enter your email address'))
                    ),
                    // Username - c_username, display email as default, check if available
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'c_username'], Q::uStr('62:User name')),
                        H::input(['type' => 'text', 'v-model' => 'c_username', 'class' => 'form-control', 'aria-describedby' => 'c_username_help', 'placeholder' => Q::uStr('62:User name'), 'required' => 'true', 'id' => 'c_username', 'data-action' => 'usernameexists']),
                        H::span(['class' => 'small orangec', 'id' => 'c_username_help'], Q::uStr('69:Please enter a unique user name'))
                    ),
                    // Company Name - d_company - check if already used
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'd_company'], Q::uStr('63:Company name')),
                        H::input(['type' => 'text', 'v-model' => 'd_company', 'class' => 'form-control col-8', 'aria-describedby' => 'd_company_help', 'placeholder' => Q::uStr('63:Company name'), 'id' => 'd_company']),
                        H::span(['class' => 'small orangec', 'id' => ''], Q::uStr('70:If you belong to a Company, please enter the name'))
                    ),
                    // Password - c_password - two fields with confirm
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'c_password'], Q::uStr('64:Password')),
                        H::div(['class' => 'form-inline'],
                            H::input(['type' => 'password', 'class' => 'form-control col-5', 'placeholder' => Q::uStr('64:Password'), 'required' => 'true', 'id' => 'c_password_confirm']),
                            H::input(['type' => 'password', 'v-model' => 'c_password', 'class' => 'form-control col-6 ml10', 'aria-describedby' => 'c_password_help', 'placeholder' => Q::uStr('64:Password'), 'required' => 'true', 'id' => 'c_password'])
                        ),
                        H::span(['class' => 'small orangec', 'id' => 'c_password_help'], Q::uStr('71:Enter a secure password of at least 8 characters including, letters, numbers and symbols. Please confirm it.'))
                    ),
                    H::div(['class' => 'form-group'],
                        H::button(['type' => 'button', 'v-on:click' => 'submitbutton', 'class' => 'btn btn-primary', 'data-action' => ''], Q::uStr('65:Submit')),
                        H::button(['type' => 'button', 'v-on:click' => 'previewbutton', 'class' => 'btn btn-warning', 'data-action' => ''], Q::uStr('66:Preview')),
                        H::button(['type' => 'button', 'v-on:click' => 'resetbutton', 'class' => 'btn btn-danger', 'data-action' => ''], Q::uStr('67:Reset'))
                    )
                    // H::div(['class' => 'form-group'],'{{$data}}')
                );

                $formdata = [
                    'c_type' => $rq['c_type'],
                    'c_group' => $rq['c_group'],
                    'c_level' => $rq['c_level'],
                    'c_status' => $rq['c_status'],
                    'c_email' => '',
                    'c_username' => '',
                    'c_password' => '',
                    'd_company' => ''

                ];

                $vue['el'] = "#dataform";
                $vue['data'] = $formdata;
                return [
                    'flag' => "Ok",
                    'html' => H::form(['id' => 'dataform', 'class' => '', 'name' => 'dataform', 'action' => '', 'method' => 'POST'], $html),
                    'data' => object_encode($vue),
                    'mounted' => ''
                ];
            
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

        /** Checks if a value exists in dbuser such as c_email or c_username
         * @param - array - usual variables
         * @return - array - Flag and Message
         **/       
        function valueExists($vars)
        {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $rq = $vars['rq'];

                $sql = "SELECT id FROM ".$vars['table']." WHERE ".$rq['fldname']." LIKE ?";
                $cell = R::getCell($sql, ['%'.$rq['check'].'%']);

                if($cell > 0) {
                    return [
                        'flag' => "NotOk",
                        'msg' => Q::cStr('491:This value already exists')  
                    ];
                } else {
                    return [
                        'flag' => "Ok"
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

        /** Registers the new User with a short form record
         *
         * @param - array - usual variables
         * @return - array - Flag and Message
         **/
        function userRegister($vars)
        {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $rq = $vars['rq'];

                // Walk through all the values in $rq
                foreach($rq as $key => $value) {    
                    $chk = strtolower(substr($key, 0, 1));  
                    switch($chk) {
                        case "c": $rqc[$key] = $value; break;
                        case "d": $rqd[$key] = $value; break;   
                        case "a": case "i": case "x": case "t": false; break; // throws token, ajaxbuster, id and x away
                        default: throw new Exception("Request key had no usable starting letter! - ".$chk." - ".$key);
                    }
                };

                // Create a Database instance  
                $updb = R::findOne($vars['table'], 'c_username = ?', [$rq['c_username']]);

                // C_ fields
                foreach($rqc as $fldc => $valc) {
                    if($fldc == 'c_password') {
                        $hasher = new PasswordHash(8, false);
                        $updb->$fldc = $hasher->HashPassword($rq['c_password']);
                    } else {
                        $updb->$fldc = $valc;
                    }
                }

                // D_ field(s)
                $doc = [];
                foreach($rqd as $fldd => $vald) {
                    $doc[$fldd] = $vald;
                }
                $updb->c_document = json_encode($doc);

                // Store record
                $result = R::store($updb);

                // Write to log
                L::wLog($rq, 'user', 'New Partner record created', 'info');

                // Generate an email here
                $mail = $clq->resolve('PHPMailer');            // Passing `true` enables exceptions
                $sendmail = $clq->resolve('Genmail');
                $msg = Q::uTxt('partner_registration');
                $msg .= H::p(
                    H::a(['href' => 'http://cliqon.com/page/'.$clq->get('idiom').'/activate/?newuser='.$rq['c_username']], 'http://cliqon.com/page/'.$clq->get('idiom').'/activate/?newuser='.$rq['c_username'])
                );
                $msg .= H::p([], '{accountaddress}');
                $args = [
                    'subject' => Q::cStr('492:User Registration'),
                    'msg' => $msg,
                    'addemail' => $rq['c_email'],
                    'addname' => $rq['c_username']
                ];
                $sendmail->sendMail($args);

                // Return the result to the browser
                if($result > 0) {
                    return [
                        'flag' => "Ok",
                        'msg' => Q::cStr('497:Look for an email in your inbox with instructions to activate your Membership')  
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
                    'method' => $method,
                ];
                L::cLog($err);
                return [
                    'flag' => "NotOk",
                    'html' => $err
                ]; 
            } 
        }

        /** Activate user registration
         * clq.red/page/en/activate/dbuser//?newuser=markrichards
         * @param - array - arguments
         * @return - array containing Msg etc.
         **/
        function userActivate($rq)
        {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq;

                $bean = R::findOne('dbuser', 'c_username = ?', [$rq['newuser']]);
                if(!$bean->c_username) {
                    throw new Exception(Q::cStr('499:The reference was not found in the database'.$bean->c_username));
                };
                $updb = R::load('dbuser', $bean->id);

                $updb->c_status = 'active';
                $updb->c_notes = 'User record activated on '.Q::lastMod();

                $result = R::store($updb);

                // Return the result to the browser
                if($result > 0) {
                    return Q::cStr('498:Your account was successfully activated. You may now login.');
                } else {
                    return Q::cStr('495:Record was not successfully written to database').': '.$result;
                }
            
            } catch (Exception $e) {
                $err = [
                    'errmsg' => $e->getMessage(),
                    'method' => $method,
                ];
                L::cLog($err);
                return [
                    'flag' => "NotOk",
                    'html' => $err
                ]; 
            }                 
        }  

        /** Activate user registration
         *
         * @param - array - arguments
         * @return - array containing Msg etc.
         **/
        function resendActivation($vars)  
        {

        }    

        /** Member has forgotten password
         * Ask for valid email address and send link to reactivate
         * @param - array - usual variables
         * @return - string - HTML for a form
         **/
        function forgotPassword(array $vars)
        {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $vue = []; $html = ""; $rq = $vars['rq'];

                $html .= H::fieldset(
                    H::legend([], Q::cStr('61:Forgot Password')),
                    // Hidden
                    H::input(['type' => 'hidden', 'v-model' => 'c_type']),
                    H::input(['type' => 'hidden', 'v-model' => 'c_group']),

                    H::p([], Q::uStr('74:Instructions')),
                    // User email - c_email, first field and check available
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'c_email'], Q::uStr('39:Email address')),
                        H::input(['type' => 'email', 'v-model' => 'c_email', 'class' => 'form-control', 'aria-describedby' => 'c_email_help', 'placeholder' => Q::uStr('72:yourname@yourdomain.com'), 'required' => 'true', 'autofocus' => 'true', 'id' => 'c_email']),
                        H::span(['class' => 'small orangec', 'id' => 'c_email_help'], Q::uStr('68:Enter your email address'))
                    ),
                    // Username - c_username, display email as default, check if available
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'c_username'], Q::uStr('62:User name')),
                        H::input(['type' => 'text', 'v-model' => 'c_username', 'class' => 'form-control', 'aria-describedby' => 'c_username_help', 'placeholder' => Q::uStr('62:User name'), 'required' => 'true', 'id' => 'c_username']),
                        H::span(['class' => 'small orangec', 'id' => 'c_username_help'], Q::uStr('73:Please enter your user name'))
                    ),
                    H::div(['class' => 'form-group'],
                        H::button(['type' => 'button', 'v-on:click' => 'submitbutton', 'class' => 'btn btn-primary', 'data-action' => ''], Q::uStr('65:Submit')),
                        H::button(['type' => 'button', 'v-on:click' => 'resetbutton', 'class' => 'btn btn-danger', 'data-action' => ''], Q::uStr('67:Reset'))
                    )
                    // H::div(['class' => 'form-group'],'{{$data}}')
                );
                $formdata = [
                    'c_type' => $rq['c_type'],
                    'c_group' => $rq['c_group'],
                    'c_email' => 'conkascom@outlook.com',
                    'c_username' => 'markrichards'
                ];

                $vue['el'] = "#dataform";
                $vue['data'] = $formdata;
                return [
                    'flag' => "Ok",
                    'html' => H::form(['id' => 'dataform', 'class' => '', 'name' => 'dataform', 'action' => '', 'method' => 'POST'], $html),
                    'data' => object_encode($vue),
                    'mounted' => ''
                ];
            
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

        /** Member has responded to forgotten password form
         * Check if we can identify the user, if so, let them change their password
         * @param - array - usual variables
         * @return - string - HTML for a message to be displayed in a Noty popup, so they can change their password
         **/
        function identifyUser(array $vars)
        {

            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $html = ""; $rq = $vars['rq'];

                if(array_key_exists('id', $rq)) {
                    $id = $rq['id'];
                } else {
                    $bean = R::findOne($vars['table'], 'WHERE c_type = ? AND c_email = ? AND c_username = ?', [$rq['c_type'], $rq['c_email'], $rq['c_username']]);
                    is_object($bean) ? $id = $bean->id : $id = 0;                    
                }

                if($id > 0) {
                    $type = 'info';
                    $txt = H::form(['id' => 'passwordform', 'class' => '', 'name' => 'passwordform', 'action' => '', 'method' => 'POST'], 
                        // Password - c_password - two fields with confirm
                        H::input(['type' => 'hidden', 'name' => 'id', 'value' => $id]),
                        H::div(['class' => 'form-group'],
                            H::label(['for' => 'c_password', 'class' => 'bold text-left'], Q::uStr('64:Password')),
                            H::div(['class' => 'form-inline'],
                                H::input(['type' => 'password', 'class' => 'form-control col-5', 'placeholder' => Q::uStr('64:Password'), 'required' => 'true', 'name' => 'c_password_confirm']),
                                H::input(['type' => 'password', 'name' => 'c_password', 'class' => 'form-control col-6 ml10', 'aria-describedby' => 'c_password_help', 'placeholder' => Q::uStr('64:Password'), 'required' => 'true', 'id' => 'c_password'])
                            ),
                            H::span(['class' => 'small orangec text-left', 'id' => 'c_password_help'], Q::uStr('71:Enter a secure password of at least 8 characters including, letters, numbers and symbols. Please confirm it.'))
                        )
                    );
                    $btns = [
                        [
                            'addClass' => 'm10 mt0 btn btn-primary btn-sm submitnoty',
                            'text' => Q::cStr('105:Submit')
                        ],
                        [
                            'addClass' => 'm10 mt0 btn btn-danger btn-sm closenoty',
                            'text' => Q::cStr('502:Close')
                        ]
                    ];
                } else {
                    $type = 'error';
                    $txt = H::h5(['class' => ''], Q::cStr('500:User details not found'));
                    $btns = [
                        [
                            'addClass' => 'm10 mt0 btn btn-danger btn-sm closenoty',
                            'text' => Q::cStr('502:Close')
                        ]
                    ];
                };

                $html = H::div(['class' => 'noty_message minh5'],
                    H::div(['class' => 'noty_text']),
                    H::div(['class' => 'noty_close'])
                );

                $response = [
                    'template' => $html,
                    'timeout' => false,
                    'type' => $type,
                    'buttons' => $btns,
                    'text' => $txt,
                    'closeWith' => ['button']
                ];

                return [
                    'flag' => "Ok",
                    'msg' => object_encode($response)
                ];

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

        function userProfile(array $vars)
        {
            
        }

        function changeStatus(array $vars)
        {

            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $html = ""; $rq = $vars['rq']; $id = $rq['id'];

                $html = H::div(['class' => 'noty_message minh5'],
                    H::div(['class' => 'noty_text']),
                    H::div(['class' => 'noty_close'])
                );

                $bean = R::load($vars['table'], $id);
                if($bean->c_status == "active") {
                    $sel = H::label(['class' => 'form-check-label'], 
                        H::input(['type' => 'radio', 'id' => 'c_status', 'value' => 'active', 'class' => 'form-check-input', 'name' => 'c_status', 'checked' => 'true']),
                        Q::cStr('9999:Active')
                    );
                    $selin = H::label(['class' => 'form-check-label'],
                        H::input(['type' => 'radio', 'value' => 'inactive', 'class' => 'form-check-input', 'name' => 'c_status']),
                        Q::cStr('9999:Inactive')
                    );
                } else {
                    $selin = H::label(['class' => 'form-check-label'], 
                        H::input(['type' => 'radio', 'id' => 'c_status', 'value' => 'inactive', 'class' => 'form-check-input', 'name' => 'c_status', 'checked' => 'true']),
                        Q::cStr('9999:Inactive')
                    );
                    $sel = H::label(['class' => 'form-check-label'],
                        H::input(['type' => 'radio', 'value' => 'active', 'class' => 'form-check-input', 'name' => 'c_status']),
                        Q::cStr('9999:Inactive')
                    );
                }

                $txt = H::form(['id' => 'statusform', 'class' => '', 'name' => 'statusform', 'action' => '', 'method' => 'POST'], 
                    // Password - c_password - two fields with confirm
                    H::input(['type' => 'hidden', 'name' => 'id', 'value' => $id]),
                    H::div(['class' => 'form-group pad'], 
                        H::h5(['class' => 'mt10'], Q::cStr('501:Change status').' - '.$id), 
                        H::div(['class' => 'form-check-inline'], $sel), 
                        H::div(['class' => 'form-check-inline'], $selin)
                    )
                );
                $btns = [
                    [
                        'addClass' => 'm10 mt0 btn btn-primary btn-sm submitnoty',
                        'text' => Q::cStr('105:Submit')
                    ],
                    [
                        'addClass' => 'm10 mt0 btn btn-danger btn-sm closenoty',
                        'text' => Q::cStr('502:Close')
                    ]
                ];              

                $response = [
                    'template' => $html,
                    'timeout' => false,
                    'type' => 'success',
                    'buttons' => $btns,
                    'text' => $txt,
                    'closeWith' => ['button']
                ];

                return [
                    'flag' => "Ok",
                    'msg' => object_encode($response)
                ];

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

        function doChangeStatus(array $vars)
        {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $html = ""; $rq = $vars['rq']; $id = $rq['id'];

                $updb = R::load($vars['table'], $id);
                $updb->c_status = $rq['c_status'];
                $updb->c_lastmodified = Q::lastMod();
                $updb->c_whomodified = Q::whoMod();
                $updb->c_notes .= Q::cAddNotes('c_status', $rq['c_status']);
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

    /** User Management - CRUD
     * creatUserRecord()
     * updateUserRecord()
     * deleteUserRecord()
     * changeUserPassword()
     * changeUserElement()
     *
     * createUser() - quick user create
     *
     *************************************  Basic User Management  *************************************************/

        /**
         * Create new user record via registration or direct input
         * user status is inactive
         * @var array post
         * @return string Ok or notOk plus message
         **/
        function createUserRecord($rq) 
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

                if(is_numeric($result)) {
                    return "Ok";
                } else {
                    return "NotOk: ".$result;
                }

            } catch(Exception $e) {
                return "NotOk: ".$e->getMessage();
            }   
        }

        /**
         * Update User
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

        /**
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

        /**
         * Change User Password
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

        /**
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

        /** Create active user
         * @param - array of user form details
         *
         **/
        function createUser($usr)
        {
           try {
                
                $hasher = new PasswordHash(8, false);
                $pwd = $hasher->HashPassword($usr['password']);

                // c_document -> $doc fields first
                $doc = [
                    'd_firstname' => self::setDefault('firstname', '', $usr),                
                    'd_midname' => self::setDefault('midname', '', $usr),
                    'd_lastname' => self::setDefault('lastname', '', $usr),
                    'd_langcd' => self::setDefault('langcd', 'en', $usr),
                    'd_avatar' => self::setDefault('avatar', '', $usr),
                    'd_comments' => self::setDefault('comments', 'No comments', $usr),
                ];            

                $userarray = [
                    'c_group' => self::setDefault('group', 'admin', $usr),
                    'c_username' => self::setDefault('username', '', $usr),
                    'c_password' => $pwd,
                    'c_level' => self::setDefault('level', '60:60:60', $usr),
                    'c_status' => self::setDefault('status', 'active', $usr),
                    'c_document' => json_encode($doc),
                    'c_email' => self::setDefault('email', '', $usr),
                    'c_lastmodified' => Q::lastMod(),
                    'c_whomodified' => 'installer',
                    'c_notes' => self::setDefault('notes', 'No additional notes', $usr),
                ];

                $updb = R::dispense('dbuser');
                foreach($userarray as $key => $val) {
                    $updb->$key = $val;
                }
                $result = R::store($updb);

                if(is_numeric($result)) {
                    return "Ok";
                } else {
                    return "NotOk";
                }

            } catch(Exception $e) {
                return $e->getMessage();
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
                // Call to Database for Username and Password
                $sql = "SELECT * FROM dbuser WHERE c_username = ? AND c_status = ?";
                $row = R::getRow($sql, [$rq['username'], 'active']);

                if($row) {
                    $hasher = new PasswordHash(8, false);
                    $check = $hasher->CheckPassword($rq['password'], $row['c_password']); // Input, Database
                    
                    if($check) {

                        $_SESSION['FullName'] = self::getUserFullName($row, false);
                        $_SESSION['UserName'] = $row['c_username'];
                        $_SESSION['Group'] = $row['c_group'];
                        $_SESSION['UserID'] = $row['id'];
                        $_SESSION['UserEmail'] = $row['c_email'];
                        $_SESSION['UserLevel'] = $row['c_level'];
                        $_SESSION['UserGroup'] = $row['c_group'];
                        $_SESSION['UserLanguage'] = $rq['langcd'];

                    } 
                                    
                } else {

                    $users = $clq->get('cfg')['site']['users'];
                    
                    $check = false;

                    foreach($users as $id => $user) {
                        // Does User exist in Config file ??
                        if($rq['username'] == $user['c_username']) {
                            if($user['c_password'] == $rq['password']) {
                                $check = true;
                                $row = $user;
                                $row['id'] = $id;
                                if($check) {

                                    $_SESSION['FullName'] = self::getUserFullName($row, false);
                                    $_SESSION['UserName'] = $row['c_username'];
                                    $_SESSION['Group'] = $row['c_group'];
                                    $_SESSION['UserID'] = $row['id'];
                                    $_SESSION['UserEmail'] = $row['c_email'];
                                    $_SESSION['UserLevel'] = $row['c_level'];
                                    $_SESSION['UserGroup'] = $row['c_group'];
                                    $_SESSION['UserLanguage'] = $rq['langcd'];

                                }                        
                                break;
                            } else {
                                break;
                            }
                            
                        }
                        
                    } // End foreach $users
                } // End if Row

                if($check) {

                    $clq->set('lcd', $rq['langcd']); 
                    $_SESSION['CliqonAdminUser'] = $_SESSION['UserName'];
                    Z::zset('Langcd', $rq['langcd']); // 2 hours
                    Z::zset('UserName', $_SESSION['UserName']); // 2 hours
                    Z::zset('UserID', $_SESSION['UserID']); // 2 hours
                    return ['flag' => 'Ok', 'msg' => ''];

                } else { 
                    return ['flag' => 'NotOk', 'msg' => Q::cStr('364:Login failed')];
                }  

            } catch(Exception $e) {
                return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            }
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

    /** User Session Management and Tokens
     * 
     * 
     * 
     *
     *************************************  User Session Management  *************************************************/       

        public static function __setTokenHdr($key)
        {
            session_start();
            $token_value = md5(sha1(time() + 5) . base64_encode($key) . sha1(time() - 5));
            header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');
            header('X-CSRF-Token: ' . $token_value);
            header('Authorization: Bearer ' . $token_value);
            $_SESSION['CSRF_TOKEN'] = $token_value;
            if(!isset($token_value)){
                header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                return new \Exception('401 Unauthorized');
            }else{
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                return print json_encode(array('token' => $_SESSION['CSRF_TOKEN']), JSON_PRETTY_PRINT) . '\n';
            }
        }
       
        public static function __getTokenHdr()
        {
            session_start();
            header("Access-Control-Allow-Origin: *");
            header('Content-Type: application/json');
            header('X-CSRF-Token: ' . $_SESSION['CSRF_TOKEN']);
            header('Authorization: Bearer ' . $token_value);
            if(!isset($_SESSION['CSRF_TOKEN'])){
                header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                return new \Exception('401 Unauthorized');
            }else{
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                return print json_encode(array('token' => $_SESSION['CSRF_TOKEN']), JSON_PRETTY_PRINT) . '\n';
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
                if($row['d_langcd'] == 'en') {
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
