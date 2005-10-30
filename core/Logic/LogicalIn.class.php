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
	 * @deprecated and obsoleted since 0.2.3, target removal release - 0.2.5
	**/
	class LogicalIn implements LogicalObject
	{
		const IN		= 'in';
		const NOT_IN	= 'not in';
		
		private $field = null;
		private $value = null;
		private $logic = null;

		public function __construct($field, $value, $logic)
		{
			$this->field = $field;
			$this->value = $value;
			$this->logic = $logic;
		}
		
		public function toString(Dialect $dialect)
		{
			if ($this->value instanceof SelectQuery)
				$value = $this->value->toString($dialect);
			elseif (is_array($this->value)) {
				
				$quoted = array();

				foreach ($this->value as $item)
					$quoted[] = $dialect->quoteValue($item);
				
				$value = implode(', ', $quoted);

			} else
				$value = $dialect->quoteValue($this->value);
			
			return
				$dialect->fieldToString($this->field).
				" {$this->logic} ({$value})";
		}
		
		public function toBoolean(Form $form)
		{
			if ($this->value instanceof SelectQuery)
				throw new UnsupportedMethodException();

			$left	= Expression::toValue($form, $this->field);
			$right	= Expression::toValue($form, $this->value);
			
			if (!is_array($right) || is_array($left))
				throw new WrongArgumentException();
			
			$out = in_array($left, $right);

			switch ($this->logic) {
				case self::IN:
					return $out;

				case self::NOT_IN:
					return !$out;

				default:
					throw new UnsupportedMethodException();
			}
		}
	}
?>