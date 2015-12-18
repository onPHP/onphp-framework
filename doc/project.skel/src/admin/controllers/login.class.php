<?php
/***************************************************************************
 *   Copyright (C) 2006 by Unknown Hero                                    *
 *   non.existent.login@forgotten.host                                     *
 ***************************************************************************/
/* $Id$ */

	final class login implements Controller
	{
		public function handleRequest(HttpRequest $request)
		{
			$form =
				Form::create()->
				add(
					Primitive::string('username')->
					setMax(64)->
					required()
				)->
				add(
					Primitive::string('password')->
					addImportFilter(
						Filter::hash()
					)->
					required()
				)->
				import($request->getPost());
			
			if (!$form->getErrors()) {
				
				try {
					$admin = Administrator::dao()->logIn(
						$form->getValue('username'),
						$form->getValue('password')
					);
				} catch (ObjectNotFoundException $e) {
					// failed to log in
					return ModelAndView::create()->setView('error');
				}
				
				if (!Session::isStarted())
					Session::start();
				
				Session::assign(Administrator::LABEL, $admin);

				return
					ModelAndView::create()->
					setView(new RedirectToView('main'));
			}
			
			return ModelAndView::create()->setView('login');
		}
	}
?>