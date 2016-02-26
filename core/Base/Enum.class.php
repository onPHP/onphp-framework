<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
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
 * @see MimeType for example
 *
 * @ingroup Base
 * @ingroup Module
 **/
abstract class Enum extends NamedObject
    implements
    Serializable
{
    /** @var array  */
    protected static $names = [/* override me */];

    /**
     * Enum constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->setInternalId($id);
    }

    /**
     * @param $id
     * @return Enum
     * @throws MissingElementException
     */
    protected function setInternalId($id)
    {
        if (isset(static::$names[$id])) {
            $this->id = $id;
            $this->name = static::$names[$id];
        } else {
            throw new MissingElementException(
                get_class($this) . ' knows nothing about such id == ' . $id
            );
        }

        return $this;
    }

    /**
     * must return any existent ID
     * 1 should be ok for most enumerations
     * @return integer
     **/
    public static function getAnyId()
    {
        return 1;
    }

    /**
     * Alias for getList()
     * @static
     * @deprecated
     * @return array
     */
    public static function getObjectList()
    {
        return static::getList();
    }

    /**
     * Array of object
     * @static
     * @return array
     */
    public static function getList() : array
    {
        $list = [];
        foreach (array_keys(static::$names) as $id) {
            $list[] = new static($id);
        }

        return $list;
    }

    /**
     * Plain list
     * @static
     * @return array
     */
    public static function getNameList() : array
    {
        return static::$names;
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
        $this->setInternalId($serialized);
    }

    /**
     * @return null|integer
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

    /**
     * @param $id
     * @return IdentifiableObject|void
     * @throws UnsupportedMethodException
     */
    public function setId($id)
    {
        throw new UnsupportedMethodException('You can not change id here, because it is politics for Enum!');
    }
}
