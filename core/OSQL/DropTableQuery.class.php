<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OSQL
 * @ingroup Module
 **/
final class DropTableQuery extends QueryIdentification
{
    /** @var null  */
    private $name = null;

    /** @var bool  */
    private $cascade = false;

    /**
     * DropTableQuery constructor.
     * @param $name
     * @param bool $cascade
     */
    public function __construct($name, $cascade = false)
    {
        $this->name = $name;
        $this->cascade = (true === $cascade);
    }

    /**
     * @throws UnsupportedMethodException
     */
    public function getId()
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        return
            'DROP TABLE ' . $dialect->quoteTable($this->name)
            . $dialect->dropTableMode($this->cascade)
            . ';';
    }
}
