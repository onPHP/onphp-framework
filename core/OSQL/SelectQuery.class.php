<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @ingroup OSQL
 **/
final class SelectQuery extends QuerySkeleton implements Named, JoinCapableQuery, Aliased
{
    /** @var bool */
    private $distinct = false;

    /** @var null */
    private $name = null;

    /** @var Joiner|null */
    private $joiner = null;

    /** @var null */
    private $limit = null;
    /** @var null */
    private $offset = null;

    /** @var array */
    private $fields = [];

    /** @var null|OrderChain */
    private $order = null;

    /** @var array */
    private $group = [];

    /** @var null */
    private $having = null;

    /**
     * SelectQuery constructor.
     */
    public function __construct()
    {
        $this->joiner = new Joiner();
        $this->order = new OrderChain();
    }

    /**
     * @see __clone
     */
    public function __clone()
    {
        $this->joiner = clone $this->joiner;
        $this->order = clone $this->order;
    }

    /**
     * @param $alias
     * @return bool
     */
    public function hasAliasInside($alias) : bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * @return null
     */
    public function getAlias()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return SelectQuery
     */
    public function setName($name) : SelectQuery
    {
        $this->name = $name;
        $this->aliases[$name] = true;

        return $this;
    }

    /**
     * @return SelectQuery
     */
    public function distinct() : SelectQuery
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDistinct() : bool
    {
        return $this->distinct;
    }

    /**
     * @return SelectQuery
     */
    public function unDistinct() : SelectQuery
    {
        $this->distinct = false;
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
     * @return SelectQuery
     */
    public function join($table, LogicalObject $logic, $alias = null) : SelectQuery
    {
        $this->joiner->join(new SQLJoin($table, $logic, $alias));
        $this->aliases[$alias] = true;

        return $this;
    }

    /**
     * @param $table
     * @param LogicalObject $logic
     * @param null $alias
     * @return SelectQuery
     */
    public function leftJoin($table, LogicalObject $logic, $alias = null) : SelectQuery
    {
        $this->joiner->leftJoin(new SQLLeftJoin($table, $logic, $alias));
        $this->aliases[$alias] = true;

        return $this;
    }

    /**
     * @param $table
     * @param LogicalObject $logic
     * @param null $alias
     * @return SelectQuery
     */
    public function rightJoin($table, LogicalObject $logic, $alias = null) : SelectQuery
    {
        $this->joiner->rightJoin(new SQLRightJoin($table, $logic, $alias));
        $this->aliases[$alias] = true;

        return $this;
    }

    /**
     * @param $table
     * @param LogicalObject $logic
     * @param null $alias
     * @return SelectQuery
     */
    public function fullOuterJoin($table, LogicalObject $logic, $alias = null)
    {
        $this->joiner->fullOuterJoin(
            new SQLFullOuterJoin($table, $logic, $alias)
        );

        $this->aliases[$alias] = true;

        return $this;
    }

    /**
     * @param OrderChain $chain
     * @return SelectQuery
     */
    public function setOrderChain(OrderChain $chain) : SelectQuery
    {
        $this->order = $chain;

        return $this;
    }

    /**
     * @param $field
     * @param null $table
     * @return SelectQuery
     */
    public function orderBy($field, $table = null) : SelectQuery
    {
        $this->order->add($this->makeOrder($field, $table));

        return $this;
    }

    /**
     * @param $field
     * @param null $table
     * @return OrderBy
     */
    private function makeOrder($field, $table = null) : OrderBy
    {
        if (
            $field instanceof OrderBy
            || $field instanceof DialectString
        ) {
            return $field;
        } else {
            return
                new OrderBy(
                    new DBField($field, $this->getLastTable($table))
                );
        }
    }

    /**
     * @param null $table
     * @return null
     */
    private function getLastTable($table = null)
    {
        if (!$table && ($last = $this->joiner->getLastTable())) {
            return $last;
        }

        return $table;
    }

    /**
     * @param $field
     * @param null $table
     * @return $this
     */
    public function prependOrderBy($field, $table = null)
    {
        $this->order->prepend($this->makeOrder($field, $table));

        return $this;
    }

    /**
     * @return SelectQuery
     * @throws WrongStateException
     */
    public function desc()
    {
        if (!$last = $this->order->getLast()) {
            throw new WrongStateException('no fields to sort');
        }

        $last->desc();

        return $this;
    }

    /**
     * @return SelectQuery
     * @throws WrongStateException
     */
    public function asc() : SelectQuery
    {
        if (!$last = $this->order->getLast()) {
            throw new WrongStateException('no fields to sort');
        }

        $last->asc();

        return $this;
    }

    /**
     * @param $field
     * @param null $table
     * @return SelectQuery
     */
    public function groupBy($field, $table = null) : SelectQuery
    {
        if ($field instanceof DialectString) {
            $this->group[] = $field;
        } else {
            $this->group[] =
                new DBField($field, $this->getLastTable($table));
        }

        return $this;
    }

    /**
     * @return SelectQuery
     */
    public function dropGroupBy() : SelectQuery
    {
        $this->group = [];
        return $this;
    }

    /**
     * @return SelectQuery
     **/
    public function having(LogicalObject $exp) : SelectQuery
    {
        $this->having = $exp;

        return $this;
    }

    /**
     * @return null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return SelectQuery
     * @throws WrongArgumentException
     */
    public function limit($limit = null, $offset = null)
    {
        if ($limit !== null) {
            Assert::isPositiveInteger($limit, 'invalid limit specified');
        }

        if ($offset !== null) {
            Assert::isInteger($offset, 'invalid offset specified');
        }

        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param $table
     * @param null $alias
     * @return SelectQuery
     */
    public function from($table, $alias = null) : SelectQuery
    {
        $this->joiner->from(new FromTable($table, $alias));

        $this->aliases[$alias] = true;

        return $this;
    }

    /**
     * @return null
     */
    public function getFirstTable()
    {
        return $this->joiner->getFirstTable();
    }

    /**
     * @param array ...$args
     * @return SelectQuery
     */
    public function multiGet(...$args) : SelectQuery
    {
        $size = count($args);

        if ($size) {
            for ($i = 0; $i < $size; ++$i) {
                $this->get($args[$i]);
            }
        }

        return $this;
    }

    /**
     * @param $field
     * @param null $alias
     * @return SelectQuery
     * @throws WrongArgumentException
     */
    public function get($field, $alias = null) : SelectQuery
    {
        $this->fields[] =
            $this->resolveSelectField(
                $field,
                $alias,
                $this->getLastTable()
            );

        if ($alias = $this->resolveAliasByField($field, $alias)) {
            $this->aliases[$alias] = true;
        }

        return $this;
    }

    /**
     * @param $array
     * @param null $prefix
     * @return SelectQuery
     */
    public function arrayGet($array, $prefix = null) : SelectQuery
    {
        $size = count($array);

        if ($prefix) {
            for ($i = 0; $i < $size; ++$i) {
                if ($array[$i] instanceof DialectString) {
                    if ($array[$i] instanceof DBField) {
                        $alias = $prefix . $array[$i]->getField();
                    } else {
                        if ($array[$i] instanceof SQLFunction) {
                            $alias =
                                $array[$i]
                                    ->setAlias(
                                        $prefix . $array[$i]->getName()
                                    )
                                    ->getAlias();
                        } else {
                            $alias = $array[$i];
                        }
                    }
                } else {
                    $alias = $prefix . $array[$i];
                }

                $this->get($array[$i], $alias);
            }
        } else {
            for ($i = 0; $i < $size; ++$i) {
                $this->get($array[$i]);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getFieldsCount() : int
    {
        return count($this->fields);
    }

    /**
     * @return int
     */
    public function getTablesCount() : int
    {
        return $this->joiner->getTablesCount();
    }

    /**
     * @return array
     */
    public function getFieldNames() : array
    {
        $nameList = [];

        foreach ($this->fields as $field) {
            if ($field instanceof SelectField) {
                if ($alias = $field->getAlias()) {
                    $nameList[] = $alias;
                    continue;
                } elseif (($subField = $field->getField()) instanceof Aliased) {
                    if ($alias = $subField->getAlias()) {
                        $nameList[] = $alias;
                        continue;
                    }
                }
            }

            $nameList[] = $field->getName();
        }

        return $nameList;
    }

    /**
     * @param $field
     * @param null $alias
     * @return QuerySkeleton|void
     * @throws UnsupportedMethodException
     */
    public function returning($field, $alias = null)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $fieldList = [];

        foreach ($this->fields as $field) {
            $fieldList[] = $this->toDialectStringField($field, $dialect);
        }

        $query =
            'SELECT ' . ($this->distinct ? 'DISTINCT ' : null)
            . implode(', ', $fieldList)
            . $this->joiner->toDialectString($dialect);

        // WHERE
        $query .= parent::toDialectString($dialect);

        if ($this->group) {
            $groupList = [];

            foreach ($this->group as $group) {
                $groupList[] = $group->toDialectString($dialect);
            }

            if ($groupList) {
                $query .= ' GROUP BY ' . implode(', ', $groupList);
            }
        }

        if ($this->having) {
            $query .= ' HAVING ' . $this->having->toDialectString($dialect);
        }

        if ($this->order->getCount()) {
            $query .= ' ORDER BY ' . $this->order->toDialectString($dialect);
        }

        if ($this->limit) {
            $query .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset) {
            $query .= ' OFFSET ' . $this->offset;
        }

        return $query;
    }

    /**
     * @return SelectQuery
     */
    public function dropFields() : SelectQuery
    {
        $this->fields = [];
        return $this;
    }

    /**
     * @return SelectQuery
     **/
    public function dropOrder()
    {
        $this->order = new OrderChain();
        return $this;
    }

    /**
     * @return SelectQuery
     **/
    public function dropLimit()
    {
        $this->limit = $this->offset = null;
        return $this;
    }
}