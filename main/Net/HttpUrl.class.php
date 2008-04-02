<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Net
	**/
	final class HttpUrl extends Url
	{
		protected $knownSubSchemes	= array();
		
		/**
		 * @return HttpUrl
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return HttpUrl
		 * 
		 * @see rfc2616, sec. 14.23.
		 * 
		 * Hint: example.com:443
		**/
		public function setHttpHost($host)
		{
			$parts = explode(':', $host, 2);
			
			$this->setHost($parts[0]);
			
			if (isset($parts[1]))
				$this->setPort($parts[1]);
			
			return $this;
		}
		
		public function ensureAbsolute()
		{
			$this->fixMistakenPath();
			
			if (!$this->scheme && !$this->getAuthority()) {
				$this->scheme = 'http';
				
				$segments = explode('/', $this->path);
				
				if (!empty($segments[0])) {
					// localhost/anything becomes http://localhost/anything
					
					$this->setAuthority(array_shift($segments));
					
					$this->setPath('/'.implode('/', $segments));
				}
			}
			
			$this->fixAuthorityFromPath();
			
			return $this;
		}
		
		public function isValidScheme()
		{
			if (!parent::isValidScheme())
				return false;
			
			if (
				$this->scheme
				&& !in_array(strtolower($this->scheme), array('http', 'https'))
			)
				return false;
			
			return true;
		}
		
		public function isValidPort()
		{
			if (!parent::isValidPort())
				return false;
			
			if (
				$this->port
				&& !in_array($this->port, array(80, 443))
				&& $this->port < 1024
			)
				return false;
			
			return true;
		}
		
		protected function isValidHostName()
		{
			if (!parent::isValidHostName())
				return false;
			
			$charPattern = $this->charPattern(null);
			
			// using rfc 2396, in order to detect bad ip address ranges like
			// 666.666.666.666 which are valid hostnames in generic uri syntax
			
			$topLabelPattern = '(([a-z])|([a-z]([a-z0-9-])*[a-z0-9]))\.?';
			
			return (
				preg_match(
					"/^($charPattern*\.)?{$topLabelPattern}$/i",
					$this->host
				) == 1
			);
		}
		
		public function normalize()
		{
			parent::normalize();
			
			$port = $this->getPort();
			$scheme = $this->getScheme();
			
			if (
				($scheme == 'http' && $port == '80')
				|| ($scheme == 'https' && $port == '443')
			)
				$this->setPort(null);
			
			if ($this->getPath() === null || $this->getPath() === '')
				$this->setPath('/');
			
			return $this;
		}
		
		public function makeComparable()
		{
			return $this->ensureAbsolute()->normalize()->setFragment(null);
		}
		
		// FIXME: move to HttpRequest
		public function toHttpRequest()
		{
			$getVars = array();
			$serverVars = array();
			
			if ($this->getHost()) {
				if ($this->getScheme() == 'https')
					$serverVars['HTTPS'] = true;
			
				$serverVars['HTTP_HOST'] = $this->getHost();
				$serverVars['SERVER_NAME'] = $this->getHost();
			}
			
			if ($this->getPath()) {
				$serverVars['REQUEST_URI'] = $this->getPath();
			}
			
			if ($this->getQuery()) {
				$serverVars['QUERY_STRING'] = $this->getQuery();
				parse_str($this->getQuery(), $getVars);
			}
			
			return HttpRequest::create()->
				setGet($getVars)->
				setServer($serverVars);
		}
	}
?>