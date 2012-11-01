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
	 * Wrapper around given childs of LogicalObject with custom logic-glue's.
	 * 
	 * @ingroup Logic
	**/
	namespace Onphp;

	final class LogicalChain extends SQLChain
	{
		/**
		 * @return \Onphp\LogicalChain
		**/
		public static function block($args, $logic)
		{
			Assert::isTrue(
				($logic == BinaryExpression::EXPRESSION_AND)
				|| ($logic == BinaryExpression::EXPRESSION_OR),
				
				"unknown logic '{$logic}'"
			);
			
			$logicalChain = new self;
			
			foreach ($args as $arg) {
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
		 * @return \Onphp\LogicalChain
		**/
		public function expAnd(LogicalObject $exp)
		{
			return $this->exp($exp, BinaryExpression::EXPRESSION_AND);
		}
		
		/**
		 * @return \Onphp\LogicalChain
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
			else { // size > 1
				$out = $chain[0]->toBoolean($form);
				
				for ($i = 1; $i < $size; ++$i) {
					$out =
						self::calculateBoolean(
							$this->logic[$i],
							$out,
							$chain[$i]->toBoolean($form)
						);
				}
				
				return $out;
			}
			
			Assert::isUnreachable();
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

			Assert::isUnreachable();
		}
	}
?>