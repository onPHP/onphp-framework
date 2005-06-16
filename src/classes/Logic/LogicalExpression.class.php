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

	class LogicalExpression implements LogicalObject
	{
		const EQUALS			= '=';
		const NOT_EQUALS		= '!=';

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

		const SIMILAR_TO		= 'SIMILAR TO';
		const NOT_SIMILAR_TO	= 'NOT SIMILAR TO';

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
		
		public function toString(DB $db)
		{
			$string = '(';

			if (null !== $left = $this->left) {
				if (is_object($left))
					$string .= $left->toString($db);
				else
					$string .= $db->quoteField($left);
			}

			$string .= " {$this->logic} ";

			if (null !== $right = $this->right) {
				if (is_object($right))
					$string .= $right->toString($db);
				else
					$string .= $db->quoteValue($this->right);
			}

			$string .= ')';

			return $string;
		}
		
		public function toBoolean(Form $form)
		{
			$both = 
				(null !== $this->left) &&
				(null !== $this->right);

			try {
				$left = &$form->getValue($this->left);
			} catch (ObjectNotFoundException $e) {
				$left = &$this->left;
			}

			try {
				$right = &$form->getValue($this->right);
			} catch (ObjectNotFoundException $e) {
				$right = &$this->right;
			}
			
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
					return $both && ($left && $right->toBoolean($form));

				case self::EXPRESSION_OR:
					return $both && ($left || $right->toBoolean($form));

				case self::LIKE:
				case self::NOT_LIKE:
				default:
					throw new UnsupportedMethodException();
					break;
			}
		}
	}
?>