<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	class SaveCommand extends TakeCommand
	{
		/**
		 * @return SaveCommand
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return ModelAndView
		**/
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			if (!$form->getErrors()) {
				ClassUtils::copyProperties($form->getValue('id'), $subject);
				
				FormUtils::form2object($form, $subject, false);
				
				return parent::run($subject, $form, $request);
			}
			
			return new ModelAndView();
		}
		
		protected function daoMethod()
		{
			return 'save';
		}
	}
