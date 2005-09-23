<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich, Konstantin V. Arkhipov        *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class FormField
	{
		private $primitiveName	= null;
		
		public function __construct($name)
		{
			$this->primitiveName = $name;
		}
		
		public static function create($name)
		{
			return new FormField($name);
		}

		public function getName()
		{
			return $this->primitiveName;
		}
		
		public function toValue(Form $form)
		{
			return $form->getValue($this->primitiveName);
		}
	}
?>