<?php
/**
 * Примитив для проверки капчи через сервис ReCAPTCHA
 * @see http://www.google.com/recaptcha/
 * @author Alex Gorbylev <alex@gorbylev.ru>
 * @date 2012.02.20
 */
class PrimitiveReCaptcha extends BasePrimitive {

	CONST
		RECAPTCHA_API_SERVER = 'http://www.google.com/recaptcha/api',
		RECAPTCHA_API_SECURE_SERVER = 'https://www.google.com/recaptcha/api',
		RECAPTCHA_VERIFY_SERVER = 'www.google.com',

		RECAPTCHA_RESPONSE_FIELD = 'recaptcha_response_field',
		RECAPTCHA_CHALLENGE_FIELD = 'recaptcha_challenge_field'
		;

	protected $reCaptchaPrivateKey = null;

	public function __construct() {
		$this->name = self::RECAPTCHA_RESPONSE_FIELD;
	}

	/**
	 * @param string $key
	 * @return PrimitiveReCaptcha
	 */
	public function setReCaptchaPrivateKey( $key ) {
		$this->reCaptchaPrivateKey = $key;
		return $this;
	}

	public function import($scope) {
		if (!BasePrimitive::import($scope))
			return null;

		if (!is_scalar($scope[$this->name]))
			return false;

		$this->value = (string) $scope[$this->name];

		if (
			is_string($this->value)
			// zero is quite special value here
			&& !empty($this->value)
			&& self::recaptcha_check_answer(
					$this->reCaptchaPrivateKey,
					$_SERVER["REMOTE_ADDR"],
					$_POST[self::RECAPTCHA_CHALLENGE_FIELD],
					$this->value
				)
		) {
			return true;
		} else {
			$this->value = null;
		}

		return false;
	}

	/**
	 * Encodes the given data into a query string format
	 * @param $data - array of string elements to be encoded
	 * @return string - encoded request
	 */
	protected static function _recaptcha_qsencode ($data) {
		$req = "";
		foreach ( $data as $key => $value ) {
			$req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
		}

		// Cut the last '&'
		$req=substr($req,0,strlen($req)-1);
		return $req;
	}

	/**
	 * Submits an HTTP POST to a reCAPTCHA server
	 * @param string $host
	 * @param string $path
	 * @param array $data
	 * @param int port
	 * @return array response
	 */
	protected static function _recaptcha_http_post($host, $path, $data, $port = 80) {
		$req = self::_recaptcha_qsencode ($data);

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';
		if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
			die ('Could not open socket');
		}

		fwrite($fs, $http_request);

		while ( !feof($fs) ) {
			$response .= fgets($fs, 1160); // One TCP-IP packet
		}
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}

	/**
	 * Gets the challenge HTML (javascript and non-javascript version).
	 * This is called from the browser, and the resulting reCAPTCHA HTML widget
	 * is embedded within the HTML form it was called from.
	 * @param string $pubkey A public key for reCAPTCHA
	 * @param string $error The error given by reCAPTCHA (optional, default is null)
	 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

	 * @return string - The HTML to be embedded in the user's form.
	 */
	public static function recaptcha_get_html ($pubkey, $error = null, $use_ssl = false) {
		if ($pubkey == null || $pubkey == '') {
			die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
		}

		if ($use_ssl) {
			$server = self::RECAPTCHA_API_SECURE_SERVER;
		} else {
			$server = self::RECAPTCHA_API_SERVER;
		}

		$errorpart = "";
		if ($error) {
		   $errorpart = "&amp;error=" . $error;
		}
		return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>';
	}

	/**
	  * Calls an HTTP POST function to verify if the user's guess was correct
	  * @param string $privkey
	  * @param string $remoteip
	  * @param string $challenge
	  * @param string $response
	  * @param array $extra_params an array of extra variables to post to the server
	  * @return boolean
	  */
	protected static function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array()) {

		if ($privkey == null || $privkey == '') {
			die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
		}

		if ($remoteip == null || $remoteip == '') {
			die ("For security reasons, you must pass the remote ip to reCAPTCHA");
		}

		//discard spam submissions
		if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
			return false;
		}

		$response =
		self::_recaptcha_http_post (
			self::RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify",
			array (
				'privatekey' => $privkey,
				'remoteip' => $remoteip,
				'challenge' => $challenge,
				'response' => $response
			) + $extra_params
		);

		$answers = explode ("\n", $response [1]);

		return (trim ($answers [0]) == 'true');
	}

//	protected static function _recaptcha_aes_pad($val) {
//		$block_size = 16;
//		$numpad = $block_size - (strlen ($val) % $block_size);
//		return str_pad($val, strlen ($val) + $numpad, chr($numpad));
//	}





}
