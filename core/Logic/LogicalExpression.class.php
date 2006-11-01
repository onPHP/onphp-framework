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
	 * Name says it all. :-)
	 * 
	 * @ingroup Logic
	**/
	final class LogicalExpression implements LogicalObject
	{
		const EQUALS_LOWER		= ' = '; // to avoid collision with EQUALS

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
					
				if (
					(
						$this->logic == self::IN
						|| $this->logic == self::NOT_IN
					)
					&& is_array($right)
				)
					$right = new SQLArray($right);
					
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
			if ($this->left instanceof LogicalObject)
				$left = $this->left->toBoolean($form);
			else 
				$left = $this->left;
			
			if ($this->right instanceof LogicalObject)
				$right = $this->right->toBoolean($form);
			else 
				$right = $this->right;
			
			$both = 
				(null !== $left)
				&& (null !== $right);

			$left	= Expression::toValue($form, $left);
			$right	= Expression::toValue($form, $right);
				
			switch ($this->logic) {
				
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