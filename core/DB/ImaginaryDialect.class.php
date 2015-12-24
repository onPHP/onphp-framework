<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Inexistent imaginary helper for OSQL's Query self-identification.
 *
 * @ingroup DB
 * @ingroup Module
 **/
class ImaginaryDialect extends Dialect
{
    private static $self = null;

    /**
     * @return ImaginaryDialect
     **/
    public static function me()
    {
        if (!self::$self) {
            self::$self = new self();
        }
        return self::$self;
    }

    /**
     * @param DBColumn $column
     * @return null
     */
    public function preAutoincrement(DBColumn $column)
    {
        return null;
    }

    /**
     * @param DBColumn $column
     * @return string
     */
    public function postAutoincrement(DBColumn $column)
    {
        return 'AUTOINCREMENT';
    }

    /**
     * @param $value
     * @return mixed
     */
    public function quoteValue($value)
    {
        return $value;
    }

    /**
     * @param $field
     * @return mixed
     */
    public function quoteField($field)
    {
        return $field;
    }

    /**
     * @param $table
     * @return mixed
     */
    public function quoteTable($table)
    {
        return $table;
    }

    /**
     * @return bool
     */
    public function hasTruncate()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasMultipleTruncate()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasReturning()
    {
        return false;
    }

    /**
     * @param $field
     * @return mixed
     */
    public function fieldToString($field)
    {
        return
            $field instanceof DialectString
                ? $field->toDialectString($this)
                : $field;
    }

    /**
     * @param $value
     * @return mixed|string
     */
    public function valueToString($value)
    {
        return
            $value instanceof DBValue
                ? $value->toDialectString($this)
                : $value;
    }

    /**
     * @param $field
     * @param $words
     * @param $logic
     * @return string
     */
    public function fullTextSearch($field, $words, $logic)
    {
        return
            '("'
            . $this->fieldToString($field)
            . '" CONTAINS "'
            . implode($logic, $words)
            . '")';
    }

    /**
     * @param $field
     * @param $words
     * @param $logic
     * @return string
     */
    public function fullTextRank($field, $words, $logic)
    {
        return
            '(RANK BY "' . $this->fieldToString($field) . '" WHICH CONTAINS "'
            . implode($logic, $words)
            . '")';
    }

    /**
     * @param $range
     * @param $ip
     * @return string
     */
    public function quoteIpInRange($range, $ip)
    {
        $string = '';

        if ($ip instanceof DialectString)
            $string .= $ip->toDialectString($this);
        else
            $string .= $this->quoteValue($ip);

        $string .= ' in (';

        if ($range instanceof DialectString)
            $string .= $range->toDialectString($this);
        else
            $string .= $this->quoteValue($range);

        $string .= ')';

        return $string;
    }
}
