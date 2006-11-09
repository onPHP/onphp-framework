<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich*
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Wrapper around given childs of LogicalObject with custom logic-glue's.
	 * 
	 * @ingroup Logic
	**/
	final class LogicalChain extends SQLChain implements LogicalObject
	{
		/**
		 * @return LogicalChain
		**/
		public static function block($args, $logic)
		{
			Assert::isTrue(
				($logic == BinaryExpression::EXPRESSION_AND)
				|| ($logic == BinaryExpression::EXPRESSION_OR),
				
				"unknown logic '{$logic}'"
			);
			
			$logicalChain = new self;
			
			foreach ($args as &$arg) {
				if (
					!$arg instanceof LogicalObject
					&& !$arg instanceof SelectQuery 
				)
					throw new WrongArgumentException(
						'unsupported object type: '.get_class($arg)
					);
					
				$logicalChain->exp($arg, $logic);
			}
			
			return $logicalChain;
		}
		
		/**
		 * @return LogicalChain
		**/
		public function expAnd(LogicalObject $exp)
		{
			return $this->exp($exp, BinaryExpression::EXPRESSION_AND);
		}
		
		/**
		 * @return LogicalChain
		**/
		public function expOr(LogicalObject $exp)
		{
			return $this->exp($exp, BinaryExpression::EXPRESSION_OR);
		}
		
		public function toBoolean(Form $form)
		{
			$chain = &$this->chain;
			
			$size = count($chain);
			
			if (!$size)
				throw new WrongArgumentException(
					'empty chain can not be calculated'
				);
			elseif ($size == 1)
				return $chain[0]->toBoolean($form);
			else {
				for ($i = 0; $i < $size; ++$i) {
					if (isset($chain[$i + 1])) {
						$out =
							self::calculateBoolean(
								$this->logic[$i + 1],
								$chain[$i]->toBoolean($form),
								$chain[$i + 1]->toBoolean($form)
							);
					} else {
						$out =
							self::calculateBoolean(
								$this->logic[$i],
								$out, 
								$chain[$i]->toBoolean($form)
							);
					}
				}
				
				return $out;
			}
			
			/* NOTREACHED */
		}
		
		private static function calculateBoolean($logic, $left, $right)
		{
			switch ($logic) {
				case BinaryExpression::EXPRESSION_AND:
					return $left && $right;

				case BinaryExpression::EXPRESSION_OR:
					return $left || $right;

				default:
					throw new WrongArgumentException(
						"unknown logic - '{$logic}'"
					);
			}

			/* NOTREACHED */
		}
	}
?>