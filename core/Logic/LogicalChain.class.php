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
	 * Wrapper around given childs of LogicalObject with custom logic-glue's.
	 * 
	 * @ingroup Logic
	**/
	final class LogicalChain extends SQLChain implements LogicalObject
	{
		public static function calculateBoolean($logic, $left, $right)
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
		
		public function expAnd(LogicalObject $exp)
		{
			return $this->exp($exp, BinaryExpression::EXPRESSION_AND);
		}
		
		public function expOr(LogicalObject $exp)
		{
			return $this->exp($exp, BinaryExpression::EXPRESSION_OR);
		}
		
		public function union(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::UNION);
		}
		
		public function unionAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::UNION_ALL);
		}
		
		public function intersect(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::INTERSECT);
		}
		
		public function intersectAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::INTERSECT_ALL);
		}
		
		public function except(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::EXCEPT);
		}
		
		public function exceptAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::EXCEPT_ALL);
		}
		
		private function exp(DialectString $exp, $logic)
		{
			$this->chain[] = $exp;
			$this->logic[] = $logic;
			
			return $this;
		}
		
		public function toBoolean(Form $form)
		{
			$chain = &$this->chain;
			
			$size = count($chain);
			
			if (! $size)
				throw new WrongArgumentException('empty chain can\'t be calculated');
			elseif ($size = 1)
				return $chain[0]->toBoolean($form);
			else {
				$out = null;
				
				for ($i = 0, $size = count($chain); $i < $size; ++$i) {
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
			
			// notreached
		}
	}
?>