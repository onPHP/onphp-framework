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
		private $rewriter	= null;
		private $scope		= null;
		
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
		public function setRequestScope(ApplicationRequestScope $scope)
		{
			$this->scope = $scope;
			
			return $this;
		}
		
		/**
		 * @return ApplicationRequestScope
		**/
		public function getRequestScope()
		{
			return $this->scope;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function addApplicationScope($scope)
		{
			$this->scope->addGlobalScope($scope);
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function addUserScope($scope)
		{
			$this->scope->addUserScope($scope);
		}
		
		
		public function currentHref($additionalScope)
		{
			return $this->rewriter->getUrl(
				$this->scope->transform(null)->
					addUserScope($additionalScope)->
						getWholeScope()
			);
		}
		
		public function scopeHref($scope)
		{
			return $this->rewriter->getUrl(
				$this->scope->transform($scope)->
					getWholeScope()
			);
		}
		
		public function baseHref()
		{
			return $this->rewriter->getUrl(
				$this->scope->transform(array())->
					getWholeScope()
			);
		}
		
		public function href($rawUrl)
		{
			$url = HttpUrl::create()->parse($rawUrl);
			
			Assert::isTrue($url->isValid());
			
			return $this->currentHref(
				$this->rewriter->getScope(
					$this->rewriter->getBase()->
						transform($url)->
							toHttpRequest()
				)
			);
		}
	}
?>