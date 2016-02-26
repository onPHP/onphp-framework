<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
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
class ExtractPart implements DialectString, MappableObject
{
    /** @var DatePart|null */
    private $what = null;
    /** @var DBField|DBValue|DialectString|null */
    private $from = null;

    /**
     * ExtractPart constructor.
     * @param DatePart $what
     * @param DialectString $from
     */
    public function __construct($what, $from)
    {
        if ($from instanceof DialectString) {
            Assert::isTrue(
                ($from instanceof DBValue)
                || ($from instanceof DBField)
            );
        } else {
            $from = new DBField($from);
        }

        if (!$what instanceof DatePart) {
            $what = new DatePart($what);
        }

        $this->what = $what;
        $this->from = $from;
    }

    /**
     * @param ProtoDAO $dao
     * @param JoinCapableQuery $query
     * @return ExtractPart
     */
    public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
    {
        return new self(
            $this->what,
            $dao->guessAtom($this->from, $query)
        );
    }

    /**
     * @param Dialect $dialect
     * @return string
     */
    public function toDialectString(Dialect $dialect) : string
    {
        return
            'EXTRACT('
            . $this->what->toString()
            . ' FROM '
            . $this->from->toDialectString($dialect)
            . ')';
    }
}
