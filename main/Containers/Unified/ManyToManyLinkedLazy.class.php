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
	final class ManyToManyLinkedLazy extends ManyToManyLinkedWorker
	{
		public function sync(&$insert, &$update = array(), &$delete)
		{
			Assert::isTrue($update === array());
			
			$dao = $this->container->getDao();
			
			$db = DBPool::getByDao($dao);
			
			if ($insert)
				for ($i = 0, $size = count($insert); $i < $size; ++$i)
					$db->queryNull($this->makeInsertQuery($insert[$i]));

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
				$query = $this->oq->toSelectQuery($uc->getDao())->dropFields();
			else
				$query = OSQL::select()->from($uc->getHelperTable());
			
			return
				$query->
					get($uc->getChildIdField())->
					distinct()->
					where(
						Expression::eq(
							new DBField($uc->getParentIdField()),
							new DBValue($uc->getParentObject()->getId())
						)
					);
		}
	}
?>