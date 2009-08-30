<?php
/****************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
	
	/**
	 * @ingroup Flow
	**/
	abstract class MethodMappedController implements Controller
	{
		private $methodMap = array();
		
		/**
		 * @return ModelAndView
		**/
		public function handleRequest(HttpRequest $request)
		{
			if ($action = $this->chooseAction($request)) {
				
				$method = $this->methodMap[$action];
				$mav = $this->{$method}($request);
				$mav->getModel()->set('action', $action);
				
				return $mav;
				
			} else {
				return ModelAndView::create();
			}
			
			/* NOTREACHED */
		}
		
		public function chooseAction(HttpRequest $request)
		{
			return Form::create()->
				add(
					Primitive::choice('action')->setList($this->methodMap)
				)->
				import($request->getGet())->
				importMore($request->getPost())->
				getValue('action');
		}
		
		/**
		 * @return MethodMappedController
		**/
		public function setMethodMapping($action, $methodName)
		{
			$this->methodMap[$action] = $methodName;
			return $this;
		}
		
		public function getMethodMapping()
		{
			return $this->methodMap;
		}
	}
?>