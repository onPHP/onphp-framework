<?php
/****************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */
	
	/**
	 * @ingroup Flow
	**/
	abstract class MethodMappedController implements Controller
	{
		private $methodMap = array();
		
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
			
			// notreached
		}
		
		/**
		 * @return string
		 */
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