<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Containers
	**/
	namespace Onphp;

	final class ManyToManyLinkedLazy extends ManyToManyLinkedWorker
	{
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\ManyToManyLinkedLazy
		**/
		public function sync($insert, $update = array(), $delete)
		{
			Assert::isTrue($update === array());
			
			$dao = $this->container->getDao();
			
			$db = DBPool::getByDao($dao);
			
			if ($insert)
				for ($i = 0, $size = count($insert); $i < $size; ++$i)
					$db->queryNull($this->makeInsertQuery($insert[$i]));

			if ($delete) {
				$db->queryNull($this->makeDeleteQuery($delete));
				
				$dao->uncacheByIds($delete);
			}

			return $this;
		}
		
		/**
		 * @return \Onphp\SelectQuery
		**/
		public function makeFetchQuery()
		{
			$uc = $this->container;
			
			return
				$this->joinHelperTable(
					$this->makeSelectQuery()->
					dropFields()->
					get(
						new DBField(
							$uc->getChildIdField(),
							$uc->getHelperTable()
						)
					)
				);
		}
	}
?>