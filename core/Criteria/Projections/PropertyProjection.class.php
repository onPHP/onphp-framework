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
	final class PropertyProjection extends BaseProjection
	{
		protected $property	= null;
		protected $alias	= null;
		
		public function toField(Criteria $criteria, JoinCapableQuery $query)
		{
			return SelectField::create(
				$criteria->getDao()->guessAtom($this->property, $query),
				$this->alias
			);
		}
	}
?>