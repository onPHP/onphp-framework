<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

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
		
		public function getAlias()
		{
			return $this->alias;
		}
		
		public function toString(Dialect $dialect)
		{
			return
				parent::toString($dialect).
				($this->alias ? ' AS '.$dialect->quoteField($this->alias) : null);
		}
	}
?>