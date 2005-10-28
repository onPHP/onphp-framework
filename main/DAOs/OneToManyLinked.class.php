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

	abstract class OneToManyLinked extends UnifiedContainer
	{
		protected function getParentIdField()
		{
			static $name = null;

			if ($name === null)
				$name = get_class($this->parent).'_id';

			return $name;
		}

		protected function getChildIdField()
		{
			return 'id';
		}

		protected function isUnlinkable()
		{
			return false;
		}

		protected function makeListFetchQuery()
		{
			return
				$this->targetize(
					$this->oq
						? $this->oq->toSelectQuery($this->dao)
						: $this->dao->makeSelectHead()
				);
		}

		protected function makeIdsFetchQuery()
		{
			return
				$this->targetize(
					$this->oq
						?
							$this->oq->toSelectQuery($this->dao)->
							dropFields()->
							get($this->getChildIdField())
						:
							OSQL::select()->from($this->dao->getTable())->
							get($this->getChildIdField())
				);
		}

		protected function syncList(&$insert, &$update, &$delete)
		{
			$dao = &$this->dao;

			if ($insert)
				for ($i = 0; $i < sizeof($insert); $i++)
					$dao->add($insert[$i]);

			if ($update)
				for ($i = 0; $i < sizeof($update); $i++)
					$dao->save($update[$i]);

			if ($delete) {
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($dao->getTable())->
					where(
						Expression::eq(
							new DBField($this->getParentIdField()),
							$this->parent->getId()
						)
					)->
					andWhere(
						Expression::in(
							$this->getChildIdField(),
							ArrayUtils::getIdsArray($delete)
						)
					)
				);
				
				foreach ($delete as $id)
					$dao->uncacheById($id);
			}

			return $this;
		}

		protected function syncIds(&$insert, &$delete)
		{
			$db = &DBFactory::getDefaultInstance();
			$dao = &$this->dao;

			if ($insert)
				$db->queryNull(
					$this->makeMassUpdateQuery($insert)
				);

			if ($delete) {
				// unlink or drop
				$this->isUnlinkable()
					?
						$db->queryNull($this->makeMassUpdateQuery($delete))
					:
						$db->queryNull(
							OSQL::delete()->from($dao->getTable())->
							where(
								Expression::in(
									$this->getChildIdField(),
									$delete
								)
							)
						);
				
				foreach ($delete as $id)
					$dao->uncacheById($id);
			}

			return $this;
		}
		
		private function targetize(SelectQuery $query)
		{
			return
				$query->where(
					Expression::eqId(
						new DBField($this->getParentIdField()),
						$this->parent
					)
				);
		}
		
		private function makeMassUpdateQuery(&$ids)
		{
			return
				OSQL::update($this->dao->getTable())->
				set($this->getParentIdField(), null)->
				where(
					Expression::in(
						$this->getChildIdField(),
						$ids
					)
				);
		}
	}
?>