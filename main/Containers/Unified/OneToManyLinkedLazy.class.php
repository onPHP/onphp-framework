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
	final class OneToManyLinkedLazy extends OneToManyLinkedWorker
	{
		public function makeFetchQuery()
		{
			$uc = $this->container;
			
			return
				$this->targetize(
					$this->oq
						?
							$this->oq->toSelectQuery($uc->getDao())->
							dropFields()->
							get($uc->getChildIdField())
						:
							OSQL::select()->from($uc->getDao()->getTable())->
							get($uc->getChildIdField())
				);
		}
		
		public function sync(&$insert, &$update = array(), &$delete)
		{
			Assert::isTrue($update === array());
			
			$db = DBPool::getByDao($this->container->getDao());
			
			$uc = $this->container;
			$dao = $uc->getDao();

			if ($insert)
				$db->queryNull($this->makeMassUpdateQuery($insert));

			if ($delete) {
				// unlink or drop
				$uc->isUnlinkable()
					?
						$db->queryNull($this->makeMassUpdateQuery($delete))
					:
						$db->queryNull(
							OSQL::delete()->from($dao->getTable())->
							where(
								Expression::in(
									$uc->getChildIdField(),
									$delete
								)
							)
						);
				
				foreach ($delete as $id)
					$dao->uncacheById($id);
			}

			return $this;
		}
		
		private function makeMassUpdateQuery(&$ids)
		{
			$uc = $this->container;
			
			return
				OSQL::update($uc->getDao()->getTable())->
				set($uc->getParentIdField(), null)->
				where(
					Expression::in(
						$uc->getChildIdField(),
						$ids
					)
				);
		}
	}
?>