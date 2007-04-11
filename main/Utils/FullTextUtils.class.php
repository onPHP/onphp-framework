<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Full-text utilities.
	 * 
	 * @ingroup Utils
	**/
	final class FullTextUtils extends StaticFactory
	{
		public static function lookup(
			FullTextDAO $dao, Criteria $criteria, $string
		)
		{
			return
				$dao->getByQuery(
					self::makeFullTextQuery($dao, $criteria, $string)->limit(1)
				);
		}
		
		public static function lookupList(
			FullTextDAO $dao, Criteria $criteria, $string
		)
		{
			return
				$dao->getListByQuery(
					self::makeFullTextQuery($dao, $criteria, $string)
				);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return SelectQuery
		**/
		public static function makeFullTextQuery(
			FullTextDAO $dao, Criteria $criteria, $string
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
				$criteria->setDao($dao)->
				add(
					Expression::fullTextOr($field, $array)
				)->
				prependOrder(
					OrderBy::create(
						Expression::fullTextRankAnd($field, $array)
					)->
					desc()
				);
		}
		
		public static function prepareSearchString($string)
		{
			$array =
				explode(
					' ',
					$string,
					substr_count($string, ' ') + 1
				);
			
			$out = array();
			
			for ($i = 0, $size = count($array); $i < $size; ++$i)
				if (
					!empty($array[$i])
					&& (
						$element = mb_ereg_replace(
							'[^а-яА-Яa-zA-Z0-9 \-\./]', null, $array[$i]
						)
					)
				)
					$out[] = $element;
			
			return $out;
		}
	}
?>