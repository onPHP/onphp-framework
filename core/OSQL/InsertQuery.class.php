<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
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
final class InsertQuery extends InsertOrUpdateQuery
{
    /**
     * @var SelectQuery
     **/
    protected $select = null;

    /**
     * Just an alias to behave like UpdateQuery.
     *
     * @param $table
     * @return InsertQuery
     */
    public function setTable($table) : InsertQuery
    {
        return $this->into($table);
    }

    /**
     * @param $table
     * @return InsertQuery
     */
    public function into($table) : InsertQuery
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param SelectQuery $select
     * @return InsertQuery
     */
    public function setSelect(SelectQuery $select) : InsertQuery
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     * @throws WrongStateException
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $query = 'INSERT INTO ' . $dialect->quoteTable($this->table) . ' ';

        if ($this->select === null) {
            $query = $this->toDialectStringValues($query, $dialect);
        } else {
            $query = $this->toDialectStringSelect($query, $dialect);
        }

        $query .= parent::toDialectString($dialect);

        return $query;
    }

    /**
     * @param $query
     * @param Dialect $dialect
     * @return string
     * @throws WrongStateException
     */
    protected function toDialectStringValues($query, Dialect $dialect) : string
    {
        $fields = [];
        $values = [];

        foreach ($this->fields as $var => $val) {
            $fields[] = $dialect->quoteField($var);

            if ($val === null) {
                $values[] = $dialect->literalToString(Dialect::LITERAL_NULL);
            } elseif (true === $val) {
                $values[] = $dialect->literalToString(Dialect::LITERAL_TRUE);
            } elseif (false === $val) {
                $values[] = $dialect->literalToString(Dialect::LITERAL_FALSE);
            } elseif ($val instanceof DialectString) {
                $values[] = $val->toDialectString($dialect);
            } else {
                $values[] = $dialect->quoteValue($val);
            }
        }

        if (!$fields || !$values) {
            throw new WrongStateException('what should i insert?');
        }

        $fields = implode(', ', $fields);
        $values = implode(', ', $values);

        return $query . "({$fields}) VALUES ({$values})";
    }

    /**
     * @param $query
     * @param Dialect $dialect
     * @return string
     * @throws WrongStateException
     */
    protected function toDialectStringSelect($query, Dialect $dialect) : string
    {
        $fields = [];

        foreach ($this->fields as $var => $val) {
            $fields[] = $dialect->quoteField($var);
        }

        if (!$fields) {
            throw new WrongStateException('what should i insert?');
        }
        if ($this->select->getFieldsCount() != count($fields)) {
            throw new WrongStateException('count of select fields must be equal with count of insert fields');
        }

        $fields = implode(', ', $fields);

        return $query . "({$fields}) ("
        . $this->select->toDialectString($dialect) . ")";
    }

    /**
     * @param LogicalObject $exp
     * @param null $logic
     * @return QuerySkeleton|void
     * @throws UnsupportedMethodException
     */
    public function where(LogicalObject $exp, $logic = null)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param LogicalObject $exp
     * @return QuerySkeleton|void
     * @throws UnsupportedMethodException
     */
    public function andWhere(LogicalObject $exp)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param LogicalObject $exp
     * @return QuerySkeleton|void
     * @throws UnsupportedMethodException
     */
    public function orWhere(LogicalObject $exp)
    {
        throw new UnsupportedMethodException();
    }
}
