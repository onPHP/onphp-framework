<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Regular expression checker for Form rules
	 * 
	 * @ingroup Logic
	**/
	class PregMatch implements LogicalObject 
	{
		private $pattern	= null;
		private $field		= null;
		
		public function __construct($pattern, $field)
		{
			$this->pattern = $pattern;
			$this->field = $field;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			throw new UnsupportedMethodException('form use only');
		}
		
		public function toBoolean(Form $form)
		{
			return (bool)preg_match(
				$this->pattern, 
				Expression::toValue($form, $this->field)
			);
		}
	}
?>