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

	abstract class ManyToManyLinked extends UnifiedContainer
	{
		abstract public function getHelperTable();

		protected function getParentTableIdField()
		{
			return 'id';
		}

		protected function syncList(&$insert, &$update, &$delete)
		{
			$db = DBFactory::getDefaultInstance();
			$dao = &$this->dao;

			if ($insert)
				for ($i = 0; $i < sizeof($insert); $i++) {
					// check existence of new object
					try {
						$dao->getById($insert[$i]->getId());
					} catch (ObjectNotFoundException $e) {
						// ok, saving it then
						$dao->add($insert[$i]);
					}

					$db->queryNull(
						$this->makeInsertQuery($insert[$i]->getId())
					);
				}

			if ($update)
				for ($i = 0; $i < sizeof($update); $i++)
					$dao->save($update[$i]);

			if ($delete) {
				$db->queryNull($this->makeDeleteQuery($delete));
				
				foreach ($delete as $id)
					$dao->uncacheById($id);
			}

			return $this;
		}

		protected function syncIds(&$insert, &$delete)
		{
			$db = DBFactory::getDefaultInstance();
			
			if ($insert)
				for ($i = 0; $i < sizeof($insert); $i++)
					$db->queryNull($this->makeInsertQuery($insert[$i]));

			if ($delete) {
				$db->queryNull($this->makeDeleteQuery($delete));
				
				foreach ($delete as $id)
					$dao->uncacheById($id);
			}

			return $this;
		}

		protected function makeListFetchQuery()
		{
			if ($this->oq)
				$query = $this->oq->toSelectQuery($this->dao);
			else
				$query = $this->dao->makeSelectHead();
			
			return
				$query->
					distinct()->
					join(
						$this->getHelperTable(),
						Expression::eq(
							new DBField(
								$this->getParentTableIdField(),
								$this->dao->getTable()
							),
							new DBField(
								$this->getChildIdField(),
								$this->getHelperTable()
							)
						)
					)->
					where(
						Expression::eq(
							new DBField($this->getParentIdField()),
							new DBValue($this->parent->getId())
						)
					);
		}

		protected function makeIdsFetchQuery()
		{
			if ($this->oq)
				$query =
					$this->oq->toSelectQuery($this->dao)->dropFields();
			else
				$query = OSQL::select()->from($this->getHelperTable());
			
			return
				$query->
					get($this->getChildIdField())->
					distinct()->
					where(
						Expression::eq(
							new DBField($this->getParentIdField()),
							new DBValue($this->parent->getId())
						)
					);
		}

		private function makeInsertQuery($childId)
		{
			return
				OSQL::insert()->into($this->getHelperTable())->
				set($this->getParentIdField(), $this->parent->getId())->
				set($this->getChildIdField(), $childId);
		}

		// only unlinking, we don't want to drop original object
		private function makeDeleteQuery(&$delete)
		{
			return
				OSQL::delete()->from($this->getHelperTable())->
				where(
					Expression::eq(
						new DBField($this->getParentIdField()),
						new DBValue($this->parent->getId())
					)
				)->
				andWhere(
					Expression::in(
						$this->getChildIdField(),
						$delete
					)
				);
		}
	}
?>