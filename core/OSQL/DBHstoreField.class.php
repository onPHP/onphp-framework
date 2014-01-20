<?php
/***************************************************************************
 *   Copyright (C) 2013 by Alexey Solomonov                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Reference for actual DB-table hstore column.
 *
 * @ingroup OSQL
 * @ingroup Module
 **/
class DBHstoreField extends DBField {

    private $key = null;

    /**
     * @return DBHstoreField
     **/
    public static function create($field, $table = null, $key = null)
    {
        $self = new self($field, $table);

        if ($key)
            $self->setKey($key);

        return $self;
    }

    public function toDialectString(Dialect $dialect)
    {
        $field =
            (
            $this->getTable()
                ? $this->getTable()->toDialectString($dialect).'.'
                : null
            )
            .$dialect->quoteField($this->getField());

        if ($this->key) {
            $field .= '->\'' . $this->key . '\'';
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
    public function setKey($key) {
        $this->key = $key;
        return $this;
    }
} 