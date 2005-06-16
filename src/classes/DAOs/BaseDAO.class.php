<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

/*
	CacheableDAO:

		abstract public function getObjectName();
		
		public function cacheById(Identifiable $object, $expires = null)
		public function cacheByQuery(Query $query, $object, $expires = null)
		public function cacheByQueryResult(QueryResult $result, $expires = null)
	
		public function getClass()
		public function getCachedById($className, $id)
		public function getCachedByQuery(Query $query)
		public function getCachedQueryResult(SelectQuery $query)
	
		public function uncacheById(Identifiable $object)
		public function uncacheByKey($key)
		public function uncacheByQuery(Query $query)
	
		protected function getCachedByKey($key)
		protected function cacheByKey($key, $object, $expires = null)


	CommonDAO (CacheableDAO):

		abstract public function getTable();
	
		abstract protected function makeSelectHead();
		abstract protected function makeObject(&$array, $prefix = null);
	
		public function getFields()
		public function getSequence()
	
		public function getById($id)
		public function getByLogic(LogicalObject $logic)
		public function getCustomList(SelectQuery $query)
		public function getQueryResult(SelectQuery $query)
		public function getByQuery(SelectQuery $query)
	
		public function getList()
		public function getListByIds($ids)
		public function getListByLogic(LogicalObject $logic)
		public function getListByQuery(SelectQuery $query)
	
		public function dropById($id)
		public function dropByIds($ids)

	NamedObjectDAO (CommonDAO):

		public function getByName($name)
		public function makeSelectHead()

		protected function getCachedByName($className, $objectName)
		protected function setQueryFields(InsertOrUpdateQuery $query, NamedObject $no)
		protected function makeNamedObject(&$array, NamedObject $no, $prefix = null)

		protected function cacheNamed(NamedObject $no)
		protected function cacheByName(NamedObject $no, $expires = null)
*/

	interface BaseDAO
	{
		public function getTable();
		public function getFields();
		public function getSequence();

		public function makeSelectHead();
		public function makeObject(&$array, $prefix = null);

		public function setQueryFields(InsertOrUpdateQuery $query);
	}

	interface IdentifiableDAO extends BaseDAO {}

	interface CacheDAO {}
?>