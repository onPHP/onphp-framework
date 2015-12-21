<?php

/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class DirectoryContext
{
    private $map = array();
    private $reverseMap = array();

    public function bind($name, $object)
    {
        if (!is_dir($name))
            throw new WrongArgumentException(
                'directory ' . $name . ' does not exists'
            );

        if (
            isset($this->map[$name])
            && $this->map[$name] !== $object
        )
            throw new WrongArgumentException('consider using rebind()');

        return $this->rebind($name, $object);
    }

    public function rebind($name, $object)
    {
        Assert::isNotNull($object);

        $this->map[$name] = $object;
        $this->reverseMap[spl_object_hash($object)] = $name;

        return $this;
    }

    public function lookup($name)
    {
        if (!isset($this->map[$name]))
            return null;

        return $this->map[$name];
    }

    public function reverseLookup($object)
    {
        if (!isset($this->reverseMap[spl_object_hash($object)]))
            return null;

        return $this->reverseMap[spl_object_hash($object)];
    }
}
