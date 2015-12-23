<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Garmonbozia Research Group                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Ideal Identifiable interface implementation. ;-)
 *
 * @see Identifiable
 *
 * @ingroup Base
 * @ingroup Module
 **/
class /* spirit of */
IdentifiableObject implements Identifiable, DialectString
{
    /**
     * @var null
     */
    protected $id = null;

    /**
     * @param $id
     * @return IdentifiableObject
     */
    public static function wrap($id)
    {
        $io = new self;

        return $io->setId($id);
    }

    /**
     * @param Dialect $dialect
     * @return mixed
     */
    public function toDialectString(Dialect $dialect)
    {
        return $dialect->quoteValue($this->getId());
    }

    /**
     * @return Identifier|null
     */
    public function getId()
    {
        if (
            $this->id instanceof Identifier
            && $this->id->isFinalized()
        ) {
            return $this->id->getId();
        } else {
            return $this->id;
        }
    }

    /**
     * @return IdentifiableObject
     **/
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}

