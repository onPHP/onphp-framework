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
		abstract public function getIdName();
		
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
				$order =
					isset($info['order'])
						? $info['order']
						: null;
				
				$query =
					OSQL::select()->get($mainId)->
					from($this->getTable());
				
				if (isset($info['order'])) {
					if ($info['order'] instanceof OrderBy)
						$query->orderBy($info['order']);
					elseif ($info['order'] instanceof OrderChain)
						$query->setOrderChain($info['order']);
					else
						throw new WrongStateException('strange order arrived');
				}
				
				$proto = reset($list)->proto();
				
				$this->processPath($proto, $path, $query);
				
				$query->where(
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
				
				if (
					$property->getRelationId() == MetaRelation::ONE_TO_MANY
				) {
					$table = $dao->getTable();
				} else {
					$table = $self->$getter()->getHelperTable();
				}
				
				$id = $this->getIdName();
				$collection = array();
				
				if ($lazy) {
					if ($property->getRelationId() == MetaRelation::MANY_TO_MANY) {
						$childId = $self->$getter()->getChildIdField();
					} else {
						$childId = $dao->getIdName();
					}
					
					$alias = 'cid'; // childId, collectionId, whatever
					
					$query->get(
						DBField::create($childId, $table), $alias
					);
					
					try {
						$rows = $dao->getCustomList($query);
						
						foreach ($rows as $row)
							if (!empty($row[$alias]))
								$collection[$row[$id]][] = $row[$alias];
						
					} catch (ObjectNotFoundException $e) {/*_*/}
				} else {
					$prefix = $dao->getTable().'_';
					
					$query->
						arrayGet(
							$dao->getFields(),
							$prefix
						);
					
					if (!$property->isRequired()) {
						$query->andWhere(
							Expression::notNull(
								DBField::create($prefix.$dao->getIdName())
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
				
				foreach ($list as $object) {
					if (!empty($collection[$object->getId()]))
						$object->$method($collection[$object->getId()], $lazy);
					else
						$object->$method(array(), $lazy);
				}
			}
			
			return $list;
		}
		
		public function processPath(
			AbstractProtoClass $proto, $probablyPath, JoinCapableQuery $query
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
					$table = $self->$getter()->getHelperTable();
					
					if (!$query->hasJoinedTable($table)) {
						$logic =
							Expression::eq(
								DBField::create(
									$this->getIdName(),
									$this->getTable()
								),
								
								DBField::create(
									$self->$getter()->getParentIdField(),
									$table
								)
							);
						
						if ($property->isRequired())
							$query->join($table, $logic);
						else
							$query->leftJoin($table, $logic);
					}
					
					$logic =
						Expression::eq(
							DBField::create(
								$dao->getIdName(),
								$dao->getTable()
							),
							
							DBField::create(
								$self->$getter()->getChildIdField(),
								$table
							)
						);
				} else {
					$logic =
						Expression::eq(
							DBField::create(
								$self->$getter()->getParentIdField(),
								$dao->getTable()
							),
							
							DBField::create(
								$this->getIdName(),
								$this->getTable()
							)
						);
				}
				
				if (!$query->hasJoinedTable($dao->getTable())) {
					if ($property->isRequired())
						$query->join($dao->getTable(), $logic);
					else
						$query->leftJoin($dao->getTable(), $logic);
				}
			} else { // OneToOne, LazyOneToOne
				$className = $property->getClassName();
				
				// prevents useless joins
				if (
					isset($path[1])
					&& ($path[1] == 'id')
					&& (count($path) == 1)
				)
					return
						new DBField(
							$property->getDumbIdName(),
							$this->getTable()
						);

				$dao = call_user_func(array($className, 'dao'));
				
				if (!$query->hasJoinedTable($dao->getTable())) {
					$logic =
						Expression::eq(
							DBField::create(
								$this->getFieldFor($property->getName()),
								$this->getTable()
							),
							
							DBField::create(
								$dao->getIdName(),
								$dao->getTable()
							)
						);
					
					if ($property->isRequired())
						$query->join($dao->getTable(), $logic);
					else
						$query->leftJoin($dao->getTable(), $logic);
				}
			}
			
			return $dao->guessAtom(implode('.', $path), $query);
		}
		
		public function guessAtom($atom, JoinCapableQuery $query)
		{
			if ($atom instanceof Property)
				return $this->mapProperty($atom);
			elseif (is_string($atom)) {
				if (strpos($atom, '.') !== false) {
					return
						$this->processPath(
							call_user_func(
								array($this->getObjectName(), 'proto')
							),
							$atom,
							$query
						);
				} elseif (array_key_exists($atom, $this->mapping))
					return $this->mapProperty(new Property($atom));
			} elseif ($atom instanceof LogicalObject)
				return $atom->toMapped($this, $query);
			elseif (
				($atom instanceof DBValue)
				|| ($atom instanceof DBField)
			) {
				return $atom;
			}
			
			return new DBValue($atom);
		}
		
		protected function mapProperty(Property $property)
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
	}
?>