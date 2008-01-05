<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Logic
	**/
	final class PrefixUnaryExpression implements LogicalObject, MappableObject
	{
		const NOT	= 'NOT';
		const MINUS	= '-';

		private $subject	= null;
		private $logic		= null;
		
		public function __construct($logic, $subject)
		{
			$this->subject	= $subject;
			$this->logic	= $logic;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return
				'('
				.$this->logic
				.' '.$dialect->toFieldString($this->subject)
				.')';
		}
		
		/**
		 * @return PrefixUnaryExpression
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$this->logic,
				$dao->guessAtom($this->subject, $query)
			);
		}
		
		public function toBoolean(Form $form)
		{
			$subject = $form->toFormValue($this->subject);
				
			switch ($this->logic) {
				case self::NOT :
					return false === $subject;

				default:
					
					throw new UnsupportedMethodException(
						"'{$this->logic}' doesn't supported yet"
					);
			}
		}
	}
?>