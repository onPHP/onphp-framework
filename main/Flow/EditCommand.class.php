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
	class EditCommand implements EditorCommand
	{
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			FormUtils::object2form($subject, $form);
			
			if ($object = $form->getValue('id'))
				FormUtils::object2form($object, $form);
			
			return ModelAndView::create();
		}
	}
?>