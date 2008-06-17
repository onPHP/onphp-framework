<?php
/****************************************************************************
 *   Copyright (C) 2008 by Dmitry V. Sokolov, Denis M. Gabaidulin           *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	class ProxyController implements Controller
	{
		private $innerController 	= null;
		private $request 			= null;
		private $requestType 		= null;
		private $requestGetter 		= null;
		
		private $requestGetterMap = array(
			RequestType::ATTACHED => 'getAttachedVar',
			RequestType::GET      => 'getGetVar',
			RequestType::POST     => 'getPostVar'
		);
		
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->requestType = RequestType::post();
		}
		
		public function setInner(Controller $controller)
		{
			$this->innerController = $controller;
			return $this;
		}
		
		public function getInner()
		{
			return $this->innerController;
		}
		
		public function handleRequest(HttpRequest $request)
		{
			return $this->getInner()->handleRequest();
		}
		
		public function setRequestType(RequestType $requestType)
		{
			$this->requestType = $requestType;
			
			return $this;
		}
		
		public function isActive()
		{
			return
				$this->request->{$this->getRequestGetter()}('controller')
				== get_class($this->innerController);
		}
		
		public function dropAction(HttpRequest &$request)
		{
			unset($request->{$this->getRequestGetter()}('action'));
			
			return $this;
		}
		
		private function getRequestGetter()
		{
			Assert::isNotNull($this->requestType);
			
			if (!$this->requestGetter)
				$this->requestGetter =
					$this->requestGetterMap[$this->requestType->getId()];
			
			return $this->requestGetter;
		}
	}
?>