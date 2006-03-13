<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Anton E. Lebedevich                        *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Connected to concrete table DBField.
	 * 
	 * @ingroup OSQL
	**/
	class SelectField extends FieldTable
	{
		private $alias = null;

		public function __construct(DBField $field, $alias)
		{
			parent::__construct($field);
			$this->alias = $alias;
		}
		
		public function getName()
		{
			return $this->field->getField();
		}

		public function toDialectString(Dialect $dialect)
		{
			return
				parent::toDialectString($dialect)
				.(
					$this->alias
						? ' AS '.$dialect->quoteField($this->alias)
						: null
				);
		}
	}
?>