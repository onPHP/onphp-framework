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
	 * @ingroup DAOs
	**/
	abstract class StorableDAO extends ProtoDAO
	{
		public function take(Identifiable $object)
		{
			return
				$object->getId()
					? $this->save($object)
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
					OSQL::update()->where(
						Expression::eqId($this->getIdName(), $object)
					),
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
		
		protected function inject(
			InsertOrUpdateQuery $query, Identifiable $object
		)
		{
			$this->checkObjectType($object);
			
			$count = DBPool::getByDao($this)->queryCount(
				$this->setQueryFields(
					$query->setTable($this->getTable()), $object
				)
			);
			
			$this->uncacheById($object->getId());
			
			if ($count !== 1)
				throw new WrongStateException(
					'racy or insane inject happened'
				);
			
			// clean out Identifier, if any
			return
				$this->identityMap[$object->getId()]
					= $object->setId($object->getId());
		}
	}
?>