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
		
		/**
		 * @return HttpRequest
		**/
		public function setFiles(/* array */ $files)
		{
			$this->files = $files;
			
			return $this;
		}
		
		public function getFiles()
		{
			return $this->files;
		}
		
		/**
		 * @return HttpRequest
		**/
		public function setSession(/* array */ &$session)
		{
			$this->session = $session;
			
			return $this;
		}
		
		/**
		 * @return HttpRequest
		**/
		public function setAttached($name, $var)
		{
			$this->attached[$name] = $var;
			
			return $this;
		}
		
		public function getAttached($name)
		{
			return $this->attached[$name];
		}
		
		/**
		 * @return HttpRequest
		**/
		public function unsetAttached($name)
		{
			unset($this->attached[$name]);
			
			return $this;
		}
		
		public function hasAttached($name)
		{
			return isset($this->attached[$name]);
		}
		
		public function &getAttachedList()
		{
			return $this->attached;
		}
		
		public function getByType(RequestType $type)
		{
			return $this->{$type->getName()};
		}
	}
?>