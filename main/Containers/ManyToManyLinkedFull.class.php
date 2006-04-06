<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
 *   voxus@onphp.org                                                       *
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
		public function sync(&$insert, &$update = array(), &$delete)
		{
			$db = DBFactory::getDefaultInstance();

			$dao = $this->container->getDao();

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
				$db->queryNull($this->makeDeleteQuery($delete));
				
				foreach ($delete as $id)
					$dao->uncacheById($id);
			}

			return $this;
		}

		public function makeFetchQuery()
		{
			$uc = $this->container;
			
			if ($this->oq)
				$query = $this->oq->toSelectQuery($uc->getDao());
			else
				$query = $uc->getDao()->makeSelectHead();
			
			return
				$query->
					distinct()->
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