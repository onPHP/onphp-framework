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

	abstract class GenericDAO extends Singletone
	{
		protected $selectHead = null;
		
		abstract public function getTable();
		abstract public function getObjectName();
		
		abstract protected function makeObject(&$array, $prefix = null);
		
		// TODO: move to DAO-level instead of DB
		// abstract public function getQueryResult(SelectQuery $query);

		abstract public function getById($id);
		abstract public function getByLogic(LogicalObject $logic);
		abstract public function getByQuery(SelectQuery $query);

		abstract public function getListByIds($ids);
		abstract public function getListByQuery(SelectQuery $query);
		abstract public function getListByLogic(LogicalObject $logic);

		abstract public function dropById($id);
		abstract public function dropByIds($ids);

		public function getFields()
		{
			return $this->fields;
		}
		
		public function getSequence()
		{
			return $this->getTable().'_id';
		}

		public function makeSelectHead()
		{
			if (null === $this->selectHead) {
				$this->selectHead = 
					OSQL::select()->
					from($this->getTable());
				
				$table = $this->getTable();
				
				foreach ($this->getFields() as $field)
					$this->selectHead->get(new DBField($field, $table));
			}
			
			return clone $this->selectHead;
		}

		public function getCustom(SelectQuery $query)
		{
			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);
			
			if ($object = DBFactory::getDefaultInstance()->queryRow($query))
				return $object;
			else
				throw new ObjectNotFoundException();
		}

		public function getCustomList(SelectQuery $query)
		{
			if ($list = DBFactory::getDefaultInstance()->querySet($query))
				return $list;
			else
				throw new ObjectNotFoundException();
		}
		
		public function getCustomRowList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			if ($list = DBFactory::getDefaultInstance()->queryColumn($query))
				return $list;
			else
				throw new ObjectNotFoundException();
		}
	}
?>