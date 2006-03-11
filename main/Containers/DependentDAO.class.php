<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Containers
	**/
	abstract class DependentDAO extends GenericDAO implements PartDAO
	{
		abstract public function getParentIdField();

		/**
		 *	We must do it, because we have collision:
		 *	method getById() defined in interface
		 *	and in abstract class
		**/
		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			return parent::getById($id, $expires);
		}
		
		public function add($parentId, Identifiable $child)
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

		public function insert($parentId, Identifiable $child)
		{
			throw new UnsupportedMethodException(
				'insert is not applicaple to dependent objects'
			);
		}

		public function import($parentId, Identifiable $child)
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

			$this->uncacheById($child->getId());

			return $this;
		}

		public function save($parentId, Identifiable $child)
		{
			$this->checkType($child);

			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields(
					OSQL::update($this->getTable())->
						where(Expression::eq('id', $child->getId())),
					$child
				)
			);

			$this->uncacheById($child->getId());

			return $this;
		}

		public function getListByParentId($parentId)
		{
			return
				$this->getListByLogic(
					Expression::eq($this->getParentIdField(), $parentId)
				);
		}

		public function getChildIdsList($parentId)
		{ 
			return
				$this->getCustomRowList(
					OSQL::select()->from($this->getTable())->
					get('id')->
					where(
						Expression::eq(
							new DBField($this->getParentIdField()),
							new DBValue($parentId)
						)
					)
				);
		}

		private function checkType(Identifiable $child)
		{
			Assert::isTrue(
				get_class($child) === $this->getObjectName(),
				"child type doesn't match DAO type"
			);
		}
	}
?>