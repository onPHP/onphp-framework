<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
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
					? $dialect->toCasted($out, $this->cast)
					: $out;
		}
	}
?>