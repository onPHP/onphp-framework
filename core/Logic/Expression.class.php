<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Factory for various childs of LogicalObject and LogicalExpression.
	 * 
	 * @ingroup Logic
	**/
	final class Expression extends StaticFactory
	{
		/**
		 * common cast methods.
		**/
		
		public static function toFormValue(Form $form, $value)
		{
			if ($value instanceof FormField)
				return $form->getValue($value->getName());
			elseif ($value instanceof LogicalObject)
				return $value->toBoolean($form);
			else
				return $value;
		}
		
		// TODO: consider moving to Dialect
		public static function toFieldString($expression, Dialect $dialect)
		{
			$string = '';
			if (null !== $expression) {
				if ($expression instanceof DialectString) {
					if ($expression instanceof SelectQuery)
						$string .= '('.$expression->toDialectString($dialect).')';
					else
						$string .= $expression->toDialectString($dialect);
				} else
					$string .= $dialect->quoteField($expression);
			}
			return $string;
		}
		
		// TODO: consider moving to Dialect
		public static function toValueString($expression, Dialect $dialect)
		{
			$string = '';
			if (null !== $expression) {
				if ($expression instanceof DialectString) {
					if ($expression instanceof SelectQuery)
						$string .= '('.$expression->toDialectString($dialect).')';
					else
						$string .= $expression->toDialectString($dialect);
				} else
					$string .= $dialect->quoteValue($expression);
			}
			return $string;
		}

		
		/**
		 * factory
		**/
		
		public static function expAnd($left, $right)
		{
			return new BinaryExpression($left, $right, BinaryExpression::EXPRESSION_AND);
		}
		
		public static function expOr($left, $right)
		{
			return new BinaryExpression($left, $right, BinaryExpression::EXPRESSION_OR);
		}
		
		public static function eq($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::EQUALS);
		}
		
		public static function eqId($field, Identifiable $object)
		{
			return self::eq($field, $object->getId());
		}
		
		public static function notEq($field, $value)
		{
			return new BinaryExpression(
				$field, $value, BinaryExpression::NOT_EQUALS
			);
		}
		
		/// greater than
		public static function gt($field, $value)
		{
			return new BinaryExpression(
				$field, $value, BinaryExpression::GREATER_THAN
			);
		}
		
		/// greater than or equals
		public static function gtEq($field, $value)
		{
			return new BinaryExpression(
				$field, $value, BinaryExpression::GREATER_OR_EQUALS
			);
		}
		
		/// lower than
		public static function lt($field, $value)
		{
			return new BinaryExpression(
				$field, $value, BinaryExpression::LOWER_THAN
			);
		}
		
		/// lower than or equals
		public static function ltEq($field, $value)
		{
			return new BinaryExpression(
				$field, $value, BinaryExpression::LOWER_OR_EQUALS
			);
		}

		public static function notNull($field)
		{
			return new UnaryExpression($field, UnaryExpression::IS_NOT_NULL);
		}
		
		public static function isNull($field)
		{
			return new UnaryExpression($field, UnaryExpression::IS_NULL);
		}
		
		public static function isTrue($field)
		{
			return new UnaryExpression($field, UnaryExpression::IS_TRUE);
		}
		
		public static function isFalse($field)
		{
			return new UnaryExpression($field, UnaryExpression::IS_FALSE);
		}
		
		public static function like($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::LIKE);
		}
		
		public static function notLike($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::NOT_LIKE);
		}

		public static function ilike($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::ILIKE);
		}
		
		public static function notIlike($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::NOT_ILIKE);
		}
		
		public static function similar($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::SIMILAR_TO);
		}
		
		public static function notSimilar($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::NOT_SIMILAR_TO);
		}
		
		public static function eqLower($field, $value)
		{
			return new EqualsLowerExpression($field, $value);
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
				return new InExpression(
					$field, $value, InExpression::IN
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
				return new InExpression(
					$field, $value, InExpression::NOT_IN
				);
			}
		}
		
		/// +
		public static function add($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::ADD);
		}
		
		/// -
		public static function sub($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::SUBSTRACT);
		}
		
		/// *
		public static function mul($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::MULTIPLY);
		}
		
		/// /
		public static function div($field, $value)
		{
			return new BinaryExpression($field, $value, BinaryExpression::DIVIDE);
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
			return self::block(
				func_get_args(), 
				BinaryExpression::EXPRESSION_OR
			);
		}

		public static function andBlock(/* ... */)
		{
			return self::block(
				func_get_args(), 
				BinaryExpression::EXPRESSION_AND
			);
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