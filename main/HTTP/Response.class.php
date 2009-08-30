<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Scheglov K.                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   Based on HTTP/Request.php (C) PEAR:                                   *
 *   Richard Heyes <richard@phpguru.org>                                   *
 ***************************************************************************/
	
	class ResponseException extends BaseException {}

	class Response
	{
		const READ_BYTES = 4096;

		private $socket 		= null;
		private $protocol 		= null;
		private $code 			= null;
		private $headers 		= array();
		private $cookies 		= array();
		private $body 			= null;
		private $chunkLength 	= 0;

		public static function create(Socket $socket)
		{
			return new Response($socket);
		}

		public function __construct(Socket $socket)
		{
			$this->socket = $socket;
		}

		public function setProtocol($protocol)
		{
			$this->protocol = $protocol;

			return $this;
		}

		public function getProtocol()
		{
			return $this->protocol;
		}

		public function setCode($code)
		{
			$this->code = $code;

			return $this;
		}

		public function getCode()
		{
			return $this->code;
		}

		public function setHeader($headerName, $headerValue)
		{
			$this->headers[$headerName] = $headerValue;

			return $this;
		}

		public function getHeader($headerName)
		{
			return
				(isset($this->headers[$headerName]))
					? $this->headers[$headerName]
					: null;
		}

		public function setCookies($cookie)
		{
			try {
				array_push($this->cookies, $cookie);
			} catch (BaseException $e) {
				throw new ResponseException("Malformed response.");
			}

			return $this;
		}

		public function getCookies()
		{
			return $this->cookies;
		}

		public function getHeaders()
		{
			return $this->headers;
		}

		public function setBody($body)
		{
			$this->body = $body;

			return $this;
		}

		public function getBody()
		{
			return $this->body;
		}

		public function setChunkLength($chunkLength)
		{
			$this->chunkLength = $chunkLength;

			return $this;
		}

		public function getChunkLength()
		{
			return $this->chunkLength;
		}

		public function process($saveBody = true)
		{
			do {
				$line = $this->socket->readLine();
				list($httpVersion, $returnCode) = sscanf($line, 'HTTP/%s %s');

				$this->setProtocol('HTTP/' . $httpVersion);
				$this->setCode(intval($returnCode));
				
				while ('' !== ($header = $this->socket->readLine()))
					$this->processHeader($header);
				
			} while (100 == $this->code);

			$chunked =
				isset($this->headers['transfer-encoding'])
				&& ('chunked' == $this->headers['transfer-encoding']);
			
			$gzipped =
				isset($this->headers['content-encoding'])
				&& ('gzip' == $this->headers['content-encoding']);

			$hasBody = false;

			while (!$this->socket->isEOF()) {
				if ($chunked) {
					$data = $this->readChunked();
				} else
					$data = $this->socket->read(self::READ_BYTES);
				
				if (!empty($data)) {
					$hasBody = true;

					if ($saveBody || $gzipped)
						$this->body .= $data;
				}
			}

			if ($hasBody) {
				if ($gzipped)
					$this->body = gzinflate(substr($this->body, 10));
			}

			return true;
		}

		private function processHeader($header)
		{
			$header = iconv("CP1251", "UTF-8", $header);
			$array = explode(': ', $header);

			$headerName = array_shift($array);
			$headerValue = implode(":", $array);

			$headerNameTmp	= strtolower($headerName);
			$headerValue	= ltrim($headerValue);

			if ('set-cookie' != $headerNameTmp) {
				$this->setHeader($headerName, $headerValue);
				$this->setHeader($headerNameTmp, $headerValue);
			} else
				$this->parseCookie($headerValue);

			return $this;			
		}

		private function parseCookie($headerValue)
		{
			$cookie = array(
				'expires'	=> null,
				'domain'	=> null,
				'path'		=> null,
				'secure'	=> false
			);

			if (!strpos($headerValue, ';')) {
				$pos = strpos($headerValue, '=');
				$cookie['name']  = trim(substr($headerValue, 0, $pos));
				$cookie['value'] = trim(substr($headerValue, $pos + 1));
			} else {
				$elements = explode(';', $headerValue);
				$pos = strpos($elements[0], '=');

				$cookie['name']  = trim(substr($elements[0], 0, $pos));
				$cookie['value'] = trim(substr($elements[0], $pos + 1));

				for ($i = 1; $i < count($elements); ++$i) {
					if (false === strpos($elements[$i], '=')) {
						$elName  = trim($elements[$i]);
						$elValue = null;
					} else
						list ($elName, $elValue) = array_map('trim', explode('=', $elements[$i]));
					
					$elName = strtolower($elName);

					if ('secure' == $elName) {
						$cookie['secure'] = true;
					} elseif ('expires' == $elName) {
						$cookie['expires'] = str_replace('"', '', $elValue);
					} elseif ('path' == $elName || 'domain' == $elName) {
						$cookie[$elName] = urldecode($elValue);
					} else
						$cookie[$elName] = $elValue;
				}
			}

			$this->setCookies($cookie);

			return $this;
		}

		private function readChunked()
		{
			if (0 == $this->chunkLength) {
				$line = $this->socket->readLine();
				
				$matches = null;

				if (preg_match('/^([0-9a-f]+)/i', $line, $matches)) {
					$this->setChunkLength(hexdec($matches[1]));

					if (0 == $this->chunkLength) {
						$this->socket->readAll();

						return null;
					}
				} elseif ($this->socket->isEOF())
					return null;
			}

			$data = $this->socket->read($this->chunkLength);
			$this->setChunkLength($this->chunkLength -  strlen($data));

			if (0 == $this->chunkLength)
				$this->socket->readLine();

			return $data;
		}
	}
?>