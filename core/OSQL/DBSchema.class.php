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
class DBSchema extends QueryIdentification
{
    /** @var array  */
    private $tables = [];
    /** @var array  */
    private $order = [];

    /**
     * @return array
     */
    public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @return array
     */
    public function getTableNames() : array
    {
        return $this->order;
    }

    /**
     * @param DBTable $table
     * @return DBSchema
     * @throws WrongArgumentException
     */
    public function addTable(DBTable $table) : DBSchema
    {
        $name = $table->getName();

        Assert::isFalse(
            isset($this->tables[$name]),
            "table '{$name}' already exist"
        );

        $this->tables[$table->getName()] = $table;
        $this->order[] = $name;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     * @throws MissingElementException
     */
    public function getTableByName($name)
    {
        if (!isset($this->tables[$name])) {
            throw new MissingElementException(
                "table '{$name}' does not exist"
            );
        }

        return $this->tables[$name];
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $out = [];

        foreach ($this->order as $name) {
            $out[] = $this->tables[$name]->toDialectString($dialect);
        }

        return implode("\n\n", $out);
    }
}
