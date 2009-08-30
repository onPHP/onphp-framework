<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup DAOs
	**/
	abstract class StorableDAO extends ProtoDAO
	{
		public function take(Identifiable $object)
		{
			return
				$object->getId()
					? $this->merge($object, true)
					: $this->add($object);
		}
		
		public function add(Identifiable $object)
		{
			return
				$this->inject(
					OSQL::insert(),
					$object->setId(
						DBPool::getByDao($this)->obtainSequence(
							$this->getSequence()
						)
					)
				);
		}
		
		public function save(Identifiable $object)
		{
			return
				$this->inject(
					$this->targetizeUpdateQuery(OSQL::update(), $object),
					$object
				);
		}
		
		public function import(Identifiable $object)
		{
			return
				$this->inject(
					OSQL::insert(),
					$object
				);
		}
		
		public function merge(Identifiable $object, $cacheOnly = true)
		{
			Assert::isNotNull($object->getId());
			
			$this->checkObjectType($object);
			
			$old = Cache::worker($this)->getCachedById($object->getId());
			
			if (!$old) { // unlikely
				if ($cacheOnly)
					return $this->save($object);
				else
					$old = Cache::worker($this)->getById($object->getId());
			}
			
			return $this->unite($object, $old);
		}
		
		public function unite(
			Identifiable $object, Identifiable $old
		)
		{
			Assert::isNotNull($object->getId());
			
			Assert::isTypelessEqual(
				$object->getId(), $old->getId(),
				'cannot merge different objects'
			);
			
			$query = OSQL::update($this->getTable());
			
			foreach ($this->getProtoClass()->getPropertyList() as $property) {
				$getter = $property->getGetter();
				
				if ($property->getClassName() === null) {
					$changed = ($old->$getter() !== $object->$getter());
				} else {
					/**
					 * way to skip pointless update and hack for recursive
					 * comparsion.
					**/
					$changed =
						($old->$getter() !== $object->$getter())
						|| ($old->$getter() != $object->$getter());
				}
				
				if ($changed)
					$property->fillQuery($query, $object);
			}
			
			if (!$query->getFieldsCount())
				return $object;
			
			$this->targetizeUpdateQuery($query, $object);
			
			return $this->doInject($query, $object);
		}
		
		/**
		 * @return UpdateQuery
		**/
		private function targetizeUpdateQuery(
			UpdateQuery $query,
			Identifiable $object
		)
		{
			return $query->where(Expression::eqId($this->getIdName(), $object));
		}
	}
?>