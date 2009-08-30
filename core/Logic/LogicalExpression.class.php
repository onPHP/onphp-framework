<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Name says it all. :-)
	 * 
	 * @ingroup Logic
	**/
	final class LogicalExpression implements LogicalObject
	{
		const EQUALS			= '=';
		const NOT_EQUALS		= '!=';
		const EQUALS_LOWER		= ' = '; // to avoid collision with EQUALS

		const IS_NULL			= 'IS NULL';
		const IS_NOT_NULL		= 'IS NOT NULL';

		const IS_TRUE			= 'IS TRUE';
		const IS_FALSE			= 'IS FALSE';

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

		const IN				= 'in';
		const NOT_IN			= 'not in';
		
		const UNION				= 'UNION';
		const UNION_ALL			= 'UNION ALL';
	
		const INTERSECT			= 'INTERSECT';
		const INTERSECT_ALL		= 'INTERSECT ALL';
	
		const EXCEPT			= 'EXCEPT';
		const EXCEPT_ALL		= 'EXCEPT ALL';
		
		private $left	= null;
		private $right	= null;
		private $logic	= null;
		
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
		
		public function toDialectString(Dialect $dialect)
		{
			$string = '(';

			if (null !== $left = $this->left) {
				if ($this->logic == self::EQUALS_LOWER )
					$left = SQLFunction::create('lower', $left);
					
				if ($left instanceof DialectString) {
					if ($left instanceof SelectQuery)
						$string .= '('.$left->toDialectString($dialect).')';
					else
						$string .= $left->toDialectString($dialect);
				} else
					$string .= $dialect->quoteField($left);
			}

			$string .= " {$this->logic} ";
			
			if (null !== $right = $this->right) {
				
				if ($this->logic == self::EQUALS_LOWER )
					$right = SQLFunction::create('lower', $right);
					
				if ($right instanceof DialectString) {
					if ($right instanceof SelectQuery)
						$string .= '('.$right->toDialectString($dialect).')';
					else
						$string .= $right->toDialectString($dialect);
				} else
					$string .= $dialect->quoteValue($this->right);
			}

			$string .= ')';

			return $string;
		}
		
		public function toBoolean(Form $form)
		{
			if ($this->left instanceof LogicalObject) {
				$this->left = $this->left->toBoolean($form);
			}
			
			if ($this->right instanceof LogicalObject) {
				$this->right = $this->right->toBoolean($form);
			}
			
			$both =
				(null !== $this->left)
				&& (null !== $this->right);

			$left	= Expression::toValue($form, $this->left);
			$right	= Expression::toValue($form, $this->right);
				
			switch ($this->logic) {
				case self::EQUALS:
					return $both && ($left == $right);

				case self::NOT_EQUALS:
					return $both && ($left != $right);

				case self::IS_NULL:
					return null === $left;

				case self::IS_NOT_NULL:
					return null !== $left;

				case self::IS_TRUE:
					return true === $left;

				case self::IS_FALSE:
					return false === $left;

				case self::GREATER_THAN:
					return $both && ($left > $right);

				case self::GREATER_OR_EQUALS:
					return $both && ($left >= $right);

				case self::LOWER_THAN:
					return $both && ($left < $right);

				case self::LOWER_OR_EQUALS:
					return $both && ($left <= $right);

				case self::SIMILAR_TO:
					return $both && (soundex($left) == soundex($right));

				case self::NOT_SIMILAR_TO:
					return $both && (soundex($left) != soundex($right));
				
				case self::EXPRESSION_AND:
					return $both && ($left && $right);
				
				case self::EXPRESSION_OR:
					return $both && ($left || $right);
				
				case self::IN:
					return $both && (in_array($left, $right));
				
				case self::NOT_IN:
					return $both && (!in_array($left, $right));
				
				case self::EQUALS_LOWER:
					return $both && (strtolower($left) === strtolower($right));

				/*
					unsupported atm:

					LIKE, NOT_LIKE,
					ILIKE, NOT_ILIKE,
					ADD, SUBSTRACT, MULTIPLY, DIVIDE,
					UNION, UNION_ALL,
					INTERSECT, INTERSECT_ALL,
					EXCEPT, EXCEPT_ALL;
				*/
				default:
					
					throw new UnsupportedMethodException(
						"'{$this->logic}' doesn't supported yet"
					);
			}
		}
	}
?>