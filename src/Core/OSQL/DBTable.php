<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\OSQL;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\DB\Dialect;

/**
 * @ingroup OSQL
**/
final class DBTable implements DialectString
{
	private $name		= null;

	private $columns	= array();
	private $order		= array();

	private $uniques	= array();

	/**
	 * @return DBTable
	**/
	public static function create($name)
	{
		return new self($name);
	}

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * @return DBTable
	**/
	public function addUniques(/* ... */)
	{
		Assert::isTrue(func_num_args() > 0);

		$uniques = array();

		foreach (func_get_args() as $name) {
			// check existence
			$this->getColumnByName($name);

			$uniques[] = $name;
		}

		$this->uniques[] = $uniques;

		return $this;
	}

	public function getUniques()
	{
		return $this->uniques;
	}

	/**
	 * @throws WrongArgumentException
	 * @return DBTable
	**/
	public function addColumn(DBColumn $column)
	{
		$name = $column->getName();

		Assert::isFalse(
			isset($this->columns[$name]),
			"column '{$name}' already exist"
		);

		$this->order[] = $this->columns[$name] = $column;

		$column->setTable($this);

		return $this;
	}

	/**
	 * @throws MissingElementException
	 * @return DBColumn
	**/
	public function getColumnByName($name)
	{
		if (!isset($this->columns[$name]))
			throw new MissingElementException(
				"column '{$name}' does not exist"
			);

		return $this->columns[$name];
	}

	/**
	 * @return DBTable
	**/
	public function dropColumnByName($name)
	{
		if (!isset($this->columns[$name]))
			throw new MissingElementException(
				"column '{$name}' does not exist"
			);

		unset($this->columns[$name]);
		unset($this->order[array_search($name, $this->order)]);

		return $this;
	}

	/**
	 * @return DBTable
	**/
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function toDialectString(Dialect $dialect)
	{
		return OSQL::createTable($this)->toDialectString($dialect);
	}

	// TODO: consider port to AlterTable class (unimplemented yet)
	public static function findDifferences(
		Dialect $dialect,
		DBTable $source,
		DBTable $target
	)
	{
		$out = array();

		$head = 'ALTER TABLE '.$dialect->quoteTable($target->getName());

		$sourceColumns = $source->getColumns();
		$targetColumns = $target->getColumns();

		foreach ($sourceColumns as $name => $column) {
			if (isset($targetColumns[$name])) {
				if (
					$column->getType()->getId()
					!= $targetColumns[$name]->getType()->getId()
				) {
					$targetColumn = $targetColumns[$name];

					$out[] =
						$head
						.' ALTER COLUMN '.$dialect->quoteField($name)
						.' TYPE '.$targetColumn->getType()->toString()
						.(
							$targetColumn->getType()->hasSize()
								?
									'('
									.$targetColumn->getType()->getSize()
									.(
										$targetColumn->getType()->hasPrecision()
											? ', '.$targetColumn->getType()->getPrecision()
											: null
									)
									.')'
								: null
						)
						.';';
				}

				if (
					$column->getType()->isNull()
					!= $targetColumns[$name]->getType()->isNull()
				) {
					$out[] =
						$head
						.' ALTER COLUMN '.$dialect->quoteField($name)
						.' '
						.(
							$targetColumns[$name]->getType()->isNull()
								? 'DROP'
								: 'SET'
						)
						.' NOT NULL;';
				}
			} else {
				$out[] =
					$head
					.' DROP COLUMN '.$dialect->quoteField($name).';';
			}
		}

		foreach ($targetColumns as $name => $column) {
			if (!isset($sourceColumns[$name])) {
				$out[] =
					$head
					.' ADD COLUMN '
					.$column->toDialectString($dialect).';';

				if ($column->hasReference()) {
					$out[] =
						'CREATE INDEX '.$dialect->quoteField($name.'_idx')
						.' ON '.$dialect->quoteTable($target->getName()).
						'('.$dialect->quoteField($name).');';
				}
			}
		}

		return $out;
	}
}
?>
