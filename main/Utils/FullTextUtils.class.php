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
	 * Full-text utilities.
	 * 
	 * @ingroup Utils
	**/
	final class FullTextUtils extends StaticFactory
	{
		public static function lookup(FullTextDAO $dao, ObjectQuery $oq, $string)
		{
			return
				$dao->getByQuery(
					self::makeFullTextQuery($dao, $oq, $string)->limit(1)
				);
		}
		
		public static function lookupList(
			FullTextDAO $dao, ObjectQuery $oq, $string
		)
		{
			return
				$dao->getListByQuery(
					self::makeFullTextQuery($dao, $oq, $string)
				);
		}
		
		public static function makeFullTextQuery(
			FullTextDAO $dao, ObjectQuery $oq, $string
		)
		{
			Assert::isString(
				$string,
				'only strings accepted today'
			);

			$array = self::prepareSearchString($string);

			if (!$array)
				throw new ObjectNotFoundException();
			
			if (!($field = $dao->getIndexField()) instanceof DBField)
				$field = new DBField(
					$dao->getIndexField(),
					$dao->getTable()
				);
			
			return
				$oq->toSelectQuery($dao)->
				andWhere(
					Expression::fullTextOr($field, $array)
				)->
				prependOrderBy(
					Expression::fullTextRankAnd($field, $array)
				)->desc();
		}
		
		public static function prepareSearchString($string)
		{
			$array = preg_split('/[\s\pP]+/u', $string);
			
			$out = array();
			
			for ($i = 0, $size = count($array); $i < $size; ++$i)
				if (
					!empty($array[$i])
					&& (
						$element = preg_replace(
							'/[^\pL\d\-\+\.\/]/u', null, $array[$i]
						)
					)
				)
					$out[] = $element;
			
			return $out;
		}
	}
?>