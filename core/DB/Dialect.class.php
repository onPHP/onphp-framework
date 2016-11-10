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
 * Base (aka ANSI) SQL dialect.
 *
 * @ingroup DB
 * @ingroup Module
 **/
abstract class /* ANSI's */
Dialect
{
    const LITERAL_NULL = 'NULL';
    const LITERAL_TRUE = 'TRUE';
    const LITERAL_FALSE = 'FALSE';

    /**
     * @var DB
     */
    protected $db = null;

    /**
     * @deprecated
     * @throws UnimplementedFeatureException
     */
    public static function me()
    {
        throw new UnimplementedFeatureException('Deprecated: dialects not extends Singleton now');
    }

    /**
     * @param $field
     * @param $type
     * @return string
     */
    public static function toCasted($field, $type)
    {
        return "CAST ({$field} AS {$type})";
    }

    /**
     * @param bool $exist
     * @return string
     */
    public static function timeZone($exist = false)
    {
        return
            $exist
                ? ' WITH TIME ZONE'
                : ' WITHOUT TIME ZONE';
    }

    /**
     * @param bool $cascade
     * @return string
     */
    public static function dropTableMode($cascade = false)
    {
        return
            $cascade
                ? ' CASCADE'
                : ' RESTRICT';
    }

    /**
     * @param DBColumn $column
     * @return mixed
     */
    abstract public function preAutoincrement(DBColumn $column);

    /**
     * @param DBColumn $column
     * @return mixed
     */
    abstract public function postAutoincrement(DBColumn $column);

    /**
     * @return mixed
     */
    abstract public function hasTruncate();

    /**
     * @return mixed
     */
    abstract public function hasMultipleTruncate();

    /**
     * @return mixed
     */
    abstract public function hasReturning();

    /**
     * @param DB $db
     * @return Dialect
     */
    public function setDB(DB $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function quoteBinary($data)
    {
        return $this->quoteValue($data);
    }

    /**
     * @param $value
     * @return mixed
     */
    abstract public function quoteValue($value);

    /**
     * @param $data
     * @return mixed
     */
    public function unquoteBinary($data)
    {
        return $data;
    }

    /**
     * @param DataType $type
     * @return null|string
     */
    public function typeToString(DataType $type)
    {
        if ($type->getId() == DataType::IP) {
            return 'varchar(19)';
        }

        if ($type->getId() == DataType::IP_RANGE) {
            return 'varchar(41)';
        }

        return $type->getName();
    }

    /**
     * @param $expression
     * @return null|string
     * @throws WrongArgumentException
     */
    public function toFieldString($expression)
    {
        return $this->toNeededString($expression, 'quoteField');
    }

    /**
     * @param $expression
     * @param $method
     * @return null|string
     * @throws WrongArgumentException
     */
    private function toNeededString($expression, $method)
    {
        if (null === $expression) {
            throw new WrongArgumentException(
                'not null expression expected'
            );
        }

        $string = null;

        if ($expression instanceof DialectString) {
            if ($expression instanceof Query) {
                $string .= '(' . $expression->toDialectString($this) . ')';
            } else {
                $string .= $expression->toDialectString($this);
            }
        } else {
            $string .= $this->$method($expression);
        }

        return $string;
    }

    /**
     * @param $expression
     * @return null|string
     * @throws WrongArgumentException
     */
    public function toValueString($expression)
    {
        return $this->toNeededString($expression, 'quoteValue');
    }

    /**
     * @param $field
     * @return string
     */
    public function fieldToString($field)
    {
        return
            $field instanceof DialectString
                ? $field->toDialectString($this)
                : $this->quoteField($field);
    }

    /**
     * @param $field
     * @return string
     */
    public function quoteField($field)
    {
        return $this->quoteTable($field);
    }

    /**
     * @param $table
     * @return string
     */
    public function quoteTable($table)
    {
        return implode(
            '.',
            array_map(
                function ($tablePart) {
                    return '"' . $tablePart . '"';
                },
                explode('.', $table)
            )
        );
    }

    public function quoteSchema($schema)
    {
        return '"'.$schema.'""';
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
                : $this->quoteValue($value);
    }

    /**
     * @param $logic
     * @return mixed
     */
    public function logicToString($logic)
    {
        return $logic;
    }

    /**
     * @param $literal
     * @return mixed
     */
    public function literalToString($literal)
    {
        return $literal;
    }

    /**
     * @param $field
     * @param $words
     * @param $logic
     * @throws UnimplementedFeatureException
     */
    public function fullTextSearch($field, $words, $logic)
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @param $field
     * @param $words
     * @param $logic
     * @throws UnimplementedFeatureException
     */
    public function fullTextRank($field, $words, $logic)
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @param $range
     * @param $ip
     * @throws UnimplementedFeatureException
     */
    public function quoteIpInRange($range, $ip)
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @return null
     * @throws WrongStateException
     */
    protected function getLink()
    {
        if (!$this->db) {
            throw new WrongStateException('Expected setted db');
        }
        if (!$this->db->isConnected()) {
            $this->db->connect();
        }

        return $this->db->getLink();
    }
}