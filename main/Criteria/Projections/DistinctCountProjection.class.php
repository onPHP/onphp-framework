<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Projections
	**/
	final class DistinctCountProjection extends BaseProjection
	{
		public function process(Criteria $criteria, JoinCapableQuery $query)
		{
			return
				$query->get(
					SQLFunction::create(
						'count',
						$this->property
							? $criteria->getDao()->guessAtom($this->property, $query)
							: '*'
					)->
					setAlias($this->alias)->
					setAggregateDistinct()
				);
		}
	}
?>