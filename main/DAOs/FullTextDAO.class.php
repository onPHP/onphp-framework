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

	/**
	 * Full-text stuff DAO support.
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
		
		protected function makeFullTextQuery(ObjectQuery $oq, $string)
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
			$array =
				explode(
					' ',
					$string,
					substr_count($string, ' ') + 1
				);
			
			$out = array();

			for ($i = 0; $i < sizeof($array); $i++)
				if (!empty($array[$i]))
					if ($element = mb_ereg_replace(
						'[^а-яА-Яa-zA-Z0-9 \-]', null, $array[$i]
						)
					)
						$out[] = $element;
			
			return $out;
		}
	}
?>