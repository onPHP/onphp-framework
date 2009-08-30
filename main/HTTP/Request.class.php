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

	class RequestException extends BaseException {}
	
	class Request
	{
		private $url			= null;
		private $method			= 'GET';
		private $httpVersion	= '1.1';
		private $requestHeaders	= array();
		private $user			= null;
		private $password		= null;
		private $socket			= null;
		
		private $proxyMandat	= null;
		
		private $postData		= null;
		private $postFiles		= array();
		private $timeout		= 0;
		private $response		= null;
		private $allowRedirects	= false;
		private $maxRedirects	= null;
		private $redirects		= null;
		private $useBrackets	= true;
		private $saveBody		= true;
		private $readTimeout	= array(); // array(seconds, microseconds)
		private $socketOptions	= array();
		
		public function __construct($url = null)
		{
			$this->url = Url::create()->setUrl($url)->setAll();
			$this->socket = Socket::create()->setTimeout(30);
			$this->response = new Response($this->socket);
			
			$this->setRequestHeader('Connection', 'close');
			$this->setRequestHeader('User-Agent', 'OnPHP Request class');
		}
		
		public static function create($url)
		{
			return new Request($url);
		}
		
		public function setCredentials(Credentials $credentials)
		{
			$this->proxyMandat = $credentials;
			
			return $this;
		}
		
		public function getCredentials()
		{
			return $this->proxyMandat;
		}
		
		public function dropCredentials()
		{
			unset($this->proxyMandat);
			
			return $this;
		}
		
		public function getUrl()
		{
			return $this->url;
		}
		
		public function setMethod($method)
		{
			$this->method = $method;
			
			return $this;
		}
		
		public function getMethod()
		{
			return $this->method;
		}
		
		public function setHttpVersion($httpVersion)
		{
			$this->httpVersion = $httpVersion;
			
			return $this;
		}
		
		public function getHttpVersion()
		{
			return $this->httpVersion;
		}
		
		public function setRequestHeader($name, $value)
		{
			$this->requestHeaders[$name] = $value;
			
			return $this;
		}
		
		public function getResponseHeader($name)
		{
			$header = $this->response->getHeader($name);
			
			return !empty($header) ? $header : null;
		}

		public function removeRequestHeader($name)
		{
			if (isset($this->requestHeaders[$name]))
				unset($this->requestHeaders[$name]);
			
			return $this;
		}
		
		public function getRequestHeaders()
		{
			return $this->requestHeaders;
		}
		
		public function getQuantityRequestHeaders()
		{
			return count($this->requestHeaders);
		}
		
		public function setUser($user)
		{
			$this->user = $user;
			
			return $this;
		}
		
		public function getUser()
		{
			return $this->user;
		}
		
		public function setPassword($password)
		{
			$this->password = $password;
			
			return $this;
		}
		
		public function getPassword()
		{
			return $this->password;
		}
		
		public function getSocket()
		{
			return $this->socket;
		}
		
		public function setPostData($name, $value, $preEncoded = false)
		{
			$this->postData[$name] =
				$preEncoded
					? $value
					: $this->arrayMapRecursive('urlencode', $value);
			
			return $this;
		}
		
		public function getPostData()
		{
			return $this->postData;
		}
		
		public function setPostFile(
			$inputName,
			$fileName,
			$contentType = 'application/octet-stream'
		)
		{
			if (!is_array($fileName) && !is_readable($fileName)) {
				throw new RequestException("file '{$fileName}' is not readable");
			} elseif (is_array($fileName)) {
				foreach ($fileName as $name) {
					if (!is_readable($name))
						throw new RequestException("file '{$name}' is not readable");
				}
			}
			
			$this->setRequestHeader('Content-Type', 'multipart/form-data');
			
			$this->postFiles[$inputName] = array(
				'name' => $fileName,
				'type' => $contentType
			);
			
			return $this;
		}
		
		public function getPostFiles()
		{
			return $this->postFiles;
		}
		
		public function setTimeout($timeout)
		{
			$this->timeout = $timeout;
			
			return $this;
		}
		
		public function getTimeout()
		{
			return $this->timeout;
		}
		
		public function getResponse()
		{
			return $this->response;
		}
		
		public function setAllowRedirects($allow = false)
		{
			$this->allowRedirects = ($allow === true ? true : false);
			
			return $this;
		}
		
		public function isAllowRedirects()
		{
			return $this->allowRedirects;
		}
		
		public function setMaxRedirects($maxRedirects)
		{
			$this->maxRedirects = $maxRedirects;
			
			return $this;
		}
		
		public function getMaxRedirects()
		{
			return $this->maxRedirects;
		}
		
		public function setRedirects($redirects)
		{
			$this->redirects = $redirects;
			
			return $this;
		}
		
		public function getRedirects()
		{
			return $this->redirects;
		}
		
		public function setUseBrackets($useBrackets = false)
		{
			$this->useBrackets = ($useBrackets === true ? true : false);
			
			return $this;
		}
		
		public function isUseBrackets()
		{
			return $this->useBrackets;
		}
		
		public function setSaveBody($saveBody = true)
		{
			$this->saveBody = ($saveBody === true ? true : false);
			
			return $this;
		}
		
		public function isSaveBody()
		{
			return $this->saveBody;
		}
		
		public function setReadTimeout($seconds, $microseconds)
		{
			$this->readTimeout = array($seconds, $microseconds);
			
			return $this;
		}
		
		public function getReadTimeout()
		{
			return $this->readTimeout;
		}
		
		public function setSocketOptions($socketOptions)
		{
			$this->socketOptions = $socketOptions;
			
			return $this;
		}
		
		public function getSocketOptions()
		{
			return $this->socketOptions;
		}
		
		public function addCookie($name, $value)
		{
			$cookies =
				isset($this->requestHeaders['Cookie'])
					? $this->requestHeaders['Cookie']. '; '
					: '';
			
			$this->setRequestHeader(
				'Cookie',
				$cookies . urlencode($name) . '=' . urlencode($value)
			);
			
			return $this;
		}
		
		public function getResponseCode()
		{
			$code = $this->response->getCode();
			return isset($code) ? $code : false;
		}
		
		public function sendRequest($saveBody = true)
		{
			$host = $this->url->getCredentials()->getHost();
			$port = $this->url->getCredentials()->getPort();
			
			$this->socket->
				setHost($host)->
				setPort($port)->
				setTimeout($this->timeout)->
				setOptions($this->getSocketOptions())->
				connect();
			
			$this->socket->write($this->buildRequest());
			
			if (!empty($this->readTimeout))
				$this->socket->setTimeout(
					$this->readTimeout[0],
					$this->readTimeout[1]
				);
			
			$this->response->process($this->isSaveBody() && $saveBody);
			
			$responseLocation = $this->response->getHeaders('location');
			if ($this->isAllowRedirects()
				&& $this->redirects <= $this->maxRedirects
				&& $this->responseCode() > 300
				&& $this->responseCode() < 399
				&& !empty($responseLocation)
			) {
				$redirect = $this->response->getHeader('location');
				
				if (preg_match('/^https?:\/\//i', $redirect)) {
					$this->url = Url::create()->setCredentials(
						Credentials::create()->setUrl($redirect)
					);
					$this->setRequestHeader('Host', $this->generateHostHeader());
				} elseif ($redirect{0} == '/') {
					$this->url->setPath($redirect);
				} elseif (substr($redirect, 0, 3) == '../' || substr($redirect, 0, 2) == './') {
					if (substr($this->url->getPath(), -1) == '/') {
						$redirect = $this->url->getPath() . $redirect;
					} else
						$redirect = dirname($this->url->getPath()) . '/' . $redirect;

					$this->url->setPath($redirect);
				} else {
					if (substr($this->url->getPath(), -1) == '/') {
						$redirect = $this->url->getPath() . $redirect;
					} else
						$redirect = dirname($this->url->getPath()) . '/' . $redirect;

					$this->url->setPath($redirect);
				}

				$this->setRedirects($this->redirects+1);

				return $this->sendRequest($saveBody);

			} elseif ($this->isAllowRedirects() && $this->getRedirects() > $this->maxRedirects)
				throw new RequestException("Too many redirects");

			$this->socket->disconnect();

			return true;
		}

		public function getResponseCookies()
		{
			$cookies = $this->response->getCookies();

			return isset($cookies) ? $cookies : false;
		}

		public function addQueryString($name, $value, $preencoded = false)
		{
			$this->url->addQueryString($name, $value, $preencoded);
		}

		private function generateHostHeader()
		{
			if (($this->url->getPort() != 80) && (strcasecmp($this->url->getProtocol(), 'http') == 0)) {
				$host = $this->url->getHost() . ':' . $this->url->getPort();
			} elseif (($this->url->getPort() != 443) && (strcasecmp($this->url->getProtocol(), 'https') == 0)) {
				$host = $this->url->getHost() . ':' . $this->url->getPort();
			} elseif (($this->url->getPort() == 443) && (strcasecmp($this->url->getProtocol(), 'https') == 0) && (strpos($this->url->getUrl(), ':443') !== false)) {
				$host = $this->url->getHost() . ':' . $this->url->getPort();
			} else
				$host = $this->url->getHost();

			return $host;
		}

		private function buildRequest()
		{
			$separator = ini_get('arg_separator.output');
			ini_set('arg_separator.output', '&');

			$queryString = $this->url->getFlatQueryString();
			$queryString = ($queryString) ? '?' . $queryString : '';

			ini_set('arg_separator.output', $separator);

			$urlPath = $this->url->getPath();

			$host = !empty($this->proxyHost) ? $this->url->getProtocol() . '://' . $this->url->getHost() : '';
			$port = (!empty($this->proxyPort) && $this->url->getPort() != 80) ? ':' . $this->url->getPort() : '';
			$path = (empty($urlPath)? '/': $this->url->getPath()) . $queryString;
			$url  = $host . $port . $path;

			$request = $this->method . ' ' . $url . ' HTTP/' . $this->httpVersion . "\r\n";

			if ('POST' != $this->method && 'PUT' != $this->method) {
				$this->removeRequestHeader('Content-Type');
			} else {
				if (empty($this->requestHeaders['Content-Type'])) {
					$this->setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				} elseif ('multipart/form-data' == $this->requestHeaders['Content-Type']) {
					$boundary = 'HTTP_Request_' . md5(uniqid('request') . microtime());
					$this->setRequestHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);
				}
			}

			if ($this->getQuantityRequestHeaders() > 0) {
				foreach ($this->requestHeaders as $name => $value)
					$request .= $name . ': ' . $value . "\r\n";
			}

			if (('POST' != $this->method
					&& 'PUT' != $this->method)
				||
				(empty($this->postData)
					&& empty($this->postFiles)))
			{

				$request .= "\r\n";
			} elseif ((!empty($this->postData) && is_array($this->postData)) || !empty($this->postFiles)) {
				if (!isset($boundary)) {
					$postData = implode('&',
									array_map(
										create_function('$a', 'return $a[0] . \'=\' . $a[1];'),
										$this->flattenArray('', $this->getPostData())
									)
								);
				} else {
					$postData = '';

					if (!empty($this->postData)) {
						$flatData = $this->flattenArray('', $this->getPostData());

						foreach ($flatData as $item) {
							$postData .= '--' . $boundary . "\r\n";
							$postData .= 'Content-Disposition: form-data; name="' . $item[0] . '"';
							$postData .= "\r\n\r\n" . urldecode($item[1]) . "\r\n";
						}
					}

					foreach ($this->postFiles as $name => $value) {
						if (is_array($value['name'])) {
							$varname = $name . ($this->isUseBrackets() ? '[]': '');
						} else {
							$varname		= $name;
							$value['name']	= array($value['name']);
						}

						foreach ($value['name'] as $key => $fileName) {
							$fp   = fopen($fileName, 'r');
							$data = fread($fp, filesize($fileName));
							fclose($fp);

							$basename = basename($fileName);
							$type = is_array($value['type'])
										? $value['type'][$key]
										: $value['type'];

							$postData .=
								'--' . $boundary . "\r\n"
								.'Content-Disposition: form-data; name="'
								.$varname
								.'"; fileName="'
								.$basename
								. '"'
								."\r\nContent-Type: ".$type
								."\r\n\r\n"
								.$data
								."\r\n";
						}
					}

					$postData .= '--' . $boundary . "\r\n";
				}

				$request .= 'Content-Length: ' . strlen($postData) . "\r\n\r\n";
				$request .= $postData;

			} elseif (!empty($this->postData)) {
				$request .= 'Content-Length: ' . strlen($this->postData) . "\r\n\r\n";
				$request .= $this->postData;
			}

			return $request;
		}

		private function flattenArray($name, $values)
		{
			if (!is_array($values)) {
				return array(array($name, $values));
			} else {
				$ret = array();

				foreach ($values as $k => $v) {
					if (empty($name)) {
						$newName = $k;
					} elseif ($this->isUseBrackets()) {
						$newName = $name . '[' . $k . ']';
					} else
						$newName = $name;

					$ret = array_merge($ret, $this->flattenArray($newName, $v));
				}

				return $ret;
			}
		}

		private function arrayMapRecursive($callback, $value)
		{
			if (!is_array($value)) {
				return call_user_func($callback, $value);
			} else {
				$map = array();
				foreach ($value as $k => $v)
					$map[$k] = $this->arrayMapRecursive($callback, $v);
				
				return $map;
			}
		}
	}
?>