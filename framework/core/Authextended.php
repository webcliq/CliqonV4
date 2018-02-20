<?php
/**
 * Auth Extended Class
 *
 * All matters relating to adding Users by a Form system
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
loadFile('framework/cord/Auth.php');
class Authextended extends Auth {

	const THISCLASS = "Authextended extends Auth";
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


}