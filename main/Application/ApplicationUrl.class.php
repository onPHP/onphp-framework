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

	final class ApplicationUrl
	{
		private $base		= null;
		
		private $applicationScope	= array();
		private $userScope			= array();
		
		private $argSeparator	= null;
		
		public static function create()
		{
			return new self();
		}
		
		public function setBase(HttpUrl $base)
		{
			$this->base = $base;
			
			return $this;
		}
		
		public function getBase()
		{
			return $this->base;
		}
		
		public function addApplicationScope($scope)
		{
			Assert::isArray($scope);
			
			$this->applicationScope = array_merge(
				$this->applicationScope, $scope
			);
			
			return $this;
		}
		
		public function addUserScope($userScope)
		{
			Assert::isArray($userScope);
			
			$this->userScope = array_merge($this->userScope, $userScope);
			
			return $this;
		}
		
		public function getArgSeparator()
		{
			if (!$this->argSeparator)
				return ini_get('arg_separator.output');
			else
				return $this->argSeparator;
		}
		
		public function setArgSeparator($argSeparator)
		{
			$this->argSeparator = $argSeparator;
			
			return $this;
		}
		
		public function currentHref(
			$additionalScope = array(),
			$absolute = false
		)
		{
			return $this->scopeHref(
				array_merge($this->userScope, $additionalScope),
				$absolute
			);
		}
		
		public function scopeHref($scope, $absolute = false)
		{
			return $this->href('?'.$this->buildQuery($scope), $absolute);
		}
		
		public function baseHref($absolute = false)
		{
			return $this->href(null, $absolute);
		}
		
		public function href($url, $absolute = false)
		{
			$parsedUrl = HttpUrl::create()->parse($url);
			
			$result = $this->base->transform($parsedUrl);
			
			if ($this->applicationScope)
				$result->appendQuery(
					$this->buildQuery($this->applicationScope),
					$this->getArgSeparator()
				);
			
			$result->normalize();
			
			if ($result->getQuery() === '')
				$result->setQuery(null);
			
			if ($absolute)
				return $result->toString();
			else
				return $result->toStringFromRoot();
		}
		
		public function absoluteHref($url)
		{
			return $this->href($url, true);
		}
		
		public function getUserQueryVars()
		{
			return $this->getQueryVars($this->userScope);
		}
		
		public function getApplicationQueryVars()
		{
			return $this->getQueryVars($this->applicationScope);
		}
		
		private function getQueryVars($scope)
		{
			$queryParts = explode(
				$this->getArgSeparator(),
				$this->buildQuery($scope)
			);
			
			$result = array();
			
			foreach ($queryParts as $queryPart) {
				if (!$queryPart)
					continue;
				
				list($key, $value) = explode('=', $queryPart, 2);
				
				$result[$key] = $value;
			}
			
			return $result;
		}
		
		private function buildQuery($scope)
		{
			return http_build_query(
				$scope, null, $this->getArgSeparator()
			);
		}
	}
?>