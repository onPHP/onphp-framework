<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Containers
	**/
	abstract class IndependentLinkDAO extends Singleton implements PartDAO
	{
		abstract public function getParentIdField();
		abstract public function getChildIdField();
		abstract public function getChildDAO();
		abstract public function getTable();

		abstract public function getObjectName();

		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->getChildDAO()->getById($id, $expires);
		}

		private function checkType(Identifiable $child)
		{
			Assert::isTrue(
				get_class($child) === $this->getObjectName(),
				"child type doesn't match DAO type"
			);
		}

		public function add($parentId, Identifiable $child)
		{
			throw new UnsupportedMethodException(
				"independent link can't use objects without id"
			);
		}

		public function dropByBothId($parentId, $childId)
		{
			try {
				DBPool::getByDao($this->getChildDAO())->queryNull(
					OSQL::delete()->from($this->getTable())->where(
						Expression::expAnd(
							Expression::eq(
								new DBField($this->getParentIdField()),
								new DBValue($parentId)
							),
							Expression::eq(
								new DBField($this->getChildIdField()),
								new DBValue($childId)
							)
						)
					)
				);
			} catch (DatabaseException $e) {
				throw $e->setMessage(
					"can't delete {$this->getObjectName()} ".
					"with parentId == {$parentId} ".
					"and childId == {$childId}"
				);
			}

			return $this;
		}

		public function dropByParentId($parentId)
		{
			try {
				DBPool::getByDao($this->getChildDAO())->queryNull(
					OSQL::delete()->from($this->getTable())->where(
						Expression::eq($this->getParentIdField(), $parentId)
					)
				);
			} catch (DatabaseException $e) {
				throw $e->setMessage(
					"can't delete {$this->getObjectName()} with parentId == {$parentId}"
				);
			}

			return $this;
		}

		public function save($parentId, Identifiable $child)
		{
			// do not throw anything here - we're too often calling it,
			// while we don't need to save links at all
			return $this;
		}

		public function import($parentId, Identifiable $child)
		{
			$this->checkType($child);

			DBPool::getByDao($this->getChildDAO())->queryNull(
				OSQL::insert()->into($this->getTable())->
					set($this->getChildIdField(), $child->getId())->
					set($this->getParentIdField(), $parentId)
			);

			return $this;
		}

		public function insert($parentId, Identifiable $child)
		{
			return $this->import($parentId, $child);
		}

		public function getListByParentId($parentId, $expires = Cache::DO_NOT_CACHE)
		{
			return $this->getChildDAO()->getListByQuery(
				$this->getChildDAO()->
					makeSelectHead()->
					join(
						$this->getTable(),
						Expression::expAnd(
							Expression::eq(
								new DBField(
									$this->getChildIdField(),
									$this->getTable()
								),
								new DBField(
									'id',
									$this->getChildDAO()->getTable()
								)
							),
							Expression::eq(
								new DBField(
									$this->getParentIdField(),
									$this->getTable()
								),
								new DBValue($parentId)
							)
						)
					),
                $expires
			);
		}

		public function getChildIdsList($parentId, $expires = Cache::DO_NOT_CACHE)
		{
			return
				$this->getChildDAO()->
					getCustomRowList(
						OSQL::select()->from($this->getTable())->
						get($this->getChildIdField())->
						where(
							Expression::eq(
								new DBField($this->getParentIdField()),
								new DBValue($parentId)
							)
						),
                        $expires
					);
		}
	}
?>