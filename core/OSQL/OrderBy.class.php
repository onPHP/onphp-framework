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
	class OrderBy extends FieldTable
	{
		private $direction = null;

		public function __construct(DialectString $field)
		{
			parent::__construct($field);

			$this->direction = new Ternary(null);
		}

		public function desc()
		{
			$this->direction->setFalse();
			return $this;
		}

		public function asc()
		{
			$this->direction->setTrue();
			return $this;
		}

		public function toDialectString(Dialect $dialect)
		{
			if ($this->field instanceof SelectQuery)
				return
					'('.$this->field->toDialectString($dialect).')'
					.$this->direction->decide(' ASC', ' DESC');
			else
				return
					parent::toDialectString($dialect)
					.$this->direction->decide(' ASC', ' DESC');
		}
	}
?>