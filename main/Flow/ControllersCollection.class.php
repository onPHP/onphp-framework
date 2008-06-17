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
	abstract class ControllersCollection implements Controller
	{
		private $innerControllers 	= array();
		private $defaultRequestType = null; // RequestType
		private $mav 				= null; // ModelAndView
		
		public function __construct()
		{
			$this->defaultRequestType = RequestType::post();
			
			$this->mav =
				ModelAndView::create()->
				setModel(Model::create());
		}
		
		public function handleRequest(HttpRequest $request)
		{
			Assert::isNotEmptyArray(
				$this->innerControllers,
				'Atleast one innerController should be exists'
			);
			
			foreach ($this->innerControllers as $controller) {
				$passedRequest = clone $request;
				
				if (!$controller->isActive())
					$controller->dropAction($passedRequest);
				
				$subMav = $controller->handleRequest($passedRequest);
				
				$model = $this->mav->getModel();
				$model->set(get_class($controller), $subMav->getModel());
			}
			
			return $this->mav;
		}
		
		public function setDefaultRequestType(RequestType $type)
		{
			$this->defaultRequestType = $type;
			
			return $this;
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
		
		public function add(Controller $controller, RequestType $type = null)
		{
			$this->innerControllers[] =
				ProxyController::create()->
				setInner($controller)->
				setRequestType($type ? $type : $this->defaultRequestType);
			
			return $this;
		}
	}
?>