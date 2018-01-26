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
class Genmail extends PHPMailer
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
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            $method = self::THISCLASS.'->'.__FUNCTION__."()";
            global $clq; 

	        //Server settings
	        $mail->SMTPDebug = 0;                               // Enable verbose debug output 0 - 3
	        $mail->isSMTP();                                    // Set mailer to use SMTP
	        $mail->Host = $this->cfg['mail']['hostname'];       // Specify main and backup SMTP servers
	        $mail->SMTPAutoTLS = true;
	        $mail->SMTPAuth = $this->cfg['mail']['smtpauth'];   // Enable SMTP authentication
	        $mail->AuthType = $this->cfg['mail']['authtype'];
	        $mail->Username = $this->cfg['mail']['username'];   // SMTP username
	        $mail->Password = $this->cfg['mail']['password'];   // SMTP password
	        $mail->SMTPSecure = $this->cfg['mail']['security']; // Enable TLS encryption, `ssl` also accepted
	        $mail->Port = $this->cfg['mail']['port'];           // TCP port to connect to

	        //Recipients
	        $mail->setFrom($this->cfg['mail']['mailfrom'], $this->cfg['mail']['mailfromname']);
	        $mail->addAddress($args['mailtoaddress'], $args['mailtoname']);     // Add a recipient
	        $mail->addReplyTo($this->cfg['mail']['mailreplyto'], $this->cfg['mail']['mailreplytoname']);
	        $mail->addBCC($this->cfg['mail']['altmailto'], $this->cfg['mail']['altmailtoname']);

	        //Content
	        $mail->isHTML(true);                                  // Set email format to HTML
	        $mail->Subject = $args['subject'];
	        $mail->Body = $args['msg'];
	        $mail->send();

            if($mail->ErrorInfo != '') {
                throw new Exception(Q::cStr('493:Message could not be sent').$mail->ErrorInfo);
            } 

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
}

