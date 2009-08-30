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
	 * Full-text stuff DAO support.
	 * 
	 * @deprecated will be removed during 0.7 session
	 * 
	 * @ingroup DAOs
	**/
	abstract class FullTextDAO extends MappedStorableDAO
	{
		public function getIndexField()
		{
			return 'fti';
		}
		
		public function lookup(ObjectQuery $oq, $string)
		{
			return
				$this->getByQuery(
					$this->makeFullTextQuery($oq, $string)->limit(1)
				);
		}
		
		public function lookupList(ObjectQuery $oq, $string)
		{
			return
				$this->getListByQuery(
					$this->makeFullTextQuery($oq, $string)
				);
		}
		
		public function makeFullTextQuery(ObjectQuery $oq, $string)
		{
			Assert::isString(
				$string,
				'only strings accepted today'
			);

			$array = $this->prepareSearchString($string);

			if (!$array)
				throw new ObjectNotFoundException();
			
			if (!($field = $this->getIndexField()) instanceof DBField)
				$field = new DBField(
					$this->getIndexField(),
					$this->getTable()
				);
			
			return
				$oq->toSelectQuery($this)->
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