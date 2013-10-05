<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	namespace Onphp;

	final class HttpRequest
	{
		// contains all variables from $_GET
		private $get 		= array();
		
		// from $_POST
		private $post		= array();
		
		// guess what
		private $server		= array();
		
		// fortune one
		private $cookie		= array();
		
		// reference, not copy
		private $session	= array();
		
		// uploads and downloads (CurlHttpClient)
		private $files		= array();
		
		// all other sh1t
		private $attached	= array();
		
		private $headers	= array();
		
		/**
		 * @var \Onphp\HttpMethod
		 */
		private $method		= null;
		
		/**
		 * @var \Onphp\HttpUrl
		 */
		private $url		= null;

		//for CurlHttpClient if you need to send raw CURLOPT_POSTFIELDS
		private $body		= null;
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public static function create()
		{
			return new static();
		}

		/**
		 * @return HttpRequest
		**/
		public static function createFromGlobals()
		{
			$request =
				static::create()->
				setGet($_GET)->
				setPost($_POST)->
				setServer($_SERVER)->
				setCookie($_COOKIE)->
				setFiles($_FILES);

			if (isset($_SESSION))
				$request->setSession($_SESSION);

			foreach ($_SERVER as $name => $value)
				if (substr($name, 0, 5) === 'HTTP_')
					$request->setHeaderVar(substr($name, 5), $value);

			if (
				$request->hasServerVar('CONTENT_TYPE')
				&& $request->getServerVar('CONTENT_TYPE') !== 'application/x-www-form-urlencoded'
			)
				$request->setBody(file_get_contents('php://input'));

			return $request;
		}
		
		public function &getGet()
		{
			return $this->get;
		}
		
		public function getGetVar($name)
		{
			return $this->get[$name];
		}
		
		public function hasGetVar($name)
		{
			return isset($this->get[$name]);
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setGet(array $get)
		{
			$this->get = $get;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setGetVar($name, $value)
		{
			$this->get[$name] = $value;
			return $this;
		}
		
		public function &getPost()
		{
			return $this->post;
		}
		
		public function getPostVar($name)
		{
			return $this->post[$name];
		}
		
		public function hasPostVar($name)
		{
			return isset($this->post[$name]);
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setPost(array $post)
		{
			$this->post = $post;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setPostVar($name, $value)
		{
			$this->post[$name] = $value;
			return $this;
		}
		
		public function &getServer()
		{
			return $this->server;
		}
		
		public function getServerVar($name)
		{
			return $this->server[$name];
		}
		
		public function hasServerVar($name)
		{
			return isset($this->server[$name]);
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setServer(array $server)
		{
			$this->server = $server;

			return $this;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setServerVar($name, $value)
		{
			$this->server[$name] = $value;
			return $this;
		}
		
		public function &getCookie()
		{
			return $this->cookie;
		}
		
		public function getCookieVar($name)
		{
			return $this->cookie[$name];
		}
		
		public function hasCookieVar($name)
		{
			return isset($this->cookie[$name]);
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setCookie(array $cookie)
		{
			$this->cookie = $cookie;
			
			return $this;
		}
		
		public function &getSession()
		{
			return $this->session;
		}
		
		public function getSessionVar($name)
		{
			return $this->session[$name];
		}
		
		public function hasSessionVar($name)
		{
			return isset($this->session[$name]);
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setFiles(array $files)
		{
			$this->files = $files;
			
			return $this;
		}
		
		public function &getFiles()
		{
			return $this->files;
		}
		
		public function getFilesVar($name)
		{
			return $this->files[$name];
		}
		
		public function hasFilesVar($name)
		{
			return isset($this->files[$name]);
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setSession(array &$session)
		{
			$this->session = &$session;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setAttachedVar($name, $var)
		{
			$this->attached[$name] = $var;
			
			return $this;
		}
		
		public function &getAttached()
		{
			return $this->attached;
		}
		
		public function getAttachedVar($name)
		{
			return $this->attached[$name];
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function unsetAttachedVar($name)
		{
			unset($this->attached[$name]);
			
			return $this;
		}
		
		public function hasAttachedVar($name)
		{
			return isset($this->attached[$name]);
		}
		
		public function getByType(RequestType $type)
		{
			return $this->{$type->getName()};
		}
		
		public function getHeaderList()
		{
			return $this->headers;
		}
		
		public function hasHeaderVar($name)
		{
			return isset($this->headers[$name]);
		}
		
		public function getHeaderVar($name)
		{
			return $this->headers[$name];
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function unsetHeaderVar($name)
		{
			unset($this->headers[$name]);
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setHeaderVar($name, $var)
		{
			$this->headers[$name] = $var;
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setHeaders(array $headers)
		{
			$this->headers = $headers;
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setMethod(HttpMethod $method)
		{
			$this->method = $method;
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpMethod
		**/
		public function getMethod()
		{
			return $this->method;
		}
		
		/**
		 * @return \Onphp\HttpRequest
		**/
		public function setUrl(HttpUrl $url)
		{
			$this->url = $url;
			return $this;
		}
		
		/**
		 * @return \Onphp\HttpUrl
		**/
		public function getUrl()
		{
			return $this->url;
		}
		
		public function hasBody()
		{
			return $this->body !== null;
		}
		
		public function getBody()
		{
			return $this->body;
		}
		
		/**
		 * @param string $body
		 * @return \Onphp\HttpRequest
		 */
		public function setBody($body)
		{
			$this->body = $body;
			return $this;
		}
	}
?>