<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Factory for various childs of LogicalObject and LogicalExpression.
	 * 
	 * @ingroup Logic
	**/
	final class Expression extends StaticFactory
	{
		const LOGIC_AND	= 'AND';
		const LOGIC_OR	= 'OR';

		/**
		 * common cast methods.
		**/
		public static function toBoolean($logic, $left, $right)
		{
			switch ($logic) {
				case self::LOGIC_AND:
					return $left && $right;

				case self::LOGIC_OR:
					return $left || $right;

				default:
					throw new WrongArgumentException(
						"unknown logic - '{$logic}'"
					);
			}

			/* NOTREACHED */
		}
		
		public static function toValue(Form $form, $value)
		{
			return
				$value instanceof FormField
					? $form->getValue($value->getName())
					: $value;
		}
		
		/**
		 * factory
		**/
		
		public static function expAnd($left, $right)
		{
			return new LogicalExpression($left, $right, self::LOGIC_AND);
		}
		
		public static function expOr($left, $right)
		{
			return new LogicalExpression($left, $right, self::LOGIC_OR);
		}
		
		public static function eq($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::EQUALS);
		}
		
		public static function eqId($field, Identifiable $object)
		{
			return self::eq($field, $object->getId());
		}
		
		public static function notEq($field, $value)
		{
			return new LogicalExpression(
				$field, $value, LogicalExpression::NOT_EQUALS
			);
		}
		
		/// greater than
		public static function gt($field, $value)
		{
			return new LogicalExpression(
				$field, $value, LogicalExpression::GREATER_THAN
			);
		}
		
		/// greater than or equals
		public static function gtEq($field, $value)
		{
			return new LogicalExpression(
				$field, $value, LogicalExpression::GREATER_OR_EQUALS
			);
		}
		
		/// lower than
		public static function lt($field, $value)
		{
			return new LogicalExpression(
				$field, $value, LogicalExpression::LOWER_THAN
			);
		}
		
		/// lower than or equals
		public static function ltEq($field, $value)
		{
			return new LogicalExpression(
				$field, $value, LogicalExpression::LOWER_OR_EQUALS
			);
		}

		public static function notNull($field)
		{
			return new LogicalExpression($field, null, LogicalExpression::IS_NOT_NULL);
		}
		
		public static function isNull($field)
		{
			return new LogicalExpression($field, null, LogicalExpression::IS_NULL);
		}
		
		public static function isTrue($field)
		{
			return new LogicalExpression($field, null, LogicalExpression::IS_TRUE);
		}
		
		public static function isFalse($field)
		{
			return new LogicalExpression($field, null, LogicalExpression::IS_FALSE);
		}
		
		public static function like($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::LIKE);
		}
		
		public static function notLike($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::NOT_LIKE);
		}

		public static function ilike($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::ILIKE);
		}
		
		public static function notIlike($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::NOT_ILIKE);
		}
		
		public static function similar($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::SIMILAR_TO);
		}
		
		public static function notSimilar($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::NOT_SIMILAR_TO);
		}
		
		public static function eqLower($field, $value)
		{
			return new LogicalExpression(
				SQLFunction::create('lower', $field),
				SQLFunction::create('lower', $value),
				LogicalExpression::EQUALS_LOWER
			);
		}
		
		public static function between($field, $left, $right)
		{
			return new LogicalBetween($field, $left, $right);
		}
		
		// {,not}in handles strings, arrays and SelectQueries
		public static function in($field, $value)
		{
			if (is_numeric($value) && $value == (int) $value)
				return self::eq($field, $value);
			elseif (is_array($value) && count($value) == 1)
				return self::eq($field, current($value));
			else {
				
				if (is_array($value))
					$value = new SQLArray($value);
				
				return new LogicalExpression(
					$field, $value, LogicalExpression::IN
				);
			}
		}
		
		public static function notIn($field, $value)
		{
			if (is_numeric($value) && $value == (int) $value)
				return self::notEq($field, $value);
			elseif (is_array($value) && count($value) == 1)
				return self::notEq($field, current($value));
			else {
				
				if (is_array($value))
					$value = new SQLArray($value);
				
				return new LogicalExpression(
					$field, $value, LogicalExpression::NOT_IN
				);
			}
		}
		
		/// +
		public static function add($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::ADD);
		}
		
		/// -
		public static function sub($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::SUBSTRACT);
		}
		
		/// *
		public static function mul($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::MULTIPLY);
		}
		
		/// /
		public static function div($field, $value)
		{
			return new LogicalExpression($field, $value, LogicalExpression::DIVIDE);
		}

		public static function fullTextAnd($field, $wordsList)
		{
			return new FullTextSearch($field, $wordsList, DB::FULL_TEXT_AND);
		}
		
		public static function fullTextOr($field, $wordsList)
		{
			return new FullTextSearch($field, $wordsList, DB::FULL_TEXT_OR);
		}
		
		public static function fullTextRankOr($field, $wordsList)
		{
			return new FullTextRank($field, $wordsList, DB::FULL_TEXT_OR);
		}
		
		public static function fullTextRankAnd($field, $wordsList)
		{
			return new FullTextRank($field, $wordsList, DB::FULL_TEXT_AND);
		}
			
		public static function orBlock(/* ... */)
		{
			return self::block(func_get_args(), self::LOGIC_OR);
		}

		public static function andBlock(/* ... */)
		{
			return self::block(func_get_args(), self::LOGIC_AND);
		}
		
		public static function chain()
		{
			return new LogicalChain();
		}

		private static function block($args, $logic)
		{
			return new LogicalBlock($args, $logic);
		}
	}
?>