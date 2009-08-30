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

	abstract class ControllersCollection implements Controller
	{
		private $innerControllers	= array();
		
		private $defaultRequestType	= null;
		private $mav				= null;
		
		public function __construct()
		{
			$this->mav =
				ModelAndView::create()->
				setModel(Model::create());
			
			$this->defaultRequestType = RequestType::post();
		}
		
		/**
		 * @return ModelAndView
		**/
		public function handleRequest(HttpRequest $request)
		{
			Assert::isNotEmptyArray(
				$this->innerControllers,
				'Add atleast one innerController first'
			);
			
			$activeController = $this->getActiveController($request);
			
			$model = $this->mav->getModel();
			
			if ($activeController) {
				$controllerName	= $activeController->getName();
				$activeMav		= $activeController->handleRequest($request);
				
				$model->set(
					TextUtils::downFirst($controllerName),
					$activeMav->getModel()
				);
				
				unset($this->innerControllers[$controllerName]);
			}
			
			foreach ($this->innerControllers as $controller) {
				$passedRequest = clone $request;
				
				$passedRequest->
					{'set'.$controller->getRequestGetter().'Var'}
					('action', null);
				
				$subMav = $controller->handleRequest($passedRequest);
				
				$model->set(
					TextUtils::downFirst($controller->getName()),
					$subMav->getModel()
				);
			}
			
			return
				isset($activeMav) && $activeMav->viewIsRedirect()
					? $activeMav
					: $this->mav;
		}
		
		/**
		 * @return ControllersCollection
		**/
		public function setMav(ModelAndView $mav)
		{
			$this->mav = $mav;
			
			return $this;
		}
		
		/**
		 * @return ModelAndView
		**/
		public function getMav()
		{
			return $this->mav;
		}
		
		/**
		 * @return ControllersCollection
		**/
		public function add(
			Controller $controller,
			RequestType $requestType = null
		)
		{
			if (!$requestType)
				$requestType = $this->defaultRequestType;
			
			$this->innerControllers[get_class($controller)] =
				ProxyController::create()->
				setInner($controller)->
				setRequestType($requestType);
			
			return $this;
		}
		
		/**
		 * @return ControllersCollection
		**/
		public function setDefaultRequestType(RequestType $requestType)
		{
			$this->defaultRequestType = $requestType;
			
			return $this;
		}
		
		/**
		 * @return Controller
		**/
		private function getActiveController(HttpRequest $request)
		{
			foreach ($this->innerControllers as $controller)
				if ($controller->isActive($request)) {
					unset($this->innerControllers[
						get_class($controller->getInner())]);
					
					return $controller;
				}
			
			return null;
		}
	}
?>