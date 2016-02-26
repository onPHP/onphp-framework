<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * PostgreSQL dialect.
 *
 * @see http://www.postgresql.org/
 *
 * @ingroup DB
 **/
class PostgresDialect extends Dialect
{
    private static $tsConfiguration = 'russian';
    private static $rankFunction = 'rank';

    /**
     * @return string
     */
    public static function getTsConfiguration() : string
    {
        return self::$tsConfiguration;
    }

    /**
     * @param $configuration
     */
    public static function setTsConfiguration($configuration)
    {
        self::$tsConfiguration = $configuration;
    }

    /**
     * @param $rank
     */
    public static function setRankFunction($rank)
    {
        self::$rankFunction = $rank;
    }

    /**
     * @param $field
     * @param $type
     * @return string
     */
    public static function toCasted($field, $type)
    {
        return "{$field}::{$type}";
    }

    /**
     * @param $data
     * @return string
     * @throws WrongStateException
     */
    public function quoteBinary($data)
    {
        $esc = pg_escape_bytea($this->getLink(), $data);
        if (mb_strpos($esc, '\\x') === 0) {
            // http://www.postgresql.org/docs/9.1/static/datatype-binary.html
            // if pg_escape_bytea use postgres 9.1+ it's return value like '\x00aabb' (new bytea hex format),
            // but must return '\\x00aabb'. So we use this fix:'
            return "E'\\" . $esc . "'";
        } else {
            //if function escape value like '\\000\\123' - all ok
            return "E'" . $esc . "'";
        }
    }

    /**
     * @param $data
     * @return string
     */
    public function unquoteBinary($data)
    {
        return pg_unescape_bytea($data);
    }

    /**
     * @param DataType $type
     * @return null|string
     */
    public function typeToString(DataType $type)
    {
        if ($type->getId() == DataType::BINARY) {
            return 'BYTEA';
        }

        if (defined('POSTGRES_IP4_ENABLED')) {

            if ($type->getId() == DataType::IP) {
                return 'ip4';
            }

            if ($type->getId() == DataType::IP_RANGE) {
                return 'ip4r';
            }
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
        return true;
    }

    /**
     * @return bool
     */
    public function hasReturning()
    {
        return true;
    }

    /**
     * @param $field
     * @param $words
     * @param $logic
     * @return string
     */
    public function fullTextSearch($field, $words, $logic)
    {
        $searchString = $this->prepareFullText($words, $logic);
        $field = $this->fieldToString($field);

        return
            "({$field} @@ to_tsquery('" . self::$tsConfiguration . "', " .
            self::quoteValue($searchString) . "))";
    }

    /**
     * @param array $words
     * @param $logic
     * @return string
     */
    public function prepareFullText(array $words, $logic)
    {
        $glue = ($logic == DB::FULL_TEXT_AND) ? ' & ' : ' | ';

        return
            mb_strtolower(
                implode(
                    $glue,
                    array_map(
                        [$this, 'quoteValue'],
                        $words
                    )
                )
            );
    }

    /**
     * @param $value
     * @return string
     * @throws WrongStateException
     */
    public function quoteValue($value)
    {
        return "'" . pg_escape_string($this->getLink(), $value) . "'";
    }

    /**
     * @param $field
     * @param $words
     * @param $logic
     * @return string
     */
    public function fullTextRank($field, $words, $logic)
    {
        $searchString = $this->prepareFullText($words, $logic);
        $field = $this->fieldToString($field);

        return
            self::$rankFunction . "({$field}, to_tsquery('" . self::$tsConfiguration . "', " .
            self::quoteValue($searchString) . "))";
    }

    /**
     * @param DBColumn $column
     * @return string
     */
    public function preAutoincrement(DBColumn $column)
    {
        self::checkColumn($column);

        return
            'CREATE SEQUENCE "'
            . $this->makeSequenceName($column) . '";';
    }

    /**
     * @param DBColumn $column
     * @throws WrongArgumentException
     */
    private static function checkColumn(DBColumn $column)
    {
        Assert::isTrue(
            ($column->getTable() !== null)
            && ($column->getDefault() === null)
        );
    }

    /**
     * @param DBColumn $column
     * @return string
     */
    protected function makeSequenceName(DBColumn $column)
    {
        return $column->getTable()->getName() . '_' . $column->getName();
    }

    /**
     * @param DBColumn $column
     * @return string
     */
    public function postAutoincrement(DBColumn $column)
    {
        self::checkColumn($column);

        return
            'default nextval(\''
            . $this->makeSequenceName($column) . '\')';
    }

    /**
     * @param $range
     * @param $ip
     * @return string
     */
    public function quoteIpInRange($range, $ip)
    {
        $string = '';

        if ($ip instanceof DialectString) {
            $string .= $ip->toDialectString($this);
        } else {
            $string .= $this->quoteValue($ip);
        }

        $string .= ' <<= ';

        if ($range instanceof DialectString) {
            $string .= $range->toDialectString($this);
        } else {
            $string .= $this->quoteValue($range);
        }

        return $string;
    }
}