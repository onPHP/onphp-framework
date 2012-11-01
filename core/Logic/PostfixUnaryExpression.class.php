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

	/**
	 * @ingroup Logic
	**/
	namespace Onphp;

	final class PostfixUnaryExpression implements LogicalObject, MappableObject
	{
		const IS_NULL			= 'IS NULL';
		const IS_NOT_NULL		= 'IS NOT NULL';

		const IS_TRUE			= 'IS TRUE';
		const IS_FALSE			= 'IS FALSE';

		private $subject	= null;
		private $logic		= null;
		private $brackets   = true;
		
		/**
		 * @return \Onphp\PostfixUnaryExpression
		 */
		public static function create($subject, $logic)
		{
			return new self($subject, $logic);
		}
		
		public function __construct($subject, $logic)
		{
			$this->subject	= $subject;
			$this->logic	= $logic;
		}
		
		/**
		 * @param boolean $noBrackets
		 * @return \Onphp\PostfixUnaryExpression
		 */
		public function noBrackets($noBrackets = true)
		{
			$this->brackets = !$noBrackets;
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$sql = $dialect->toFieldString($this->subject)
				.' '.$dialect->logicToString($this->logic);
			return $this->brackets ? "({$sql})" : $sql;
		}
		
		/**
		 * @return \Onphp\PostfixUnaryExpression
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			$expression = new self(
				$dao->guessAtom($this->subject, $query),
				$this->logic
			);
			
			return $expression->noBrackets(!$this->brackets);
		}
		
		public function toBoolean(Form $form)
		{
			Assert::isTrue($this->brackets, 'brackets must be enabled');
			$subject = $form->toFormValue($this->subject);
				
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