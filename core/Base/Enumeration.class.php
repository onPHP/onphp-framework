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
 * Parent of all enumeration classes.
 *
 * @see AccessMode for example
 *
 * @ingroup Base
 * @ingroup Module
 **/
abstract class Enumeration extends NamedObject implements Serializable
{
    /** @var array  */
    protected $names = [/* override me */];

    /**
     * Enumeration constructor.
     * @param $id
     */
    final public function __construct($id)
    {
        $this->setId($id);
    }

    /**
     * @param $id
     * @return $this
     * @throws MissingElementException
     */
    public function setId($id)
    {
        $names = $this->getNameList();

        if (isset($names[$id])) {
            $this->id = $id;
            $this->name = $names[$id];
        } else {
            throw new MissingElementException(
                get_class($this) . ' knows nothing about such id == ' . $id
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getNameList() : array
    {
        return $this->names;
    }

    /**
     * @param Enumeration $enum
     * @return array
     */
    public static function getList(Enumeration $enum) : array
    {
        return $enum->getObjectList();
    }

    /**
     * @return array
     */
    public function getObjectList() : array
    {
        $list = [];
        $names = $this->getNameList();

        foreach (array_keys($names) as $id) {
            $list[] = new $this($id);
        }

        return $list;
    }

    /**
     * must return any existent ID
     * 1 should be ok for most enumerations
     *
     * @return integer
     */
    public static function getAnyId() : int
    {
        return 1;
    }

    /**
     * @return string
     */
    public function serialize() : string
    {
        return (string) $this->id;
    }

    /**
     * @param string $serialized
     * @throws MissingElementException
     */
    public function unserialize($serialized)
    {
        $this->setId($serialized);
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return $this->name;
    }
}
