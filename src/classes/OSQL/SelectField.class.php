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

	class SelectField extends FieldTable
	{
		private $alias = null;

		public function __construct(DBField $field, $alias)
		{
			parent::__construct($field);
			$this->alias = $alias;
		}

		public function toString(DB $db)
		{
			return
				parent::toString($db).
				($this->alias ? ' AS '.$db->quoteField($this->alias) : '');
		}
	}
?>