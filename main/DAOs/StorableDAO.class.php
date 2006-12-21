<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class StorableDAO extends GenericDAO
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
			
			if ($fields === null) {
				if ($this->mapping)
					foreach ($this->getMapping() as $prop => $field)
						$fields[] = ($field === null ? $prop : $field);
				elseif ($this->fields)
					$fields = &$this->fields;
				else
					throw new WrongStateException(
						'there are no fields specified for '
						."'{$this->getObjectName()}DAO'"
					);
			}
			
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
		
		public function guessAtom($atom, JoinCapableQuery $query)
		{
			if ($atom instanceof Property)
				return $this->mapProperty($atom, $query);
			elseif (is_string($atom) && array_key_exists($atom, $this->mapping))
				return $this->mapProperty(new Property($atom), $query);
			elseif ($atom instanceof LogicalObject)
				return $atom->toMapped($this, $query);
			elseif ($atom instanceof DBValue)
				return $atom;
			
			return new DBValue($atom);
		}
		
		protected function mapProperty(Property $property, JoinCapableQuery $query)
		{
			$name = $property->getName();
			
			Assert::isTrue(
				array_key_exists(
					$name,
					$this->mapping
				)
			);
			
			if (!$this->mapping[$name])
				return $name;
			
			return $this->mapping[$name];
		}

		protected function inject(
			InsertOrUpdateQuery $query, Identifiable $object
		)
		{
			Assert::isTrue(
				get_class($object) === $this->getObjectName(),
				'strange object given, i can not inject it'
			);
			
			DBPool::getByDao($this)->queryNull(
				$this->setQueryFields(
					$query->setTable($this->getTable()), $object
				)
			);
			
			$this->uncacheById($object->getId());
			
			// clean out Identifier, if any
			return $object->setId($object->getId());
		}
	}
?>