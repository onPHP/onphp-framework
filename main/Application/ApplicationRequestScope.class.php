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

	final class ApplicationRequestScope
	{
		private $globalScope	= array();
		private $userScope		= array();
		
		private $namedScopes	= array();
		
		/**
		 * @return ApplicationUrl
		**/
		public static function create()
		{
			return new self;
		}
		
		public function transform($newUserScope = null)
		{
			$result = clone $this;
			
			if ($newUserScope !== null)
				$result->userScope = $newUserScope;
			
			return $result;
		}
		
		
		public function getWholeScope()
		{
			$result = $this->globalScope + $this->userScope;
			
			foreach ($this->namedScopes as $scope)
				$result = $result + $scope;
			
			return $result;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function addGlobalScope($scope)
		{
			return $this->addScope($this->globalScope, $scope);
		}
		
		public function getGlobalScope()
		{
			return $this->globalScope;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function addUserScope($scope)
		{
			return $this->addScope($this->userScope, $scope);
		}
		
		public function getUserScope()
		{
			return $this->userScope;
		}
		
		public function setUserScopeVar($var, $value)
		{
			$this->userScope[$var] = $value;
			
			return $this;
		}
		
		public function addNamedScope($name, $scope)
		{
			if (!isset($this->namedScopes[$name]))
				$this->namedScopes[$name] = array();
			
			$this->addScope($this->namedScopes[$name], $scope);
		}
		
		private function addScope(&$original, $additional)
		{
			Assert::isArray($additional);
			
			$original = ArrayUtils::mergeRecursiveUnique(
				$original, $additional
			);
			
			return $this;
		}
	}
?>