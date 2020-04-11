<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\OSQL;

use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Base\Identifiable;
use OnPHP\Main\Base\Range;
use OnPHP\Main\Base\DateRange;
use OnPHP\Core\Base\Time;
use OnPHP\Core\Base\Stringable;
use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\DB\Dialect;

/**
 * Single roof for InsertQuery and UpdateQuery.
 *
 * @ingroup OSQL
**/
abstract class InsertOrUpdateQuery
	extends QuerySkeleton
	implements SQLTableName
{
	protected $table	= null;
	protected $fields	= array();

	abstract public function setTable($table);

	public function getTable()
	{
		return $this->table;
	}

	public function getFieldsCount()
	{
		return count($this->fields);
	}

	/**
	 * @return InsertOrUpdateQuery
	**/
	public function set($field, $value = null)
	{
		$this->fields[$field] = $value;

		return $this;
	}

	/**
	 * @throws MissingElementException
	 * @return InsertOrUpdateQuery
	**/
	public function drop($field)
	{
		if (!array_key_exists($field, $this->fields))
			throw new MissingElementException("unknown field '{$field}'");

		unset($this->fields[$field]);

		return $this;
	}

	/**
	 * @return InsertOrUpdateQuery
	**/
	public function lazySet($field, /* Identifiable */ $object = null)
	{
		if ($object === null)
			$this->set($field, null);
		elseif ($object instanceof Identifiable)
			$this->set($field, $object->getId());
		elseif ($object instanceof Range)
			$this->
				set($field.'_min', $object->getMin())->
				set($field.'_max', $object->getMax());
		elseif ($object instanceof DateRange)
			$this->
				set($field.'_start', $object->getStart())->
				set($field.'_end', $object->getEnd());
		elseif ($object instanceof Time)
			$this->set($field, $object->toFullString());
		elseif ($object instanceof Stringable)
			$this->set($field, $object->toString());
		else
			$this->set($field, $object);

		return $this;
	}

	/**
	 * @return InsertOrUpdateQuery
	**/
	public function setBoolean($field, $value = false)
	{
		try {
			Assert::isTernaryBase($value);
			$this->set($field, $value);
		} catch (WrongArgumentException $e) {/*_*/}

		return $this;
	}

	/**
	 * Adds values from associative array.
	 *
	 * @return InsertOrUpdateQuery
	**/
	public function arraySet($fields)
	{
		Assert::isArray($fields);

		$this->fields = array_merge($this->fields, $fields);

		return $this;
	}

	public function toDialectString(Dialect $dialect)
	{
		$this->checkReturning($dialect);

		if (empty($this->returning))
			return parent::toDialectString($dialect);

		$query =
			parent::toDialectString($dialect)
			.' RETURNING '
			.$this->toDialectStringReturning($dialect);

		return $query;
	}
}
?>
