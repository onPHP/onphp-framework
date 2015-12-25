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
 **/
class ForeignChangeAction extends Enumeration
{
    const
        NO_ACTION = 0x01,
        RESTRICT = 0x02,
        CASCADE = 0x03,
        SET_NULL = 0x04,
        SET_DEFAULT = 0x05;

    protected $names = [
        self::NO_ACTION => 'NO ACTION', // default one
        self::RESTRICT => 'RESTRICT',
        self::CASCADE => 'CASCADE',
        self::SET_NULL => 'SET NULL',
        self::SET_DEFAULT => 'SET DEFAULT'
    ];

    /**
     * @return ForeignChangeAction
     **/
    public static function noAction()
    {
        return new self(self::NO_ACTION);
    }

    /**
     * @return ForeignChangeAction
     **/
    public static function restrict()
    {
        return new self(self::RESTRICT);
    }

    /**
     * @return ForeignChangeAction
     **/
    public static function cascade()
    {
        return new self(self::CASCADE);
    }

    /**
     * @return ForeignChangeAction
     **/
    public static function setNull()
    {
        return new self(self::SET_NULL);
    }

    /**
     * @return ForeignChangeAction
     **/
    public static function setDefault()
    {
        return new self(self::SET_DEFAULT);
    }
}
