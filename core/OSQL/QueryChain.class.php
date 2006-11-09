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
		/**
		 * @return QueryChain
		**/
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
		
		/**
		 * @return QueryChain
		**/
		public function union(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::UNION);
		}
		
		/**
		 * @return QueryChain
		**/
		public function unionAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::UNION_ALL);
		}
		
		/**
		 * @return QueryChain
		**/
		public function intersect(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::INTERSECT);
		}
		
		/**
		 * @return QueryChain
		**/
		public function intersectAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::INTERSECT_ALL);
		}
		
		/**
		 * @return QueryChain
		**/
		public function except(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::EXCEPT);
		}
		
		/**
		 * @return QueryChain
		**/
		public function exceptAll(SelectQuery $query)
		{
			return $this->exp($query, CombineQuery::EXCEPT_ALL);
		}
	}
?>