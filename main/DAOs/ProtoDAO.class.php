<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
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
	abstract class ProtoDAO extends GenericDAO
	{
		public function fetchCollections(
			/* array */ $collections, /* array */ $list
		)
		{
			$ids = array();
			
			foreach ($list as $object) {
				$ids[] = $object->getId();
			}
			
			$mainId = DBField::create(
				$this->getIdName(),
				$this->getTable()
			);
			
			foreach ($collections as $path => $info) {
				$lazy = $info['lazy'];
				
				$query =
					OSQL::select()->get($mainId)->
					from($this->getTable());
				
				if ($criteria = $info['criteria']) {
					$query->
						andWhere($criteria->getLogic())->
						setOrderChain($criteria->getOrder());
				}
				
				$proto = reset($list)->proto();
				
				$this->processPath($proto, $path, $query, $this->getTable());
				
				$query->andWhere(
					Expression::in($mainId, $ids)
				);
				
				// find final destination
				foreach (explode('.', $path) as $name) {
					$property = $proto->getPropertyByName($name);
					$className = $property->getClassName();
					
					$proto = call_user_func(
						array(
							$className,
							'proto'
						)
					);
				}
				
				$dao = call_user_func(array($className, 'dao'));
				
				$selfName = $this->getObjectName();
				$self = new $selfName;
				$getter = 'get'.ucfirst($property->getName());
				
				Assert::isTrue(
					$property->getRelationId() == MetaRelation::ONE_TO_MANY
					|| $property->getRelationId() == MetaRelation::MANY_TO_MANY
				);
				
				$table = $dao->getJoinName($property->getColumnName());
				
				$id = $this->getIdName();
				$collection = array();
				
				if ($lazy) {
					if ($property->getRelationId() == MetaRelation::MANY_TO_MANY) {
						$childId = $self->$getter()->getChildIdField();
					} else {
						$childId = $dao->getIdName();
					}
					
					$alias = 'cid'; // childId, collectionId, whatever
					
					$field = DBField::create($childId);
					
					$query->get($field, $alias);
					
					if (!$property->isRequired())
						$query->andWhere(Expression::notNull($field));
					
					try {
						$rows = $dao->getCustomList($query);
						
						foreach ($rows as $row)
							if (!empty($row[$alias]))
								$collection[$row[$id]][] = $row[$alias];
						
					} catch (ObjectNotFoundException $e) {/*_*/}
				} else {
					$prefix = $table.'_';
					
					$query->
						arrayGet(
							$dao->getFields(),
							$prefix
						);
					
					if (!$property->isRequired()) {
						$query->andWhere(
							Expression::notNull(
								DBField::create($dao->getIdName(), $table)
							)
						);
					}
					
					try {
						// otherwise we don't know which object
						// belongs to which collection
						$rows = $dao->getCustomList($query);
						
						foreach ($rows as $row) {
							$collection[$row[$id]][] =
								$dao->makeObject($row, $prefix);
						}
					} catch (ObjectNotFoundException $e) {/*_*/}
				}
				
				$method = 'fill'.ucfirst($property->getName());
				
				Assert::isTrue(
					method_exists(reset($list), $method),
					'can not find filler method'
				);
				
				foreach ($list as $object) {
					if (!empty($collection[$object->getId()]))
						$object->$method($collection[$object->getId()], $lazy);
					else
						$object->$method(array(), $lazy);
				}
			}
			
			return $list;
		}
		
		private function processPath(
			AbstractProtoClass $proto, 
			$probablyPath, 
			JoinCapableQuery $query,
			$table,
			$prefix = null
		)
		{
			$path = explode('.', $probablyPath);
			
			try {
				$property = $proto->getPropertyByName($path[0]);
			} catch (MissingElementException $e) {
				// oh, it's a value, not a property
				return new DBValue($probablyPath);
			}
			
			unset($path[0]);
			
			Assert::isTrue(
				$property->getRelationId() != null
				&& !$property->isGenericType()
			);
			
			$propertyDao = call_user_func(
				array(
					$property->getClassName(),
					'dao'
				)
			);
			
			Assert::isNotNull(
				$propertyDao,
				'can not find target dao for "'.$property->getName().'" property'
				.' at "'.get_class($proto).'"'
			);
			
			$alias = $propertyDao->getJoinName(
				$property->getColumnName(),
				$prefix
			);
			
			if (
				$property->getRelationId() == MetaRelation::ONE_TO_MANY
				|| $property->getRelationId() == MetaRelation::MANY_TO_MANY
			) {
				$remoteName = $property->getClassName();
				$selfName = $this->getObjectName();
				$self = new $selfName;
				$getter = 'get'.ucfirst($property->getName());
				$dao = call_user_func(array($remoteName, 'dao'));
				
				if ($property->getRelationId() == MetaRelation::MANY_TO_MANY) {
					$helperTable = $self->$getter()->getHelperTable();
					$helperAlias = $helperTable;
					
					if (!$query->hasJoinedTable($helperAlias)) {
						$logic =
							Expression::eq(
								DBField::create(
									$this->getIdName(),
									$table
								),
								
								DBField::create(
									$self->$getter()->getParentIdField(),
									$helperAlias
								)
							);
						
						if ($property->isRequired())
							$query->join($helperTable, $logic, $helperAlias);
						else
							$query->leftJoin($helperTable, $logic, $helperAlias);
					}
					
					$logic =
						Expression::eq(
							DBField::create(
								$propertyDao->getIdName(),
								$alias
							),
							
							DBField::create(
								$self->$getter()->getChildIdField(),
								$helperAlias
							)
						);
				} else {
					$logic =
						Expression::eq(
							DBField::create(
								$self->$getter()->getParentIdField(),
								$alias
							),
							
							DBField::create(
								$this->getIdName(),
								$table
							)
						);
				}
				
				if (!$query->hasJoinedTable($alias)) {
					if ($property->isRequired())
						$query->join($dao->getTable(), $logic, $alias);
					else
						$query->leftJoin($dao->getTable(), $logic, $alias);
				}
			} else { // OneToOne, LazyOneToOne
				
				// prevents useless joins
				if (
					isset($path[1])
					&& ($path[1] == $propertyDao->getIdName())
					&& (count($path) == 1)
				)
					return
						new DBField(
							$property->getColumnName(),
							$table
						);

				if (!$query->hasJoinedTable($alias)) {
					$logic =
						Expression::eq(
							DBField::create(
								$property->getColumnName(),
								$table
							),
							
							DBField::create(
								$propertyDao->getIdName(),
								$alias
							)
						);
					
					if ($property->isRequired())
						$query->join($propertyDao->getTable(), $logic, $alias);
					else
						$query->leftJoin($propertyDao->getTable(), $logic, $alias);
				}
			}
			
			return $propertyDao->guessAtom(
				implode('.', $path), 
				$query,
				$alias, 
				$propertyDao->getJoinPrefix($property->getColumnName(), $prefix)
			);
		}
		
		public function guessAtom(
			$atom, 
			JoinCapableQuery $query,
			$table = null,
			$prefix = null
		)
		{
			if ($table === null)
				$table = $this->getTable();

			if ($atom instanceof Property) {
				
				return $this->mapProperty($atom, $table);
				
			} elseif (is_string($atom)) {
				if (strpos($atom, '.') !== false) {
					return
						$this->processPath(
							call_user_func(
								array($this->getObjectName(), 'proto')
							),
							$atom,
							$query,
							$table,
							$prefix
						);
				} elseif (in_array($atom, $this->getMapping()))
					return $this->mapProperty(new Property($atom), $table);
				elseif (
					($query instanceof SelectQuery)
					&& $query->hasAliasInside($atom)
				) {
					return new DBField($atom);
				}
			} elseif ($atom instanceof MappableObject)
				return $atom->toMapped($this, $query);
			elseif (
				($atom instanceof DBValue)
				|| ($atom instanceof DBField)
			) {
				return $atom;
			}
			
			return new DBValue($atom);
		}
		
		private function mapProperty(Property $property, $table)
		{
			$name = $property->getName();
			$mapping = $this->getMapping();
			
			Assert::isTrue(
				array_key_exists($name, $mapping)
			);
			
			return new DBField($mapping[$name], $table);
		}
	}
?>