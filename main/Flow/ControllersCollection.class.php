<?php
/****************************************************************************
 *   Copyright (C) 2008 by Dmitry V. Sokolov                                *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	abstract class ControllersCollection implements Controller
	{
		private $innerControllers 	= array();
		private $defaultRequestType = null;
		private $mav;
		
		public function __construct()
		{
			$this->mav =
				ModelAndView::create()->
				setModel(Model::create());
			
			$this->defaultRequestType = RequestType::post();
		}
		
		public function handleRequest(HttpRequest $request)
		{
			Assert::isNotEmptyArray(
				$this->innerControllers,
				'Add atleast one innerController first'
			);
			
			foreach ($this->innerControllers as $controller) {
				$passedRequest = clone $request;
				
				if (!$controller->isActive($request)) {
					$passedRequest->
						{'set'.$controller->getRequestGetter().'Var'}
						('action', null);
				}
				
				$subMav = $controller->handleRequest($passedRequest);
				$model = $this->mav->getModel();
				
				$model->set(
					TextUtils::downFirst(get_class($controller->getInner())),
					$subMav->getModel()
				);
			}
			
			return $this->mav;
		}
		
		public function setMav(ModelAndView $mav)
		{
			$this->mav = $mav;
			
			return $this;
		}
		
		public function getMav()
		{
			return $this->mav;
		}
		
		public function add(
			Controller $controller,
			RequestType $requestType = null
		)
		{
			if (!$requestType)
				$requestType = $this->defaultRequestType;
			
			$this->innerControllers[] =
				ProxyController::create()->
				setInner($controller)->
				setRequestType($requestType);
			
			return $this;
		}
		
		public function setDefaultRequestType(RequestType $requestType)
		{
			$this->defaultRequestType = $requestType;
			
			return $this;
		}
	}
?>