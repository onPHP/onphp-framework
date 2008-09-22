<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
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
	abstract class ProtoDAO extends GenericDAO
	{
		public function fetchCollections(
			/* array */ $collections, /* array */ $list
		)
		{
			Assert::isNotEmptyArray($list);
			
			$ids = ArrayUtils::getIdsArray($list);
			
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
					$query = $criteria->setDao($this)->fillSelectQuery($query);
				}
				
				$proto = reset($list)->proto();
				
				$this->processPath($proto, $path, $query, $this->getTable());
				
				$query->andWhere(
					Expression::in($mainId, $ids)
				);
				
				$propertyPath = $info['propertyPath'];
				
				$property	= $propertyPath->getFinalProperty();
				$proto		= $propertyPath->getFinalProto();
				$dao		= $propertyPath->getFinalDao();
				
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
					
					foreach ($dao->getFields() as $field) {
						$query->get(
							DBField::create(
								$field, $table
							),
							$prefix.$field
						);
					}
					
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
			$parentRequired = true,
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
					if ($property->isRequired() && $parentRequired)
						$query->join($dao->getTable(), $logic, $alias);
					else
						$query->leftJoin($dao->getTable(), $logic, $alias);
				}
			} else { // OneToOne, lazy OneToOne
				
				// prevents useless joins
				if (
					isset($path[1])
					&& (count($path) == 1)
					&& ($path[1] == $propertyDao->getIdName())
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
					
					if ($property->isRequired() && $parentRequired)
						$query->join($propertyDao->getTable(), $logic, $alias);
					else
						$query->leftJoin($propertyDao->getTable(), $logic, $alias);
				}
			}
			
			return $propertyDao->guessAtom(
				implode('.', $path),
				$query,
				$alias,
				$property->isRequired() && $parentRequired,
				$propertyDao->getJoinPrefix($property->getColumnName(), $prefix)
			);
		}
		
		public function guessAtom(
			$atom,
			JoinCapableQuery $query,
			$table = null,
			$parentRequired = true,
			$prefix = null
		)
		{
			if ($table === null)
				$table = $this->getTable();

			if (is_string($atom)) {
				if (strpos($atom, '.') !== false) {
					return
						$this->processPath(
							call_user_func(
								array($this->getObjectName(), 'proto')
							),
							$atom,
							$query,
							$table,
							$parentRequired,
							$prefix
						);
				} elseif (
					array_key_exists(
						$atom,
						$mapping = $this->getMapping()
					)
				) {
					// BC, <0.9
					if (!$mapping[$atom])
						return new DBField($atom, $table);
					
					return new DBField($mapping[$atom], $table);
				} elseif (
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
	}
?>