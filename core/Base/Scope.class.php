<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class Scope
	{
		private $scope = array();
		
		/**
		 * @return Scope
		**/
		public static function create()
		{
			return new self;
		}
		
		public function merge(array $additionalScope)
		{
			$this->scope =
				ArrayUtils::mergeRecursiveUnique(
					$this->scope, $additionalScope
				);
			
			return $this;
		}
		
		public function transform(array $additionalScope)
		{
			$result = clone $this;
			
			$scopeCopy = $this->scope;
			
			$result->
				setScope($scopeCopy)->
				merge($additionalScope);
			
			return $result;
		}
		
		public function setScope(array &$scope)
		{
			$this->scope = &$scope;
			
			return $this;
		}
		
		public function getScope()
		{
			return $this->scope;
		}
		
		public function setScopeVar($var, $value)
		{
			$this->scope[$var] = $value;
			
			return $this;
		}
		
		public function hasScopeVar($var)
		{
			return isset($this->scope[$var]);
		}
		
		public function getScopeVar($var)
		{
			if (!$this->hasScopeVar($var))
				throw WrongArgumentException("knows nothing about $var");
			
			return $this->scope[$var];
		}
		
		public function getInnerScope($var)
		{
			Assert::isArray(
				$this->getScopeVar($var),
				'subscope must be an array'
			);
			
			return self::create()->setScope(&$this->scope[$var]);
		}
	}
?>