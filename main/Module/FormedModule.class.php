<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class FormedModule extends BaseModule
	{
		protected $form = null;
		
		public function __construct()
		{
			parent::__construct();

			$this->form = new Form();
		}
		
		public function getForm()
		{
			return $this->form;
		}

		protected function blowOut()
		{
			if (!HeaderUtils::redirectBack())
				HeaderUtils::redirect(
					ModuleFactory::spawn(DEFAULT_MODULE)
				);
			
			return $this;
		}
	}
?>