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
		public static function getIdName()
		{
			return 'id';
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
			elseif (is_string($atom)) {
				if (strpos($atom, '.') !== false)
					return $this->processPath($atom, $query);
				elseif (array_key_exists($atom, $this->mapping))
					return $this->mapProperty(new Property($atom), $query);
			} elseif ($atom instanceof LogicalObject)
				return $atom->toMapped($this, $query);
			elseif ($atom instanceof DBValue)
				return $atom;
			
			return new DBValue($atom);
		}
		
		protected function processPath($path, JoinCapableQuery $query)
		{
			$path = explode('.', $path);
			
			$property = $path[0];
			unset($path[0]);
			
			// prevents useless joins
			if (
				isset($path[1])
				&& ($path[1] == 'id')
				&& (count($path) == 1)
			) {
				$onlyId = true;
			} else {
				$onlyId = false;
			}
			
			$className = $this->getClassFor($property);
			
			if (is_array($className)) { // container
				$containerName = $className[0];
				$objectName = $className[1];
				
				$table =
					call_user_func(
						array($containerName, 'getHelperTable')
					);
				
				$dao = call_user_func(array($objectName, 'dao'));
				
				if (!$query->hasJoinedTable($table))
					$query->
						join(
							$table,
							
							Expression::eq(
								DBField::create(
									$this->getIdName(),
									$this->getTable()
								),
								
								DBField::create(
									call_user_func(
										array($containerName, 'getParentIdField')
									),
									$table
								)
							)
						);
				
				if ($onlyId)
					return
						DBField::create(
							call_user_func(
								array($containerName, 'getChildIdField')
							),
							$table
						);
				elseif (!$query->hasJoinedTable($dao->getTable()))
					$query->join(
						$dao->getTable(),
						
						Expression::eq(
							DBField::create(
								$dao->getIdName(),
								$dao->getTable()
							),
							
							DBField::create(
								call_user_func(
									array($containerName, 'getChildIdField')
								),
								$table
							)
						)
					);
			} else {
				if ($onlyId)
					return
						new DBField(
							$this->getFieldFor($property),
							$this->getTable()
						);

				$dao = call_user_func(array($className, 'dao'));
				
				if (!$query->hasJoinedTable($dao->getTable()))
					$query->
						join(
							$dao->getTable(),
							
							Expression::eq(
								DBField::create(
									$this->getFieldFor($property),
									$this->getTable()
								),
								
								DBField::create(
									$dao->getIdName(),
									$dao->getTable()
								)
							)
						);
			}
			
			return $dao->guessAtom(implode('.', $path), $query);
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
			
			if ($this->mapping[$name] === null)
				return new DBField($name, $this->getTable());
			
			return new DBField($this->mapping[$name], $this->getTable());
		}

		protected function inject(
			InsertOrUpdateQuery $query, Identifiable $object
		)
		{
			$this->checkObjectType($object);
			
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