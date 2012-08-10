<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey Denisov                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Street magic logic
	 */
	class TaggableSmartHandler implements TaggableHandler
	{
		const ID_POSTFIX = '|id|';

		public function getCacheObjectTags(IdentifiableObject $object, $className)
		{
			return array($this->getTagByClassAndId($className, $object->getId()));
		}

		public function getUncacheObjectTags(IdentifiableObject $object, $className)
		{
			$tags = $this->getCacheObjectTags($object, $className);
			$tags = array_merge($this->getDefaultTags($className), $tags);

			foreach ($this->getLinkProperties($className) as $name => $property) {
				/* @var $property LightMetaProperty */
				if ($name == 'id') {
					continue;
				}
				if ($property->getClassName()) {
					if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
						if ($property->getFetchStrategyId() == FetchStrategy::LAZY) {
							if ($linkedObjectId = $object->{$property->getGetter().'Id'}()) {
								$tags[] = $this->getTagByClassAndId($property->getClassName(), $linkedObjectId);
							}
						} elseif ($property->getFetchStrategyId()) {
							if (
								($linkedObject = $object->{$property->getGetter()}())
								&& $linkedObject instanceof IdentifiableObject
								&& $linkedObjectId = $linkedObject->getId()
							) {
								$tags[] = $this->getTagByClassAndId($property->getClassName(), $linkedObjectId);
							}
						}
					} elseif ($property->getRelationId() == MetaRelation::MANY_TO_MANY) {
						$daoHelper = $object->{$property->getGetter()}();
						/* @var $daoHelper ManyToManyLinked */
						$tags[] = $daoHelper->getHelperTable();
					}
				}
			}

			return $tags;
		}

		public function getQueryTags(SelectQuery $query, $className)
		{
			$columns = $this->getLinkObjectColumnListByClass($className);

			$tagList = array();
			if ($query->getTablesCount() > 1 || !$this->isLazy($className)) {
				foreach ($query->getJoinedTables() as $table) {
					/* @var $table SQLRealTableName */
					$tagList[] = $table->getRealTable();
				}
			} else {
				foreach ($query->getWhere() as $whereObject) {
					if ($whereObject instanceof BinaryExpression) {
						if ($tag = $this->getTagByBinaryExpression($whereObject, $query, $className, $columns)) {
							$tagList[] = $tag;
						}
					}
					if ($whereObject instanceof LogicalChain) {
						foreach ($whereObject->getChain() as $logic) {
							if ($logic instanceof BinaryExpression) {
								if ($tag = $this->getTagByBinaryExpression($logic, $query, $className, $columns)) {
									$tagList[] = $tag;
								}
							}
						}
					}
				}
				
				if (empty($tagList)) {
					$tagList = $this->getDefaultTags($className);
				}
			}

			return $tagList;
		}

		public function getNullObjectTags($id, $className)
		{
			$tags = $this->getDefaultTags($className);
			$tags[] = $this->getTagByClassAndId($className, $id);

			return $tags;
		}

		public function getDefaultTags($className)
		{
			return array($this->getTableByClassName($className));
		}

		protected function getTagByClassAndId($className, $id)
		{
			return $this->getTableByClassName($className).self::ID_POSTFIX.$id;
		}

		protected final function getTagByBinaryExpression(BinaryExpression $expression, SelectQuery $query, &$className, &$columns)
		{
			$tag = null;

			if ($expression->getLogic() == BinaryExpression::EQUALS) {
				$first = $expression->getLeft();
				$second = $expression->getRight();
				if ($second instanceof DBField || $first instanceof DBValue) {
					$first = $expression->getRight();
					$second = $expression->getLeft();
				}
				
				$columnClassName = null;
				$idValue = null;
				if (
					$first instanceof DBField
					&& isset($columns[$first->getField()])
				) {
					if ($first->getTable() === null) {
						$table = $query->getFirstTable();
					} elseif ($first->getTable() instanceof FromTable) {
						$table = $first->getTable();
					}
					if ($table instanceof FromTable) {
						$table = $table->getTable();
					}

					if ($table !== null && $table == $this->getTableByClassName($className)) {
						$columnClassName = $columns[$first->getField()];
					}
				} elseif (is_string($first) && $first && isset($columns[$first])) {
					$table = $query->getFirstTable();
					if ($table instanceof FromTable) {
						$table = $table->getTable();
					}
					if ($table !== null && $table == $this->getTableByClassName($className)) {
						$columnClassName = $columns[$first];
					}
				}

				if ($second instanceof DBValue) {
					$idValue = $second->getValue();
				} elseif ((is_integer($second) || is_string($second)) && $second) {
					$idValue = $second;
				}

				if ($columnClassName && $idValue) {
					$tag = $this->getTagByClassAndId($columnClassName, $idValue);
				}
			}

			return $tag;
		}

		protected function getLinkObjectColumnListByClass($className)
		{
			static $result = array();
			if (!isset($result[$className])) {
				$columnList = array();
				foreach ($this->getLinkProperties($className) as $property) {
					/* @var $property LightMetaProperty */
					if ($property->getRelationId() == MetaRelation::ONE_TO_ONE || $property->getName() == 'id') {
						$columnList[$property->getColumnName()] = $property->getClassName();
					}
				}
				$result[$className] = $columnList;
			}
			return $result[$className];
		}
		
		protected function getTableByClassName($className) {
			if (ClassUtils::isInstanceOf($className, 'DAOConnected')) {
				return ClassUtils::callStaticMethod("{$className}::dao")->getTable();
			} else {
				return $className.'|className|';
			}
		}

		protected function isLazy($className)
		{
			foreach ($this->getLinkProperties($className) as $property) {
				if (
					$property->getRelationId() == MetaRelation::ONE_TO_ONE
					&& ($property->getFetchStrategyId() == FetchStrategy::CASCADE
						|| $property->getFetchStrategyId() == FetchStrategy::JOIN)
				) {
					return false;
				}
			}

			return true;
		}
		
		protected function getLinkProperties($className) {
			$propertyList = array();
			foreach ($this->getPropertiesByClassName($className) as $name => $property) {
				if ($property instanceof InnerMetaProperty) {
					$propertyList = array_merge(
						$propertyList,
						$this->getLinkProperties($property->getClassName())
					);
				} elseif ($property instanceof LightMetaProperty) {
					switch ($property->getType()) {
						case 'identifier':
						case 'identifierList':
						case 'integerIdentifier':
						case 'integerIdentifierList':
						case 'scalarIdentifier':
						case 'scalarIdentifierList':
							if ($property->getClassName()) {
								$propertyList[] = $property;
							}
							break;
					}
				}
			}
			return $propertyList;
		}

		protected function getPropertiesByClassName($className)
		{
			$proto = ClassUtils::callStaticMethod($className.'::proto');

			return $proto->getPropertyList();
		}
	}
?>