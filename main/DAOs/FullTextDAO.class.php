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

	abstract class FullTextDAO extends StorableDAO
	{
		public function getIndexField()
		{
			return 'fti';
		}
		
		public function lookupList($string)
		{
			Assert::isString(
				$string,
				'only strings accepted today'
			);

			$array =
				explode(
					' ',
					$string,
					substr_count($string, ' ') + 1
				);

			for ($i = 0; $i < sizeof($array); $i++)
				if (empty($array[$i]) || strlen($array[$i]) < 2)
					unset($array[$i]);

			if (!$array)
				throw new ObjectNotFoundException();

			return
				$this->getListByQuery(
					$this->makeSelectHead()->
					where(
						Expression::fullTextOr(
							new DBField($this->getIndexField(), $this->getTable()),
							$array
						)
					)->
					orderBy(
						Expression::fullTextRankOr(
							new DBField($this->getIndexField(), $this->getTable()),
							$array
						)
					)->desc()
				);
		}
	}
?>