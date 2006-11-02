<?php
/****************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Logic
	**/
	final class UnaryExpression implements LogicalObject
	{
		const IS_NULL			= 'IS NULL';
		const IS_NOT_NULL		= 'IS NOT NULL';

		const IS_TRUE			= 'IS TRUE';
		const IS_FALSE			= 'IS FALSE';

		private $subject	= null;
		private $logic		= null;
		
		public function __construct($subject, $logic)
		{
			$this->subject	= $subject;
			$this->logic	= $logic;
		}
		
		public function getSubject()
		{
			return $this->subject;
		}
		
		public function getLogic()
		{
			return $this->logic;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			// TODO: incorrect for prefix operators like '-' and 'NOT'
			return 
				'('
				.Expression::toFieldString($this->subject, $dialect)
				.' '.$this->logic.')'; 
			
		}
		
		public function toBoolean(Form $form)
		{
			if ($this->subject instanceof LogicalObject)
				$subject = $this->subject->toBoolean($form);
			else 
				$subject = $this->subject;
			
			$subject = Expression::toValue($form, $subject);
				
			switch ($this->logic) {
				case self::IS_NULL:
					return null === $subject;

				case self::IS_NOT_NULL:
					return null !== $subject;

				case self::IS_TRUE:
					return true === $subject;

				case self::IS_FALSE:
					return false === $subject;

				default:
					
					throw new UnsupportedMethodException(
						"'{$this->logic}' doesn't supported yet"
					);
			}
		}
	}
?>