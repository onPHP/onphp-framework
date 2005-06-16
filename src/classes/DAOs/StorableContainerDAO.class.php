<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class StorableContainerDAO
	{
		public function save($parentId, StorableContainer $childs)
		{
			$childDAO = &$childs->getPartDAO();
			$deletedList = $childs->getDeletedList();

			foreach ($deletedList as $id => &$deleted)
				$childDAO->dropByBothId($parentId, $id);

			$childs->clearDeletedList();

			$objectsList = $childs->getSavedList();
			foreach ($objectsList as &$object) {
				if (is_object($object) && $object->isChanged())
					$childDAO->save($parentId, $object);
			}

			$this->processAdd($parentId, $childs);

			$insertList = $childs->getInsertList();
			foreach ($insertList as &$insert) {
				$childDAO->insert($parentId, $insert);
				$childs->addSaved($insert);
			}
			$childs->clearInsertList();

			return $this;
		}
		
		public function import($parentId, StorableContainer $childs)
		{
			$childDAO = &$childs->getPartDAO();
			$objectsList = $childs->getSavedList();

			foreach ($objectsList as &$object)
				$childDAO->import($parentId, $object);

			$this->processAdd($parentId, $childs, $childDAO);
			
			$insertList = $childs->getInsertList();
			foreach ($insertList as &$insert) {
				$childDAO->import($parentId, $insert);
				$childs->addSaved($insert);
			}
			$childs->clearInsertList();

			return $this;
		}

		public function getByParentId($parentId, PartDAO $childDAO) 
		{
			$container = new StorableContainer($childDAO);
			
			try {
				$objects = $childDAO->getListByParentId($parentId);
			} catch (ObjectNotFoundException $e) {
				return $container;
			}
			
			return $container->setSavedList($objects);
		}
		
		public function dropByParentId($parentId, PartDAO $childDAO)
		{
			$childDAO->dropByParentId($parentId);

			return $this;
		}
		
		/**
		 * Private section.
		**/

		private function processAdd($parentId, StorableContainer $childs)
		{
			$childDAO = &$childs->getPartDAO();
			$addList = $childs->getNewList();

			foreach ($addList as &$new) {
				$childDAO->add($parentId, $new);
				$childs->addSaved($new);
			}

			$childs->clearNewList();
			
			return $this;
		}
	}
?>