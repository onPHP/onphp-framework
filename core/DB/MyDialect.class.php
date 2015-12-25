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
 * MySQL dialect.
 *
 * @see http://www.mysql.com/
 * @see http://www.php.net/mysql
 *
 * @ingroup DB
 **/
class MyDialect extends Dialect
{
    const IN_BOOLEAN_MODE = 1;

    /**
     * @param bool $cascade
     * @return null
     */
    public static function dropTableMode($cascade = false)
    {
        return null;
    }

    /**
     * @param bool $exist
     * @return null
     */
    public static function timeZone($exist = false)
    {
        return null;
    }

    /**
     * @param $field
     * @return string
     * @throws WrongArgumentException
     */
    public function quoteField($field)
    {
        if (strpos($field, '.') !== false) {
            throw new WrongArgumentException();
        } elseif (strpos($field, '::') !== false) {
            throw new WrongArgumentException();
        }

        return "`{$field}`";
    }

    /**
     * @param $table
     * @return string
     */
    public function quoteTable($table)
    {
        return "`{$table}`";
    }

    /**
     * @param $data
     * @return string
     */
    public function quoteBinary($data)
    {
        return "'" . mysql_real_escape_string($data) . "'";
    }

    /**
     * @param DataType $type
     * @return null|string
     */
    public function typeToString(DataType $type)
    {
        if ($type->getId() == DataType::BINARY) {
            return 'BLOB';
        }

        return parent::typeToString($type);
    }

    /**
     * @return bool
     */
    public function hasTruncate()
    {
        return true;
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
     * @param DBColumn $column
     * @return null
     */
    public function preAutoincrement(DBColumn $column)
    {
        $column->setDefault(null);

        return null;
    }

    /**
     * @param DBColumn $column
     * @return string
     */
    public function postAutoincrement(DBColumn $column)
    {
        return 'AUTO_INCREMENT';
    }

    /**
     * @param $fields
     * @param $words
     * @param $logic
     * @return string
     */
    public function fullTextSearch($fields, $words, $logic)
    {
        return
            ' MATCH ('
            . implode(
                ', ',
                array_map(
                    [$this, 'fieldToString'],
                    $fields
                )
            )
            . ') AGAINST ('
            . self::prepareFullText($words, $logic)
            . ')';
    }

    /**
     * @param $words
     * @param $logic
     * @return string
     * @throws WrongArgumentException
     */
    private static function prepareFullText($words, $logic)
    {
        Assert::isArray($words);

        $retval = self::quoteValue(implode(' ', $words));

        if (self::IN_BOOLEAN_MODE === $logic) {
            return addcslashes($retval, '+-<>()~*"') . ' ' . 'IN BOOLEAN MODE';
        } else {
            return $retval;
        }
    }

    /**
     * @param $value
     * @return string
     * @throws WrongStateException
     */
    public function quoteValue($value)
    {
        /// @see Sequenceless for this convention

        if ($value instanceof Identifier && !$value->isFinalized()) {
            return "''";
        } // instead of 'null', to be compatible with v. 4

        return "'" . mysql_real_escape_string($value, $this->getLink()) . "'";
    }
}

