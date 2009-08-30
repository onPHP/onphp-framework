<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	final class LogicUtils extends StaticFactory
	{
		public static function getOpenRange(
			DBField $left, DBField $right, $min = null, $max = null
		)
		{
			Assert::isFalse(
				($min === null) && ($max === null),
				'how can i build logic from emptyness?'
			);
			
			if ($min !== null)
				$min = new DBValue($min);
			
			if ($max !== null)
				$max = new DBValue($max);
			
			$chain = new LogicalChain();
			
			if ($min !== null && $max !== null) {
				$chain->expOr(
					Expression::orBlock(
						Expression::andBlock(
							Expression::notNull($left),
							Expression::notNull($right),
							Expression::expOr(
								Expression::between($min, $left, $right),
								Expression::between($left, $min, $max)
							)
						),
						Expression::andBlock(
							Expression::isNull($left),
							Expression::ltEq($min, $right)
						),
						Expression::andBlock(
							Expression::isNull($right),
							Expression::ltEq($left, $max)
						),
						Expression::andBlock(
							Expression::isNull($left),
							Expression::isNull($right)
						)
					)
				);
			} elseif ($min !== null && $max === null) {
				$chain->expOr(
					Expression::orBlock(
						Expression::andBlock(
							Expression::notNull($right),
							Expression::ltEq($min, $right)
						),
						Expression::isNull($right)
					)
				);
			} elseif ($max !== null && $min === null) {
				$chain->expOr(
					Expression::orBlock(
						Expression::andBlock(
							Expression::notNull($left),
							Expression::ltEq($left, $max)
						),
						Expression::isNull($left)
					)
				);
			}
			
			return $chain;
		}
	}
?>