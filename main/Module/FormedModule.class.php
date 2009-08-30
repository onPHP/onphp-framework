<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Module with incapsulated Form object.
	 * 
	 * @ingroup Module
	**/
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

		protected function blowOut($module = DEFAULT_MODULE)
		{
			if (!HeaderUtils::redirectBack())
				HeaderUtils::redirect(
					ModuleFactory::spawn($module)
				);
			
			return $this;
		}
	}
?>