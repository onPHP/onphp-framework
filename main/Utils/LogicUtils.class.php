<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class LogicUtils
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
							Expression::between($right, $min, $max)
						),
						Expression::andBlock(
							Expression::isNull($right),
							Expression::between($left, $min, $max)
						)
					)
				);
			} elseif ($min !== null && $max === null) {
				$chain->expOr(
					Expression::orBlock(
						Expression::andBlock(
							Expression::notNull($left),
							Expression::notNull($right),
							Expression::between($min, $left, $right)
						),
						Expression::andBlock(
							Expression::isNull($left),
							Expression::gtEq($right, $min)
						),
						Expression::andBlock(
							Expression::isNull($right),
							Expression::gtEq($left, $min)
						)
					)
				);
			} elseif ($max !== null && $min === null) {
				$chain->expOr(
					Expression::orBlock(
						Expression::andBlock(
							Expression::notNull($left),
							Expression::notNull($right),
							Expression::between($max, $left, $right)
						),
						Expression::andBlock(
							Expression::isNull($left),
							Expression::ltEq($right, $max)
						),
						Expression::andBlock(
							Expression::isNull($right),
							Expression::ltEq($left, $max)
						)
					)
				);
			}
			
			return $chain;
		}
	}
?>