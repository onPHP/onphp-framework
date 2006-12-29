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
	abstract class AggregateProjection extends BaseProjection
	{
		abstract public function getFunctionName();
		
		public function toField(Criteria $criteria, JoinCapableQuery $query)
		{
			Assert::isFalse(!$this->property);
			
			return
				SQLFunction::create(
					$this->getFunctionName(),
					$criteria->getDao()->guessAtom($this->property, $query)
				)->
				setAlias($this->alias);
		}
	}
?>