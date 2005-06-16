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

	class StorableContainer
	{
		private $new 		= array(); // new without id
		private $insert		= array(); // new with id, independent links
		private $saved		= array(); // in database
		private $deleted	= array();
		
		private $partDAO	= null;
		
		public static function create(PartDAO $partDAO)
		{
			return new StorableContainer($partDAO);
		}

		public function __construct(PartDAO $partDAO)
		{
			$this->partDAO = $partDAO;
		}

		public function getPartDAO()
		{
			return $this->partDAO;
		}

		public function add(Storable $object)
		{
			if ($object->isNew())
				$this->new[] = $object;
			else
				try {
					$this->update($object);
				} catch (WrongStateException $e) {
					$this->insert[$object->getId()] = $object;
				}

			return $this;
		}
		
		public function update(Storable $object)
		{
			
			if (($id = $object->getId()) && isset($this->saved[$id]))
				$this->saved[$id] = $object;
			else
				throw new WrongStateException('you should use add for new objects');
		}

		public function getById($id)
		{
			if (!isset($this->saved[$id]))
				throw new WrongArgumentException("id not found in saved objects");

			if (!is_object($this->saved[$id]))
				$this->saved[$id] = $this->partDAO->getById($id);

			return $this->saved[$id]; 
		}

		public function dropById($id)
		{
			if (!isset($this->saved[$id]))
				throw new ObjectNotFoundException('id not found in saved objects');
			
			$this->deleted[$id] = $this->saved[$id];
			unset($this->saved[$id]);
			return $this;
		}

		public function getList()
		{
			if (count($this->new))
				throw WrongStateException('can\'t enumerate objects without id, save them first');

			// populate saved
			foreach ($this->saved as $id => $val) {
				$this->getById($id);
			}

			return $this->saved + $this->insert;
		}

		public function getCount()
		{
			return count($this->saved) + count($this->insert);
		}

		/**
		 * everything below is for DAO use only
		**/
		public function addSaved(Storable $object)
		{
			if ($object !== null && $object->getId())
				$this->saved[$object->getId()] = $object;
			else
				throw WrongArgumentException('saved object must have an id');

			return $this;
		}
		
		public function setSavedList($list)
		{
			Assert::isArray($list);

			$this->saved = $list;

			return $this;
		}

		public function getNewList()
		{
			return $this->new;
		}

		public function getDeletedList()
		{
			return $this->deleted;
		}

		public function clearDeletedList()
		{
			unset($this->deleted);
			$this->deleted = array();

			return $this;
		}

		public function getSavedList()
		{
			return $this->saved;
		}

		public function clearNewList()
		{
			unset($this->new);
			$this->new = array();

			return $this;
		}

		public function getInsertList()
		{
			return $this->insert;
		}

		public function clearInsertList()
		{
			unset($this->insert);
			$this->insert = array();

			return $this;
		}
	}
?>
