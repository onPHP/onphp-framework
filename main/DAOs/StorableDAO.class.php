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
	namespace Onphp;

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
			if ($object === $old)
				return $this->save($object);
			
			return $this->unite($object, $old);
		}
		
		public function unite(
			Identifiable $object, Identifiable $old
		)
		{
			$query = $this->getProtoClass()
				->fillQuery(OSQL::update($this->getTable()), $object, $old);
			
			if (!$query->getFieldsCount())
				return $object;
			
			return $this->doInject(
				$this->targetizeUpdateQuery($query, $object),
				$object
			);
		}
		
		/**
		 * @return \Onphp\UpdateQuery
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
