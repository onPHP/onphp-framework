<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	final class FieldGroup implements DialectString
	{
		private $list = array();
		
		/**
		 * @return FieldGroup
		**/
		public function add(Castable $field)
		{
			$this->list[] = $field;
			
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if (!$this->list)
				return null;
			
			$out = array();
			
			foreach ($this->list as $field)
				$out[] = $field->toDialectString($dialect);
			
			return implode(', ', $out);
		}
	}
?>