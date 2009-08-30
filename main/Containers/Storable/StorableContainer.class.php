<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see UnifiedContainer for alternative
	 * 
	 * @ingroup Containers
	**/
	class StorableContainer
	{
		// lazy loading state
		const LOAD_IDS		= 1;
		const LOAD_OBJECTS	= 2;
		const LOADED		= 3;

		private $loaded		= null;
		private $parentId	= null;

		private $new 		= array(); // new without id
		private $insert		= array(); // new with id, independent links
		private $saved		= array(); // in database
		private $deleted	= array();
		
		private $partDAO	= null;
		private $expires	= null;
		
		public static function create(
			PartDAO $partDAO, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			return new StorableContainer($partDAO, $expires);
		}

		public function __construct(
			PartDAO $partDAO, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$this->partDAO = $partDAO;
			$this->expires = $expires;
			
			$this->loaded = self::LOADED;
		}

		public function getPartDAO()
		{
			return $this->partDAO;
		}

		public function add(Identifiable $object)
		{
			$this->checkLoaded();
			if (!$object->getId())
				$this->new[] = $object;
			else
				try {
					$this->update($object);
				} catch (WrongStateException $e) {
					$this->insert[$object->getId()] = $object;
				}

			return $this;
		}
		
		public function update(Identifiable $object)
		{
			$this->checkLoaded();
			if (($id = $object->getId()) && isset($this->saved[$id]))
				$this->saved[$id] = $object;
			else
				throw new WrongStateException(
					'you should use add for new objects'
				);

			return $this;
		}

		public function getById($id)
		{
			$this->checkLoaded();
			if (!isset($this->saved[$id]))
				throw new WrongArgumentException(
					'id not found in saved objects'
				);

			if (!is_object($this->saved[$id]))
				$this->saved[$id] = $this->partDAO->getById($id, $this->expires);

			return $this->saved[$id];
		}

		public function dropById($id)
		{
			$this->checkLoaded();
			if (!isset($this->saved[$id]))
				throw new WrongArgumentException(
					'id not found in saved objects'
				);
			
			$this->deleted[$id] = $this->saved[$id];
			unset($this->saved[$id]);

			return $this;
		}

		public function getList()
		{
			$this->checkLoaded();

			// populate saved
			foreach ($this->saved as $id => &$val) {
				$this->getById($id);
			}

			return $this->new + $this->saved + $this->insert;
		}
		
		public function getIdsList()
		{
			$this->checkLoaded();

			if (count($this->new))
				throw WrongStateException(
					'can not enumerate objects without id, save them first'
				);

			return array_keys($this->saved + $this->insert);
		}

		public function getCount()
		{
			$this->checkLoaded();

			return count($this->saved) + count($this->insert);
		}

		// DAO part
		public function save($parentId)
		{
			if ($this->loaded != self::LOADED)
				return $this;

			foreach ($this->deleted as $id => &$deleted)
				$this->partDAO->dropByBothId($parentId, $id);

			$this->deleted = array();

			foreach ($this->saved as &$object) {
				if (is_object($object)) // TODO: compare with clone
					$this->partDAO->save($parentId, $object);
			}

			$this->processAdd($parentId);

			foreach ($this->insert as &$insert) {
				$this->partDAO->insert($parentId, $insert);
				$this->addSaved($insert);
			}

			$this->insert = array();

			return $this;
		}
		
		public function import($parentId)
		{
			if ($this->loaded != self::LOADED)
				throw WrongStateException(
					'can not import such container'
				);

			foreach ($this->saved as &$object)
				$this->partDAO->import($parentId, $object);

			$this->processAdd($parentId);
			
			foreach ($this->insert as &$insert) {
				$this->partDAO->import($parentId, $insert);
				$this->addSaved($insert);
			}

			$this->insert = array();

			return $this;
		}

		public static function getByParentId(
			$parentId, PartDAO $childDAO, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$container = new StorableContainer($childDAO, $expires);
			$container->loaded = self::LOAD_OBJECTS;
			$container->parentId = $parentId;

			return $container;
		}

		public static function getIdsByParentId(
			$parentId, PartDAO $childDAO, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$container = new StorableContainer($childDAO, $expires);
			$container->loaded = self::LOAD_IDS;
			$container->parentId = $parentId;

			return $container;
		}
			
		public static function dropByParentId($parentId, PartDAO $childDAO)
		{
			return $childDAO->dropByParentId($parentId);
		}

		private function checkLoaded()
		{
			if ($this->loaded == self::LOAD_IDS && $this->parentId) {

				try {
					$list = $this->partDAO->getChildIdsList($this->parentId);

					foreach ($list as $id)
						$this->saved[$id] = $id;

				} catch (ObjectNotFoundException $e) {/* we don't care */}

			} elseif ($this->loaded == self::LOAD_OBJECTS && $this->parentId) {

				try {
					$list = $this->partDAO->getListByParentId($this->parentId);
					$this->saved = ArrayUtils::convertObjectList($list);

				} catch (ObjectNotFoundException $e) {/* we don't care */}

			} elseif ($this->loaded != self::LOADED)
				throw new WrongStateException('container in a bad state');

			$this->loaded = self::LOADED;

			return $this;
		}
		
		private function processAdd($parentId)
		{
			foreach ($this->new as &$new) {
				$this->partDAO->add($parentId, $new);
				$this->addSaved($new);
			}

			$this->new = array();
			
			return $this;
		}

		private function addSaved(Identifiable $object)
		{
			if ($object !== null && $object->getId())
				$this->saved[$object->getId()] = $object;
			else
				throw new WrongArgumentException('saved object must have an id');

			return $this;
		}
	}
?>