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
	class DropCommand implements EditorCommand
	{
		/**
		 * @return DropCommand
		**/
		public static function create()
		{
			return new self;
		}
		
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			if ($object = $form->getValue('id')) {
				try {
					if (!$object instanceof Identifiable)
						// already deleted
						throw new ObjectNotFoundException();
					
					$object->dao()->dropById($object->getId());
					
					return
						ModelAndView::create()->
						setView(BaseEditor::COMMAND_SUCCEEDED);
						
				} catch (ObjectNotFoundException $e) {
					$form->markMissing('id');
				}
			}
			
			return ModelAndView::create();
		}
	}
?>