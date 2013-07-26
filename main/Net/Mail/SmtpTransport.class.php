<?php
/**
 * onPHP SMTP email transport class
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2013.07.17
 */

class SmtpTransport {

	const DEFAULT_PORT = 25;

	const DEBUG_DISABLED = 0;
	const DEBUG_MINIMUM = 10;
	const DEBUG_MEDIUM = 20;
	const DEBUG_MAXIMUM = 30;
	const DEBUG_TOTAL = 40;

	protected $useSSL = false;

	protected $useTLS = false;

	/** @var string */
	protected $serverName = 'localhost';

	/** @var string */
	protected $authMethod = 'LOGIN';

	/** @var int */
	protected $streamTimeout = 30;

	/** @var int */
	protected $operationTimeLimit = 15;

	/** @var string */
	protected $crlf = "\r\n";

	/** @var bool */
	protected $useVerp = false;

	/** @var int */
	protected $debugLevel = 0;

	/** @var Closure */
	protected $debugWriter = null;

	/** @var resource */
	protected $connection = null;

	/** @var string */
	protected $serverAuthMethods = null;

	/** @var string */
	protected $lastRecipient = null;

	/**
	 * @return SmtpTransport
	 */
	public static function create() {
		return new self;
	}

	protected function __construct() {
		$this->debugWriter = function($message){
			echo $message."\n";
		};
	}

	/**
	 * @return boolean
	 */
	public function isUseSSL() {
		return $this->useSSL;
	}

	/**
	 * @param boolean $useSsl
	 * @return SmtpTransport
	 */
	public function setUseSSL($useSsl) {
		$this->useSSL = $useSsl;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isUseTLS() {
		return $this->useTLS;
	}

	/**
	 * @param boolean $useTls
	 * @return SmtpTransport
	 */
	public function setUseTLS($useTls) {
		$this->useTLS = $useTls;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getServerName() {
		return $this->serverName;
	}

	/**
	 * @param string $serverName
	 * @return SmtpTransport
	 */
	public function setServerName($serverName) {
		$this->serverName = $serverName;
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function setAuthMethodPlain() {
		$this->authMethod = 'PLAIN';
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function setAuthMethodLogin() {
		$this->authMethod = 'LOGIN';
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function setAuthMethodCramMD5() {
		$this->authMethod = 'CRAM-MD5';
		return $this;
	}

	/**
	 * @return int
	 */
	public function getStreamTimeout() {
		return $this->streamTimeout;
	}

	/**
	 * Sets the SMTP timeout value for socket, in seconds
	 * @param int $streamTimeout
	 * @return SmtpTransport
	 */
	public function setStreamTimeout($streamTimeout) {
		$this->streamTimeout = $streamTimeout;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getOperationTimeLimit() {
		return $this->operationTimeLimit;
	}

	/**
	 * Sets the SMTP timelimit value for reads, in seconds
	 * @param int $operationTimeLimit
	 * @return SmtpTransport
	 */
	public function setOperationTimeLimit($operationTimeLimit) {
		$this->operationTimeLimit = $operationTimeLimit;
		return $this;

	}

	/**
	 * @return string
	 */
	public function getCrlf() {
		return $this->crlf;
	}

	/**
	 * SMTP reply line ending
	 * @param string $crlf
	 * @return SmtpTransport
	 */
	public function setCrlf($crlf) {
		$this->crlf = $crlf;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isUseVerp() {
		return (bool)$this->useVerp;
	}

	/**
	 * Sets VERP use on/off
	 * @param boolean $useVerp
	 * @return SmtpTransport
	 */
	public function setUseVerp($useVerp) {
		$this->useVerp = $useVerp;
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function debugDisabled() {
		$this->debugLevel = self::DEBUG_DISABLED;
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function debugMinimum() {
		$this->debugLevel = self::DEBUG_MINIMUM;
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function debugMedium() {
		$this->debugLevel = self::DEBUG_MEDIUM;
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function debugMaximum() {
		$this->debugLevel = self::DEBUG_MAXIMUM;
		return $this;
	}

	/**
	 * @return SmtpTransport
	 */
	public function debugTotal() {
		$this->debugLevel = self::DEBUG_TOTAL;
		return $this;
	}

	/**
	 * @return callable
	 */
	public function getDebugWriter() {
		return $this->debugWriter;
	}

	/**
	 * @param callable $debugWriter
	 * @return SmtpTransport
	 */
	public function setDebugWriter($debugWriter) {
		$this->debugWriter = $debugWriter;
		return $this;
	}

//	public function sendMail(Mail $mail) {
//
//	}
//
//	public function sendMimeMail(MimeMail $mail) {
//
//	}
//
//	public function sendRawMail($to, $subject, $message, $additional_headers = null, $additional_parameters = null) {
//
//	}

	//===== common functions

	/**
	 * Connect to an SMTP server
	 *
	 * SMTP CODE SUCCESS: 220
	 * SMTP CODE FAILURE: 421
	 * @param string $host SMTP server IP or host name
	 * @param int $port The port number to connect to, or use the default port if not specified
	 * @param array $options An array of options compatible with stream_context_create()
	 * @throws SmtpTransportException
	 * @return SmtpTransport
	 */
	public function connect($host, $port = null, $options = array()) {
		// Make sure we are __not__ connected
		if($this->isConnected()) {
			return $this;
		}

		if(empty($port)) {
			$port = self::DEFAULT_PORT;
		}

		if($this->isUseSsl()) {
			$host = 'ssl://'.$host;
		}

		// Connect to the SMTP server
		$errno = 0;
		$errstr = '';
		$socket_context = stream_context_create($options);
		//Need to suppress errors here as connection failures can be handled at a higher level
		$this->connection = @stream_socket_client($host.":".$port, $errno, $errstr, $this->streamTimeout, STREAM_CLIENT_CONNECT, $socket_context);

		// Verify we connected properly
		if(empty($this->connection)) {
			$this->debugOut("SMTP->ERROR: Failed to connect to server: $errstr ($errno)", self::DEBUG_MINIMUM);
			throw new SmtpTransportException('Failed to connect to server');
		}

		// SMTP server can take longer to respond, give longer timeout for first read
		// Windows does not have support for this timeout function
		if(substr(PHP_OS, 0, 3) != 'WIN') {
			$max = ini_get('max_execution_time');
			if ($max != 0 && $this->streamTimeout > $max) { // Don't bother if unlimited
				@set_time_limit($this->streamTimeout);
			}
			stream_set_timeout($this->connection, $this->streamTimeout, 0);
		}

		// get any announcement
		$announce = $this->execCommand(null, 220);

		$this->debugOut('SMTP: connected :: ' . $announce['payload'], self::DEBUG_MINIMUM);

		return $this;
	}

	/**
	 * Sends the HELO command to the smtp server.
	 * This makes sure that we and the server are in
	 * the same known state.
	 *
	 * Implements from rfc 821: HELO <SP> <domain> <CRLF>
	 *
	 * SMTP CODE SUCCESS: 250
	 * SMTP CODE ERROR  : 500, 501, 504, 421
	 *
	 * @throws SmtpTransportException
	 * @throws WrongStateException
	 * @return SmtpTransport
	 */
	public function hello() {
		$this->assertConnection(__METHOD__);

		// if hostname for HELO was not specified send default
		if(empty($this->serverName)) {
			// determine appropriate default to send to server
			$this->serverName = 'localhost';
		}

		$helloSent = false;

		// Send extended hello first (RFC 2821)
		try {
			$hello = $this->execCommand('EHLO ' . $this->serverName, 250);
			$helloSent = true;
		} catch(SmtpTransportException $e) {
			// skip...
		}
		// Send common hello
		if( !$helloSent ) {
			try {
				$hello = $this->execCommand('HELO ' . $this->serverName, 250);
				$helloSent = true;
			} catch(SmtpTransportException $e) {
				// skip...
			}
		}
		// Check hello result
		if( !$helloSent ) {
			$this->debugOut("SMTP->ERROR: {$hello['code']}: {$hello['payload']}");
			throw new SmtpTransportException('EHLO/HELO not accepted');
		}
		// Parse available auth methods
		$authString = substr($hello['payload'], 5, strpos($hello['payload'], "\n")-6);
		$this->serverAuthMethods = explode(' ', $authString);

		$this->debugOut('SMTP: hello passed', self::DEBUG_MEDIUM);

		return $this;
	}

	/**
	 * Initiate a TLS communication with the server.
	 *
	 * SMTP CODE 220 Ready to start TLS
	 * SMTP CODE 501 Syntax error (no parameters allowed)
	 * SMTP CODE 454 TLS not available due to temporary reason
	 * @throws SmtpTransportException
	 * @throws WrongStateException
	 * @return SmtpTransport
	 */
	public function startTLS() {
		$this->assertConnection(__METHOD__);

		$initCrypto = false;
		try {
			$this->execCommand('STARTTLS', 220);
			$initCrypto = true;
		} catch(SmtpTransportException $e) {
			// skip...
		}

		if( $initCrypto ) {
			// Begin encrypted connection
			if(!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
				throw new SmtpTransportException('Could not init TLS on socket level');
			}
		} else {
			$this->debugOut('STARTTLS not accepted from server', self::DEBUG_MEDIUM);
		}

		$this->debugOut('SMTP: StarTLS started', self::DEBUG_MEDIUM);

		return $this;
	}

	/**
	 * Performs SMTP authentication.  Must be run after running the
	 * Hello() method.
	 * @param string $username
	 * @param string $password
	 * @throws SmtpTransportException
	 * @throws WrongStateException
	 * @return SmtpTransport
	 */
	public function authenticate($username, $password) {
		$this->assertConnection(__METHOD__);

		if( !in_array($this->authMethod, $this->serverAuthMethods) ) {
			throw new WrongStateException("Auth method '{$this->authMethod}' is not supported by server; use: ".implode(', ', $this->serverAuthMethods));
		}
		// Start authentication
		switch ($this->authMethod) {
			case 'PLAIN': {
				try {
					$this->execCommand('AUTH '.$this->authMethod.' '.base64_encode("\0".$username."\0".$password), 235);
				} catch(SmtpTransportException $e) {
					throw new SmtpTransportException('Authentication not accepted from server', $e->getCode(), $e);
				}
			} break;
			case 'LOGIN': {
				try {
					$this->execCommand('AUTH '.$this->authMethod, 334);
					$this->execCommand(base64_encode($username), 334);
					$this->execCommand(base64_encode($password), 235);
				} catch(SmtpTransportException $e) {
					throw new SmtpTransportException('Authentication not accepted from server', $e->getCode(), $e);
				}
			} break;
			case 'CRAM-MD5': {
				try {
					$reply = $this->execCommand('AUTH '.$this->authMethod, 334);
					// Get the challenge
					$challenge = base64_decode($reply['payload']);
					// Build the response
					$response = base64_encode( $username . ' ' . $this->hmac($challenge, $password) );
					$this->execCommand($response, 235);
				} catch(SmtpTransportException $e) {
					throw new SmtpTransportException('Authentication not accepted from server', $e->getCode(), $e);
				}
			} break;
		}

		$this->debugOut('SMTP: Authentication passed', self::DEBUG_MINIMUM);

		return $this;
	}

	/**
	 * Starts a mail transaction from the email address specified in
	 * $from. Returns true if successful or false otherwise. If True
	 * the mail transaction is started and then one or more Recipient
	 * commands may be called followed by a Data command.
	 *
	 * Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
	 *
	 * SMTP CODE SUCCESS: 250
	 * SMTP CODE SUCCESS: 552, 451, 452
	 * SMTP CODE SUCCESS: 500, 501, 421
	 * @param string $from
	 * @throws SmtpTransportException
	 * @throws WrongArgumentException
	 * @throws WrongStateException
	 * @return SmtpTransport
	 */
	public function startMail($from) {
		$this->assertConnection(__METHOD__);
		$this->assertValidEmail($from);

		$command = 'MAIL FROM:<' . $from . '>';
		if( $this->isUseVerp() ) {
			$command .= ' XVERP';
		}
		try {
			$this->execCommand($command, 250);
		} catch(SmtpTransportException $e) {
			throw new SmtpTransportException('MAIL not accepted from server', $e->getCode(), $e);
		}

		$this->debugOut('SMTP: mail from :: ' . $from, self::DEBUG_MEDIUM);

		return $this;
	}

	/**
	 * Sends the command RCPT to the SMTP server with the TO: argument of $to.
	 * Returns true if the recipient was accepted false if it was rejected.
	 *
	 * Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
	 *
	 * SMTP CODE SUCCESS: 250, 251
	 * SMTP CODE FAILURE: 550, 551, 552, 553, 450, 451, 452
	 * SMTP CODE ERROR  : 500, 501, 503, 421
	 * @param string $to
	 * @throws SmtpTransportException
	 * @throws WrongArgumentException
	 * @throws WrongStateException
	 * @return SmtpTransport
	 */
	public function setRecipient($to) {
		$this->assertConnection(__METHOD__);
		$this->assertValidEmail($to);

		try {
			$this->execCommand('RCPT TO:<' . $to . '>', 250);
		} catch(SmtpTransportException $e) {
			throw new SmtpTransportException('RCPT not accepted from server', $e->getCode(), $e);
		}

		$this->lastRecipient = $to;
		$this->debugOut('SMTP: rctp to :: ' . $to, self::DEBUG_MEDIUM);

		return $this;
	}

	/**
	 * Issues a data command and sends the msg_data to the server
	 * finializing the mail transaction. $msg_data is the message
	 * that is to be send with the headers. Each header needs to be
	 * on a single line followed by a <CRLF> with the message headers
	 * and the message body being separated by and additional <CRLF>.
	 *
	 * Implements rfc 821: DATA <CRLF>
	 *
	 * SMTP CODE INTERMEDIATE: 354
	 *     [data]
	 *     <CRLF>.<CRLF>
	 *     SMTP CODE SUCCESS: 250
	 *     SMTP CODE FAILURE: 552, 554, 451, 452
	 * SMTP CODE FAILURE: 451, 554
	 * SMTP CODE ERROR  : 500, 501, 503, 421
	 * @param string $data
	 * @throws SmtpTransportException
	 * @throws MailNotSentException
	 * @throws WrongArgumentException
	 * @return SmtpTransport
	 */
	public function setContent($data) {
		$this->assertConnection(__METHOD__);

		try {
			$this->execCommand('DATA', 354);
		} catch(SmtpTransportException $e) {
			throw new SmtpTransportException('DATA not accepted from server', $e->getCode(), $e);
		}

		/* the server is ready to accept data!
		 * according to rfc 821 we should not send more than 1000
		 * including the CRLF
		 * characters on a single line so we will break the data up
		 * into lines by \r and/or \n then if needed we will break
		 * each of those into smaller lines to fit within the limit.
		 * in addition we will be looking for lines that start with
		 * a period '.' and append and additional period '.' to that
		 * line. NOTE: this does not count towards limit.
		 */

		// normalize the line breaks so we know the explode works
		$data = str_replace(array("\r\n", "\r"), "\n", $data);
		$lines = explode("\n", $data);


		$field = substr($lines[0], 0, strpos($lines[0], ':'));
		$in_headers = false;
		if(!empty($field) && !strstr($field, ' ')) {
			$in_headers = true;
		}

		$max_line_length = 998; // used below; set here for ease in change

		while(list(, $line) = @each($lines)) {
			$lines_out = null;
			if($line == '' && $in_headers) {
				$in_headers = false;
			}
			// ok we need to break this line up into several smaller lines
			while(strlen($line) > $max_line_length) {
				$pos = strrpos(substr($line, 0, $max_line_length), ' ');

				// Patch to fix DOS attack
				if(!$pos) {
					$pos = $max_line_length - 1;
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				} else {
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos + 1);
				}

				/* if processing headers add a LWSP-char to the front of new line
				 * rfc 822 on long msg headers
				 */
				if($in_headers) {
					$line = "\t" . $line;
				}
			}
			$lines_out[] = $line;

			// send the lines to the server
			while(list(, $line_out) = @each($lines_out)) {
				if(strlen($line_out) > 0)
				{
					if(substr($line_out, 0, 1) == '.') {
						$line_out = '.' . $line_out;
					}
				}
				$this->execCommand($line_out, 250);
			}
		}

		// message data has been sent
		try {
			$this->execCommand('.', 251);
		} catch(SmtpTransportException $e) {
			throw new MailNotSentException($e->getMessage(), $e->getCode());
		}

		$this->debugOut('SMTP: mail sent to :: '.$this->lastRecipient, self::DEBUG_MINIMUM);

		return $this;
	}

	/**
	 * Sends the RSET command to abort and transaction that is
	 * currently in progress. Returns true if successful false
	 * otherwise.
	 *
	 * Implements rfc 821: RSET <CRLF>
	 *
	 * SMTP CODE SUCCESS: 250
	 * SMTP CODE ERROR  : 500, 501, 504, 421
	 * @throws SmtpTransportException
	 * @throws WrongStateException
	 * @return SmtpTransport
	 */
	public function reset() {
		$this->assertConnection(__METHOD__);

		// clear last recipient
		$this->lastRecipient = null;

		// send the quit command to the server
		try {
//			$this->execCommand('RSET', 250);
		} catch(SmtpTransportException $e) {
			throw new SmtpTransportException('RSET not accepted from server', $e->getCode(), $e);
		}

		return $this;
	}

	/**
	 * Sends the quit command to the server and then closes the socket
	 * if there is no error or the $close_on_error argument is true.
	 *
	 * Implements from rfc 821: QUIT <CRLF>
	 *
	 * SMTP CODE SUCCESS: 221
	 * SMTP CODE ERROR  : 500
	 * @throws SmtpTransportException
	 * @throws WrongStateException
	 * @return SmtpTransport
	 */
	public function quit() {
		$this->assertConnection(__METHOD__);

		// send the quit command to the server
		try {
			$this->execCommand('QUIT', 221);
		} catch(SmtpTransportException $e) {
			throw new SmtpTransportException('QUIT not accepted from server', $e->getCode(), $e);
		}

		$this->debugOut('SMTP: quited', self::DEBUG_MINIMUM);

		return $this;
	}

	/**
	 * Closes the socket and cleans up the state of the class.
	 * It is not considered good to use this function without
	 * first trying to use QUIT.
	 * @return SmtpTransport
	 */
	public function close() {
		$this->error = null; // so there is no confusion
		$this->helo_rply = null;
		if(!empty($this->connection)) {
			// close the connection and cleanup
			fclose($this->connection);
			$this->connection = null;
		}

		$this->debugOut('SMTP: connection closed', self::DEBUG_MINIMUM);

		return $this;
	}

	/**
	 * Returns true if connected to a server otherwise false
	 * @return bool
	 */
	public function isConnected() {
		if(!empty($this->connection)) {
			$sock_status = stream_get_meta_data($this->connection);
			if($sock_status['eof']) {
				// the socket is valid but we are not connected
				$this->debugOut('SMTP->NOTICE: EOF caught while checking if connected', self::DEBUG_TOTAL);
				$this->close();
				return false;
			}
			return true; // everything looks good
		}
		return false;
	}

	/**
	 * Assert established connection
	 * @param string $method
	 * @throws WrongStateException
	 */
	protected function assertConnection($method) {
		if(!$this->isConnected()) {
			throw new WrongStateException("Called $method() without being connected");
		}
	}

	/**
	 * Assert valid email address
	 * @param string $email
	 * @throws WrongArgumentException
	 */
	protected function assertValidEmail($email) {
		$form =
			Form::create()
				->add(
					Primitive::string('email')
						->setAllowedPattern(PrimitiveString::MAIL_PATTERN)
						->setMin(6)
						->setMax(1024)
						->required()
				)
				->import(
					array('email'=>$email)
				);
		if( $form->getErrors() ) {
			throw new WrongArgumentException("Provided string \"{$email}\" is not valid email");
		}
	}

	//===== internal functions

	protected function execCommand($command, $expectedCode) {
		if( !is_null($command) ) {
			$this->rawPut($command . $this->crlf);
			$this->debugOut('', self::DEBUG_MAXIMUM);
			$this->debugOut("Send command {$command}", self::DEBUG_MAXIMUM);
		}
		$reply = trim($this->rawGet());

		$code = intval( trim(substr($reply, 0, 4)) );
		$payload = substr($reply, 4);

		$this->debugOut("Got answer with code {$code} and payload: {$payload}", self::DEBUG_MAXIMUM);
		if( $code!=$expectedCode ) {
			throw new SmtpTransportException("Wrong answer code got {$code} but {$expectedCode} expected; last command: {$command}");
		}

		return array(
			'code' => $code,
			'payload' => $payload,
		);
	}

	/**
	 * Read in as many lines as possible
	 * either before eof or socket timeout occurs on the operation.
	 * With SMTP we can tell if we have more lines to read if the
	 * 4th character is '-' symbol. If it is a space then we don't
	 * need to read anything else.
	 * @throws NetworkException
	 * @return string
	 */
	protected function rawGet() {
		$data = '';
		$endtime = 0;
		/* If for some reason the fp is bad, don't inf loop */
		if (!is_resource($this->connection)) {
			return $data;
		}
		stream_set_timeout($this->connection, $this->streamTimeout);
		if ($this->operationTimeLimit > 0) {
			$endtime = time() + $this->operationTimeLimit;
		}
		while(is_resource($this->connection) && !feof($this->connection)) {
			$str = @fgets($this->connection, 515);
			$data .= $str;
			// if 4th character is a space, we are done reading, break the loop
			if(substr($str, 3, 1) == ' ') { break; }
			// Timed-out? Log and break
			$info = stream_get_meta_data($this->connection);
			if ($info['timed_out']) {
				$message = "SMTP->rawGet(): timed-out ({$this->streamTimeout} seconds)";
				$this->debugOut($message, self::DEBUG_MINIMUM);
				throw new NetworkException($message);
			}
			// Now check if reads took too long
			if ($endtime) {
				if (time() > $endtime) {
					$message = "SMTP->rawGet(): timelimit reached ({$this->operationTimeLimit} seconds)";
					$this->debugOut($message, self::DEBUG_MINIMUM);
					throw new NetworkException($message);
				}
			}
		}
		$this->debugOut("SMTP get data: \"$data\"", self::DEBUG_TOTAL);
		return $data;
	}

	/**
	 * Sends data to the server
	 * Returns number of bytes sent to the server or FALSE on error
	 * @param string $data
	 * @return int
	 */
	protected function rawPut($data) {
		$this->debugOut("SMTP put data: $data", self::DEBUG_TOTAL);
		return fwrite($this->connection, $data);
	}

	/**
	 * Works like hash_hmac('md5', $data, $key) in case that function is not available
	 * @access protected
	 * @param string $data
	 * @param string $key
	 * @return string
	 */
	protected function hmac($data, $key) {
		if (function_exists('hash_hmac')) {
			return hash_hmac('md5', $data, $key);
		}

		// The following borrowed from http://php.net/manual/en/function.mhash.php#27225

		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// Hacked by Lance Rushing

		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack('H*', md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return md5($k_opad  . pack('H*', md5($k_ipad . $data)));
	}

	/**
	 * @param string $message
	 * @param int $minLevel
	 * @throws WrongStateException
	 */
	protected function debugOut($message, $minLevel = 100) {
		if( $this->debugLevel < $minLevel ) {
			return;
		}
		if( !is_callable($this->debugWriter) ) {
			throw new WrongStateException('Debug writer is not callable!');
		}
		call_user_func($this->debugWriter, $message);
	}

}