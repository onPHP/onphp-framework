<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
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
	final class ManyToManyLinkedFull extends ManyToManyLinkedWorker
	{
		/**
		 * @return ManyToManyLinkedFull
		**/
		public function sync(&$insert, &$update = array(), &$delete)
		{
			$dao = $this->container->getDao();

			$db = DBPool::getByDao($dao);

			if ($insert)
				for ($i = 0, $size = count($insert); $i < $size; ++$i) {
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
				for ($i = 0, $size = count($update); $i < $size; ++$i)
					$dao->save($update[$i]);

			if ($delete) {
				$ids = array();
				
				foreach ($delete as $object)
					$ids[] = $object->getId();
				
				$db->queryNull($this->makeDeleteQuery($ids));
				
				$dao->uncacheById($ids);
			}

			return $this;
		}

		/**
		 * @return SelectQuery
		**/
		public function makeFetchQuery()
		{
			$uc = $this->container;
			
			return
				$this->makeSelectQuery()->
					join(
						$uc->getHelperTable(),
						Expression::eq(
							new DBField(
								$uc->getParentTableIdField(),
								$uc->getDao()->getTable()
							),
							new DBField(
								$uc->getChildIdField(),
								$uc->getHelperTable()
							)
						)
					)->
					where(
						Expression::eq(
							new DBField($uc->getParentIdField()),
							new DBValue($uc->getParentObject()->getId())
						)
					);
		}
	}
?>