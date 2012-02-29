<?php
/****************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
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
	final class BinaryExpression implements LogicalObject, MappableObject
	{
		const EQUALS			= '=';
		const NOT_EQUALS		= '!=';
		
		const EXPRESSION_AND	= 'AND';
		const EXPRESSION_OR		= 'OR';

		const GREATER_THAN		= '>';
		const GREATER_OR_EQUALS	= '>=';

		const LOWER_THAN		= '<';
		const LOWER_OR_EQUALS	= '<=';

		const LIKE				= 'LIKE';
		const NOT_LIKE			= 'NOT LIKE';
		const ILIKE				= 'ILIKE';
		const NOT_ILIKE			= 'NOT ILIKE';

		const SIMILAR_TO		= 'SIMILAR TO';
		const NOT_SIMILAR_TO	= 'NOT SIMILAR TO';
		
		const ADD				= '+';
		const SUBSTRACT			= '-';
		const MULTIPLY			= '*';
		const DIVIDE			= '/';
		const MOD				= '%';
		
		private $left	= null;
		private $right	= null;
		private $logic	= null;
		private $brackets = true;
		
		/**
		 * @return BinaryExpression
		 */
		public static function create($left, $right, $logic)
		{
			return new self($left, $right, $logic);
		}
		
		public function __construct($left, $right, $logic)
		{
			$this->left		= $left;
			$this->right	= $right;
			$this->logic	= $logic;
		}
		
		public function getLeft()
		{
			return $this->left;
		}
		
		public function getRight()
		{
			return $this->right;
		}
		
		public function getLogic()
		{
			return $this->logic;
		}
		
		/**
		 * @param boolean $noBrackets
		 * @return BinaryExpression
		 */
		public function noBrackets($noBrackets = true)
		{
			$this->brackets = !$noBrackets;
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$sql = $dialect->toFieldString($this->left)
				.' '.$dialect->logicToString($this->logic).' '
				.$dialect->toValueString($this->right);
			return $this->brackets ? "({$sql})" : $sql;
		}
		
		/**
		 * @return BinaryExpression
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			$expression = new self(
				$dao->guessAtom($this->left, $query),
				$dao->guessAtom($this->right, $query),
				$this->logic
			);
			
			return $expression->noBrackets(!$this->brackets);
		}
		
		public function toBoolean(Form $form)
		{
			$left	= $form->toFormValue($this->left);
			$right	= $form->toFormValue($this->right);
			
			$both =
				(null !== $left)
				&& (null !== $right);
				
			switch ($this->logic) {
				case self::EQUALS:
					return $both && ($left == $right);

				case self::NOT_EQUALS:
					return $both && ($left != $right);

				case self::GREATER_THAN:
					return $both && ($left > $right);

				case self::GREATER_OR_EQUALS:
					return $both && ($left >= $right);

				case self::LOWER_THAN:
					return $both && ($left < $right);

				case self::LOWER_OR_EQUALS:
					return $both && ($left <= $right);

				case self::EXPRESSION_AND:
					return $both && ($left && $right);
				
				case self::EXPRESSION_OR:
					return $both && ($left || $right);
				
				case self::ADD:
					return $both && ($left + $right);
				
				case self::SUBSTRACT:
					return $both && ($left - $right);
				
				case self::MULTIPLY:
					return $both && ($left * $right);
				
				case self::DIVIDE:
					return $both && $right && ($left / $right);
					
				case self::MOD:
					return $both && $right && ($left % $right);
				
				default:
					throw new UnsupportedMethodException(
						"'{$this->logic}' doesn't supported yet"
					);
			}
		}
	}
?>