<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class MappedStorableDAO extends SmartDAO implements MappedDAO
	{
		// override later
		protected $mapping = array();
		
		public function getIdName()
		{
			return 'id';
		}
		
		public function getMapping()
		{
			return $this->mapping;
		}
		
		public function getFields()
		{
			static $fields = null;
			
			if ($fields === null)
				foreach ($this->mapping as $prop => $field)
					$fields[] = ($field === null ? $prop : $field);
			
			return $fields;
		}

		public function take(Identifiable $object)
		{
			return
				$object->getId()
					? $this->save($object)
					: $this->add($object);
		}
		
		public function add(Identifiable $object)
		{
			$object =
				$this->inject(
					OSQL::insert(),
					$object->setId(
						DBFactory::getDefaultInstance()->obtainSequence(
							$this->getSequence()
						)
					)
				);
			
			$this->dropLists();
			
			return $object;
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

		protected function inject(
			InsertOrUpdateQuery $query, Identifiable $object
		)
		{
			DBFactory::getDefaultInstance()->queryNull(
				$this->setQueryFields(
					$query->setTable($this->getTable()), $object
				)
			);
			
			$this->uncacheById($object->getId());
			
			return $object;
		}
	}
?>