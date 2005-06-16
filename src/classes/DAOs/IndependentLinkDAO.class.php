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

	abstract class IndependentLinkDAO extends Singletone implements PartDAO
	{
		abstract public function getParentIdField();
		abstract public function getChildIdField();
		abstract public function getChildDAO();
		abstract public function getTable();

		abstract public function getObjectName();

		public function getById($id)
		{
			return $this->getChildDAO()->getById($id);
		}

		private function checkType(Storable $child)
		{
			if (get_class($child) != $this->getObjectName())
				throw new WrongArgumentException('child type doesn\'t match DAO type');
		}

		public function add($parentId, Storable $child)
		{
			throw new UnsupportedMethodException(
				'independent link can\'t use objects without id'
			);
		}

		public function dropByBothId($parentId, $childId)
		{
			if (!DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->where(
						Expression::expAnd(
							Expression::eq($this->getParentIdField(), $parentId),
							Expression::eq($this->getChildIdField(), $childId)
						)
					)
				)
			)
				throw new DatabaseException(
					"can't delete {$this->getObjectName()} with parentId == {$parentId}".
					"and childId == {$childId}"
				);

			return $this;
		}

		public function dropByParentId($parentId)
		{
			if (!DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->where(
						Expression::eq($this->getParentIdField(), $parentId)
					)
				)
			)
				throw new DatabaseException(
					"can't delete {$this->getObjectName()} with parentId == {$parentId}"
				);

			return $this;
		}

		public function save($parentId, Storable $child)
		{
			$this->checkType($child);

			DBFactory::getDefaultInstance()->queryNull(
				OSQL::update($this->getTable())->
					set($this->getChildIdField(), $child->getId())->
					set($this->getParentIdField(), $parentId)
			);

			return $this;
		}

		public function import($parentId, Storable $child)
		{
			$this->checkType($child);

			DBFactory::getDefaultInstance()->queryNull(
				OSQL::insert()->into($this->getTable())->
					set($this->getChildIdField(), $child->getId())->
					set($this->getParentIdField(), $parentId)
			);

			return $this;
		}

		public function insert($parentId, Storable $child)
		{
			return $this->import($parentId, $child);
		}

		public function getListByParentId($parentId)
		{
			if ($list = $this->getChildIdsList($parentId)) {
				$out = array();
				
				for ($i = 0; $i < sizeof($list); $i++)
					$out[$list[$i]] = $list[$i];

				return $out;
			}
			
			return array();
		}
		
		private function getChildIdsList($parentId)
		{
			return
				$this->getChildDAO()->
					getCustomRowList(
						OSQL::select()->from($this->getTable())->
						get($this->getChildIdField())->
						where(
							Expression::eq(
								$this->getParentIdField(),
								$parentId
							)
						),
						Memcached::DO_NOT_CACHE
					);
		}
	}
?>