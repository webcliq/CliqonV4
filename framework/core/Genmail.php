<?php
/**
 * Generic Mail Class
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Genmail
{
	const THISCLASS = "Genmail extends PHPMailer";
	private static $idioms;
    public $tblname = 'dbuser';
    public $cfg;

	function __construct() 
	{
		global $clq;
		global $cfg;
		self::$idioms = $cfg['site']['idioms'];
	}

	function sendMail($args)
	{
        $method = self::THISCLASS.'->'.__FUNCTION__."()";                          
        try {
            global $clq; 
            $this->cfg = $clq->get('cfg');
            $mcfg = $this->cfg['mail'];

            $phpmail = $clq->resolve('PHPMailer');
            $smtp = $clq->resolve('SMTP');
            $mail = new PHPMailer(true); // Passing `true` enables exceptions

	        //Server settings
	        $mail->SMTPDebug = 0;                               // Enable verbose debug output 0 - 3
	        $mail->isSMTP();                                    // Set mailer to use SMTP
	        $mail->Host = $mcfg['hostname'];       // Specify main and backup SMTP servers
	        $mail->SMTPAutoTLS = true;
	        $mail->SMTPAuth = $mcfg['smtpauth'];   // Enable SMTP authentication
	        $mail->AuthType = $mcfg['authtype'];
	        $mail->Username = $mcfg['username'];   // SMTP username
	        $mail->Password = $mcfg['password'];   // SMTP password
	        $mail->SMTPSecure = $mcfg['security']; // Enable TLS encryption, `ssl` also accepted
	        $mail->Port = $mcfg['port'];           // TCP port to connect to

	        //Recipients
	        $mail->setFrom($mcfg['mailfrom'], $mcfg['mailfromname']);
	        $mail->addAddress($args['mailtoaddress'], $args['mailtoname']);     // Add a recipient
	        $mail->addReplyTo($mcfg['mailreplyto'], $mcfg['mailreplytoname']);
            if($mcfg['altmailto'] != '') {
                $mail->addBCC($mcfg['altmailto'], $mcfg['altmailtoname']);
            }

	        //Content
	        $mail->isHTML(true);                                  // Set email format to HTML
	        $mail->Subject = $args['subject'];
	        $mail->Body = $args['msg'];
	        $mail->send();

            if($mail->ErrorInfo != '') {
                throw new Exception(Q::cStr('493:Message could not be sent').$mail->ErrorInfo);
            } 

            return true;
        
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

	function diagnoseEmail($vars)
	{

        $method = self::THISCLASS.'->'.__FUNCTION__."()";                          
        try {
            
            global $clq; global $mmsg;
            $this->cfg = $clq->get('cfg');
            $mcfg = $this->cfg['mail'];
            $rq = $vars['rq'];     
     
            $phpmail = $clq->resolve('PHPMailer');
            $smtp = $clq->resolve('SMTP');
            $mail = new PHPMailer(true); // Passing `true` enables exceptions

            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->SMTPDebug = 2;                               // Enable verbose debug output 0 - 3
            $mail->Debugoutput = function($str, $level) {$GLOBALS['mmsg'] .= "$level: $str\n";};
            
            $mail->isSMTP();                                    // Set mailer to use SMTP
            $mail->Host = $mcfg['hostname'];       // Specify main and backup SMTP servers
            $mail->SMTPAutoTLS = true;
            $mail->SMTPAuth = $mcfg['smtpauth'];   // Enable SMTP authentication
            $mail->AuthType = $mcfg['authtype'];
            $mail->Username = $mcfg['username'];   // SMTP username
            $mail->Password = $mcfg['password'];   // SMTP password
            $mail->SMTPSecure = $mcfg['security']; // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $mcfg['port'];           // TCP port to connect to

            //Recipients
            $mail->setFrom($mcfg['mailfrom'], $mcfg['mailfromname']);
            $mail->addAddress($rq['mailto'], $rq['mailtoname']);     // Add a recipient
            $mail->addAddress($rq['mailreplyto'], $rq['mailreplytoname']);
            $mail->addReplyTo($rq['mailreplyto'], $rq['mailreplytoname']);
            if($mcfg['altmailto'] != '') {
                $mail->addBCC($mcfg['altmailto'], $mcfg['altmailtoname']);
            }
            
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $rq['subject'];
            $mail->Body = $rq['message'];
            $mail->send();

            if($mail->ErrorInfo != '') {
                throw new Exception(Q::cStr('493:Message could not be sent').$mail->ErrorInfo);
            }

            return [$mmsg];
        } catch (Exception $e) {
            $mmsg = $e->getMessage();
            return [$mmsg];
        }              
	}
}

