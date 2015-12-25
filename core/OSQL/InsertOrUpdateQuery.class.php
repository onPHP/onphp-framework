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
 * Single roof for InsertQuery and UpdateQuery.
 *
 * @ingroup OSQL
 **/
abstract class InsertOrUpdateQuery extends QuerySkeleton implements SQLTableName
{
    /** @var null */
    protected $table = null;
    /** @var array */
    protected $fields = [];

    /**
     * @return null
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param $table
     * @return mixed
     */
    abstract public function setTable($table);

    /**
     * @return int
     */
    public function getFieldsCount() : int
    {
        return count($this->fields);
    }

    /**
     * @throws MissingElementException
     * @return InsertOrUpdateQuery
     **/
    public function drop($field)
    {
        if (!array_key_exists($field, $this->fields)) {
            throw new MissingElementException("unknown field '{$field}'");
        }

        unset($this->fields[$field]);

        return $this;
    }

    /**
     * @param Identifiable $field
     * @param null $object
     * @return InsertOrUpdateQuery
     */
    public function lazySet($field, $object = null) : InsertOrUpdateQuery
    {
        if ($object === null) {
            $this->set($field, null);
        } elseif ($object instanceof Identifiable) {
            $this->set($field, $object->getId());
        } elseif ($object instanceof Range) {
            $this
                ->set($field . '_min', $object->getMin())
                ->set($field . '_max', $object->getMax());
        } elseif ($object instanceof DateRange) {
            $this
                ->set($field . '_start', $object->getStart())
                ->set($field . '_end', $object->getEnd());
        } elseif ($object instanceof Time) {
            $this->set($field, $object->toFullString());
        } elseif ($object instanceof Stringable) {
            $this->set($field, $object->toString());
        } else {
            $this->set($field, $object);
        }

        return $this;
    }

    /**
     * @param $field
     * @param null $value
     * @return InsertOrUpdateQuery
     */
    public function set($field, $value = null) : InsertOrUpdateQuery
    {
        $this->fields[$field] = $value;

        return $this;
    }

    /**
     * @param $field
     * @param bool $value
     * @return InsertOrUpdateQuery
     */
    public function setBoolean($field, $value = false) : InsertOrUpdateQuery
    {
        try {
            Assert::isTernaryBase($value);
            $this->set($field, $value);
        } catch (WrongArgumentException $e) {/*_*/
        }

        return $this;
    }

    /**
     * Adds values from associative array.
     *
     * @param $fields
     * @return InsertOrUpdateQuery
     * @throws WrongArgumentException
     */
    public function arraySet($fields) : InsertOrUpdateQuery
    {
        Assert::isArray($fields);

        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }

    /**
     * @param Dialect $dialect
     * @return null|string
     * @throws UnimplementedFeatureException
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $this->checkReturning($dialect);

        if (empty($this->returning)) {
            return parent::toDialectString($dialect);
        }

        $query =
            parent::toDialectString($dialect)
            . ' RETURNING '
            . $this->toDialectStringReturning($dialect);

        return $query;
    }
}
