<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class DBValue implements SQLExpression 
	{
		private $value = null;

		public function __construct($value)
		{
			$this->value = $value;
		}

		public function getValue()
		{
			return $this->value;
		}

		public function toString(DB $db)
		{
			return $db->quoteValue($this->value);
		}
	}
?>