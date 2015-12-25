<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Anton E. Lebedevich                        *
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
final class UpdateQuery extends InsertOrUpdateQuery implements JoinCapableQuery
{
    /** @var Joiner|null  */
    private $joiner = null;

    /**
     * UpdateQuery constructor.
     * @param null $table
     */
    public function __construct($table = null)
    {
        $this->table = $table;
        $this->joiner = new Joiner();
    }

    /**
     * @see __clone
     */
    public function __clone()
    {
        $this->joiner = clone $this->joiner;
    }

    /**
     * @param $table
     * @param null $alias
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $this->joiner->from(new FromTable($table, $alias));

        return $this;
    }

    /**
     * @param $table
     * @return bool
     */
    public function hasJoinedTable($table) : bool
    {
        return $this->joiner->hasJoinedTable($table);
    }

    /**
     * @param $table
     * @param LogicalObject $logic
     * @param null $alias
     * @return UpdateQuery
     */
    public function join($table, LogicalObject $logic, $alias = null) : UpdateQuery
    {
        $this->joiner->join(new SQLJoin($table, $logic, $alias));
        return $this;
    }

    /**
     * @param $table
     * @param LogicalObject $logic
     * @param null $alias
     * @return UpdateQuery
     */
    public function leftJoin($table, LogicalObject $logic, $alias = null) : UpdateQuery
    {
        $this->joiner->leftJoin(new SQLLeftJoin($table, $logic, $alias));
        return $this;
    }

    /**
     * @param $table
     * @param LogicalObject $logic
     * @param null $alias
     * @return UpdateQuery
     */
    public function rightJoin($table, LogicalObject $logic, $alias = null) : UpdateQuery
    {
        $this->joiner->rightJoin(new SQLRightJoin($table, $logic, $alias));
        return $this;
    }

    /**
     * @param $table
     * @return UpdateQuery
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $query = 'UPDATE ' . $dialect->quoteTable($this->table) . ' SET ';

        $sets = [];

        foreach ($this->fields as $var => $val) {
            if ($val instanceof DialectString) {
                $sets[] =
                    $dialect->quoteField($var)
                    . ' = ('
                    . $val->toDialectString($dialect)
                    . ')';
            } elseif ($val === null) {
                $sets[] = $dialect->quoteField($var) . ' = '
                    . $dialect->literalToString(Dialect::LITERAL_NULL);
            } elseif (true === $val) {
                $sets[] = $dialect->quoteField($var) . ' = '
                    . $dialect->literalToString(Dialect::LITERAL_TRUE);
            } elseif (false === $val) {
                $sets[] = $dialect->quoteField($var) . ' = '
                    . $dialect->literalToString(Dialect::LITERAL_FALSE);
            } else {
                $sets[] =
                    $dialect->quoteField($var)
                    . ' = '
                    . $dialect->quoteValue($val);
            }
        }

        return
            $query
            . implode(', ', $sets)
            . $this->joiner->toDialectString($dialect)
            . parent::toDialectString($dialect);
    }
}
