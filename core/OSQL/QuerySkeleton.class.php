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

/**
 * @ingroup OSQL
 **/
abstract class QuerySkeleton extends QueryIdentification
{
    /** @var array  */
    protected $where = [];    // where clauses
    /** @var array  */
    protected $whereLogic = [];    // logic between where's
    /** @var array  */
    protected $aliases = [];
    /** @var array  */
    protected $returning = [];

    /**
     * @return array
     */
    public function getWhere() : array
    {
        return $this->where;
    }

    /**
     * @return array
     */
    public function getWhereLogic() : array
    {
        return $this->whereLogic;
    }

    /**
     * @param LogicalObject $exp
     * @return QuerySkeleton
     * @throws WrongArgumentException
     */
    public function andWhere(LogicalObject $exp)
    {
        return $this->where($exp, 'AND');
    }


    /**
     * @param LogicalObject $exp
     * @param null $logic
     * @return QuerySkeleton
     * @throws WrongArgumentException
     */
    public function where(LogicalObject $exp, $logic = null) : QuerySkeleton
    {
        if ($this->where && !$logic) {
            throw new WrongArgumentException(
                'you have to specify expression logic'
            );
        } else {
            if (!$this->where && $logic) {
                $logic = null;
            }

            $this->whereLogic[] = $logic;
            $this->where[] = $exp;
        }

        return $this;
    }

    /**
     * @param LogicalObject $exp
     * @return QuerySkeleton
     * @throws WrongArgumentException
     */
    public function orWhere(LogicalObject $exp) : QuerySkeleton
    {
        return $this->where($exp, 'OR');
    }

    /**
     * @param $field
     * @param null $alias
     * @return QuerySkeleton
     * @throws WrongArgumentException
     */
    public function returning($field, $alias = null) : QuerySkeleton
    {
        $this->returning[] =
            $this->resolveSelectField(
                $field,
                $alias,
                $this->table
            );

        if ($alias = $this->resolveAliasByField($field, $alias)) {
            $this->aliases[$alias] = true;
        }

        return $this;
    }

    /**
     * @param $field
     * @param $alias
     * @param $table
     * @return DBField|SelectField|SelectQuery
     * @throws WrongArgumentException
     * @throws WrongStateException
     */
    protected function resolveSelectField($field, $alias, $table)
    {
        if (is_object($field)) {
            if (
                ($field instanceof DBField)
                && ($field->getTable() === null)
            ) {
                $result = new SelectField(
                    $field->setTable($table),
                    $alias
                );
            } elseif ($field instanceof SelectQuery) {
                $result = $field;
            } elseif ($field instanceof DialectString) {
                $result = new SelectField($field, $alias);
            } else {
                throw new WrongArgumentException('unknown field type');
            }

            return $result;
        } elseif (false !== strpos($field, '*')) {
            throw new WrongArgumentException(
                'do not fsck with us: specify fields explicitly'
            );
        } elseif (false !== strpos($field, '.')) {
            throw new WrongArgumentException(
                'forget about dot: use DBField'
            );
        } else {
            $fieldName = $field;
        }

        $result = new SelectField(
            new DBField($fieldName, $table), $alias
        );

        return $result;
    }

    /**
     * @param $field
     * @param $alias
     * @return null
     */
    protected function resolveAliasByField($field, $alias)
    {
        if (is_object($field)) {
            if (
                ($field instanceof DBField)
                && ($field->getTable() === null)
            ) {
                return null;
            }

            if (
                $field instanceof SelectQuery
                || ($field instanceof DialectString && $field instanceof Aliased)
            ) {
                return $field->getAlias();
            }
        }

        return $alias;
    }

    /**
     * @return QuerySkeleton
     */
    public function dropReturning() : QuerySkeleton
    {
        $this->returning = [];

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return null|string
     */
    public function toDialectString(Dialect $dialect)
    {
        if ($this->where) {
            $clause = ' WHERE';
            $outputLogic = false;

            for ($i = 0, $size = count($this->where); $i < $size; ++$i) {

                if ($exp = $this->where[$i]->toDialectString($dialect)) {

                    $clause .= "{$this->whereLogic[$i]} {$exp} ";
                    $outputLogic = true;

                } elseif (!$outputLogic && isset($this->whereLogic[$i + 1])) {
                    $this->whereLogic[$i + 1] = null;
                }

            }

            return rtrim($clause, ' ');
        }

        return null;
    }

    /**
     * @return QuerySkeleton
     */
    public function spawn()
    {
        return clone $this;
    }

    /**
     * @param Dialect $dialect
     * @return QuerySkeleton
     * @throws UnimplementedFeatureException
     */
    protected function checkReturning(Dialect $dialect) : QuerySkeleton
    {
        if (
            $this->returning
            && !$dialect->hasReturning()
        ) {
            throw new UnimplementedFeatureException();
        }

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    protected function toDialectStringReturning(Dialect $dialect) : string
    {
        $fields = [];

        foreach ($this->returning as $field) {
            $fields[] = $this->toDialectStringField($field, $dialect);
        }

        return implode(', ', $fields);
    }

    /**
     * @param $field
     * @param Dialect $dialect
     * @return string
     * @throws WrongArgumentException
     */
    protected function toDialectStringField($field, Dialect $dialect) : string
    {
        if ($field instanceof SelectQuery) {
            Assert::isTrue(
                null !== $alias = $field->getName(),
                'can not use SelectQuery to table without name as get field: '
                . $field->toDialectString(ImaginaryDialect::me())
            );

            return
                "({$field->toDialectString($dialect)}) AS " .
                $dialect->quoteField($alias);
        } else {
            return $field->toDialectString($dialect);
        }
    }
}