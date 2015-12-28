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
 * @ingroup Module
 **/
class Joiner implements DialectString
{
    /** @var array  */
    private $from = [];
    /** @var array  */
    private $tables = [];

    /**
     * @param FromTable $from
     * @return Joiner
     */
    public function from(FromTable $from)
    {
        $this->from[] = $from;

        return $this;
    }

    /**
     * @param $table
     * @return bool
     */
    public function hasJoinedTable($table) : bool
    {
        return isset($this->tables[$table]);
    }

    /**
     * @return int
     */
    public function getTablesCount() : int
    {
        return count($this->from);
    }

    /**
     * @param SQLJoin $join
     * @return Joiner
     */
    public function join(SQLJoin $join) : Joiner
    {
        $this->from[] = $join;
        $this->tables[$join->getTable()] = true;

        return $this;
    }

    /**
     * @param SQLLeftJoin $join
     * @return Joiner
     */
    public function leftJoin(SQLLeftJoin $join) : Joiner
    {
        $this->from[] = $join;
        $this->tables[$join->getTable()] = true;

        return $this;
    }

    /**
     * @param SQLRightJoin $join
     * @return Joiner
     */
    public function rightJoin(SQLRightJoin $join) : Joiner
    {
        $this->from[] = $join;
        $this->tables[$join->getTable()] = true;

        return $this;
    }

    /**
     * @param SQLFullOuterJoin $join
     * @return Joiner
     */
    public function fullOuterJoin(SQLFullOuterJoin $join)
    {
        $this->from[] = $join;
        $this->tables[$join->getTable()] = true;

        return $this;
    }

    /**
     * @return null
     */
    public function getFirstTable()
    {
        if ($this->from) {
            return $this->from[0]->getTable();
        }

        return null;
    }

    /**
     * @return null
     */
    public function getLastTable()
    {
        if ($this->from) {
            return $this->from[count($this->from) - 1]->getTable();
        }

        return null;
    }

    /**
     * @param Dialect $dialect
     * @return null|string
     */
    public function toDialectString(Dialect $dialect)
    {
        $fromString = null;

        for ($i = 0, $size = count($this->from); $i < $size; ++$i) {
            if ($i == 0) {
                $separator = null;
            } elseif (
                $this->from[$i] instanceof FromTable &&
                !$this->from[$i]->getTable() instanceof SelectQuery
            ) {
                $separator = ', ';
            } else {
                $separator = ' ';
            }

            $fromString .=
                $separator
                . $this->from[$i]->toDialectString($dialect);
        }

        if ($fromString) {
            return ' FROM ' . $fromString;
        }

        return null;
    }
}
