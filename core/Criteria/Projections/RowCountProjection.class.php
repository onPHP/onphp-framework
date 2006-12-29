<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
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
	final class RowCountProjection extends BaseProjection
	{
		public function toField(Criteria $criteria, JoinCapableQuery $query)
		{
			return
				SQLFunction::create(
					'count',
					$this->property
						? $criteria->getDao()->guessAtom($this->property, $query)
						: '*'
				)->
				setAlias($this->alias);
		}
	}
?>