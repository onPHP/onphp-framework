<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Projections
	**/
	final class GroupByClassProjection extends ClassProjection
	{
		/**
		 * @return GroupByClassProjection
		**/
		public static function create($class)
		{
			return new self($class);
		}
		
		/* void */ protected function subProcess(JoinCapableQuery $query, $field)
		{
			$query->groupBy($field);
		}
	}
?>