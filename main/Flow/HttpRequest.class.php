<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
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

		// all other shit
		private $attached	= array();
		
		public function getGet()
		{
			return $this->get;
		}
		
		public function setGet(/* array */ $get)
		{
			$this->get = $get;
			
			return $this;
		}
		
		public function getPost()
		{
			return $this->post;
		}
		
		public function setPost(/* array */ $post)
		{
			$this->post = $post;
			
			return $this;
		}
		
		public function getServer()
		{
			return $this->server;
		}
		
		public function setServer(/* array */ $server)
		{
			$this->server = $server;
			
			return $this;
		}
		
		public function getCookie()
		{
			return $this->cookie;
		}
		
		public function setCookie(/* array */ $cookie)
		{
			$this->cookie = $cookie;
			
			return $this;
		}
		
		public function getSession()
		{
			return $this->session;
		}
		
		public function setSession(/* array */ &$session)
		{
			$this->session = $session;
			
			return $this;
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
		
		public function unsetAttachedVar($name)
		{
			unset($this->attached[$name]);
			return $this;
		}
		
		public function getAttachedAsArray()
		{
			return $this->attached;
		}
	}
?>