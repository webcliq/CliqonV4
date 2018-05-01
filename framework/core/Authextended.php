<?php
/**
 * Auth Extended Class
 *
 * All matters relating to adding Users who are visitors to the site and not necessarily Administrators
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

class Authextended extends Auth {

	const THISCLASS = "Authextended extends Auth";
    public $tblname = 'dbuser';
    public $cfg;

	function __construct() 
	{
        global $clq;
        global $cfg; $this->cfg = $cfg;
	}

    /** Display functions - to support User Management for front end website
     *
     * displayUsers()
     * displayLogin() - displays a Login form
     * displayRegister() - displays a Registration form
     *
     ****************************************************************************************************/

        /** Display Users  
         * @param - array - usual variables
         * @return - array - consisting of Flag (Ok or NotOk) and HTML content generated from template
         **/
         function displayUsers($vars)
         {

            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $rq = $vars['rq'];
                $idiom = $vars['idiom'];
                $table = $vars['table'];
                $tabletype = $vars['tabletype'];
                $this->cfg = $clq->get('cfg');
                $extn = $this->cfg['site']['extension'];
                $js = ""; $content = ""; $thisvars = ['rq' => $rq, 'idiom' => $idiom];

                $model = $clq->resolve('Model'); 
                $dtcfg = $model->stdModel('datatable', $table, $tabletype);   

                // Expand and Adjust
                $advsearch = "";

                // Order columns by order
                foreach($dtcfg['columns'] as $key => $config) {
                    if(array_key_exists('visible', $config) and $config['visible'] == 'false') {
                        unset($dtcfg['columns'][$key]);
                        if(!array_key_exists('order', $config)) {
                        $dtcfg['columns'][$key]['order'] = 'z';
                    }}
                };
                $dtcfg['columns'] = Q::array_orderby($dtcfg['columns'], 'order', SORT_ASC);     

                foreach($dtcfg['columns'] as $fid => $prop) {
                    $dtcfg['columns'][$fid]['title'] = Q::cStr($prop['title']);
                    array_key_exists('titleTooltip', $prop) ? $dtcfg['columns'][$fid]['titleTooltip'] = Q::cStr($prop['titleTooltip']) : null ;
                };

                // Row icons
                foreach($dtcfg['rowicons'] as $i => $icn) {
                    array_key_exists('title', $icn) ? $dtcfg['rowicons'][$i]['title'] = Q::cStr($icn['title']) : $dtcfg['rowicons'][$i]['title'] = "" ;
                    array_key_exists('formid', $icn) ? $dtcfg['rowicons'][$i]['formid'] = $icn['formid'] : $dtcfg['rowicons'][$i]['formid'] = "popupform" ;
                };

                // Format pager select
                // pageselect = '5,10,15,20,25'
                $dtcfg['pagerselect'] = [];           
                $pageselect = explode(',', $dtcfg['pageselect']);
                foreach($pageselect as $n => $v) {
                    $v = trim($v);
                    $dtcfg['pagerselect'][] = ['value' => $v, 'text' => $v];
                };
               
                // Template variables these are used and converted by the template
                unset($dtcfg['id']);
                unset($dtcfg['tableclass']);                
                $thisvars = [
                    'table' => $table,
                    'tabletype' => $tabletype,
                    'idiom' => $idiom,
                    'tblopts' => $dtcfg,
                    'dtcfg' => F::jsonEncode($dtcfg),
                    'xtrascripts' => ""
                ];  
                $tpl = "usertable.".$extn;
                $content = Q::publishTpl($tpl, $thisvars, "views/components", "cache/".$idiom);
                $clq->set('js', $js);       
                return ['flag' => 'Ok', 'msg' => $content];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'vars' => $vars,
                ];
                L::cLog($err);
                return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            } 
         }         

        /** Display Login  
         * @param - array - usual variables
         * @return - array - consisting of Flag (Ok or NotOk) and HTML content generated from template
         **/
         function displayLogin($vars)
         {

            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $rq = $vars['rq'];
                $idiom = $vars['idiom'];
                $table = $vars['table'];
                $this->cfg = $clq->get('cfg');
                $extn = $this->cfg['site']['extension'];
                $js = ""; $content = ""; $thisvars = ['rq' => $rq, 'idiom' => $idiom, 'idioms' => $clq->get('idioms'), 'viewpath' => $clq->get('rootpath').'views/'];
                $tpl = "login.".$extn;
                $content = Q::publishTpl($tpl, $thisvars, "views/components", "cache/".$idiom);
                $clq->set('js', $js);       
                return ['flag' => 'Ok', 'msg' => $content];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'vars' => $vars,
                ];
                L::cLog($err);
                return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            } 
         }

        /** Display Register  
         * @param - array - usual variables
         * @return - array - consisting of Flag (Ok or NotOk) and HTML content generated from template
         **/
         function displayRegister($vars)
         {

            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $rq = $vars['rq'];
                $idiom = $vars['idiom'];
                $table = $vars['table'];
                $this->cfg = $clq->get('cfg');
                $extn = $this->cfg['site']['extension'];
                $js = ""; $content = ""; $thisvars = ['rq' => $rq, 'idiom' => $idiom, 'idioms' => $clq->get('idioms'), 'viewpath' => $clq->get('rootpath').'views/'];

                // Data
                if(array_key_exists('recid', $rq) and $rq['recid'] > 0) {
                    $sql = "SELECT * FROM dbuser WHERE id = ?";
                    $row = R::getRow($sql, [$rq['recid']]);
                    $db = $clq->resolve('Db');
                    $usr = D::extractAndMergeRow($row);
                } else {
                    $usr = C::cfgReadFile($clq->get('basedir').'views/config/userdefault.cfg');
                };

                $tpl = "register.".$extn;
                $content = Q::publishTpl($tpl, $thisvars, "views/components", "cache/".$idiom);
                $clq->set('js', $js);       
                return ['flag' => 'Ok', 'msg' => $content, 'data' => $usr];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'vars' => $vars,
                ];
                L::cLog($err);
                return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            } 
         }

    /** User Management  
     *
     * valueExists()
     * userRegister()
     * sendUserActivate()     
     * userActivate()
     *
     * forgotPassword()
     * identifyUser()
     *
     *
     *
     *
     *
     ***************************************************************************************************/

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
                $this->cfg = $clq->get('cfg');
                $recid = $rq['id'];

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
                $recid == 0 ? $updb = R::dispense($vars['table']) : $updb = R::load($vars['table'], $recid);

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
                $updb->c_lastmodified = Q::lastMod();
                $updb->c_whomodified = Q::whoMod();
                
                $result = R::store($updb);

                if($recid == 0) {

                    // Write to log
                    L::wLog($rq, 'user', 'New User record created', 'info');  

                    // Generate an email here
                    $mail = $clq->resolve('PHPMailer');            // Passing `true` enables exceptions
                    $sendmail = $clq->resolve('Genmail');

                    $msg = Q::cStr('492:User Registration');
                    $protocol = $clq->get('protocol');
                    $url = $protocol.$this->cfg['site']['website'].'/cms/'.$clq->get('idiom').'/activate/?newuser='.$rq['c_username'];
                    $msg .= H::p([], H::a(['href' => $url], $url));
                    $msg .= H::p([], '{accountaddress}');
                    $args = [
                        'subject' => Q::cStr('492:User Registration'),
                        'msg' => $msg,
                        'mailtoaddress' => $rq['c_email'],
                        'mailtoname' => $rq['c_username']
                    ];
                    $sendmail->sendMail($args);
                    $msg = Q::cStr('497:Look for an email in your inbox with instructions to activate your Membership');
                } else {
                    $msg = Q::cStr('370:Record update successfully');
                }
                    
                // Return the result to the browser
                if($result > 0) {
                    return [
                        'flag' => "Ok",
                        'msg' => $msg
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

        /** Send or Resend User Activation  
         *
         * @param - array - arguments
         * @return - array containing Msg etc.
         **/
         function sendUserActivate($vars)  
         {
            $method = self::THISCLASS.'->'.__FUNCTION__."()";
            try {

                global $clq;
                $rq = $vars['rq'];
                $recid = $rq['recid'];
                $sql = "SELECT * FROM dbuser WHERE id = ?";
                $rawrow = R::getRow($sql, [$recid]);
                $db = $clq->resolve('Db');
                $row = D::extractAndMergeRow($rawrow);

                // Generate an email here
                $mail = $clq->resolve('PHPMailer');            // Passing `true` enables exceptions
                $sendmail = $clq->resolve('Genmail');

                $msg = Q::cStr('492:User Registration');
                $protocol = $clq->get('protocol');
                $url = $protocol.$this->cfg['site']['website'].'/cms/'.$clq->get('idiom').'/activate/?newuser='.$row['c_username'];
                $msg .= H::p([], H::a(['href' => $url], $url));
                $msg .= H::p([], '{accountaddress}');
                $args = [
                    'subject' => Q::cStr('492:User Registration'),
                    'msg' => $msg,
                    'mailtoaddress' => $row['c_email'],
                    'mailtoname' => $row['c_username']
                ];
                $sendmail->sendMail($args);    

                $updb = R::load('dbuser', $recid);

                // Store record
                $updb->c_status = 'inactive';
                $updb->c_lastmodified = Q::lastMod();
                $updb->c_whomodified = 'admin';
                $result = R::store($updb);

                // Return the result to the browser
                if(is_numeric($result) and $result > 0) {
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
            $method = self::THISCLASS.'->'.__FUNCTION__."()";
            try {

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

        /** Member has forgotten password 
         * Ask for valid email address and send link to reactivate
         * @param - array - usual variables
         * @return - string - HTML for a form
         **/
         function forgotPassword(array $vars)
         {
            try {

                $method = self::THISCLASS.'->'.__FUNCTION__."()";
                global $clq; $vue = []; $html = "";

                $html .= H::fieldset(
                    H::legend([], Q::cStr('90:Change Password')),

                    H::p([], Q::cStr('559:Instructions')),
                    // User email - c_email, first field and check available
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'c_email'], Q::cStr('95:Email address')),
                        H::input(['type' => 'email', 'v-model' => 'row.c_email', 'class' => 'form-control', 'aria-describedby' => 'c_email_help', 'placeholder' => Q::cStr('9999:yourname@yourdomain.com'), 'required' => 'true', 'autofocus' => 'true', 'id' => 'c_email']),
                        H::span(['class' => 'small orangec', 'id' => 'c_email_help'], Q::cStr('575:Enter your email address'))
                    ),
                    // Username - c_username, display email as default, check if available
                    H::div(['class' => 'form-group'],
                        H::label(['for' => 'c_username'], Q::cStr('1:User name')),
                        H::input(['type' => 'text', 'v-model' => 'row.c_username', 'class' => 'form-control', 'aria-describedby' => 'c_username_help', 'placeholder' => Q::cStr('1:User name'), 'required' => 'true', 'id' => 'c_username']),
                        H::span(['class' => 'small orangec', 'id' => 'c_username_help'], Q::cStr('529:Please enter your user name'))
                    ),
                    H::div(['class' => 'form-group'],
                        H::button(['type' => 'button', 'v-on:click' => 'submitbutton', 'class' => 'btn btn-primary', 'data-action' => ''], Q::cStr('105:Submit')),
                        H::button(['type' => 'button', 'v-on:click' => 'resetbutton', 'class' => 'btn btn-danger', 'data-action' => ''], Q::cStr('122:Reset'))
                    )
                    // H::div(['class' => 'form-group'],'{{$data}}')
                );
                $formdata = [
                    'c_email' => 'fredo@esporles.net',
                    'c_username' => 'fredo'
                ];

                $vue['el'] = "#dataform";
                $vue['data'] = $formdata;
                return [
                    'flag' => "Ok",
                    'html' => H::form(['id' => 'dataform', 'class' => '', 'name' => 'dataform', 'action' => '', 'method' => 'POST'], $html),
                    'data' => $vue
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

            $method = self::THISCLASS.'->'.__FUNCTION__."()"; 
            try {

                global $clq; $html = ""; $rq = $vars['rq'];

                if(array_key_exists('id', $rq)) {
                    $id = $rq['id'];
                } else {
                    $bean = R::findOne($vars['table'], 'WHERE c_email = ? AND c_username = ?', [$rq['c_email'], $rq['c_username']]);
                    is_object($bean) ? $id = $bean->id : $id = 0;                    
                }

                if($id > 0) {
                    $type = 'info';
                    $txt = H::form(['id' => 'passwordform', 'class' => '', 'name' => 'passwordform', 'action' => '', 'method' => 'POST'], 
                        // Password - c_password - two fields with confirm
                        H::input(['type' => 'hidden', 'name' => 'id', 'value' => $id]),
                        H::div(['class' => 'form-group'],
                            H::label(['for' => 'c_password', 'class' => 'h5 text-left'], Q::cStr('2:Password')),
                            H::div(['class' => 'form-inline'],
                                H::input(['type' => 'password', 'class' => 'form-control col-5', 'placeholder' => Q::cStr('2:Password'), 'required' => 'true', 'name' => 'c_password_confirm']),
                                H::input(['type' => 'password', 'name' => 'c_password', 'class' => 'form-control col-6 ml10', 'aria-describedby' => 'c_password_help', 'placeholder' => Q::cStr('2:Password'), 'required' => 'true', 'id' => 'c_password'])
                            ),
                            H::span(['class' => 'small text-left', 'id' => 'c_password_help'], Q::cStr('571:Enter a secure password of at least 8 characters including, letters, numbers and symbols. Please confirm it.'))
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
                    H::div(['class' => 'noty_text pad']),
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
                    'msg' => $response
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
           

        /** Delete User  
         * Delete User from Database
         * @param - array - usual variables
         * @return - string - HTML for a form
         **/
         function doDeleteUser(array $vars)
         {
            $method = self::THISCLASS.'->'.__FUNCTION__."()";
            try {
                
                global $clq; $rq = $vars['rq'];

                $sql = "DELETE FROM ".$vars['table']." WHERE ".$rq['recid']." = ?";
                $result = R::exec($sql, [$rq['recid']]);

                if(is_numeric($result) and $result > 0) {
                    return [
                        'flag' => "Ok",
                        'msg' => Q::cStr('572:Record removed')  
                    ];
                } else {
                    return [
                        'flag' => "NotOk",
                        'msg' => Q::cStr('573:Record NOT removed')  
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
                    'msg' => $e->getMessage()
                ]; 
            }             
         }

        /** View User  
         * Display a User Profile
         * @param - array - usual variables
         * @return - string - HTML for a form
         **/
         function userProfile(array $vars)
         {
            $method = self::THISCLASS.'->'.__FUNCTION__.'()';
            try {

                global $clq;
                $rq = $vars['rq'];
                $idiom = $vars['idiom'];
                $table = $vars['table'];
                $this->cfg = $clq->get('cfg');
                $extn = $this->cfg['site']['extension'];
                $js = ""; $content = ""; $thisvars = ['rq' => $rq, 'idiom' => $idiom];

                // Data
                $sql = "SELECT * FROM dbuser WHERE id = ?";
                $row = R::getRow($sql, [$rq['recid']]);
                $db = $clq->resolve('Db');
                $usr = D::extractAndMergeRow($row);

                $tpl = "profile.".$extn;
                $content = Q::publishTpl($tpl, $thisvars, "views/components", "cache/".$idiom);
                $clq->set('js', $js);       
                return ['flag' => 'Ok', 'msg' => $content, 'data' => $usr];

            } catch(Exception $e) {
                $err = [
                    'method' => $method,
                    'errmsg' => $e->getMessage(),
                    'vars' => $vars,
                ];
                L::cLog($err);
                return ['flag' => 'NotOk', 'msg' => $e->getMessage()];
            }          
         }

        /** Display Change Status formlet  
         * Create data for Change Status popup
         * @param - array - usual variables
         * @return - string - HTML for a form
         **/
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

        /** Do the change of Status  
         * Undertake the change of Status
         * @param - array - usual variables
         * @return - string - HTML for a form
         **/
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