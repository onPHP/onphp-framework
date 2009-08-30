<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Scheglov K.                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   Based on Net/URL.php (C) PEAR: Richard Heyes <richard at php net>     *
 *                                                                         *
 ***************************************************************************/

	class UrlException extends BaseException {}
	
	class Url
	{
		private $url			= null;
		private $protocol		= "http";
		
		private $credentials	= null;
		
		private $path			= null;
		private $anchor			= null;
		
		private $queryString	= array();
		private $useBrackets	= true;
		
		public static function create()
		{
			return new Url();
		}
		
		public function setCredentials(Credentials $credentials)
		{
			$this->credentials = $credentials;
			
			return $this;
		}
		
		public function getCredentials()
		{
			return $this->credentials;
		}
		
		public function dropCredentials()
		{
			unset($this->credentials);
			
			return $this;
		}
		
		public function setUrl($url)
		{
			$this->url = $url;
			
			return $this;
		}
		
		public function setAll()
		{
			if (!empty($this->url)) {
				$urlinfo = parse_url($this->url);
				
				$this->queryString = array();
				
				$credentials = new Credentials();
				
				foreach ($urlinfo as $key => $value) {
					switch ($key) {
						case 'user':
							$credentials->setUsername($value);
							break;
						
						case 'pass':
							$credentials->setPassword($value);
							break;
						
						case 'host':
							$credentials->setHost($value);
							break;
						
						case 'port':
							$credentials->setPort($value);
							break;
						
						case 'path':
							if ($value{0} == '/') {
								$this->path = $value;
							} else {
								$path =
									dirname($this->path) == DIRECTORY_SEPARATOR
										? ''
										: dirname($this->path);
								
								$this->path = sprintf('%s/%s', $path, $value);
							}
							
							break;
						
						case 'query':
							$this->queryString = $this->parseRawQueryString($value);
							break;
						
						case 'fragment':
							$this->anchor = $value;
							break;
					}
				}
				
				$this->setCredentials($credentials);
			}
			
			return $this;
		}
		
		public function getUrl()
		{
			return $this->url;
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
		
		public function setPath($path)
		{
			$this->path = $path;
			
			return $this;
		}
		
		public function getPath()
		{
			return $this->path;
		}
		
		public function setAnchor($anchor)
		{
			$this->anchor = $anchor;
			
			return $this;
		}
		
		public function getAnchor()
		{
			return $this->anchor;
		}
		
		public function setUseBrackets($useBrackets = true)
		{
			$this->useBrackets = ($useBrackets === true ? true : false);	
		}
		
		public function isUserBrackets()
		{
			return $this->useBrackets;
		}
		
		public function setQueryString($string)
		{
			if (!empty($string)) {
				$this->queryString = parseRawQueryString($string);
			} else
				$this->queryString = null;
			
			return $this;
		}
		
		public function isQueryString()
		{
			return ($this->queryString === null ? false : true);
		}
		
		public function getQueryString()
		{
			return $this->queryString;
		}
		
		public function getFlatQueryString()
		{
			if ($this->queryString) {
				foreach ($this->queryString as $name => $value) {
					if (is_array($value)) {
						foreach ($value as $key => $val)
							$queryString[] = $this->isUseBrackets()
								? sprintf('%s[%s]=%s', $name, $key, $val)
								: ($name . '=' . $val);
					} elseif (!is_null($value)) {
						$queryString[] = $name . '=' . $value;
					} else
						$queryString[] = $name;
				}
				
				$queryString = implode(
					ini_get('arg_separator.output'), $queryString
				);
			} else
				$queryString = null;
			
			return $queryString;
		}
		
		public function addQueryString($name, $value, $preEncoded = false)
		{
			if ($preEncoded)
				$this->queryString[$name] = $value;
			else
				$this->queryString[$name] =
					is_array($value)
						? array_map('rawurlencode', $value)
						: rawurlencode($value);
			
			return $this;
		}
		
		public function removeQueryString($name)
		{
			if (isset($this->queryString[$name]))
				unset($this->queryString[$name]);
			
			return $this;
		}
		
		public function getStandardPort($scheme)
		{
			switch (strtolower($scheme)) {
				case 'http':	return 80;
				case 'https':	return 443;
				case 'ftp':		return 21;
				case 'imap':	return 143;
				case 'imaps':	return 993;
				case 'pop3':	return 110;
				case 'pop3s':	return 995;
				
				default:		return null;
		   }
		}
		
		private function parseRawQueryString($queryString)
		{
			$parts =
				preg_split(
					'/['
						.preg_quote(ini_get('arg_separator.input'), '/')
						. ']/',
					$queryString,
					-1,
					PREG_SPLIT_NO_EMPTY
				);
			
			$return = array();
			
			foreach ($parts as $part) {
				if (strpos($part, '=') !== false) {
					$value = substr($part, strpos($part, '=') + 1);
					$key   = substr($part, 0, strpos($part, '='));
				} else {
					$value = null;
					$key   = $part;
				}
				
				if (substr($key, -2) == '[]') {
					$key = substr($key, 0, -2);
					if (!is_array($return[$key])) {
						$return[$key]   = array();
						$return[$key][] = $value;
					} else
						$return[$key][] = $value;
				} elseif (!$this->useBrackets AND !empty($return[$key])) {
					$return[$key]   = (array)$return[$key];
					$return[$key][] = $value;
				} else
					$return[$key] = $value;
			}
			
			return $return;
		}
	}
?>