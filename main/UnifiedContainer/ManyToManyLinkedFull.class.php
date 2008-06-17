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
/* $Id$ */

	/**
	 * @ingroup Containers
	**/
	final class ManyToManyLinkedFull extends ManyToManyLinkedWorker
	{
		/**
		 * @return ManyToManyLinkedFull
		**/
		public function sync($insert, $update = array(), $delete)
		{
			$dao = $this->container->getDao();
			
			$db = DBPool::getByDao($dao);
			
			if ($insert)
				for ($i = 0, $size = count($insert); $i < $size; ++$i) {
					$db->queryNull(
						$this->makeInsertQuery(
							$dao->take($insert[$i])->getId()
						)
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
				
				$dao->uncacheByIds($ids);
			}
			
			return $this;
		}
		
		/**
		 * @return SelectQuery
		**/
		public function makeFetchQuery()
		{
			return
				$this->joinHelperTable(
					$this->makeSelectQuery()
				);
		}
	}
?>