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

	class DBRaw implements LogicalObject
	{
		private $string = null;
		
		public function __construct($rawString)
		{
			$this->string = $rawString;
		}
		
		public function toString(DB $db)
		{
			return $this->string;
		}
		
		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException();
		}
	}
?>