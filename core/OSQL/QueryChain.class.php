<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich*
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	final class QueryChain extends SQLChain
	{
		public static function block($args, $logic)
		{
			$queryChain = new self;
			
			foreach ($args as &$arg) {
				if (!$arg instanceof SelectQuery)
					throw new WrongArgumentException(
						'unsupported object type: '.get_class($arg)
					);
				$queryChain->exp($arg, $logic);
			}
			return $queryChain;
		}
		
		public function union(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::UNION);
		}
		
		public function unionAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::UNION_ALL);
		}
		
		public function intersect(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::INTERSECT);
		}
		
		public function intersectAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::INTERSECT_ALL);
		}
		
		public function except(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::EXCEPT);
		}
		
		public function exceptAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::EXCEPT_ALL);
		}
	}
?>