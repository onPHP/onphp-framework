<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry E. Demidov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Projections
	**/
	final class HavingProjection implements ObjectProjection
	{
		private $logic = null;
		
		public function __construct(LogicalObject $logic)
		{
			$this->logic = $logic;
		}
		
		/**
		 * @return JoinCapableQuery
		**/
		public function process(Criteria $criteria, JoinCapableQuery $query)
		{
			return
				$query->having(
					$this->logic->toMapped($criteria->getDao(), $query)
				);
		}
	}
