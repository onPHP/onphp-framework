<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup DAOs
	**/
	interface BaseDAO
	{
		/// single object getters
		//@{
		public function getById($id);
		public function getByLogic(LogicalObject $logic);
		public function getByQuery(SelectQuery $query);
		public function getCustom(SelectQuery $query);
		//@}
		
		/// object's list getters
		//@{
		public function getListByIds(array $ids);
		public function getListByQuery(SelectQuery $query);
		public function getListByLogic(LogicalObject $logic);
		public function getPlainList();
		//@}
		
		/// custom list getters
		//@{
		public function getCustomList(SelectQuery $query);
		public function getCustomRowList(SelectQuery $query);
		//@}

		/// query result getter
		//@{
		public function getQueryResult(SelectQuery $query);
		//@}
		
		/// erasers
		//@{
		public function drop(Identifiable $object);
		public function dropById($id);
		public function dropByIds(array $ids);
		//@}
		
		/// uncachers
		//@{
		public function uncacheById($id);
		/**
		 * @return UncacherBase
		 */
		public function getUncacherById($id);
		public function uncacheByIds($ids);
		public function uncacheLists();
		//@}
	}
?>
