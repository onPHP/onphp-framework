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
	
	//deprecated
	class AppUrl
	{
		private $scheme			= 'http';
		private $domain			= null;
		private $path			= '/';
		
		protected $navigationSchema	= null;
		
		/**
		 * @return ApplicationUrl
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function http()
		{
			$this->scheme = 'http';
			
			return $this;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function https()
		{
			$this->scheme = 'https';
			
			return $this;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setScheme($scheme)
		{
			$this->scheme = $scheme;
			
			return $this;
		}
		
		public function getScheme()
		{
			return $this->scheme;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setDomain($domain)
		{
			$this->domain = $domain;
			
			return $this;
		}
		
		public function getDomain($level = null)
		{
			if (!$level)
				return $this->domain;
			
			$domainParts = explode('.', $this->domain);
			
			while (count($domainParts) > $level)
				$domainParts = array_shift($domainParts);
			
			return implode('.', $domainParts);
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setPath($path)
		{
			if (substr($path, 0, 1) !== '/')
				$path = '/'.$path;
			
			if (substr($path, -1, 1) !== '/')
				throw new WrongArgumentException('path must end in / (slash)');
			
			$this->path = $path;
			
			return $this;
		}
		
		public function getPath()
		{
			return $this->path;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setUrl($url)
		{
			Assert::isString($url);
			
			$info = parse_url($url);
			
			if ($info === false)
				throw new WrongArgumentException('seriously malformed url');
			
			if (!isset($info['host']))
				throw new WrongArgumentException('domain must be specified');
			
			$notAllowedParts =
				array('query', 'port', 'user', 'pass', 'fragment');
			
			foreach ($notAllowedParts as $notAllowedPart) {
				if (isset($info[$notAllowedPart]))
					throw new WrongArgumentException(
						"not allowed url part: $notAllowedPart"
					);
			}
			
			if (isset($info['scheme']))
				$this->setScheme($info['scheme']);
			
			$this->setDomain($info['host']);
			
			if (isset($info['path']))
				$this->setPath($info['path']);
			
			return $this;
		}
		
		public function getUrl()
		{
			return $this->scheme.'://'.$this->domain.$this->path;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setNavigationSchema(NavigationSchema $schema)
		{
			$this->navigationSchema = $schema;
			
			return $this;
		}
		
		public function getNavigationSchema()
		{
			return $this->navigationSchema;
		}
		
		public function getNavigationPath(NavigationArea $area)
		{
			// TODO: implement some kind of default?
			if (!$this->navigationSchema)
				throw new WrongStateException(
					"should i use the default schema?"
				);
			
			return $this->navigationSchema->getNavigationUrl($area);
		}
		
		public function getNavigationUrl(NavigationArea $area)
		{
			return $this->path.$this->getNavigationPath($area);
		}
	}
?>