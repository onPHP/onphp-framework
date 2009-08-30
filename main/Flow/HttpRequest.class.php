<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

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
		
		public static function create()
		{
			return new self;
		}
		
		public function &getGet()
		{
			return $this->get;
		}
		
		public function setGet(/* array */ $get)
		{
			$this->get = $get;
			
			return $this;
		}
		
		public function &getPost()
		{
			return $this->post;
		}
		
		public function setPost(/* array */ $post)
		{
			$this->post = $post;
			
			return $this;
		}
		
		public function &getServer()
		{
			return $this->server;
		}
		
		public function setServer(/* array */ $server)
		{
			$this->server = $server;
			
			return $this;
		}
		
		public function &getCookie()
		{
			return $this->cookie;
		}
		
		public function setCookie(/* array */ $cookie)
		{
			$this->cookie = $cookie;
			
			return $this;
		}
		
		public function &getSession()
		{
			return $this->session;
		}
		
		public function setFiles(/* array */ $files)
		{
			$this->files = $files;
			
			return $this;
		}
		
		public function getFiles()
		{
			return $this->files;
		}
		
		public function setSession(/* array */ &$session)
		{
			$this->session = &$session;
			
			return $this;
		}
		
		/**
		 * @deprecated by setAttachedVar
		**/
		public function setAttached($name, $var)
		{
			return $this->setAttachedVar($name, $var);
		}
		
		public function setAttachedVar($name, $var)
		{
			$this->attached[$name] = $var;
			
			return $this;
		}
		
		public function getAttachedVar($name)
		{
			return $this->attached[$name];
		}
		
		/**
		 * @deprecated by getAttachedVar
		**/
		public function getAttached($name)
		{
			return $this->getAttachedVar($name);
		}
		
		public function unsetAttachedVar($name)
		{
			unset($this->attached[$name]);
			
			return $this;
		}
		
		/**
		 * @deprecated by unsetAttachedVar
		**/
		public function unsetAttached($name)
		{
			return $this->unsetAttachedVar($name);
		}
		
		public function hasAttachedVar($name)
		{
			return isset($this->attached[$name]);
		}
		
		/**
		 * @deprecated by hasAttached
		**/
		public function hasAttached($name)
		{
			return $this->hasAttachedVar($name);
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