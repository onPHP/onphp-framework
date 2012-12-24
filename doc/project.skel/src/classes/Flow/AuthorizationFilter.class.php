<?php
/***************************************************************************
 *   Copyright (C) 2006 by Unknown Hero                                    *
 *   non.existent.login@forgotten.host                                     *
 ***************************************************************************/
/* $Id$ */

	final class AuthorizationFilter implements Controller
	{
		private $controller = null;
		
		public function __construct(Controller $controller)
		{
			$this->controller = $controller;
		}
		
		public function handleRequest(HttpRequest $request)
		{
			if (!Session::isStarted())
				Session::start();
			
			if (
				!Session::get(Administrator::LABEL) instanceof Administrator
				&& !$this->controller instanceof login
			) {
				Session::destroy();
				return ModelAndView::create()->setView('login');
			}
			
			return $this->controller->handleRequest($request);
		}
	}
