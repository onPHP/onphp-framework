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
	class TakeCommand implements EditorCommand
	{
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			if (!$form->getErrors()) {
				FormUtils::form2object($form, $subject);
				
				$subject = $subject->dao()->take($subject);
				
				return
					ModelAndView::create()->
					setView(
						EditorController::COMMAND_SUCCEEDED
					)->
					setModel(
						Model::create()->
						set('id', $subject->getId())
					);
			}
			
			return new ModelAndView();
		}
	}
?>