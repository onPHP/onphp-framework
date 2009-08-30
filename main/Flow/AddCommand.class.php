<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	class AddCommand extends TakeCommand
	{
		/**
		 * @return AddCommand
		**/
		public static function create()
		{
			return new self;
		}
		
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			$form->markGood('id');

			if (!$form->getErrors()) {
				FormUtils::form2object($form, $subject);
				
				return parent::run($subject, $form, $request);
			}
			
			return new ModelAndView();
		}
	}
?>