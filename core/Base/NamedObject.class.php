<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @see Named
 *
 * @ingroup Base
 * @ingroup Module
 **/
abstract class NamedObject extends IdentifiableObject implements Named, Stringable
{
    /**
     * @var null
     */
    protected $name = null;

    /**
     * @param Named $left
     * @param Named $right
     * @return integer
     */
    public static function compareNames(Named $left, Named $right) : int
    {
        return strcasecmp($left->getName(), $right->getName());
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return NamedObject
     **/
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return "[{$this->id}] {$this->name}";
    }
}
