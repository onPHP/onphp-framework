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

	class InExpression implements LogicalObject
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
		
		public function toString(DB $db)
		{
			if ($this->value instanceof SelectQuery)
				$value = $this->value->toString($db);
			elseif (is_array($this->value)) {
				$value = '';

				foreach ($this->value as $item)
					$item = $db->quoteValue($item);
				
				$value = implode(', ', $this->value);

			} else
				$value = $db->quoteValue($this->value);
			
			return
				$db->fieldToString($this->field).
				" {$this->logic} ({$value})";
		}
		
		public function toBoolean(Form $form)
		{
			if ($this->value instanceof SelectQuery)
				throw new UnsupportedMethodException();
			elseif (is_array($this->value)) {
				$out = in_array($form->getValue($this->field), $this->value);

				switch ($this->logic) {
					case self::IN:
						return $out;

					case self::NOT_IN:
						return !$out;

					default:
						throw new UnsupportedMethodException();
				}
			} else
				throw new UnsupportedMethodException();
		}
	}
?>