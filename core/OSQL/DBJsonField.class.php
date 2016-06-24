<?php
/***************************************************************************
 *   Copyright (C) 2015 by Anton Gurov                                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Reference for actual DB-table Json field.
 *
 * @ingroup OSQL
 * @ingroup Module
 **/
class DBJsonField extends DBField
{
    /**
     * @var null|string
     */
    private $key = null;

    /**
     * DBJsonField constructor.
     * @param $field
     * @param null $table
     * @param null $key
     */
    function __construct($field, $table = null, $key = null)
    {
        if (!is_null($key)) $this->setKey($key);
        parent::__construct($field, $table);

    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect)
    {
        $field =
            (
            $this->getTable()
                ? $this->getTable()->toDialectString($dialect) . '.'
                : null
            )
            . $dialect->quoteField($this->getField());
        if ($this->key) {
            $field .= '->>\'' . $this->key . '\'';
        }
        $field = '(' . $field . ')';
        return
            $this->cast
                ? $dialect->toCasted($field, $this->cast)
                : $field;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
}