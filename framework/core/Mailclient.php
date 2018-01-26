<?php
/**
 * This library is a wrapper around the Imap library functions included in php. This class in particular manages a
 * connection to the server (imap, pop, etc) and allows for the easy retrieval of stored messages.
 *
 * @package Fetch
 * @author  Robert Hafner <tedivm@tedivm.com>
 */
class Mailclient
{
    /**
     * When SSL isn't compiled into PHP we need to make some adjustments to prevent soul crushing annoyances.
     *
     * @var bool
     */
    public static $sslEnable = true;

    /**
     * These are the flags that depend on ssl support being compiled into imap.
     *
     * @var array
     */
    public static $sslFlags = array('ssl', 'validate-cert', 'novalidate-cert', 'tls', 'notls');

    /**
     * This is used to prevent the class from putting up conflicting tags. Both directions- key to value, value to key-
     * are checked, so if "novalidate-cert" is passed then "validate-cert" is removed, and vice-versa.
     *
     * @var array
     */
    public static $exclusiveFlags = array('validate-cert' => 'novalidate-cert', 'tls' => 'notls');

    /**
     * This is the domain or server path the class is connecting to.
     *
     * @var string
     */
    protected $serverPath;

    /**
     * This is the name of the current mailbox the connection is using.
     *
     * @var string
     */
    protected $mailbox = '';

    /**
     * This is the username used to connect to the server.
     *
     * @var string
     */
    protected $username;

    /**
     * This is the password used to connect to the server.
     *
     * @var string
     */
    protected $password;

    /**
     * This is an array of flags that modify how the class connects to the server. Examples include "ssl" to enforce a
     * secure connection or "novalidate-cert" to allow for self-signed certificates.
     *
     * @link http://us.php.net/manual/en/function.imap-open.php
     * @var array
     */
    protected $flags = array();

    /**
     * This is the port used to connect to the server
     *
     * @var int
     */
    protected $port;

    /**
     * This is the set of options, represented by a bitmask, to be passed to the server during connection.
     *
     * @var int
     */
    protected $options = 0;

    /**
     * This is the set of connection parameters
     *
     * @var array
     */
    protected $params = array();

    /**
     * This is the resource connection to the server. It is required by a number of imap based functions to specify how
     * to connect.
     *
     * @var resource
     */
    protected $imapStream;

    /**
     * This is the name of the service currently being used. Imap is the default, although pop3 and nntp are also
     * options
     *
     * @var string
     */
    protected $service = 'imap';

    /**
     * This constructor takes the location and service thats trying to be connected to as its arguments.
     *
     * @param string      $serverPath
     * @param null|int    $port
     * @param null|string $service
     */
    public function __construct($serverPath, $port = 143, $service = 'imap')
    {
        $this->serverPath = $serverPath;

        $this->port = $port;

        switch ($port) {
            case 143:
                $this->setFlag('novalidate-cert');
                break;

            case 993:
                $this->setFlag('ssl');
                break;
        }

        $this->service = $service;
    }

    /**
     * This function sets the username and password used to connect to the server.
     *
     * @param string $username
     * @param string $password
     * @param bool   $tryFasterAuth tries to auth faster by disabling GSSAPI & NTLM auth methods (set to false if you use either of these auth methods)
     */
    public function setAuthentication($username, $password, $tryFasterAuth=true)
    {
        $this->username = $username;
        $this->password = $password;
        if ($tryFasterAuth) {
            $this->setParam('DISABLE_AUTHENTICATOR', array('GSSAPI','NTLM'));
        }
    }

    /**
     * This function sets the mailbox to connect to.
     *
     * @param  string $mailbox
     * @return bool
     */
    public function setMailBox($mailbox = '')
    {
        if (!$this->hasMailBox($mailbox)) {
            return false;
        }

        $this->mailbox = $mailbox;
        if (isset($this->imapStream)) {
            $this->setImapStream();
        }

        return true;
    }

    public function getMailBox()
    {
        return $this->mailbox;
    }

    /**
     * This function sets or removes flag specifying connection behavior. In many cases the flag is just a one word
     * deal, so the value attribute is not required. However, if the value parameter is passed false it will clear that
     * flag.
     *
     * @param string           $flag
     * @param null|string|bool $value
     */
    public function setFlag($flag, $value = null)
    {
        if (!self::$sslEnable && in_array($flag, self::$sslFlags))
            return;

        if (isset(self::$exclusiveFlags[$flag])) {
            $kill = self::$exclusiveFlags[$flag];
        } elseif ($index = array_search($flag, self::$exclusiveFlags)) {
            $kill = $index;
        }

        if (isset($kill) && false !== $index = array_search($kill, $this->flags))
            unset($this->flags[$index]);

        $index = array_search($flag, $this->flags);
        if (isset($value) && $value !== true) {
            if ($value == false && $index !== false) {
                unset($this->flags[$index]);
            } elseif ($value != false) {
                $match = preg_grep('/' . $flag . '/', $this->flags);
                if (reset($match)) {
                    $this->flags[key($match)] = $flag . '=' . $value;
                } else {
                    $this->flags[] = $flag . '=' . $value;
                }
            }
        } elseif ($index === false) {
            $this->flags[] = $flag;
        }
    }

    /**
     * This funtion is used to set various options for connecting to the server.
     *
     * @param  int        $bitmask
     * @throws \Exception
     */
    public function setOptions($bitmask = 0)
    {
        if (!is_numeric($bitmask))
            throw new \RuntimeException('Function requires numeric argument.');

        $this->options = $bitmask;
    }

    /**
     * This function is used to set connection parameters
     *
     * @param string $key
     * @param string $value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * This function gets the current saved imap resource and returns it.
     *
     * @return resource
     */
    public function getImapStream()
    {
        if (empty($this->imapStream))
            $this->setImapStream();

        return $this->imapStream;
    }

    /**
     * This function takes in all of the connection date (server, port, service, flags, mailbox) and creates the string
     * thats passed to the imap_open function.
     *
     * @return string
     */
    public function getServerString()
    {
        $mailboxPath = $this->getServerSpecification();

        if (isset($this->mailbox))
            $mailboxPath .= $this->mailbox;

        return $mailboxPath;
    }

    /**
     * Returns the server specification, without adding any mailbox.
     *
     * @return string
     */
    protected function getServerSpecification()
    {
        $mailboxPath = '{' . $this->serverPath;

        if (isset($this->port))
            $mailboxPath .= ':' . $this->port;

        if ($this->service != 'imap')
            $mailboxPath .= '/' . $this->service;

        foreach ($this->flags as $flag) {
            $mailboxPath .= '/' . $flag;
        }

        $mailboxPath .= '}';

        return $mailboxPath;
    }

    /**
     * This function creates or reopens an imapStream when called.
     *
     */
    protected function setImapStream()
    {
        if (!empty($this->imapStream)) {
            if (!imap_reopen($this->imapStream, $this->getServerString(), $this->options, 1))
                throw new \RuntimeException(imap_last_error());
        } else {
            $imapStream = @imap_open($this->getServerString(), $this->username, $this->password, $this->options, 1, $this->params);

            if ($imapStream === false)
                throw new \RuntimeException(imap_last_error());

            $this->imapStream = $imapStream;
        }
    }

    /**
     * This returns the number of messages that the current mailbox contains.
     *
     * @param  string $mailbox
     * @return int
     */
    public function numMessages($mailbox='')
    {
        $cnt = 0;
        if ($mailbox==='') {
            $cnt = imap_num_msg($this->getImapStream());
        } elseif ($this->hasMailbox($mailbox) && $mailbox !== '') {
            $oldMailbox = $this->getMailBox();
            $this->setMailbox($mailbox);
            $cnt = $this->numMessages();
            $this->setMailbox($oldMailbox);
        }

        return ((int) $cnt);
    }

    /**
     * This function returns an array of ImapMessage object for emails that fit the criteria passed. The criteria string
     * should be formatted according to the imap search standard, which can be found on the php "imap_search" page or in
     * section 6.4.4 of RFC 2060
     *
     * @link http://us.php.net/imap_search
     * @link http://www.faqs.org/rfcs/rfc2060
     * @param  string   $criteria
     * @param  null|int $limit
     * @return array    An array of ImapMessage objects
     */
    public function search($criteria = 'ALL', $limit = null)
    {
        if ($results = imap_search($this->getImapStream(), $criteria, SE_UID)) {
            if (isset($limit) && count($results) > $limit)
                $results = array_slice($results, 0, $limit);

            $messages = array();

            foreach ($results as $messageId)
                $messages[] = new Message($messageId, $this);

            return $messages;
        } else {
            return array();
        }
    }

    /**
     * This function returns the recently received emails as an array of ImapMessage objects.
     *
     * @param  null|int $limit
     * @return array    An array of ImapMessage objects for emails that were recently received by the server.
     */
    public function getRecentMessages($limit = null)
    {
        return $this->search('Recent', $limit);
    }

    /**
     * Returns the emails in the current mailbox as an array of ImapMessage objects.
     *
     * @param  null|int  $limit
     * @return Message[]
     */
    public function getMessages($limit = null)
    {
        $numMessages = $this->numMessages();

        if (isset($limit) && is_numeric($limit) && $limit < $numMessages)
            $numMessages = $limit;

        if ($numMessages < 1)
            return array();

        $stream   = $this->getImapStream();
        $messages = array();
        for ($i = 1; $i <= $numMessages; $i++) {
            $uid        = imap_uid($stream, $i);
            $messages[] = new Message($uid, $this);
        }

        return $messages;
    }

    /**
     * Returns the emails in the current mailbox as an array of ImapMessage objects
     * ordered by some ordering
     *
     * @see    http://php.net/manual/en/function.imap-sort.php
     * @param  int       $orderBy
     * @param  bool      $reverse
     * @param  int       $limit
     * @return Message[]
     */
    public function getOrderedMessages($orderBy, $reverse, $limit)
    {
        $msgIds = imap_sort($this->getImapStream(), $orderBy, $reverse ? 1 : 0, SE_UID);

        return array_map(array($this, 'getMessageByUid'), array_slice($msgIds, 0, $limit));
    }

    /**
     * Returns the requested email or false if it is not found.
     *
     * @param  int          $uid
     * @return Message|bool
     */
    public function getMessageByUid($uid)
    {
        try {
            $message = new \Fetch\Message($uid, $this);

            return $message;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * This function removes all of the messages flagged for deletion from the mailbox.
     *
     * @return bool
     */
    public function expunge()
    {
        return imap_expunge($this->getImapStream());
    }

    /**
     * Checks if the given mailbox exists.
     *
     * @param $mailbox
     *
     * @return bool
     */
    public function hasMailBox($mailbox)
    {
        return (boolean) $this->getMailBoxDetails($mailbox);
    }

    /**
    * Return information about the mailbox or mailboxes
    *
    * @param $mailbox
    *
    * @return array
    */
    public function getMailBoxDetails($mailbox)
    {
        return imap_getmailboxes(
            $this->getImapStream(),
            $this->getServerString(),
            $this->getServerSpecification() . $mailbox
        );
    }

    /**
     * Creates the given mailbox.
     *
     * @param $mailbox
     *
     * @return bool
     */
    public function createMailBox($mailbox)
    {
        return imap_createmailbox($this->getImapStream(), $this->getServerSpecification() . $mailbox);
    }

    /**
     * List available mailboxes
     *
     * @param string $pattern
     *
     * @return array
     */
    public function listMailBoxes($pattern = '*')
    {
        return imap_list($this->getImapStream(), $this->getServerSpecification(), $pattern);
    }

    /**
     * Deletes the given mailbox.
     *
     * @param $mailbox
     *
     * @return bool
     */
     public function deleteMailBox($mailbox)
     {
         return imap_deletemailbox($this->getImapStream(), $this->getServerSpecification() . $mailbox);
     }
}

class Message
{
    /**
     * This is the connection/mailbox class that the email came from.
     *
     * @var Server
     */
    protected $imapConnection;

    /**
     * This is the unique identifier for the message. This corresponds to the imap "uid", which we use instead of the
     * sequence number.
     *
     * @var int
     */
    protected $uid;

    /**
     * This is a reference to the Imap stream generated by 'imap_open'.
     *
     * @var resource
     */
    protected $imapStream;

    /**
     * This as an string which contains raw header information for the message.
     *
     * @var string
     */
    protected $rawHeaders;

    /**
     * This as an object which contains header information for the message.
     *
     * @var \stdClass
     */
    protected $headers;

    /**
     * This is an object which contains various status messages and other information about the message.
     *
     * @var \stdClass
     */
    protected $messageOverview;

    /**
     * This is an object which contains information about the structure of the message body.
     *
     * @var \stdClass
     */
    protected $structure;

    /**
     * This is an array with the index being imap flags and the value being a boolean specifying whether that flag is
     * set or not.
     *
     * @var array
     */
    protected $status = array();

    /**
     * This is an array of the various imap flags that can be set.
     *
     * @var string
     */
    protected static $flagTypes = array(self::FLAG_RECENT, self::FLAG_FLAGGED, self::FLAG_ANSWERED, self::FLAG_DELETED, self::FLAG_SEEN, self::FLAG_DRAFT);

    /**
     * This holds the plantext email message.
     *
     * @var string
     */
    protected $plaintextMessage;

    /**
     * This holds the html version of the email.
     *
     * @var string
     */
    protected $htmlMessage;

    /**
     * This is the date the email was sent.
     *
     * @var int
     */
    protected $date;

    /**
     * This is the subject of the email.
     *
     * @var string
     */
    protected $subject;

    /**
     * This is the size of the email.
     *
     * @var int
     */
    protected $size;

    /**
     * This is an array containing information about the address the email came from.
     *
     * @var string
     */
    protected $from;

    /**
     * This is an array containing information about the address the email was sent from.
     *
     * @var string
     */
    protected $sender;

    /**
     * This is an array of arrays that contains information about the addresses the email was sent to.
     *
     * @var array
     */
    protected $to;

    /**
     * This is an array of arrays that contains information about the addresses the email was cc'd to.
     *
     * @var array
     */
    protected $cc;

    /**
     * This is an array of arrays that contains information about the addresses the email was bcc'd to.
     *
     * @var array
     */
    protected $bcc;

    /**
     * This is an array of arrays that contain information about the addresses that should receive replies to the email.
     *
     * @var array
     */
    protected $replyTo;

    /**
     * This is an array of ImapAttachments retrieved from the message.
     *
     * @var Attachment[]
     */
    protected $attachments = array();

    /**
     * Contains the mailbox that the message resides in.
     *
     * @var string
     */
    protected $mailbox;

    /**
     * This value defines the encoding we want the email message to use.
     *
     * @var string
     */
    public static $charset = 'UTF-8';

    /**
     * This value defines the flag set for encoding if the mb_convert_encoding
     * function can't be found, and in this case iconv encoding will be used.
     *
     * @var string
     */
    public static $charsetFlag = '//TRANSLIT';

    /**
     * These constants can be used to easily access available flags
     */
    const FLAG_RECENT = 'recent';
    const FLAG_FLAGGED = 'flagged';
    const FLAG_ANSWERED = 'answered';
    const FLAG_DELETED = 'deleted';
    const FLAG_SEEN = 'seen';
    const FLAG_DRAFT = 'draft';

    /**
     * This constructor takes in the uid for the message and the Imap class representing the mailbox the
     * message should be opened from. This constructor should generally not be called directly, but rather retrieved
     * through the apprioriate Imap functions.
     *
     * @param int    $messageUniqueId
     * @param MailClient $mailbox
     */
    public function __construct($messageUniqueId, MailClient $connection)
    {
        $this->imapConnection = $connection;
        $this->mailbox        = $connection->getMailBox();
        $this->uid            = $messageUniqueId;
        $this->imapStream     = $this->imapConnection->getImapStream();
        if($this->loadMessage() !== true)
            throw new \RuntimeException('Message with ID ' . $messageUniqueId . ' not found.');
    }

    /**
     * This function is called when the message class is loaded. It loads general information about the message from the
     * imap server.
     *
     */
    protected function loadMessage()
    {

        /* First load the message overview information */

        if(!is_object($messageOverview = $this->getOverview()))

            return false;

        $this->subject = MIME::decode($messageOverview->subject, self::$charset);
        $this->date    = strtotime($messageOverview->date);
        $this->size    = $messageOverview->size;

        foreach (self::$flagTypes as $flag)
            $this->status[$flag] = ($messageOverview->$flag == 1);

        /* Next load in all of the header information */

        $headers = $this->getHeaders();

        if (isset($headers->to))
            $this->to = $this->processAddressObject($headers->to);

        if (isset($headers->cc))
            $this->cc = $this->processAddressObject($headers->cc);

        if (isset($headers->bcc))
            $this->bcc = $this->processAddressObject($headers->bcc);

        if (isset($headers->sender))
            $this->sender = $this->processAddressObject($headers->sender);

        $this->from    = isset($headers->from) ? $this->processAddressObject($headers->from) : array('');
        $this->replyTo = isset($headers->reply_to) ? $this->processAddressObject($headers->reply_to) : $this->from;

        /* Finally load the structure itself */

        $structure = $this->getStructure();

        if (!isset($structure->parts)) {
            // not multipart
            $this->processStructure($structure);
        } else {
            // multipart
            foreach ($structure->parts as $id => $part)
                $this->processStructure($part, $id + 1);
        }

        return true;
    }

    /**
     * This function returns an object containing information about the message. This output is similar to that over the
     * imap_fetch_overview function, only instead of an array of message overviews only a single result is returned. The
     * results are only retrieved from the server once unless passed true as a parameter.
     *
     * @param  bool      $forceReload
     * @return \stdClass
     */
    public function getOverview($forceReload = false)
    {
        if ($forceReload || !isset($this->messageOverview)) {
            // returns an array, and since we just want one message we can grab the only result
            $results               = imap_fetch_overview($this->imapStream, $this->uid, FT_UID);
            if ( sizeof($results) == 0 ) {
                throw new \RuntimeException('Error fetching overview');
            }
            $this->messageOverview = array_shift($results);
            if ( ! isset($this->messageOverview->date)) {
                $this->messageOverview->date = null;
            }
        }

        return $this->messageOverview;
    }

    /**
     * This function returns an object containing the raw headers of the message.
     *
     * @param  bool   $forceReload
     * @return string
     */
    public function getRawHeaders($forceReload = false)
    {
        if ($forceReload || !isset($this->rawHeaders)) {
            // raw headers (since imap_headerinfo doesn't use the unique id)
            $this->rawHeaders = imap_fetchheader($this->imapStream, $this->uid, FT_UID);
        }

        return $this->rawHeaders;
    }

    /**
     * This function returns an object containing the headers of the message. This is done by taking the raw headers
     * and running them through the imap_rfc822_parse_headers function. The results are only retrieved from the server
     * once unless passed true as a parameter.
     *
     * @param  bool      $forceReload
     * @return \stdClass
     */
    public function getHeaders($forceReload = false)
    {
        if ($forceReload || !isset($this->headers)) {
            // raw headers (since imap_headerinfo doesn't use the unique id)
            $rawHeaders = $this->getRawHeaders();

            // convert raw header string into a usable object
            $headerObject = imap_rfc822_parse_headers($rawHeaders);

            // to keep this object as close as possible to the original header object we add the udate property
            if (isset($headerObject->date)) {
                $headerObject->udate = strtotime($headerObject->date);
            } else {
                $headerObject->date = null;
                $headerObject->udate = null;
            }

            $this->headers = $headerObject;
        }

        return $this->headers;
    }

    /**
     * This function returns an object containing the structure of the message body. This is the same object thats
     * returned by imap_fetchstructure. The results are only retrieved from the server once unless passed true as a
     * parameter.
     *
     * @param  bool      $forceReload
     * @return \stdClass
     */
    public function getStructure($forceReload = false)
    {
        if ($forceReload || !isset($this->structure)) {
            $this->structure = imap_fetchstructure($this->imapStream, $this->uid, FT_UID);
        }

        return $this->structure;
    }

    /**
     * This function returns the message body of the email. By default it returns the plaintext version. If a plaintext
     * version is requested but not present, the html version is stripped of tags and returned. If the opposite occurs,
     * the plaintext version is given some html formatting and returned. If neither are present the return value will be
     * false.
     *
     * @param  bool        $html Pass true to receive an html response.
     * @return string|bool Returns false if no body is present.
     */
    public function getMessageBody($html = false)
    {
        if ($html) {
            if (!isset($this->htmlMessage) && isset($this->plaintextMessage)) {
                $output = nl2br($this->plaintextMessage);

                return $output;

            } elseif (isset($this->htmlMessage)) {
                return $this->htmlMessage;
            }
        } else {
            if (!isset($this->plaintextMessage) && isset($this->htmlMessage)) {
                $output = preg_replace('/\s*\<br\s*\/?\>/i', PHP_EOL, trim($this->htmlMessage) );
                $output = strip_tags($output);

                return $output;
            } elseif (isset($this->plaintextMessage)) {
                return $this->plaintextMessage;
            }
        }

        return false;
    }

    /**
     * This function returns the plain text body of the email or false if not present.
     * @return string|bool Returns false if not present
     */
    public function getPlainTextBody()
    {
        return isset($this->plaintextMessage) ? $this->plaintextMessage : false;
    }

    /**
     * This function returns the HTML body of the email or false if not present.
     * @return string|bool Returns false if not present
     */
    public function getHtmlBody()
    {
        return isset($this->htmlMessage) ? $this->htmlMessage : false;
    }

    /**
     * This function returns either an array of email addresses and names or, optionally, a string that can be used in
     * mail headers.
     *
     * @param  string            $type     Should be 'to', 'cc', 'bcc', 'from', 'sender', or 'reply-to'.
     * @param  bool              $asString
     * @return array|string|bool
     */
    public function getAddresses($type, $asString = false)
    {
        $type = ( $type == 'reply-to' ) ? 'replyTo' : $type;
        $addressTypes = array('to', 'cc', 'bcc', 'from', 'sender', 'replyTo');

        if (!in_array($type, $addressTypes) || !isset($this->$type) || count($this->$type) < 1)
            return false;

        if (!$asString) {
            if ($type == 'from')
                return $this->from[0];
            elseif ($type == 'sender')
                return $this->sender[0];

            return $this->$type;
        } else {
            $outputString = '';
            foreach ($this->$type as $address) {
                if (isset($set))
                    $outputString .= ', ';
                if (!isset($set))
                    $set = true;

                $outputString .= isset($address['name']) ?
                    $address['name'] . ' <' . $address['address'] . '>'
                    : $address['address'];
            }

            return $outputString;
        }
    }

    /**
     * This function returns the date, as a timestamp, of when the email was sent.
     *
     * @return int
     */
    public function getDate()
    {
        return isset($this->date) ? $this->date : false;
    }

    /**
     * This returns the subject of the message.
     *
     * @return string
     */
    public function getSubject()
    {
        return isset($this->subject) ? $this->subject : null;
    }

    /**
     * This function marks a message for deletion. It is important to note that the message will not be deleted form the
     * mailbox until the Imap->expunge it run.
     *
     * @return bool
     */
    public function delete()
    {
        return imap_delete($this->imapStream, $this->uid, FT_UID);
    }

    /**
     * This function returns Imap this message came from.
     *
     * @return Server
     */
    public function getImapBox()
    {
        return $this->imapConnection;
    }

    /**
     * This function takes in a structure and identifier and processes that part of the message. If that portion of the
     * message has its own subparts, those are recursively processed using this function.
     *
     * @param \stdClass $structure
     * @param string    $partIdentifier
     */
    protected function processStructure($structure, $partIdentifier = null)
    {
        $parameters = self::getParametersFromStructure($structure);

        if ((isset($parameters['name']) || isset($parameters['filename']))
            || (isset($structure->subtype) && strtolower($structure->subtype) == 'rfc822')
        ) {
            $attachment          = new Attachment($this, $structure, $partIdentifier);
            $this->attachments[] = $attachment;
        } elseif ($structure->type == 0 || $structure->type == 1) {
            $messageBody = isset($partIdentifier) ?
                imap_fetchbody($this->imapStream, $this->uid, $partIdentifier, FT_UID | FT_PEEK)
                : imap_body($this->imapStream, $this->uid, FT_UID | FT_PEEK);

            $messageBody = self::decode($messageBody, $structure->encoding);

            if (!empty($parameters['charset']) && $parameters['charset'] !== self::$charset) {
                $mb_converted = false;
                if (function_exists('mb_convert_encoding')) {
                    if (!in_array($parameters['charset'], mb_list_encodings())) {
                        if ($structure->encoding === 0) {
                            $parameters['charset'] = 'US-ASCII';
                        } else {
                            $parameters['charset'] = 'UTF-8';
                        }
                    }

                    $messageBody = @mb_convert_encoding($messageBody, self::$charset, $parameters['charset']);
                    $mb_converted = true;
                }
                if (!$mb_converted) {
                    $messageBodyConv = @iconv($parameters['charset'], self::$charset . self::$charsetFlag, $messageBody);

                    if ($messageBodyConv !== false) {
                        $messageBody = $messageBodyConv;
                    }
                }
            }

            if (strtolower($structure->subtype) === 'plain' || ($structure->type == 1 && strtolower($structure->subtype) !== 'alternative')) {
                if (isset($this->plaintextMessage)) {
                    $this->plaintextMessage .= PHP_EOL . PHP_EOL;
                } else {
                    $this->plaintextMessage = '';
                }

                $this->plaintextMessage .= trim($messageBody);
            } elseif (strtolower($structure->subtype) === 'html') {
                if (isset($this->htmlMessage)) {
                    $this->htmlMessage .= '<br><br>';
                } else {
                    $this->htmlMessage = '';
                }

                $this->htmlMessage .= $messageBody;
            }
        }

        if (isset($structure->parts)) { // multipart: iterate through each part

            foreach ($structure->parts as $partIndex => $part) {
                $partId = $partIndex + 1;

                if (isset($partIdentifier))
                    $partId = $partIdentifier . '.' . $partId;

                $this->processStructure($part, $partId);
            }
        }
    }

    /**
     * This function takes in the message data and encoding type and returns the decoded data.
     *
     * @param  string     $data
     * @param  int|string $encoding
     * @return string
     */
    public static function decode($data, $encoding)
    {
        if (!is_numeric($encoding)) {
            $encoding = strtolower($encoding);
        }

        switch (true) {
            case $encoding === 'quoted-printable':
            case $encoding === 4:
                return quoted_printable_decode($data);

            case $encoding === 'base64':
            case $encoding === 3:
                return base64_decode($data);

            default:
                return $data;
        }
    }

    /**
     * This function returns the body type that an imap integer maps to.
     *
     * @param  int    $id
     * @return string
     */
    public static function typeIdToString($id)
    {
        switch ($id) {
            case 0:
                return 'text';

            case 1:
                return 'multipart';

            case 2:
                return 'message';

            case 3:
                return 'application';

            case 4:
                return 'audio';

            case 5:
                return 'image';

            case 6:
                return 'video';

            default:
            case 7:
                return 'other';
        }
    }

    /**
     * Takes in a section structure and returns its parameters as an associative array.
     *
     * @param  \stdClass $structure
     * @return array
     */
    public static function getParametersFromStructure($structure)
    {
        $parameters = array();
        if (isset($structure->parameters))
            foreach ($structure->parameters as $parameter)
                $parameters[strtolower($parameter->attribute)] = $parameter->value;

        if (isset($structure->dparameters))
            foreach ($structure->dparameters as $parameter)
                $parameters[strtolower($parameter->attribute)] = $parameter->value;

        return $parameters;
    }

    /**
     * This function takes in an array of the address objects generated by the message headers and turns them into an
     * associative array.
     *
     * @param  array $addresses
     * @return array
     */
    protected function processAddressObject($addresses)
    {
        $outputAddresses = array();
        if (is_array($addresses))
            foreach ($addresses as $address) {
                if (property_exists($address, 'mailbox') && $address->mailbox != 'undisclosed-recipients') {
                    $currentAddress = array();
                    $currentAddress['address'] = $address->mailbox . '@' . $address->host;
                    if (isset($address->personal)) {
                        $currentAddress['name'] = MIME::decode($address->personal, self::$charset);
                    }
                    $outputAddresses[] = $currentAddress;
                }
            }

        return $outputAddresses;
    }

    /**
     * This function returns the unique id that identifies the message on the server.
     *
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * This function returns the attachments a message contains. If a filename is passed then just that ImapAttachment
     * is returned, unless
     *
     * @param  null|string             $filename
     * @return array|bool|Attachment[]
     */
    public function getAttachments($filename = null)
    {
        if (!isset($this->attachments) || count($this->attachments) < 1)
            return false;

        if (!isset($filename))
            return $this->attachments;

        $results = array();
        foreach ($this->attachments as $attachment) {
            if ($attachment->getFileName() == $filename)
                $results[] = $attachment;
        }

        switch (count($results)) {
            case 0:
                return false;

            case 1:
                return array_shift($results);

            default:
                return $results;
                break;
        }
    }

    /**
     * This function checks to see if an imap flag is set on the email message.
     *
     * @param  string $flag Recent, Flagged, Answered, Deleted, Seen, Draft
     * @return bool
     */
    public function checkFlag($flag = self::FLAG_FLAGGED)
    {
        return (isset($this->status[$flag]) && $this->status[$flag] === true);
    }

    /**
     * This function is used to enable or disable one or more flags on the imap message.
     *
     * @param  string|array              $flag   Flagged, Answered, Deleted, Seen, Draft
     * @param  bool                      $enable
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function setFlag($flag, $enable = true)
    {
        $flags = (is_array($flag)) ? $flag : array($flag);

        foreach ($flags as $i => $flag) {
            $flag = ltrim(strtolower($flag), '\\');
            if (!in_array($flag, self::$flagTypes) || $flag == self::FLAG_RECENT)
                throw new \InvalidArgumentException('Unable to set invalid flag "' . $flag . '"');

            if ($enable) {
                $this->status[$flag] = true;
            } else {
                unset($this->status[$flag]);
            }

            $flags[$i] = $flag;
        }

        $imapifiedFlag = '\\'.implode(' \\', array_map('ucfirst', $flags));

        if ($enable === true) {
            return imap_setflag_full($this->imapStream, $this->uid, $imapifiedFlag, ST_UID);
        } else {
            return imap_clearflag_full($this->imapStream, $this->uid, $imapifiedFlag, ST_UID);
        }
    }

    /**
     * This function is used to move a mail to the given mailbox.
     *
     * @param $mailbox
     *
     * @return bool
     */
    public function moveToMailBox($mailbox)
    {
        $currentBox = $this->imapConnection->getMailBox();
        $this->imapConnection->setMailBox($this->mailbox);

        $returnValue = imap_mail_copy($this->imapStream, $this->uid, $mailbox, CP_UID | CP_MOVE);
        imap_expunge($this->imapStream);

        $this->mailbox = $mailbox;

        $this->imapConnection->setMailBox($currentBox);

        return $returnValue;
    }
}

class Attachment
{

    /**
     * This is the structure object for the piece of the message body that the attachment is located it.
     *
     * @var \stdClass
     */
    protected $structure;

    /**
     * This is the unique identifier for the message this attachment belongs to.
     *
     * @var int
     */
    protected $messageId;

    /**
     * This is the ImapResource.
     *
     * @var resource
     */
    protected $imapStream;

    /**
     * This is the id pointing to the section of the message body that contains the attachment.
     *
     * @var int
     */
    protected $partId;

    /**
     * This is the attachments filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * This is the size of the attachment.
     *
     * @var int
     */
    protected $size;

    /**
     * This stores the data of the attachment so it doesn't have to be retrieved from the server multiple times. It is
     * only populated if the getData() function is called and should not be directly used.
     *
     * @internal
     * @var array
     */
    protected $data;

    /**
     * This function takes in an ImapMessage, the structure object for the particular piece of the message body that the
     * attachment is located at, and the identifier for that body part. As a general rule you should not be creating
     * instances of this yourself, but rather should get them from an ImapMessage class.
     *
     * @param Message   $message
     * @param \stdClass $structure
     * @param string    $partIdentifier
     */
    public function __construct(Message $message, $structure, $partIdentifier = null)
    {
        $this->messageId  = $message->getUid();
        $this->imapStream = $message->getImapBox()->getImapStream();
        $this->structure  = $structure;

        if (isset($partIdentifier))
            $this->partId = $partIdentifier;

        $parameters = Message::getParametersFromStructure($structure);

        if (isset($parameters['filename'])) {
            $this->setFileName($parameters['filename']);
        } elseif (isset($parameters['name'])) {
            $this->setFileName($parameters['name']);
        }

        $this->size = $structure->bytes;

        $this->mimeType = Message::typeIdToString($structure->type);

        if (isset($structure->subtype))
            $this->mimeType .= '/' . strtolower($structure->subtype);

        $this->encoding = $structure->encoding;
    }

    /**
     * This function returns the data of the attachment. Combined with getMimeType() it can be used to directly output
     * data to a browser.
     *
     * @return string
     */
    public function getData()
    {
        if (!isset($this->data)) {
            $messageBody = isset($this->partId) ?
                imap_fetchbody($this->imapStream, $this->messageId, $this->partId, FT_UID)
                : imap_body($this->imapStream, $this->messageId, FT_UID);

            $messageBody = Message::decode($messageBody, $this->encoding);
            $this->data  = $messageBody;
        }

        return $this->data;
    }

    /**
     * This returns the filename of the attachment, or false if one isn't given.
     *
     * @return string
     */
    public function getFileName()
    {
        return (isset($this->filename)) ? $this->filename : false;
    }

    /**
     * This function returns the mimetype of the attachment.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * This returns the size of the attachment.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * This function returns the object that contains the structure of this attachment.
     *
     * @return \stdClass
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * This function saves the attachment to the passed directory, keeping the original name of the file.
     *
     * @param  string $path
     * @return bool
     */
    public function saveToDirectory($path)
    {
        $path = rtrim($path, '/') . '/';

        if (is_dir($path))
            return $this->saveAs($path . $this->getFileName());

        return false;
    }

    /**
     * This function saves the attachment to the exact specified location.
     *
     * @param  string $path
     * @return bool
     */
    public function saveAs($path)
    {
        $dirname = dirname($path);
        if (file_exists($path)) {
            if (!is_writable($path)) {
                return false;
            }
        } elseif (!is_dir($dirname) || !is_writable($dirname)) {
            return false;
        }

        if (($filePointer = fopen($path, 'w')) == false) {
            return false;
        }

        switch ($this->encoding) {
            case 3: //base64
                $streamFilter = stream_filter_append($filePointer, 'convert.base64-decode', STREAM_FILTER_WRITE);
                break;

            case 4: //quoted-printable
                $streamFilter = stream_filter_append($filePointer, 'convert.quoted-printable-decode', STREAM_FILTER_WRITE);
                break;

            default:
                $streamFilter = null;
        }

        // Fix an issue causing server to throw an error
        // See: https://github.com/tedious/Fetch/issues/74 for more details
        $fetch  = imap_fetchbody($this->imapStream, $this->messageId, $this->partId ?: 1, FT_UID);
        $result = imap_savebody($this->imapStream, $filePointer, $this->messageId, $this->partId ?: 1, FT_UID);

        if ($streamFilter) {
            stream_filter_remove($streamFilter);
        }

        fclose($filePointer);

        return $result;
    }

    protected function setFileName($text)
    {
        $this->filename = MIME::decode($text, Message::$charset);
    }
}

class MIME
{
    /**
     * @param string $text
     * @param string $targetCharset
     *
     * @return string
     */
    public static function decode($text, $targetCharset = 'utf-8')
    {
        if (null === $text) {
            return null;
        }

        $result = '';

        foreach (imap_mime_header_decode($text) as $word) {
            $ch = 'default' === $word->charset ? 'ascii' : $word->charset;

            $result .= iconv($ch, $targetCharset, $word->text);
        }

        return $result;
    }
} // Classes end