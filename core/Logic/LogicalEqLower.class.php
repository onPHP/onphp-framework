<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov, Anton Lebedevich   *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @deprecated by LogicalExpression
	 * @obsoleted since 0.2.3, target removal release - 0.2.4
	**/
	class LogicalEqLower implements LogicalObject
	{
		private $field = null;
		private $value = null;

		public function __construct($field, $value)
		{
			$this->field = $field;
			$this->value = $value;
		}

		public function toString(Dialect $dialect)
		{
			return
				"(lower({$dialect->fieldToString($this->field)}) = ".
				"lower({$dialect->quoteValue($this->value)}))";
		}
		
		public function toBoolean(Form $form)
		{
			$left	= Expression::toValue($form, $this->field);
			$right	= Expression::toValue($form, $this->value);

			return strtolower($left) === strtolower($right);
		}
	}
?>