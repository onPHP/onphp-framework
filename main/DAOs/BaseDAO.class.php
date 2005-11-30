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

	interface BaseDAO
	{
		//@{
		// single object getters
		public function get(ObjectQuery $oq);
		public function getById($id);
		public function getByLogic(LogicalObject $logic);
		public function getByQuery(SelectQuery $query);
		public function getCustom(SelectQuery $query);
		//@}
		
		//@{
		// object's list getters
		public function getList(ObjectQuery $oq);
		public function getListByIds($ids);
		public function getListByQuery(SelectQuery $query);
		public function getListByLogic(LogicalObject $logic);
		public function getPlainList();
		//@}
		
		//@{
		// custom list getters
		public function getCustomList(SelectQuery $query);
		public function getCustomRowList(SelectQuery $query);
		//@}

		//@{
		// query result getters
		public function getCountedList(ObjectQuery $oq);
		public function getQueryResult(SelectQuery $query);
		//@}
		
		//@{
		// cache getters
		public function getCachedById($id);
		public function getCachedByQuery(Query $query);
		//@}
		
		//@{
		// erasers
		public function dropById($id);
		public function dropByIds($ids);
		//@}
		
		//@{
		// cachers
		public function cacheById(Identifiable $object);
		public function cacheByQuery(
			SelectQuery $query, /* Identifiable */ $object
		);
		public function cacheListByQuery(SelectQuery $query, /* array */ $array);
		//@}
		
		//@{
		// uncachers
		public function uncacheById($id);
		public function uncacheByIds($ids);
		public function uncacheLists();
		//@}
	}
?>