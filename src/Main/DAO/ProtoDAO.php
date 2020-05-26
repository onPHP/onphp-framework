<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\DAO;

use OnPHP\Core\Base\Assert;
use OnPHP\Main\Util\ArrayUtils;
use OnPHP\Core\OSQL\DBField;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Meta\Entity\MetaRelation;
use OnPHP\Core\Exception\ObjectNotFoundException;
use OnPHP\Core\OSQL\InsertOrUpdateQuery;
use OnPHP\Main\Base\AbstractProtoClass;
use OnPHP\Core\OSQL\JoinCapableQuery;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\OSQL\DBValue;
use OnPHP\Core\OSQL\SelectQuery;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\Logic\MappableObject;
use OnPHP\Core\OSQL\DialectString;

/**
 * @ingroup DAO
**/
abstract class ProtoDAO extends GenericDAO
{
	public function getJoinPrefix($field, $prefix = null)
	{
		return $this->getJoinName($field, $prefix).'__';
	}

	public function getJoinName($field, $prefix = null)
	{
		return dechex(crc32($prefix.$this->getTable())).'_'.$field;
	}

	public function fetchCollections(
		array $collections, array $list
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

			$proto = reset($list)->proto();

			$this->processPath($proto, $path, $query, $this->getTable());

			if ($criteria = $info['criteria']) {
				$query = $criteria->setDao($this)->fillSelectQuery($query);
			}

			$query->andWhere(
				Expression::in($mainId, $ids)
			);

			$propertyPath = $info['propertyPath'];

			$property	= $propertyPath->getFinalProperty();
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

				$field = DBField::create(
					$childId,
					$self->$getter()->getHelperTable()
				);

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
						DBField::create($field, $table),
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

			$suffix = ucfirst($property->getName());
			$fillMethod = 'fill'.$suffix;
			$getMethod = 'get'.$suffix;

			Assert::isTrue(
				method_exists(reset($list), $fillMethod),
				'can not find filler'
			);

			Assert::isTrue(
				method_exists(reset($list), $getMethod),
				'can not find getter'
			);

			foreach ($list as $object) {
				if (!empty($collection[$object->getId()]))
					$object->$fillMethod($collection[$object->getId()], $lazy);
				else
					$object->$getMethod()->mergeList(array());
			}
		}

		return $list;
	}

	protected function setQueryFields(InsertOrUpdateQuery $query, $object)
	{
		$this->checkObjectType($object);

		return $this->getProtoClass()->fillQuery($query, $object);
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

		Assert::classExists($property->getClassName());

		// checking whether we're playing with value object
		if (!method_exists($property->getClassName(), 'dao')) {
			if (
				method_exists($property->getClassName(), 'proto')
				&& count($path) > 1
			) {
				return
					$this->processPath(
						$property->getProto(),
						implode('.', $path),
						$query,
						$table
					);
			} else {
				return
					$this->guessAtom(
						implode('.', $path),
						$query,
						$table,
						$prefix
					);
			}
		} else {
			$propertyDao = call_user_func(
				array($property->getClassName(), 'dao')
			);

			Assert::isNotNull(
				$propertyDao,
				'can not find target dao for "'.$property->getName()
				.'" property at "'.get_class($proto).'"'
			);
		}

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
			$getter = $property->getGetter();
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

		if ($path) {
			return $propertyDao->guessAtom(
				implode('.', $path),
				$query,
				$alias,
				$property->isRequired() && $parentRequired,
				$propertyDao->getJoinPrefix($property->getColumnName(), $prefix)
			);
		}

		// ok, we're done
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
			|| ($atom instanceof DialectString)
		) {
			return $atom;
		}

		return new DBValue($atom);
	}
}
?>
