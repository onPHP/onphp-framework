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
	 * see RFC 3986
	 *
	 * TODO: normalization and comparsion
	**/
	class GenericUri
	{
		const CHARS_UNRESERVED		= 'a-z0-9-._~';
		const CHARS_SUBDELIMS		= '!$&\'()*+,;=';
		const PATTERN_PCTENCODED	= '%[0-9a-f][0-9a-f]';
		
		protected $scheme		= null;
		
		protected $userInfo	= null;
		protected $host		= null;
		protected $port		= null;
		
		protected $path		= null;
		protected $query	= null;
		protected $fragment	= null;
		
		/**
		 * @return GenericUri
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GenericUri
		**/
		final public function parse($uri, $guessClass = false)
		{
			$schemePattern = '([^:/?#]+):';
			
			if (
				$guessClass
				&& ($knownSubSchemes = $this->getKnownSubSchemes())
				&& preg_match("~^{$schemePattern}~", $uri, $matches)
				&& isset($knownSubSchemes[strtolower($matches[1])])
			)
				$class = $knownSubSchemes[strtolower($matches[1])];
			else
				$class = get_class($this);
			
			$result = new $class;
			
			$schemeHierPattern = $result->getSchemeHierPattern(
				$schemePattern, $result->getHierPattern()
			);
			
			$queryFragmentPattern = $result->getQueryFragmentPattern();
			
			$pattern = "~^$schemeHierPattern$queryFragmentPattern$~";
			
			if (!preg_match($pattern, $uri, $matches))
				throw new WrongArgumentException('not well-formed URI');
			
			if ($matches[1])
				$result->setScheme($matches[2]);
			
			// yanetut.
			array_shift($matches);
			array_shift($matches);
			array_shift($matches);
			array_unshift($matches, null);
			
			return $result->applyPatternMatches($matches);
		}
		
		final public function transform(GenericUri $reference, $strict = true)
		{
			if ($this->getScheme() === null)
				throw new WrongStateException(
					'URI without scheme cannot be a base URI'
				);
			
			if (
				$reference->getScheme() !== ($strict ? null : $this->getScheme())
			) {
				$class = get_class($reference);
				$result = new $class;
				
				$result->
					setScheme($reference->getScheme())->
					setUserInfo($reference->getUserInfo())->
					setHost($reference->getHost())->
					setPort($reference->getPort())->
					setPath(self::removeDotSegments($reference->getPath()))->
					setQuery($reference->getQuery());
			} else {
				$class = get_class($this);
				$result = new $class;
				
				$result->setScheme($this->getScheme());
				
				if ($reference->getAuthority() !== null) {
					$result->
						setUserInfo($reference->getUserInfo())->
						setHost($reference->getHost())->
						setPort($reference->getPort())->
						setPath(self::removeDotSegments($reference->getPath()))->
						setQuery($reference->getQuery());
				} else {
					$result->
						setUserInfo($this->getUserInfo())->
						setHost($this->getHost())->
						setPort($this->getPort());
					
					$path = $reference->getPath();
					
					if (!$path) {
						$result->
							setPath($this->getPath())->
							setQuery(
								$reference->getQuery() !== null
								? $reference->getQuery()
								: $this->getQuery()
							);
					} else {
						$result->setQuery($reference->getQuery());
						
						if ($path[0] == '/')
							$result->setPath($path);
						else
							$result->setPath(
								self::removeDotSegments(
									self::mergePath($reference->getPath())
								)
							);
					}
				}
			}
			
			$result->setFragment($reference->getFragment());
			
			return $result;
		}
		
		public function getKnownSubSchemes()
		{
			return array_merge(
				Urn::create()->getKnownSubSchemes(),
				Url::create()->getKnownSubSchemes()
			);
		}
		
		/**
		 * @return GenericUri
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
		 * @return GenericUri
		**/
		public function setUserInfo($userInfo)
		{
			$this->userInfo = $userInfo;
			
			return $this;
		}
		
		public function getUserInfo()
		{
			return $this->userInfo;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setHost($host)
		{
			$this->host = $host;
			
			return $this;
		}
		
		public function getHost()
		{
			return $this->host;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setPort($port)
		{
			if (
				$port
				&& ($port < 1 || $port > 65535)
			)
				throw new WrongArgumentException(
					'port must be an integer from 1 to 65535'
				);
			
			$this->port = $port;
			
			return $this;
		}
		
		public function getPort()
		{
			return $this->port;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setPath($path)
		{
			$this->path = $path;
			
			return $this;
		}
		
		public function getPath()
		{
			return $this->path;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setQuery($query)
		{
			$this->query = $query;
			
			return $this;
		}
		
		/**
		 * @return GenericUri
		**/
		public function appendQuery($string, $separator = '&')
		{
			$query = $this->query;
			
			if ($query)
				$query .= $separator;
			
			$query .= $string;
			
			$this->setQuery($query);
			
			return $this;
		}
		
		public function getQuery()
		{
			return $this->query;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setFragment($fragment)
		{
			$this->fragment = $fragment;
			
			return $this;
		}
		
		public function getFragment()
		{
			return $this->fragment;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setAuthority($authority)
		{
			$authorityPattern = '~^(([^@]*)@)?((\[.+\])|([^:]*))(:(.*))?$~';
			
			if (
				!preg_match(
					$authorityPattern, $authority, $authorityMatches
				)
			)
				throw new WrongArgumentException(
					'not well-formed authority part'
				);
			
			if ($authorityMatches[1])
				$this->setUserInfo($authorityMatches[2]);
			
			$this->setHost($authorityMatches[3]);
			
			if (!empty($authorityMatches[6]))
				$this->setPort($authorityMatches[7]);
			
			return $this;
		}
		
		public function getAuthority()
		{
			$result = null;
			
			if ($this->userInfo !== null)
				$result .= $this->userInfo.'@';
			
			if ($this->host !== null)
				$result .= $this->host;
			
			if ($this->port !== null)
				$result .= ':'.$this->port;
			
			return $result;
		}
		
		public function setSchemeSpecificPart($schemeSpecificPart)
		{
			throw new UnsupportedMethodException('use parse() instead');
		}
		
		public function getSchemeSpecificPart()
		{
			$result = null;
			
			$authority = $this->getAuthority();
			
			if ($authority !== null)
				$result .= '//'.$authority;
			
			$result .= $this->path;
			
			if ($this->query !== null)
				$result .= '?'.$this->query;
			
			if ($this->fragment !== null)
				$result .= '#'.$this->fragment;
			
			return $result;
		}
		
		public function toString()
		{
			$result = null;
			
			if ($this->scheme !== null)
				$result .= $this->scheme.':';
			
			$result .= $this->getSchemeSpecificPart();
			
			return $result;
		}
		
		public function isValid()
		{
			return
				$this->isValidScheme()
				&& $this->isValidUserInfo()
				&& $this->isValidHost()
				&& $this->isValidPath()
				&& $this->isValidQuery()
				&& $this->isValidFragment();
		}
		
		public function isValidScheme()
		{
			return (
				$this->scheme === null
				|| preg_match('~^[a-z][-+.a-z0-9]*$~i', $this->scheme) == 1
			);
		}
		
		public function isValidUserInfo()
		{
			$charPattern = $this->charPattern(':');
			
			return (preg_match("/^$charPattern*$/i", $this->scheme) == 1);
		}
		
		public function isValidHost()
		{
			if (empty($this->host))
				return true;
			
			$decOctet = 
				'(\d)|'			// 0-9
				.'([1-9]\d)|'	// 10-99
				.'(1\d\d)|'		// 100-199
				.'(2[0-4]\d)|'	// 200-249
				.'(25[0-5])';	// 250-255
			
			$ipV4Address = "($decOctet)\.($decOctet)\.($decOctet)\.($decOctet)";
			
			$hexdig = '[0-9a-f]';
			
			$h16 = "$hexdig{1,4}";
			$ls32 = "(($h16:$h16)|($ipV4Address))";
			
			$ipV6Address =
				"  (                        ($h16:){6} $ls32)"
				."|(                      ::($h16:){5} $ls32)"
				."|(              ($h16)? ::($h16:){4} $ls32)"
				."|( (($h16:){0,1} $h16)? ::($h16:){3} $ls32)"
				."|( (($h16:){0,2} $h16)? ::($h16:){2} $ls32)"
				."|( (($h16:){0,3} $h16)? :: $h16:     $ls32)"
				."|( (($h16:){0,4} $h16)? ::           $ls32)"
				."|( (($h16:){0,5} $h16)? ::           $h16 )"
				."|( (($h16:){0,6} $h16)? ::                )";
			
			$unreserved = self::CHARS_UNRESERVED;
			$subDelims = self::CHARS_SUBDELIMS;
			
			$ipVFutureAddress =
				"v$hexdig+\.[{$unreserved}{$subDelims}:]+";
			
			if (
				preg_match(
					"/^\[(($ipV6Address)|($ipVFutureAddress))\]$/ix",
					$this->host
				)
			)
				return true;
			
			if (preg_match("/^$ipV4Address$/i", $this->host)) {
				return true;
			}
			
			return $this->isValidHostName();
		}
		
		public function isValidPort()
		{
			return (preg_match('~^\d*$~', $this->port) == 1);
		}
		
		public function isValidPath()
		{
			$charPattern = $this->charPattern(':@');
			
			return (
				preg_match(
					"/^($charPattern+)?"
					."(\/$charPattern*)*$/i",
					$this->path
				) == 1
			);
		}
		
		public function isValidQuery()
		{
			return $this->isValidFragmentOrQuery($this->query);
		}
		
		public function isValidFragment()
		{
			return $this->isValidFragmentOrQuery($this->fragment);
		}
		
		public function isAbsolute()
		{
			return ($this->scheme != null);
		}
		
		public function isRelative()
		{
			return (!$this->isAbsolute());
		}
		
		protected function isValidHostName()
		{
			$charPattern = $this->charPattern(null);
			
			return (
				preg_match(
					"/^$charPattern*$/i",
					$this->host
				) == 1
			);
		}
		
		protected function getSchemeHierPattern($schemePattern, $hierPattern)
		{
			return "($schemePattern)?$hierPattern?";
		}
		
		protected function getHierPattern()
		{
			return '(//([^/?#]*))';
			#       ^1 ^2
		}
		
		protected function getQueryFragmentPattern()
		{
			return '([^?#]*)(\?([^#]*))?(#(.*))?';
			#       ^3      ^4 ^5       ^6^7
		}
		
		/**
		 * @return GenericUri
		**/
		protected function applyPatternMatches($matches)
		{
			if ($matches[1])
				$this->setAuthority($matches[2]);
			
			$this->setPath($matches[3]);
			
			if (!empty($matches[4]))
				$this->setQuery($matches[5]);
			
			if (!empty($matches[6]))
				$this->setFragment($matches[7]);
			
			return $this;
		}
		
		protected function charPattern($extraChars = null)
		{
			$unreserved = self::CHARS_UNRESERVED;
			$subDelims = self::CHARS_SUBDELIMS;
			$pctEncoded = self::PATTERN_PCTENCODED;
			
			return
				"(([{$unreserved}{$subDelims}$extraChars])"
				."|({$pctEncoded}))";
		}
		
		private function isValidFragmentOrQuery($string)
		{
			$charPattern = $this->charPattern(':@\/?');
			
			return (preg_match("/^$charPattern*$/i", $string) == 1);
		}
		
		private static function removeDotSegments($path)
		{
			$segments = array();
			
			while ($path) {
				if (strpos($path, '../') === 0) {
					$path = substr($path, 3);
					
				} elseif (strpos($path, './') === 0) {
					$path = substr($path, 2);
					
				} elseif (strpos($path, '/./') === 0) {
					$path = substr($path, 2);
					
				} elseif ($path == '/.') {
					$path = '/';
					
				} elseif (strpos($path, '/../') === 0) {
					$path = substr($path, 3);
					
					if ($segments) {
						array_pop($segments);
					}
					
				} elseif ($path == '/..') {
					$path = '/';
					
					if ($segments) {
						array_pop($segments);
					}
					
				} elseif (($path == '..') || ($path == '.')) {
					$path = null;
					
				} else {
					$i = 0;
					
					if ($path[0] == '/')
						$i = 1;
					
					$i = strpos($path, '/', $i);
					
					if ($i === false)
						$i = strlen($path);
					
					$segments[] = substr($path, 0, $i);
					
					$path = substr($path, $i);
				}
			}
			
			return implode('', $segments);
		}
		
		private function mergePath($path)
		{
			if ($this->getAuthority() !== null && !$this->getPath())
				return '/'.$path;
			
			$segments = explode('/', $this->path);
			
			array_pop($segments);
			
			return implode('/', $segments).'/'.$path;
		}
	}
?>