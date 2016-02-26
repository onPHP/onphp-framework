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
 * Values row implementation.
 *
 * @ingroup OSQL
 **/
class SQLArray implements DialectString
{
    /** @var array  */
    private $array = [];

    /**
     * SQLArray constructor.
     * @param $array
     */
    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * @return array
     */
    public function getArray() : array
    {
        return $this->array;
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        $array = $this->array;

        if (is_array($array)) {
            $quoted = [];

            foreach ($array as $item) {
                if ($item instanceof DialectString) {
                    $quoted[] = $item->toDialectString($dialect);
                } else {
                    $quoted[] = $dialect->valueToString($item);
                }
            }

            $value = implode(', ', $quoted);
        } else {
            $value = $dialect->quoteValue($array);
        }

        return "({$value})";
    }
}