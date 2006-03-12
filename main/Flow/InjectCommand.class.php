<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	abstract class InjectCommand implements EditorCommand
	{
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			if ($this instanceof AddCommand)
				$form->markGood('id');
			
			if (!$form->getErrors()) {
				FormUtils::setPropertiesTo($subject, $form);
				$object = $dao->take($subject);
				
				return
					ModelAndView::create()->setModel(
						Model::create()->
							setVar('action', 'edit')->
							setVar('id', $object->getId())
					)->
					setView('selfRedirect');
			}
			
			return new ModelAndView();
		}
	}
?>