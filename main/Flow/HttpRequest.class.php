<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
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
		
		// uploads
		private $files		= array();

		// all other sh1t
		private $attached	= array();
		
		/**
		 * @return HttpRequest
		**/
		public static function create()
		{
			return new self;
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
		 * @return HttpRequest
		**/
		public function setGet(/* array */ $get)
		{
			$this->get = $get;
			
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
		 * @return HttpRequest
		**/
		public function setPost(/* array */ $post)
		{
			$this->post = $post;
			
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
		 * @return HttpRequest
		**/
		public function setServer(/* array */ $server)
		{
			$this->server = $server;
			
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
		 * @return HttpRequest
		**/
		public function setCookie(/* array */ $cookie)
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
		 * @return HttpRequest
		**/
		public function setFiles(/* array */ $files)
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
		 * @return HttpRequest
		**/
		public function setSession(/* array */ &$session)
		{
			$this->session = &$session;
			
			return $this;
		}
		
		/**
		 * @return HttpRequest
		**/
		public function setAttachedVar($name, $var)
		{
			$this->attached[$name] = $var;
			
			return $this;
		}

		/**
		 * @deprecated by getAttached
		**/
		public function getAttachedList()
		{
			return $this->getAttached();
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
		 * @return HttpRequest
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
	}
?>