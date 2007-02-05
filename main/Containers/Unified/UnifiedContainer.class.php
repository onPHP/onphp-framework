<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

/*
	UnifiedContainer:

		child's and parent's field names:
			abstract public function getChildIdField()
			abstract public function getParentIdField()

		all we need from outer world:
			public function __construct(
				Identifiable $parent, UnifiedContainer $dao, $lazy = true
			)

		if you want to apply ObjectQuery's "filter":
			public function setObjectQuery(ObjectQuery $oq)
		
		or Criteria (ObjectQuery will be ignored):
			public function setCriteria(Criteria $criteria)

		first you should fetch whatever you want:
			public function fetch()

		then you can get it:
			public function getList()
		
		set you modified list:
			public function setList($list)

		finally, sync fetched data and stored one:
			public function save()

	OneToManyLinked <- UnifiedContainer:

		indicates whether child can be free (parent_id nullable):
			protected function isUnlinkable()

	ManyToManyLinked <- UnifiedContainer:

		helper's table name:
			abstract public function getHelperTable()

		id field name at parent's primary table:
			protected function getParentTableIdField()
*/

	/**
	 * IdentifiableObject childs collection handling.
	 * 
	 * @see StorableContainer for alternative
	 * 
	 * @ingroup Containers
	**/
	abstract class UnifiedContainer
	{
		protected $worker	= null;
		protected $parent	= null;

		protected $dao		= null;
		
		protected $lazy		= true;
		protected $fetched	= false;

		protected $list		= array();
		protected $clones	= array();
		
		abstract public function getParentIdField();
		abstract public function getChildIdField();
		
		public function __construct(
			Identifiable $parent, GenericDAO $dao, $lazy = true
		)
		{
			Assert::isBoolean($lazy);
			
			$this->parent 	= $parent;
			$this->lazy		= $lazy;
			$this->dao		= $dao;

			$childClass = $dao->getObjectName();
			
			Assert::isTrue(
				new $childClass instanceof Identifiable,
				"child object should be at least Identifiable"
			);
		}
		
		public function getParentObject()
		{
			return $this->parent;
		}
		
		/**
		 * @return GenericDAO
		**/
		public function getDao()
		{
			return $this->dao;
		}
		
		public function isLazy()
		{
			return $this->lazy;
		}
		
		public function isFetched()
		{
			return $this->fetched;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return UnifiedContainer
		**/
		public function setCriteria(Criteria $criteria)
		{
			Assert::isTrue(
				$criteria->getDao() === null
				|| (
					$criteria->getDao() === $this->dao
				),
				"criteria's dao doesn't match container's one"
			);
			
			if (!$criteria->getDao())
				$criteria->setDao($this->dao);
			
			$this->worker->setCriteria($criteria);
			
			return $this;
		}
		
		/**
		 * @deprecated by Criteria
		 * 
		 * @throws WrongArgumentException
		 * @return UnifiedContainer
		**/
		public function setObjectQuery(ObjectQuery $oq)
		{
			Assert::isTrue(
				$this->dao instanceof StorableDAO,
				'you must extends from StorableDAO to be able to use ObjectQueries'
			);
			
			$this->worker->setObjectQuery($oq);

			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return UnifiedContainer
		**/
		public function setList($list)
		{
			Assert::isArray($list);
			
			$this->list = $list;
			
			return $this;
		}
		
		/**
		 * @return UnifiedContainer
		**/
		public function replaceList(/* array */ $list)
		{
			Assert::isArray($list);
			
			return $this->importList($list);
		}

		public function getList()
		{
			if (!$this->isFetched())
				$this->fetch();
			
			return $this->list;
		}
		
		/**
		 * @throws WrongStateException
		 * @return UnifiedContainer
		**/
		public function fetch()
		{
			if (!$this->parent->getId())
				throw new WrongStateException(
					'save parent object first'
				);
			
			try {
				$this->fetchList();
			} catch (ObjectNotFoundException $e) {
				// yummy
			}
			
			$this->fetched = true;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return UnifiedContainer
		**/
		public function save()
		{
			Assert::isArray(
				$this->list,
				"that's not an array :-/"
			);

			if (!$this->fetched)
				throw new WrongStateException(
					'do not want to save non-fetched collection'
				);
			
			$list	= $this->list;
			$clones	= $this->clones;
			
			$ids = $insert = $delete = $update = array();

			if ($this->lazy) {
				foreach ($list as $id) {
					if (!isset($clones[$id]))
						$insert[] = $ids[$id] = $id;
					else
						$ids[$id] = $id;
				}
				
				foreach ($clones as $id) {
					if (!isset($ids[$id]))
						$delete[] = $id;
				}
			} else {
				foreach ($list as $object) {
					$id = $object->getId();
					
					if (null === $id) {
						$insert[] = $object;
					} elseif (
						isset($clones[$id])
						&& (
							($object !== $clones[$id])
							|| ($object != $clones[$id])
						)
					) {
						$update[] = $object;
					} elseif (!isset($clones[$id])) {
						$insert[] = $object;
					}
					
					if (null !== $id)
						$ids[$id] = $object;
				}
				
				foreach ($clones as $id => $object) {
					if (!isset($ids[$id]))
						$delete[] = $object;
				}
			}

			$db = DBPool::getByDao($this->getDao());
			
			if (!$db->inTransaction()) {
				$db->queueStart()->begin();

				try {
					$this->worker->sync($insert, $update, $delete);
					
					$db->commit()->queueFlush();
				} catch (DatabaseException $e) {
					$db->queueDrop()->rollback();
					throw $e;
				}
			} else {
				$this->worker->sync($insert, $update, $delete);
			}
			
			$this->dao->uncacheLists();

			return $this;
		}

		protected function fetchList()
		{
			$query = $this->worker->makeFetchQuery();
			
			if ($this->lazy)
				$list = $this->dao->getCustomRowList($query);
			else
				$list = $this->dao->getListByQuery($query);

			return $this->importList($list);
		}
		
		/**
		 * @return UnifiedContainer
		**/
		private function importList(/* array */ $list)
		{
			if ($this->lazy) {
				foreach ($list as $id) {
					$this->list[$id] = $id;
					$this->clones[$id] = $id;
				}
			} else {
				$this->list = $list;
				
				foreach ($list as $object)
					$this->clones[$object->getId()] = clone $object;
			}
			
			$this->fetched = true;
			
			return $this;
		}
	}
?>