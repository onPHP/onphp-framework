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

	abstract class FieldTable extends Castable implements DialectString
	{
		protected $field = null;

		public function __construct(DialectString $field)
		{
			$this->field = $field;
		}

		public function toString(Dialect $dialect)
		{
			$out = $dialect->fieldToString($this->field);
			
			return
				$this->cast
					? $dialect->toCasted($out)
					: $out;
		}
	}
?>