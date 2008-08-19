<?php
/***************************************************************************
 *   Copyright (C) 2008 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/*$id$*/

	/**
	 * using java.utils.Collection Interface
	 * see http://java.sun.com/javase/6/docs/api/java/util/Collection.html
	 *
	 * @ingroup Http
	**/
	final class CookieCollection
	{
		private $cookies = array();
		
		/**
		 * @return CookieCollection
		 */
		public static function create()
		{
			return new self;
		}
		
		public function add(Cookie $cookie)
		{
			$this->cookies[$cookie->getName()] = $cookie;
			
			return $this;
		}
		
		public function addAll(array /*of Cookies*/ $cookies)
		{
			foreach ($cookies as $cookie)
				$this->cookies[$cookie->getName()] = $cookie;
				
			return $this;
		}
		
		public function clear()
		{
			$this->cookies = array();
			
			return $this;
		}
		
		public function contains(Cookie $cookie)
		{
			return isset($this->cookies[$cookie->getName()]);
		}
		
		public function containsAll(array /*of Cookies*/ $cookies)
		{
			return (array_intersect($cookies, $this->cookies) == $cookies);
		}
		
		public function isEmpty()
		{
			return (count($this->cookies) == 0);
		}
		
		public function size()
		{
			return count($this->cookies);
		}
		
		public function remove(Cookie $cookie)
		{
			if (isset($this->cookies[$cookie->getName()]))
				unset($this->cookies[$cookie->getName()]);
				
			return $this;
		}
		
		public function removeAll(array /*of Cookies*/ $cookies)
		{
			$this->cookies = array_diff($this->cookies, $cookies);
			
			return $this;
		}
		
		public function retainAll(array /*of Cookies*/ $cookies)
		{
			$this->cookies = $cookies;
			
			return $this;
		}
		
		public function getList()
		{
			return $this->cookies;
		}
		
		public function httpSetAll()
		{
			foreach ($this->cookies as $cookie)
				$cookie->httpSet();
				
			return $this;
		}

	}
?>