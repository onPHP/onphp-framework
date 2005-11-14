<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class OneToManyLinkedFull extends OneToManyLinkedWorker
	{
		public function makeFetchQuery()
		{
			$uc = $this->container;
			
			return
				$this->targetize(
					$this->oq
						? $this->oq->toSelectQuery($uc->getDao())
						: $uc->getDao()->makeSelectHead()
				);
		}

		public function sync(&$insert, &$update, &$delete = array())
		{
			$uc = $this->container;
			$dao = $uc->getDao();

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
							new DBField($uc->getParentIdField()),
							$uc->getParentObject()->getId()
						)
					)->
					andWhere(
						Expression::in(
							$uc->getChildIdField(),
							ArrayUtils::getIdsArray($delete)
						)
					)
				);
				
				foreach ($delete as $object)
					$dao->uncacheById($object->getId());
			}

			return $this;
		}
	}
?>