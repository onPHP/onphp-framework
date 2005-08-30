<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

/**
	UnifiedContainer:

		must have for internal usage:
			abstract protected function makeListFetchQuery()
			abstract protected function makeIdsFetchQuery()

			abstract protected function syncList(&$insert, &$update, &$delete)
			abstract protected function syncIds(&$insert, &$delete)

		child's and parent's field names:
			abstract protected function getChildIdField()
			abstract protected function getParentIdField()

		overrideable:
			protected function preQuerize(SelectQuery $query)

		all we need from outer world:
			public function __construct(
				Identifiable $parent, UnifiedContainer $dao, $lazy = true
			)

		if you want to apply PreQuery's "filter":
			public function setPreQuery(PreQuery $pq)

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
			protected function isUnlinkinable()

	ManyToManyLinked <- UnifiedContainer:

		helper's table name:
			abstract public function getHelperTable()

		id field name at parent's primary table:
			protected function getParentTableIdField()
**/

	abstract class UnifiedContainer
	{
		protected $parent	= null;

		protected $dao		= null;
		protected $pq		= null;
		
		protected $lazy		= true;

		protected $list		= null;
		protected $clones	= null;

		abstract protected function getChildIdField();
		abstract protected function getParentIdField();

		abstract protected function makeListFetchQuery();
		abstract protected function makeIdsFetchQuery();

		abstract protected function syncList(&$insert, &$update, &$delete);
		abstract protected function syncIds(&$insert, &$delete);

		public function __construct(
			DAOConnected $parent, CommonDAO $dao, $lazy = true
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

		public function setPreQuery(PreQuery $pq)
		{
			Assert::brothers(
				$this->parent, $pq->getObject(),
				"wtf?"
			);

			$this->pq = $pq;

			return $this;
		}
		
		public function setList($list)
		{
			Assert::isArray($list);
			
			$this->list = $list;
			
			return $this;
		}

		public function getList()
		{
			return $this->list;
		}
		
		public function fetch()
		{
			if (!$this->parent->getId())
				throw new WrongStateException(
					'save parent object first'
				);
			
			return
				$this->lazy
					? $this->fetchIdsList()
					: $this->fetchList();
		}
		
		public function save()
		{
			Assert::isArray(
				$this->list,
				"that's not an array :-/"
			);
			
			$list	= $this->list;
			$clones	= $this->clones;
			
			$insert = $delete = $update = array();
			
			if ($clones)
				$ids =
					array_merge(
						array_keys($list),
						array_keys($clones)
					);
			else
				$ids = array_keys($list);
			
			foreach ($ids as $id) {
				if (
					!$this->lazy
					&& isset($list[$id], $clones[$id])
					&& $list[$id] != $clones[$id]
				)
					$update[] = $list[$id];
				elseif (isset($list[$id]) && !isset($clones[$id]))
					$insert[] = $list[$id];
				elseif (isset($clones[$id]) && !isset($list[$id]))
					$delete[] = $clones[$id];
			}
			
			$db = &DBFactory::getDefaultInstance();

			$db->begin()->queueStart();
			
			try {
				$this->lazy
					? $this->syncIds($insert, $delete)
					: $this->syncList($insert, $update, $delete);
			} catch (DatabaseException $e) {
				$db->queueDrop()->rollback();
				throw $e;
			}

			$db->commit()->queueFlush();

			return $this;
		}

		protected function fetchList()
		{
			$this->list =
				$this->dao->getListByQuery(
					$this->makeListFetchQuery()
				);

			foreach ($this->list as $id => &$object)
				$this->clones[$id] = clone $object;

			return $this;
		}

		protected function fetchIdsList()
		{
			$ids =
				$this->dao->getCustomRowList(
					$this->makeIdsFetchQuery()
				);
	
			foreach ($ids as $id) {
				$this->list[$id] = $id;
				$this->clones[$id] = $id;
			}

			return $this;
		}

		protected function preQuerize(SelectQuery $query)
		{
			if ($pq = &$this->pq) {
				$pq->pointQuery(
					$pq->limitQuery($query)
				);

				if ($this->chain->getSize())
					$query->andWhere($this->chain);

				if ($order = $pq->getOrder())
					$query->orderBy($order, $pq->dao()->getTable());
			}

			return $query;
		}
	}
?>