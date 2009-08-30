<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
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

		public function sync(&$insert, &$update = array(), &$delete)
		{
			$uc = $this->container;
			$dao = $uc->getDao();

			if ($delete) {
				DBPool::getByDao($dao)->queryNull(
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

			if ($insert)
				for ($i = 0, $size = count($insert); $i < $size; ++$i)
					$dao->add($insert[$i]);

			if ($update)
				for ($i = 0, $size = count($update); $i < $size; ++$i)
					$dao->save($update[$i]);

			return $this;
		}
	}
?>