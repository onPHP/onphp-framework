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

	class ApplicationUrl implements Stringable
	{
		private $rewriter		= null;
		
		private $globalScope	= null;
		private $scope			= null;
		
		public function __construct()
		{
			$this->globalScope = Scope::create();
			$this->scope = Scope::create();
		}
		
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
		public function setRewriter(HttpRewriter $rewriter)
		{
			$this->rewriter = $rewriter;
			
			return $this;
		}
		
		/**
		 * @return HttpRewriter
		**/
		public function getRewriter()
		{
			return $this->rewriter;
		}
		
		
		/**
		 * @return ApplicationUrl
		**/
		public function setRequestScope(Scope $scope)
		{
			$this->scope = $scope;
			
			return $this;
		}
		
		/**
		 * @return Scope
		**/
		public function getRequestScope()
		{
			return $this->scope;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setApplicationScope(Scope $scope)
		{
			$this->globalScope = $scope;
			
			return $this;
		}
		
		/**
		 * @return Scope
		**/
		public function getApplicationScope()
		{
			return $this->globalScope;
		}
		
		/**
		 * @return array
		**/
		public function getWholeScopeVars()
		{
			return $this->scope->getScope() + $this->globalScope->getScope();
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function addApplicationScope(array $scope)
		{
			$this->globalScope->merge($scope);
			
			return $this;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function addUserScope(array $scope)
		{
			$this->scope->merge($scope);
			
			return $this;
		}
		
		
		/**
		 * @return ApplicationUrl
		**/
		public function currentHref(array $additionalScope = array())
		{
			return $this->transform(
				$this->scope->transform($additionalScope)
			);
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function scopeHref(array $scope)
		{
			return $this->transform(
				Scope::create()->setScope($scope)
			);
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function baseHref()
		{
			return $this->transform(Scope::create());
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function transform(Scope $newScope)
		{
			$result = clone $this;
			
			$result->setRequestScope($newScope);
			
			return $result;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function href($rawUrl)
		{
			$url = HttpUrl::create()->parse($rawUrl);
			
			Assert::isTrue($url->isValid());
			
			return $this->scopeHref(
				$this->rewriter->getScope(
					$this->rewriter->getBase()->
						transform($url)
				)
			);
		}
		
		public function toHttpUrl()
		{
			Assert::isNotNull($this->rewriter);
			
			return
				$this->rewriter->
					getUrl(
						$this->scope->getScope()
						+ $this->globalScope->getScope()
					);
		}
		
		public function toString()
		{
			return $this->toHttpUrl()->toString();
		}
	}
?>