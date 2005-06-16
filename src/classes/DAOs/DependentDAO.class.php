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

	abstract class DependentDAO extends CommonDAO implements PartDAO
	{
		abstract public function getParentIdField();

		private function checkType(Storable $child)
		{
			if (get_class($child) != $this->getObjectName())
				throw new WrongArgumentException('child type doesn\'t match DAO type');
		}

		public function add($parentId, Storable $child)
		{
			$this->checkType($child);

			return
				$this->import($parentId, 
					$child->setId(
						DBFactory::getDefaultInstance()->
						obtainSequence(
							$this->getSequence()
						)
					)
				);
		}

		public function dropByBothId($parentId, $childId)
		{
			return parent::dropById($childId);
		}

		public function dropByParentId($parentId)
		{
			DBFactory::getDefaultInstance()->queryNull(
				OSQL::delete()->from($this->getTable())->where(
					Expression::eq($this->getParentIdField(), $parentId)
				)
			);
			
			return $this;
		}

		public function insert($parentId, Storable $child)
		{
			throw new UnsupportedMethodException(
				'insert is not applicaple to dependent objects'
			);
		}

		public function import($parentId, Storable $child)
		{
			$this->checkType($child);

			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields(
					OSQL::insert()->into($this->getTable())->
						set('id', $child->getId())->
						set($this->getParentIdField(), $parentId),
					$child
				)
			);

			$this->uncacheById($child);

			return $this;
		}

		public function save($parentId, Storable $child)
		{
			$this->checkType($child);
			
			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields(
					OSQL::update($this->getTable())->
						where(Expression::eq('id', $child->getId())),
					$child
				)
			);

			$this->uncacheById($child);

			return $this;
		}

		public function getListByParentId($parentId)
		{
			$list =
				$this->getListByLogic(
					Expression::eq($this->getParentIdField(), $parentId),
					MEMCACHED::DO_NOT_CACHE
				);

			return ArrayUtils::convertObjectList($list);
		}
	}
?>