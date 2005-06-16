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

	class OrderBy extends FieldTable
	{
		private $direction = null;

		public function __construct(DBField $field)
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

		public function toString(DB $db)
		{
			$string = parent::toString($db);

			if ($this->direction->isTrue())
				$string .= ' ASC';

			if ($this->direction->isFalse())
				$string .= ' DESC';

			return $string;
		}
	}
?>