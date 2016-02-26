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

/**
 * @ingroup OSQL
 **/
class DBTable implements DialectString
{
    /** @var null  */
    private $name = null;

    /** @var array  */
    private $columns = [];
    /** @var array  */
    private $order = [];

    /** @var array  */
    private $uniques = [];

    /**
     * DBTable constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * @param Dialect $dialect
     * @param DBTable $source
     * @param DBTable $target
     * @return array
     */
    public static function findDifferences(
        Dialect $dialect,
        DBTable $source,
        DBTable $target
    ) {
        $out = [];

        $head = 'ALTER TABLE ' . $dialect->quoteTable($target->getName());

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
                        . ' ALTER COLUMN ' . $dialect->quoteField($name)
                        . ' TYPE ' . $targetColumn->getType()->toString()
                        . (
                        $targetColumn->getType()->hasSize()
                            ?
                            '('
                            . $targetColumn->getType()->getSize()
                            . (
                            $targetColumn->getType()->hasPrecision()
                                ? ', ' . $targetColumn->getType()->getPrecision()
                                : null
                            )
                            . ')'
                            : null
                        )
                        . ';';
                }

                if (
                    $column->getType()->isNull()
                    != $targetColumns[$name]->getType()->isNull()
                ) {
                    $out[] =
                        $head
                        . ' ALTER COLUMN ' . $dialect->quoteField($name)
                        . ' '
                        . (
                        $targetColumns[$name]->getType()->isNull()
                            ? 'DROP'
                            : 'SET'
                        )
                        . ' NOT NULL;';
                }
            } else {
                $out[] =
                    $head
                    . ' DROP COLUMN ' . $dialect->quoteField($name) . ';';
            }
        }

        foreach ($targetColumns as $name => $column) {
            if (!isset($sourceColumns[$name])) {
                $out[] =
                    $head
                    . ' ADD COLUMN '
                    . $column->toDialectString($dialect) . ';';

                if ($column->hasReference()) {
                    $out[] =
                        'CREATE INDEX ' . $dialect->quoteField($name . '_idx')
                        . ' ON ' . $dialect->quoteTable($target->getName()) .
                        '(' . $dialect->quoteField($name) . ');';
                }
            }
        }

        return $out;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return DBTable
     **/
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array ...$args
     * @return DBTable
     * @throws MissingElementException
     * @throws WrongArgumentException
     */
    public function addUniques(...$args) : DBTable
    {
        Assert::isNotEmptyArray($args);

        $uniques = [];

        foreach ($args as $name) {
            // check existence
            $this->getColumnByName($name);

            $uniques[] = $name;
        }

        $this->uniques[] = $uniques;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     * @throws MissingElementException
     */
    public function getColumnByName($name)
    {
        if (!isset($this->columns[$name])) {
            throw new MissingElementException(
                "column '{$name}' does not exist"
            );
        }

        return $this->columns[$name];
    }

    /**
     * @return array
     */
    public function getUniques() : array
    {
        return $this->uniques;
    }

    /**
     * @param DBColumn $column
     * @return DBTable
     * @throws WrongArgumentException
     */
    public function addColumn(DBColumn $column) : DBTable
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
     * @param $name
     * @return DBTable
     * @throws MissingElementException
     */
    public function dropColumnByName($name) : DBTable
    {
        if (!isset($this->columns[$name])) {
            throw new MissingElementException(
                "column '{$name}' does not exist"
            );
        }

        unset($this->columns[$name]);
        unset($this->order[array_search($name, $this->order)]);

        return $this;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    // TODO: consider port to AlterTable class (unimplemented yet)

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect)
    {
        return (new CreateTableQuery($this))->toDialectString($dialect);
    }
}
